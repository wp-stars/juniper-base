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
use Borlabs\Cookie\Dto\System\AuditDto;
use Borlabs\Cookie\Repository\ConsentStatistic\ConsentStatisticByDayGroupedByServiceGroupRepository;
use Borlabs\Cookie\Support\Database;

final class ConsentStatisticByDayGroupedByServiceGroupTableMigration
{
    private ConsentStatisticByDayGroupedByServiceGroupInstall $consentStatisticByDayGroupedByServiceGroupInstall;

    private ConsentStatisticByDayGroupedByServiceGroupUpgrade $consentStatisticByDayGroupedByServiceGroupUpgrade;

    private WpDb $wpdb;

    public function __construct(
        ConsentStatisticByDayGroupedByServiceGroupInstall $consentStatisticByDayGroupedByServiceGroupInstall,
        ConsentStatisticByDayGroupedByServiceGroupUpgrade $consentStatisticByDayGroupedByServiceGroupUpgrade,
        WpDb $wpdb
    ) {
        $this->consentStatisticByDayGroupedByServiceGroupInstall = $consentStatisticByDayGroupedByServiceGroupInstall;
        $this->consentStatisticByDayGroupedByServiceGroupUpgrade = $consentStatisticByDayGroupedByServiceGroupUpgrade;
        $this->wpdb = $wpdb;
    }

    /**
     * @param string $prefix optional; Default: `$wpdb->prefix`; Default prefix for the table name
     */
    public function run(string $prefix = ''): AuditDto
    {
        if (empty($prefix)) {
            $prefix = $this->wpdb->prefix;
        }

        if (Database::tableExists($prefix . ConsentStatisticByDayGroupedByServiceGroupRepository::TABLE) === false) {
            $createStatus = $this->consentStatisticByDayGroupedByServiceGroupInstall->createTable($prefix);

            if ($createStatus === false) {
                return new AuditDto(false, $this->wpdb->last_error);
            }
        }

        $upgradeStatus = $this->consentStatisticByDayGroupedByServiceGroupUpgrade->upgrade($prefix);

        return new AuditDto($upgradeStatus);
    }
}
