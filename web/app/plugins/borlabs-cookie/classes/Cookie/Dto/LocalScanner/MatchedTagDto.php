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
use Borlabs\Cookie\Enum\LocalScanner\TagTypeEnum;

class MatchedTagDto extends AbstractDto
{
    public string $phrase;

    public string $tag;

    public TagTypeEnum $type;

    public function __construct(TagTypeEnum $type, string $phrase, string $tag)
    {
        $this->phrase = $phrase;
        $this->tag = $tag;
        $this->type = $type;
    }
}
