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

namespace Borlabs\Cookie\System\ThirdPartyCacheClearer;

use Borlabs\Cookie\Adapter\WpFunction;
use Borlabs\Cookie\Container\Container;
use Borlabs\Cookie\System\Config\GeneralConfig;

final class ThirdPartyCacheClearerManager
{
    private Container $container;

    private GeneralConfig $generalConfig;

    private array $registry = [
        AutoptimizeCacheClearer::class,
        BorlabsCacheCacheClearer::class,
        LiteSpeedCacheCacheClearer::class,
        SiteGroundSpeedOptimizerCacheClearer::class,
        ThemifyCacheClearer::class,
        W3TotalCacheCacheClearer::class,
        WpFastestCacheCacheClearer::class,
        WpOptimizeCacheClearer::class,
        WpRocketCacheClearer::class,
        WpSuperCacheCacheClearer::class,
    ];

    private WpFunction $wpFunction;

    public function __construct(
        Container $container,
        GeneralConfig $generalConfig,
        WpFunction $wpFunction
    ) {
        $this->container = $container;
        $this->generalConfig = $generalConfig;
        $this->wpFunction = $wpFunction;
    }

    public function clearCache()
    {
        $clearThirdPartyCacheStatus = (bool) $this->wpFunction->applyFilter(
            'borlabsCookie/thirdPartyCacheClearer/shouldClearCache',
            $this->generalConfig->get()->clearThirdPartyCache,
        );

        if (!$clearThirdPartyCacheStatus) {
            return;
        }

        foreach ($this->registry as $cacheClearerClass) {
            /** @var \Borlabs\Cookie\System\ThirdPartyCacheClearer\ThirdPartyCacheClearerInterface $cacheClearer */
            $cacheClearer = $this->container->get($cacheClearerClass);
            $cacheClearer->clearCache();
        }
    }
}
