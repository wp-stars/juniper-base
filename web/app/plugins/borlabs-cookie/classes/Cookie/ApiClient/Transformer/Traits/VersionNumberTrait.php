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

namespace Borlabs\Cookie\ApiClient\Transformer\Traits;

use Borlabs\Cookie\Dto\Package\VersionNumberDto;

trait VersionNumberTrait
{
    public function transformToVersionNumberDto(string $version): VersionNumberDto
    {
        $matches = [];
        preg_match('/^(\d+)(\.(\d+))?(\.(\d+))?(\.(\d+))?$/', $version, $matches);

        return new VersionNumberDto(
            isset($matches[1]) ? (int) $matches[1] : 0,
            isset($matches[3]) ? (int) $matches[3] : 0,
            isset($matches[5]) ? (int) $matches[5] : 0,
        );
    }

    public function versionNumberToString(VersionNumberDto $versionNumberDto): string
    {
        return $versionNumberDto->major . '.' . $versionNumberDto->minor . '.' . $versionNumberDto->patch;
    }
}
