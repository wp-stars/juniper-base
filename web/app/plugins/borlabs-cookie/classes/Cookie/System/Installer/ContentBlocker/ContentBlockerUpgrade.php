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

final class ContentBlockerUpgrade
{
    private ContentBlockerInstall $contentBlockerInstall;

    private WpDb $wpdb;

    public function __construct(ContentBlockerInstall $contentBlockerInstall, WpDb $wpdb)
    {
        $this->contentBlockerInstall = $contentBlockerInstall;
        $this->wpdb = $wpdb;
    }

    public function upgrade(string $prefix = ''): bool
    {
        if (empty($prefix)) {
            $prefix = $this->wpdb->prefix;
        }

        $tableName = $prefix . 'borlabs_cookie_content_blocker';

        return true;
    }
}
