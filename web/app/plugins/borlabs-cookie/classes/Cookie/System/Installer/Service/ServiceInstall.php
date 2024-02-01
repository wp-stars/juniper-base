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

namespace Borlabs\Cookie\System\Installer\Service;

use Borlabs\Cookie\Adapter\WpDb;
use Borlabs\Cookie\Repository\Provider\ProviderRepository;
use Borlabs\Cookie\Repository\Service\ServiceRepository;
use Borlabs\Cookie\Repository\ServiceGroup\ServiceGroupRepository;
use Borlabs\Cookie\Support\Database;

final class ServiceInstall
{
    private WpDb $wpdb;

    public function __construct(
        WpDb $wpdb
    ) {
        $this->wpdb = $wpdb;
    }

    public function createTable(string $prefix = ''): bool
    {
        if (empty($prefix)) {
            $prefix = $this->wpdb->prefix;
        }

        $providerForeignKeyName = Database::generateForeignKeyName(
            $prefix . ServiceRepository::TABLE,
            $prefix . ProviderRepository::TABLE,
            'provider_id',
        );
        $serviceGroupForeignKeyName = Database::generateForeignKeyName(
            $prefix . ServiceRepository::TABLE,
            $prefix . ServiceGroupRepository::TABLE,
            'service_group_id',
        );

        $createResult = $this->wpdb->query(
            '
            CREATE TABLE IF NOT EXISTS ' . $prefix . ServiceRepository::TABLE . ' (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `provider_id` int(11) unsigned NOT NULL,
                `service_group_id` int(11) unsigned NOT NULL,
                `borlabs_service_package_key` varchar(64) NULL,
                `key` varchar(64) NOT NULL,
                `description` text NOT NULL DEFAULT \'\',
                `fallback_code` text NOT NULL DEFAULT \'\',
                `language` varchar(16) NOT NULL,
                `name` varchar(255) NOT NULL,
                `opt_in_code` text NOT NULL DEFAULT \'\',
                `opt_out_code` text NOT NULL DEFAULT \'\',
                `position` int(11) unsigned NOT NULL DEFAULT \'1\',
                `settings_fields` text NULL,
                `status` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
                `undeletable` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
                PRIMARY KEY (`id`),
                UNIQUE KEY `key_language` (`key`, `language`),
                KEY `service_group_id` (`service_group_id`),
                FOREIGN KEY ' . $providerForeignKeyName . ' (`provider_id`)
                    REFERENCES ' . $prefix . ProviderRepository::TABLE . ' (`id`),
                FOREIGN KEY ' . $serviceGroupForeignKeyName . ' (`service_group_id`)
                    REFERENCES ' . $prefix . ServiceGroupRepository::TABLE . ' (`id`)
            ) ' . $this->wpdb->get_charset_collate() . ' ENGINE=INNODB
        ',
        );

        return !($createResult === false);
    }
}
