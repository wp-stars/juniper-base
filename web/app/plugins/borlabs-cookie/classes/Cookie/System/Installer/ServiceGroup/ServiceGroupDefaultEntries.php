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

namespace Borlabs\Cookie\System\Installer\ServiceGroup;

use Borlabs\Cookie\System\Installer\DefaultEntriesManager;
use Borlabs\Cookie\System\Installer\ServiceGroup\Entry\EssentialEntry;
use Borlabs\Cookie\System\Installer\ServiceGroup\Entry\ExternalMediaEntry;
use Borlabs\Cookie\System\Installer\ServiceGroup\Entry\MarketingEntry;
use Borlabs\Cookie\System\Installer\ServiceGroup\Entry\StatisticsEntry;
use Borlabs\Cookie\System\Installer\ServiceGroup\Entry\UnclassifiedEntry;

final class ServiceGroupDefaultEntries extends DefaultEntriesManager
{
    public array $registry = [
        EssentialEntry::class,
        ExternalMediaEntry::class,
        MarketingEntry::class,
        StatisticsEntry::class,
        UnclassifiedEntry::class,
    ];
}
