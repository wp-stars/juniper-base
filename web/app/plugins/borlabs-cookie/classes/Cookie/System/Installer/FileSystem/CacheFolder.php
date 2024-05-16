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

namespace Borlabs\Cookie\System\Installer\FileSystem;

use Borlabs\Cookie\Dto\System\AuditDto;

final class CacheFolder
{
    /**
     * @var \Borlabs\Cookie\System\FileSystem\CacheFolder
     */
    private $cacheFolder;

    /**
     * @var \Borlabs\Cookie\System\Installer\FileSystem\FileSystem
     */
    private $fileSystem;

    public function __construct(\Borlabs\Cookie\System\FileSystem\CacheFolder $cacheFolder, FileSystem $fileSystem)
    {
        $this->cacheFolder = $cacheFolder;
        $this->fileSystem = $fileSystem;
    }

    public function run(): AuditDto
    {
        return $this->createFolder();
    }

    private function createFolder(): AuditDto
    {
        return $this->fileSystem->createFolder(
            $this->cacheFolder->getPath(),
            $this->cacheFolder->getRootPath(),
            'cache',
            'BORLABS_COOKIE_CACHE_PATH',
        );
    }
}
