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
use Borlabs\Cookie\Dto\System\AuditDto;
use Borlabs\Cookie\Repository\CloudScan\CloudScanExternalResourceRepository;
use Borlabs\Cookie\Support\Database;

final class CloudScanExternalResourceTableMigration
{
    private CloudScanExternalResourceInstall $cloudScanExternalResourceInstall;

    private CloudScanExternalResourceUpgrade $cloudScanExternalResourceUpgrade;

    private WpDb $wpdb;

    public function __construct(
        WpDb $wpdb,
        CloudScanExternalResourceInstall $cloudScanExternalResourceInstall,
        CloudScanExternalResourceUpgrade $cloudScanExternalResourceUpgrade
    ) {
        $this->wpdb = $wpdb;
        $this->cloudScanExternalResourceInstall = $cloudScanExternalResourceInstall;
        $this->cloudScanExternalResourceUpgrade = $cloudScanExternalResourceUpgrade;
    }

    /**
     * @param string $prefix optional; Default: `$wpdb->prefix`; Default prefix for the table name
     */
    public function run(string $prefix = ''): AuditDto
    {
        if (empty($prefix)) {
            $prefix = $this->wpdb->prefix;
        }

        if (Database::tableExists($prefix . CloudScanExternalResourceRepository::TABLE) === false) {
            $createStatus = $this->cloudScanExternalResourceInstall->createTable($prefix);

            if ($createStatus === false) {
                return new AuditDto(false, $this->wpdb->last_error);
            }
        }

        $upgradeStatus = $this->cloudScanExternalResourceUpgrade->upgrade($prefix);

        return new AuditDto($upgradeStatus);
    }
}
