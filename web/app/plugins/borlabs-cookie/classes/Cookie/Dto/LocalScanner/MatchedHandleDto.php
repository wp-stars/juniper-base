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

namespace Borlabs\Cookie\Dto\LocalScanner;

use Borlabs\Cookie\Dto\AbstractDto;
use Borlabs\Cookie\Enum\LocalScanner\HandleTypeEnum;

class MatchedHandleDto extends AbstractDto
{
    public string $handle;

    public string $phrase;

    public HandleTypeEnum $type;

    public string $url;

    public function __construct(HandleTypeEnum $type, string $handle, string $phrase, string $url)
    {
        $this->handle = $handle;
        $this->phrase = $phrase;
        $this->type = $type;
        $this->url = $url;
    }
}
