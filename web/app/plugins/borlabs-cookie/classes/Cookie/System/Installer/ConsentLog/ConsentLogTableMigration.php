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

namespace Borlabs\Cookie\System\Installer\ConsentLog;

use Borlabs\Cookie\Adapter\WpDb;
use Borlabs\Cookie\Dto\System\AuditDto;
use Borlabs\Cookie\Repository\ConsentLog\ConsentLogRepository;
use Borlabs\Cookie\Support\Database;

final class ConsentLogTableMigration
{
    private ConsentLogInstall $consentLogInstall;

    private ConsentLogUpgrade $consentLogUpgrade;

    private WpDb $wpdb;

    public function __construct(
        ConsentLogInstall $consentLogInstall,
        ConsentLogUpgrade $consentLogUpgrade,
        WpDb $wpdb
    ) {
        $this->consentLogInstall = $consentLogInstall;
        $this->consentLogUpgrade = $consentLogUpgrade;
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

        if (Database::tableExists($prefix . ConsentLogRepository::TABLE) === false) {
            $createStatus = $this->consentLogInstall->createTable($prefix);

            if ($createStatus === false) {
                return new AuditDto(false, $this->wpdb->last_error);
            }
        }

        $upgradeStatus = $this->consentLogUpgrade->upgrade($prefix);

        return new AuditDto($upgradeStatus);
    }
}
