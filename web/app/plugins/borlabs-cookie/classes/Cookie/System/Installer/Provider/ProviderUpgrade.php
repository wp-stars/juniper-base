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

namespace Borlabs\Cookie\System\Installer\Provider;

use Borlabs\Cookie\Adapter\WpDb;
use Borlabs\Cookie\Repository\Provider\ProviderRepository;
use Borlabs\Cookie\Support\Database;

final class ProviderUpgrade
{
    private ProviderInstall $providerInstall;

    private WpDb $wpdb;

    public function __construct(ProviderInstall $providerInstall, WpDb $wpdb)
    {
        $this->providerInstall = $providerInstall;
        $this->wpdb = $wpdb;
    }

    public function upgrade(string $prefix = ''): bool
    {
        if (empty($prefix)) {
            $prefix = $this->wpdb->prefix;
        }

        $tableName = $prefix . ProviderRepository::TABLE;

        if (Database::columnExists('borlabs_service_package_key', $tableName) === false) {
            $this->wpdb->query('ALTER TABLE `' . $tableName . '` ADD `borlabs_service_package_key` varchar(64) NULL AFTER `id`');
            $this->wpdb->query('ALTER TABLE `' . $tableName . '` ADD INDEX `borlabs_service_package_key` (`borlabs_service_package_key`)');
        }

        return true;
    }
}
