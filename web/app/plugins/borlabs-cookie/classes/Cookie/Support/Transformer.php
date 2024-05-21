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

namespace Borlabs\Cookie\Support;

use Borlabs\Cookie\Dto\System\KeyValueDto;
use Borlabs\Cookie\DtoList\System\KeyValueDtoList;

/**
 * Which transformer fell down the stairs? Stumblebee.
 */
final class Transformer
{
    public static function buildNestedArray(array $input): array
    {
        $output = [];

        foreach ($input as $key => $value) {
            $parts = explode('_', $key);

            if (count($parts) >= 2) {
                $prefix = $parts[0];
                $suffix = implode('_', array_slice($parts, 1));

                if (!isset($output[$prefix])) {
                    $output[$prefix] = [];
                }
                $output[$prefix] = array_merge_recursive($output[$prefix], self::buildNestedArray([$suffix => $value]));
            } else {
                $output[$key] = $value;
            }
        }

        return $output;
    }

    public static function flattenArray(array $array, array &$flattened = []): array
    {
        foreach ($array as $item) {
            if (is_array($item) || is_object($item)) {
                self::flattenArray((array) $item, $flattened);
            } else {
                $flattened[] = $item;
            }
        }

        return $flattened;
    }

    /**
     * TODO.
     */
    public static function naturalSortArrayByObjectProperty(array $objectList, string $propertyName): array
    {
        $sortedArray = $objectList;
        usort($sortedArray, function ($a, $b) use ($propertyName) {
            return strnatcmp($a->{$propertyName}, $b->{$propertyName});
        });

        return $sortedArray;
    }

    public static function toKeyValueDtoList(array $array, ?string $key = null, ?string $value = null): KeyValueDtoList
    {
        return new KeyValueDtoList(array_map(function ($arrayValue, $arrayKey) use ($key, $value) {
            return new KeyValueDto(
                (string) ($key === null ? $arrayKey : (is_object($arrayValue) ? $arrayValue->{$key} : $arrayValue[$key])),
                (string) ($value === null ? $arrayValue : (is_object($arrayValue) ? $arrayValue->{$value} : $arrayValue[$value])),
            );
        }, $array, array_keys($array)));
    }
}
