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

namespace Borlabs\Cookie\System\Installer\ConsentStatistic;

use Borlabs\Cookie\Adapter\WpDb;
use Borlabs\Cookie\Repository\ConsentStatistic\ConsentStatisticByHourGroupedByServiceGroupRepository;

final class ConsentStatisticByHourGroupedByServiceGroupInstall
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

        $createResult = $this->wpdb->query(
            '
            CREATE TABLE IF NOT EXISTS ' . $prefix . ConsentStatisticByHourGroupedByServiceGroupRepository::TABLE . ' (
                `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
                `service_group_key` varchar(64) NOT NULL,
                `cookie_version` int(11) unsigned NOT NULL,
                `date` date NOT NULL,
                `hour` tinyint(1) unsigned NOT NULL,
                `is_anonymous` tinyint(1) unsigned NOT NULL,
                `count` int(11) unsigned NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `service_group_key_hour_grouped_by_service_group` (`service_group_key`, `cookie_version`, `date`, `hour`, `is_anonymous`)
            ) ' . $this->wpdb->get_charset_collate() . ' ENGINE=INNODB
        ',
        );

        return !($createResult === false);
    }
}
