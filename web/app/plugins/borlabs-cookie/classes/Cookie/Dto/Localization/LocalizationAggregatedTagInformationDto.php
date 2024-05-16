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

namespace Borlabs\Cookie\Dto\Localization;

use Borlabs\Cookie\Dto\AbstractDto;

class LocalizationAggregatedTagInformationDto extends AbstractDto
{
    /**
     * @var array<string>
     */
    public array $contents;

    public int $counter;

    public string $id;

    public function __construct(
        string $id,
        array $contents = [],
        int $counter = 0
    ) {
        $this->id = $id;
        $this->contents = $contents;
        $this->counter = $counter;
    }
}
