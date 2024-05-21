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

namespace Borlabs\Cookie\Dto\CloudScan;

use Borlabs\Cookie\Dto\AbstractDto;

class ExternalResourceExampleDto extends AbstractDto
{
    public string $pageId;

    public string $pageUrl;

    public string $resourceUrl;

    public function __construct(string $pageId, string $pageUrl, string $resourceUrl)
    {
        $this->pageId = $pageId;
        $this->pageUrl = $pageUrl;
        $this->resourceUrl = $resourceUrl;
    }
}
