<?php
/*
 *  Copyright (c) 2024 Borlabs GmbH. All rights reserved.
 *  This file may not be redistributed in whole or significant part.
 *  Content of this file is protected by international copyright laws.
 *
 *  ----------------- Borlabs Cookie IS NOT FREE SOFTWARE -----------------
 *
 *  @copyright Borlabs GmbH, https://borlabs.io
 */

declare(strict_types=1);

namespace Borlabs\Cookie\System\Updater;

use Borlabs\Cookie\Adapter\WpDb;
use Borlabs\Cookie\Adapter\WpFunction;
use Borlabs\Cookie\ApiClient\PluginUpdateApiClient;
use Borlabs\Cookie\Container\Container;
use Borlabs\Cookie\Enum\System\AutomaticUpdateEnum;
use Borlabs\Cookie\Exception\ApiClient\PluginUpdateApiClientException;
use Borlabs\Cookie\Exception\IncompatibleTypeException;
use Borlabs\Cookie\System\Config\PluginConfig;
use Borlabs\Cookie\System\Log\Log;
use Borlabs\Cookie\System\Option\Option;
use stdClass;

class Updater
{
    private Container $container;

    private Log $log;

    private Option $option;

    private PluginConfig $pluginConfig;

    private PluginUpdateApiClient $pluginUpdateApiClient;

    private WpDb $wpdb;

    private WpFunction $wpFunction;

    public function __construct(
        Container $container,
        Log $log,
        Option $option,
        PluginConfig $pluginConfig,
        PluginUpdateApiClient $pluginUpdateApiClient,
        WpDb $wpdb,
        WpFunction $wpFunction
    ) {
        $this->container = $container;
        $this->log = $log;
        $this->option = $option;
        $this->pluginConfig = $pluginConfig;
        $this->pluginUpdateApiClient = $pluginUpdateApiClient;
        $this->wpdb = $wpdb;
        $this->wpFunction = $wpFunction;
    }

    public function fileUpdateComplete($wpUpgraderInstance, $itemUpdateData)
    {
        if (!isset($itemUpdateData['action'], $itemUpdateData['type'], $itemUpdateData['plugins'])) {
            return;
        }

        if ($itemUpdateData['action'] !== 'update' || $itemUpdateData['type'] !== 'plugin') {
            return;
        }

        if (!in_array(BORLABS_COOKIE_BASENAME, $itemUpdateData['plugins'], true)) {
            return;
        }

        $this->processUpdate();
    }

    public function getLatestVersion($transient)
    {
        // Skip the API request if the transient contains the plugin data
        if (isset($transient->response[BORLABS_COOKIE_BASENAME])) {
            return $transient;
        }

        $latestPluginVersion = null;

        try {
            $latestPluginVersion = $this->pluginUpdateApiClient->requestLatestPluginVersion();
        } catch (IncompatibleTypeException $e) {
            $this->log->error($e->getMessage(), $e->getContext());
        } catch (PluginUpdateApiClientException $e) {
            $this->log->critical($e->getMessage());
        }

        if ($latestPluginVersion === null) {
            return $transient;
        }

        // Skip, when the current version is already the latest version
        if (
            is_null($latestPluginVersion->new_version)
            || version_compare(BORLABS_COOKIE_VERSION, $latestPluginVersion->new_version, '>=')
        ) {
            return $transient;
        }

        // $transient can be null if third-party plugins force a plugin refresh an kill the object
        if (!is_object($transient) && !isset($transient->response)) {
            $transient = new stdClass();
            $transient->response = [];
        }

        $latestPluginVersion->disable_autoupdate = $this->shouldAutomaticUpdateDisabled($latestPluginVersion->new_version);
        /*
         * Casting our Dto to a stdClass as required by WordPress.
         * We could also pass our Dto instead, but there might be future versions of WordPress that require a stdClass.
         */
        $transient->response[BORLABS_COOKIE_BASENAME] = (object) (array) $latestPluginVersion;

        return $transient;
    }

    public function getPluginInformation($result, $action, $args)
    {
        if (!isset($action) || $action !== 'plugin_information') {
            return $result;
        }

        if ($args->slug !== BORLABS_COOKIE_SLUG) {
            return $result;
        }

        try {
            $pluginInformation = $this->pluginUpdateApiClient->requestPluginInformation();
        } catch (IncompatibleTypeException $e) {
            $this->log->error($e->getMessage(), $e->getContext());
        } catch (PluginUpdateApiClientException $e) {
            $this->log->critical($e->getMessage());
        }

        if (!isset($pluginInformation)) {
            return $result;
        }

        return (object) (array) $pluginInformation;
    }

    public function handleAutomaticUpdateStatus()
    {
        $autoUpdatePluginsList = $this->option->getThirdPartyOption('auto_update_plugins', []);

        if (!is_array($autoUpdatePluginsList->value)) {
            $autoUpdatePluginsList->value = [];
        }

        // Remove from auto_update_plugins list
        if ($this->pluginConfig->get()->automaticUpdate == AutomaticUpdateEnum::AUTO_UPDATE_NONE()) {
            if (in_array(BORLABS_COOKIE_BASENAME, $autoUpdatePluginsList->value, true)) {
                $index = array_search(BORLABS_COOKIE_BASENAME, $autoUpdatePluginsList->value, true);

                if ($index !== false) {
                    unset($autoUpdatePluginsList->value[$index]);
                    sort($autoUpdatePluginsList->value);
                }
            }
        } else {
            if (!in_array(BORLABS_COOKIE_BASENAME, $autoUpdatePluginsList->value, true)) {
                $autoUpdatePluginsList->value[] = BORLABS_COOKIE_BASENAME;
            }
        }

        // Update WordPress auto_update_plugins option
        $this->option->setThirdPartyOption('auto_update_plugins', $autoUpdatePluginsList->value);
    }

    public function processUpdate()
    {
        $blogId = $this->wpFunction->getCurrentBlogId();
        $prefix = $this->wpdb->prefix;

        $this->container->get('Borlabs\Cookie\System\Installer\MigrationService')->run($prefix);

        if (!$this->wpFunction->isMultisite()) {
            return;
        }

        $sites = $this->wpFunction->getSites();

        if (count($sites) === 0) {
            return;
        }

        foreach ($sites as $site) {
            if ($site->blog_id !== 1) {
                $this->wpFunction->switchToBlog((int) $site->blog_id);
                $prefix = $this->wpdb->prefix;
                // TODO Handle different languages
                $this->container->get('Borlabs\Cookie\System\Installer\MigrationService')->run($prefix);
            }
            $this->wpFunction->switchToBlog($blogId);
        }
    }

    public function register()
    {
        $this->wpFunction->addAction(
            'plugins_api',
            [$this, 'getPluginInformation'],
            9001,
            3,
        );
        $this->wpFunction->addAction(
            'pre_set_site_transient_update_plugins',
            [$this, 'getLatestVersion'],
        );
    }

    public function shouldAutomaticUpdateDisabled(string $newVersion): bool
    {
        if ($this->pluginConfig->get()->automaticUpdate == AutomaticUpdateEnum::AUTO_UPDATE_NONE()) {
            return true;
        }

        if ($this->pluginConfig->get()->automaticUpdate == AutomaticUpdateEnum::AUTO_UPDATE_MINOR()) {
            $currentVersion = BORLABS_COOKIE_VERSION;
            $currentVersionParts = explode('.', $currentVersion);
            $newVersionParts = explode('.', $newVersion);

            if ($currentVersionParts[0] < $newVersionParts[0]) {
                return true;
            }
        }

        return false;
    }
}
