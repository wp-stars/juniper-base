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

namespace Borlabs\Cookie\System\Installer\Package;

use Borlabs\Cookie\Adapter\WpDb;
use Borlabs\Cookie\Repository\Package\PackageRepository;
use Borlabs\Cookie\Support\Database;

final class PackageUpgrade
{
    private PackageInstall $packageInstall;

    private WpDb $wpdb;

    public function __construct(PackageInstall $packageInstall, WpDb $wpdb)
    {
        $this->packageInstall = $packageInstall;
        $this->wpdb = $wpdb;
    }

    public function upgrade(string $prefix = ''): bool
    {
        if (empty($prefix)) {
            $prefix = $this->wpdb->prefix;
        }

        $tableName = $prefix . PackageRepository::TABLE;

        if (Database::columnExists('is_deprecated', $tableName) === false) {
            $this->wpdb->query('ALTER TABLE `' . $tableName . '` ADD `is_deprecated` tinyint(1) unsigned NOT NULL DEFAULT \'0\' AFTER `installed_at`');
        }

        if (Database::columnExists('borlabs_service_package_successor_key', $tableName) === false) {
            $this->wpdb->query('ALTER TABLE `' . $tableName . '` ADD `borlabs_service_package_successor_key` varchar(64) NOT NULL DEFAULT \'\' AFTER `borlabs_service_package_key`');
        }

        return true;
    }
}
