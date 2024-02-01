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

namespace Borlabs\Cookie\System\WordPressAdminDriver;

use Borlabs\Cookie\Adapter\WpFunction;
use Borlabs\Cookie\Container\Container;
use Borlabs\Cookie\Dto\System\RequestDto;
use Borlabs\Cookie\Localization\WordPressAdminInitLocalizationStrings;
use Borlabs\Cookie\Support\Sanitizer;
use Borlabs\Cookie\System\CompatibilityPatch\CompatibilityPatchManager;
use Borlabs\Cookie\System\Config\GeneralConfig;
use Borlabs\Cookie\System\ContentBlocker\ContentBlockerManager;
use Borlabs\Cookie\System\Language\Language;
use Borlabs\Cookie\System\License\License;
use Borlabs\Cookie\System\License\LicenseStatusMessage;
use Borlabs\Cookie\System\Message\MessageManager;
use Borlabs\Cookie\System\MetaBox\MetaBoxService;
use Borlabs\Cookie\System\Option\Option;
use Borlabs\Cookie\System\Shortcode\ShortcodeHandler;
use Borlabs\Cookie\System\SystemCheck\SystemCheck;

final class WordPressAdminInit
{
    private CompatibilityPatchManager $compatibilityPatchManager;

    private Container $container;

    private ContentBlockerManager $contentBlockerManager;

    private GeneralConfig $generalConfig;

    private Language $language;

    private License $license;

    private LicenseStatusMessage $licenseStatusMessage;

    private MessageManager $messageManager;

    private MetaBoxService $metaBoxService;

    private Option $option;

    private ShortcodeHandler $shortcodeHandler;

    private SystemCheck $systemCheck;

    private WordPressAdminInitLocalizationStrings $wordPressAdminInitLocalizationStrings;

    private WordPressAdminResources $wordPressAdminResources;

    private WordPressPageManager $wordPressPageManager;

    private WpFunction $wpFunction;

    public function __construct(
        CompatibilityPatchManager $compatibilityPatchManager,
        Container $container,
        ContentBlockerManager $contentBlockerManager,
        GeneralConfig $generalConfig,
        Language $language,
        License $license,
        LicenseStatusMessage $licenseStatusMessage,
        MessageManager $messageManager,
        MetaBoxService $metaBoxService,
        Option $option,
        ShortcodeHandler $shortcodeHandler,
        SystemCheck $systemCheck,
        WordPressAdminInitLocalizationStrings $wordPressAdminInitLocalizationStrings,
        WordPressAdminResources $wordPressAdminResources,
        WordPressPageManager $wordPressPageManager,
        WpFunction $wpFunction
    ) {
        $this->compatibilityPatchManager = $compatibilityPatchManager;
        $this->container = $container;
        $this->contentBlockerManager = $contentBlockerManager;
        $this->generalConfig = $generalConfig;
        $this->language = $language;
        $this->license = $license;
        $this->licenseStatusMessage = $licenseStatusMessage;
        $this->messageManager = $messageManager;
        $this->metaBoxService = $metaBoxService;
        $this->option = $option;
        $this->shortcodeHandler = $shortcodeHandler;
        $this->systemCheck = $systemCheck;
        $this->wordPressAdminInitLocalizationStrings = $wordPressAdminInitLocalizationStrings;
        $this->wordPressAdminResources = $wordPressAdminResources;
        $this->wordPressPageManager = $wordPressPageManager;
        $this->wpFunction = $wpFunction;
    }

    /**
     * addActionLinks function.
     *
     * @param mixed $links
     */
    public function addActionLinks($links)
    {
        if (is_array($links)) {
            array_unshift(
                $links,
                '<a href="' . $this->wpFunction->escUrl($this->wpFunction->getAdminUrl('admin.php?page=borlabs-cookie')) . '">'
                . $this->wordPressAdminInitLocalizationStrings::get()['pluginLinks']['dashboard']
                . '</a>',
                '<a href="' . $this->wpFunction->escUrl($this->wpFunction->getAdminUrl('admin.php?page=borlabs-cookie-settings')) . '">'
                . $this->wordPressAdminInitLocalizationStrings::get()['pluginLinks']['settings']
                . '</a>',
                '<a href="' . $this->wpFunction->escUrl($this->wpFunction->getAdminUrl('admin.php?page=borlabs-cookie-license')) . '">'
                . $this->wordPressAdminInitLocalizationStrings::get()['pluginLinks']['license']
                . '</a>',
            );
        }

        return $links;
    }

