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

namespace Borlabs\Cookie\System\Installer\CloudScan;

use Borlabs\Cookie\Adapter\WpDb;
use Borlabs\Cookie\Repository\CloudScan\CloudScanRepository;
use Borlabs\Cookie\Repository\CloudScan\CloudScanSuggestionRepository;
use Borlabs\Cookie\Support\Database;

final class CloudScanSuggestionInstall
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

        $foreignKeyName = Database::generateForeignKeyName(
            $prefix . CloudScanSuggestionRepository::TABLE,
            $prefix . CloudScanRepository::TABLE,
            'cloud_scan_id',
        );

        $createResult = $this->wpdb->query(
            '
            CREATE TABLE IF NOT EXISTS ' . $prefix . CloudScanSuggestionRepository::TABLE . ' (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `cloud_scan_id` int(11) unsigned NOT NULL,
                `borlabs_service_package_key` varchar(64) NULL,
                `pages` text NOT NULL,
                PRIMARY KEY (`id`),
                FOREIGN KEY ' . $foreignKeyName . ' (`cloud_scan_id`)
                    REFERENCES ' . $prefix . CloudScanRepository::TABLE . ' (`id`)
            ) ' . $this->wpdb->get_charset_collate() . ' ENGINE=INNODB
        ',
        );

        return !($createResult === false);
    }
}
