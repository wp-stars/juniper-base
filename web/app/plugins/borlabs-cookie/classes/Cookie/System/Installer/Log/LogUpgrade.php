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

namespace Borlabs\Cookie\System\Installer\Log;

use Borlabs\Cookie\Adapter\WpDb;
use Borlabs\Cookie\Repository\Log\LogRepository;

final class LogUpgrade
{
    private LogInstall $logInstall;

    private WpDb $wpdb;

    public function __construct(LogInstall $logInstall, WpDb $wpdb)
    {
        $this->logInstall = $logInstall;
        $this->wpdb = $wpdb;
    }

    public function upgrade(string $prefix = ''): bool
    {
        if (empty($prefix)) {
            $prefix = $this->wpdb->prefix;
        }

        $tableName = $prefix . LogRepository::TABLE;

        return true;
    }
}
