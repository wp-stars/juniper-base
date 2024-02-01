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

namespace Borlabs\Cookie\ScheduleEvent;

use Borlabs\Cookie\Adapter\WpFunction;
use Borlabs\Cookie\System\Package\PackageManager;

class PackageListUpdateEvent implements ScheduleEventInterface
{
    public const EVENT_NAME = 'PackageListUpdate';

    private PackageManager $packageManager;

    private WpFunction $wpFunction;

    public function __construct(
        PackageManager $packageManager,
        WpFunction $wpFunction
    ) {
        $this->packageManager = $packageManager;
        $this->wpFunction = $wpFunction;
    }

    public function deregister(): void
    {
        $this->wpFunction->wpClearScheduledHook(ScheduleEventManager::EVENT_PREFIX . self::EVENT_NAME);
    }

    public function register(): void
    {
        $this->wpFunction->addAction(ScheduleEventManager::EVENT_PREFIX . self::EVENT_NAME, [$this, 'run']);

        if (!$this->wpFunction->wpNextScheduled(ScheduleEventManager::EVENT_PREFIX . self::EVENT_NAME)) {
            $this->wpFunction->wpScheduleEvent(
                time(),
                'twicedaily',
                ScheduleEventManager::EVENT_PREFIX . self::EVENT_NAME,
            );
        }
    }

    public function run(): void
    {
        $this->packageManager->updatePackageList();
    }
}
