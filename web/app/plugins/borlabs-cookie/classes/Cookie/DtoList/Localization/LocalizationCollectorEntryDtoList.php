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

namespace Borlabs\Cookie\DtoList\Localization;

use Borlabs\Cookie\Dto\Localization\LocalizationCollectorEntryDto;
use Borlabs\Cookie\DtoList\AbstractDtoList;

/**
 * @extends AbstractDtoList<LocalizationCollectorEntryDto>
 */
final class LocalizationCollectorEntryDtoList extends AbstractDtoList
{
    public const DTO_CLASS = LocalizationCollectorEntryDto::class;

    public function __construct(
        ?array $localizationCollectorEntryList = null
    ) {
        parent::__construct($localizationCollectorEntryList);
    }

    public static function __listFromJson(array $data)
    {
        $list = [];

        foreach ($data as $key => $value) {
            $entry = new LocalizationCollectorEntryDto(
                $value->localizationClassName,
                $value->text,
                $value->context,
                $value->domain,
                $value->translation,
            );
            $list[$key] = $entry;
        }

        return $list;
    }

    public static function __listToJson(array $data)
    {
        $list = [];

        foreach ($data as $key => $value) {
            $list[$key] = LocalizationCollectorEntryDto::prepareForJson($value);
        }

        return $list;
    }
}