    /**
     * extendPluginUpdateMessage function.
     *
     * @param mixed $pluginData
     * @param mixed $response
     */
    public function extendPluginUpdateMessage($pluginData, $response)
    {
        // Check license
        $licenseData = $this->license->get();

        if (empty($licenseData)) {
            echo '<br>';
            echo $this->licenseStatusMessage->getMessageEnterLicenseKey();
        } elseif (!empty($licenseData->validUntil) && strtotime($licenseData->validUntil) < strtotime(date('Y-m-d'))) {
            echo '<br>';
            echo $this->licenseStatusMessage->getLicenseMessageKeyExpired();
        }
    }

    /**
     * handleSystemCheck function.
     */
    public function handleSystemCheck()
    {
        $currentScreenData = $this->wpFunction->getCurrentScreen();

        if (is_string($currentScreenData->id) && strpos($currentScreenData->id, 'borlabs-cookie') !== false) {
            // Check if license is expired
            if ($currentScreenData->id !== 'borlabs-cookie_page_borlabs-cookie-license') {
                $this->licenseStatusMessage->handleMessageActivateLicenseKey();
                $this->licenseStatusMessage->handleMessageLicenseExpired();
                $this->licenseStatusMessage->handleMessageLicenseNotValidForCurrentBuild();
            }

            $systemCheckReport = $this->systemCheck->report();

            if (!empty($systemCheckReport)) {
                foreach ($systemCheckReport as $reportType) {
                    foreach ($reportType as $audit) {
                        if ($audit->success === false) {
                            $this->messageManager->error($audit->message);
                        }
                    }
                }
            }

            // Check if Borlabs Cookie is active but only if plugin is unlocked
            if ($this->license->isPluginUnlocked()) {
                if (
                    $this->generalConfig->get()->borlabsCookieStatus === false
                    && empty($_POST['borlabsCookieStatus'])
                ) {
                    $this->messageManager->warning($this->wordPressAdminInitLocalizationStrings::get()['alert']['borlabsCookieNotActive']);
                }
            }
        }
    }

    public function register()
    {
        // Set current request
        $request = new RequestDto(
            Sanitizer::requestData($_POST),
            Sanitizer::requestData($_GET),
            Sanitizer::requestData($_SERVER),
        );
        $this->container->add('currentRequest', $request);

        // Detect language and load text domain.
        $this->language->setInitializationSignal();
        $this->language->init();
        $this->language->loadTextDomain();
        $this->language->handleLanguageSwitchRequest();

        // Load compatibility patches
        $this->compatibilityPatchManager->loadPatches();

        // Add menu
        add_action('admin_menu', [$this->wordPressPageManager, 'register']);

        // Load JavaScript & CSS
        add_action('admin_enqueue_scripts', [$this->wordPressAdminResources, 'register']);

        // System Check
        add_action('current_screen', [$this, 'handleSystemCheck']);

        // Extend update plugin message
        add_action('in_plugin_update_message-' . BORLABS_COOKIE_BASENAME, [$this, 'extendPluginUpdateMessage'], 10, 2);

        // Add action links to plugin page
        add_filter('plugin_action_links_' . BORLABS_COOKIE_BASENAME, [$this, 'addActionLinks']);
        add_filter('script_loader_tag', [$this->wordPressAdminResources, 'transformScriptTagsToModules'], 100, 2);

        // Meta Box
        add_action('wp_loaded', [$this->metaBoxService, 'register']);

        // Register shortcodes
        if ($this->wpFunction->wpDoingAjax() === true) {
            $this->contentBlockerManager->init();
            $this->wpFunction->addShortcode('borlabs-cookie', [$this->shortcodeHandler, 'handle']);
        }
    }
}
