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

namespace Borlabs\Cookie\DtoList\Telemetry;

use Borlabs\Cookie\Dto\Telemetry\ServiceDto;
use Borlabs\Cookie\DtoList\AbstractDtoList;

/**
 * @extends AbstractDtoList<ServiceDto>
 */
final class ServiceDtoList extends AbstractDtoList
{
    public const DTO_CLASS = ServiceDto::class;

    public function __construct(
        ?array $serviceList = null
    ) {
        parent::__construct($serviceList);
    }

    public static function __listFromJson(array $data)
    {
        $list = [];

        foreach ($data as $key => $serviceData) {
            $service = new ServiceDto();
            $service->key = $serviceData->key;
            $service->name = $serviceData->name;
            $service->providerName = $serviceData->providerName;
            $service->providerPrivacyUrl = $serviceData->providerPrivacyUrl;

            $list[$key] = $service;
        }

        return $list;
    }

    public static function __listToJson(array $data)
    {
        $list = [];

        foreach ($data as $key => $services) {
            $list[$key] = ServiceDto::prepareForJson($services);
        }

        return $list;
    }
}
