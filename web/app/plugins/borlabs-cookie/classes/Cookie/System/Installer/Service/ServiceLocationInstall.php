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
use Borlabs\Cookie\Repository\Service\ServiceLocationRepository;
use Borlabs\Cookie\Repository\Service\ServiceRepository;
use Borlabs\Cookie\Support\Database;

final class ServiceLocationInstall
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

        $foreignKeyName = Database::generateForeignKeyName(
            $prefix . ServiceLocationRepository::TABLE,
            $prefix . ServiceRepository::TABLE,
            'service_id',
        );

        $createResult = $this->wpdb->query(
            '
            CREATE TABLE IF NOT EXISTS ' . $prefix . ServiceLocationRepository::TABLE . ' (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `service_id` int(11) unsigned NOT NULL,
                `hostname` varchar(255) NOT NULL,
                `path` varchar(255) NOT NULL DEFAULT \'/\',
                PRIMARY KEY (`id`),
                KEY `service_id` (`service_id`),
                FOREIGN KEY ' . $foreignKeyName . ' (`service_id`)
                    REFERENCES ' . $prefix . ServiceRepository::TABLE . ' (`id`)
            ) ' . $this->wpdb->get_charset_collate() . ' ENGINE=INNODB
        ',
        );

        return !($createResult === false);
    }
}
