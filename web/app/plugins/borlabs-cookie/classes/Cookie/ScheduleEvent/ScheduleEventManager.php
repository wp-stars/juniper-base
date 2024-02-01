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
use Borlabs\Cookie\Container\Container;

final class ScheduleEventManager
{
    public const EVENT_PREFIX = 'BorlabsCookie';

    /**
     * Tolerance for overdue flag in seconds.
     */
    private const CRON_BUFFER = 600;

    private Container $container;

    private array $registry = [
        CheckCloudScanScanStatusEvent::class,
        ConsentStatisticAggregationEvent::class,
        ConsentLogCleanUpEvent::class,
        GeoIpDatabaseUpdateEvent::class,
        IabTcfUpdateEvent::class,
        NewsUpdateEvent::class,
        PackageListUpdateEvent::class,
        ScannerPageSelectionKeywordDatabaseUpdateEvent::class,
        TelemetryDataTransmissionEvent::class,
    ];

    private WpFunction $wpFunction;

    public function __construct(
        Container $container,
        WpFunction $wpFunction
    ) {
        $this->container = $container;
        $this->wpFunction = $wpFunction;
    }

    public function deregister(): void
    {
        foreach ($this->registry as $scheduleEventClass) {
            /** @var ScheduleEventInterface $scheduleEvent */
            $scheduleEvent = $this->container->get($scheduleEventClass);
            $scheduleEvent->deregister();
        }
    }

    public function getStatus(): array
    {
        $eventsStatus = [];

        foreach ($this->registry as $scheduleEventClass) {
            $eventName = self::EVENT_PREFIX . $this->container->get($scheduleEventClass)::EVENT_NAME;
            $eventsStatus[$eventName] = [
                'registered' => false,
                'overdue' => false,
            ];
        }
        $crons = $this->wpFunction->getCronArray();

        foreach ($crons as $time => $hooks) {
            foreach ($hooks as $hook => $hookEvents) {
                if (isset($eventsStatus[$hook])) {
                    $eventsStatus[$hook]['registered'] = true;

                    if ($time - time() < (self::CRON_BUFFER * -1)) {
                        $eventsStatus[$hook]['overdue'] = true;
                    }
                }
            }
        }

        return $eventsStatus;
    }

    public function register(): void
    {
        foreach ($this->registry as $scheduleEventClass) {
            /** @var ScheduleEventInterface $scheduleEvent */
            $scheduleEvent = $this->container->get($scheduleEventClass);
            $scheduleEvent->register();
        }
    }
}
