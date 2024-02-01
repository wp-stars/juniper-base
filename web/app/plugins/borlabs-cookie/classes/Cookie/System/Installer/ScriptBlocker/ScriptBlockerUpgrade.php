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

namespace Borlabs\Cookie\System\Installer\ScriptBlocker;

use Borlabs\Cookie\Adapter\WpDb;
use Borlabs\Cookie\Repository\ScriptBlocker\ScriptBlockerRepository;

final class ScriptBlockerUpgrade
{
    private ScriptBlockerInstall $scriptBlockerInstall;

    private WpDb $wpdb;

    public function __construct(ScriptBlockerInstall $scriptBlockerInstall, WpDb $wpdb)
    {
        $this->scriptBlockerInstall = $scriptBlockerInstall;
        $this->wpdb = $wpdb;
    }

    public function upgrade(string $prefix = ''): bool
    {
        if (empty($prefix)) {
            $prefix = $this->wpdb->prefix;
        }

        $tableName = $prefix . ScriptBlockerRepository::TABLE;

        return true;
    }
}
