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

namespace Borlabs\Cookie\System\Installer\Migrations;

use Borlabs\Cookie\Adapter\WpFunction;
use Borlabs\Cookie\Container\Container;
use Borlabs\Cookie\System\Language\Language;
use Borlabs\Cookie\System\Log\Log;
use Borlabs\Cookie\System\Script\ScriptConfigBuilder;
use Borlabs\Cookie\System\Style\StyleBuilder;
use Borlabs\Cookie\System\ThirdPartyCacheClearer\ThirdPartyCacheClearerManager;

class Migration_3_0_0_13
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function run()
    {
        $language = $this->container->get(Language::class);
        $wpFunction = $this->container->get(WpFunction::class);
        $status = $this->container->get(ScriptConfigBuilder::class)->updateJavaScriptConfigFileAndIncrementConfigVersion(
            $language->getSelectedLanguageCode(),
        );

        $this->container->get(Log::class)->info(
            sprintf(
                'JavaScript config file updated: %s',
                $status ? 'Yes' : 'No',
            ),
            [
                'language' => $language->getSelectedLanguageCode(),
            ],
        );

        $status = $this->container->get(StyleBuilder::class)->updateCssFileAndIncrementStyleVersion(
            $wpFunction->getCurrentBlogId(),
            $language->getSelectedLanguageCode(),
        );

        $this->container->get(Log::class)->info(
            sprintf(
                'CSS file updated: %s',
                $status ? 'Yes' : 'No',
            ),
            [
                'blogId' => $wpFunction->getCurrentBlogId(),
                'language' => $language->getSelectedLanguageCode(),
            ],
        );

        $this->container->get(ThirdPartyCacheClearerManager::class)->clearCache();

        // Special case for WP Rocket, as only rocket_clean_minify() clears the asset cache.
        if (function_exists('rocket_clean_minify')) {
            rocket_clean_minify();

            $this->container->get(Log::class)->info('WP Rocket asset cache cleared.');
        }
    }
}
