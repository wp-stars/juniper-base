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
use Borlabs\Cookie\Exception\GenericException;
use Borlabs\Cookie\System\IabTcf\IabTcfService;

final class IabTcfUpdateEvent implements ScheduleEventInterface
{
    public const EVENT_NAME = 'IabTcfUpdate';

    private IabTcfService $iabTcfService;

    private WpFunction $wpFunction;

    public function __construct(
        IabTcfService $iabTcfService,
        WpFunction $wpFunction
    ) {
        $this->iabTcfService = $iabTcfService;
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
                'daily',
                ScheduleEventManager::EVENT_PREFIX . self::EVENT_NAME,
            );
        }
    }

    public function run(): void
    {
        try {
            $this->iabTcfService->updateGlobalVendorListFile();
            $this->iabTcfService->updatePurposeTranslationFiles();
            $this->iabTcfService->updateVendors();
        } catch (GenericException $e) {
        }
    }
}
