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

namespace Borlabs\Cookie\System\Installer\ContentBlocker;

use Borlabs\Cookie\Adapter\WpDb;
use Borlabs\Cookie\Repository\ContentBlocker\ContentBlockerRepository;
use Borlabs\Cookie\Repository\Provider\ProviderRepository;
use Borlabs\Cookie\Repository\Service\ServiceRepository;
use Borlabs\Cookie\Support\Database;

final class ContentBlockerInstall
{
    private WpDb $wpdb;

    public function __construct(WpDb $wpdb)
    {
        $this->wpdb = $wpdb;
    }

    public function createTable(string $prefix = ''): bool
    {
        if (empty($prefix)) {
            $prefix = $this->wpdb->prefix;
        }

        $providerForeignKeyName = Database::generateForeignKeyName(
            $prefix . ContentBlockerRepository::TABLE,
            $prefix . ProviderRepository::TABLE,
            'provider_id',
        );
        $serviceForeignKeyName = Database::generateForeignKeyName(
            $prefix . ContentBlockerRepository::TABLE,
            $prefix . ServiceRepository::TABLE,
            'service_id',
        );

        $createResult = $this->wpdb->query(
            '
            CREATE TABLE IF NOT EXISTS ' . $prefix . ContentBlockerRepository::TABLE . ' (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `provider_id` int(11) unsigned NOT NULL,
                `service_id` int(11) unsigned NULL,
                `borlabs_service_package_key` varchar(64) NULL,
                `key` varchar(64) NOT NULL,
                `description` text NOT NULL DEFAULT \'\',
                `javascript_global` text NOT NULL DEFAULT \'\',
                `javascript_initialization` text NOT NULL DEFAULT \'\',
                `language` varchar(16) NOT NULL,
                `language_strings` text NULL,
                `name` varchar(255) NOT NULL,
                `preview_css` text NOT NULL DEFAULT \'\',
                `preview_html` text NOT NULL,
                `preview_image` text NOT NULL DEFAULT \'\',
                `settings_fields` text NULL,
                `status` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
                `undeletable` tinyint(1) unsigned NOT NULL DEFAULT \'0\',
                PRIMARY KEY (`id`),
                INDEX (`provider_id`),
                INDEX (`borlabs_service_package_key`),
                UNIQUE KEY `key_language` (`key`, `language`),
                FOREIGN KEY ' . $serviceForeignKeyName . ' (`service_id`)
                    REFERENCES ' . $prefix . ServiceRepository::TABLE . ' (`id`),
                FOREIGN KEY ' . $providerForeignKeyName . ' (`provider_id`)
                    REFERENCES ' . $prefix . ProviderRepository::TABLE . ' (`id`)
            ) ' . $this->wpdb->get_charset_collate() . ' ENGINE=INNODB
        ',
        );

        return !($createResult === false);
    }
}
