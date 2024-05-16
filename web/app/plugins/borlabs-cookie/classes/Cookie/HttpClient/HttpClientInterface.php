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

namespace Borlabs\Cookie\HttpClient;

use Borlabs\Cookie\Dto\ApiClient\ServiceResponseDto;

interface HttpClientInterface
{
    public function get(
        string $url,
        object $data,
        ?string $salt = null
    ): ServiceResponseDto;

    public function post(
        string $url,
        object $data,
        ?string $salt = null
    ): ServiceResponseDto;
}
