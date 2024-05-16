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

namespace Borlabs\Cookie\System\Template;

use Borlabs\Cookie\Dto\System\KeyValueDto;
use Borlabs\Cookie\Dto\System\SettingsFieldDto;
use Borlabs\Cookie\DtoList\System\KeyValueDtoList;
use Borlabs\Cookie\DtoList\System\SettingsFieldDtoList;
use Borlabs\Cookie\Enum\System\SettingsFieldDataTypeEnum;
use Borlabs\Cookie\Enum\System\SettingsFieldVisibilityEnum;
use Borlabs\Cookie\Repository\ServiceGroup\ServiceGroupRepository;
use Borlabs\Cookie\Support\Searcher;

final class FieldGenerator
{
    private ServiceGroupRepository $serviceGroupRepository;

    private Template $template;

    public function __construct(ServiceGroupRepository $serviceGroupRepository, Template $template)
    {
        $this->serviceGroupRepository = $serviceGroupRepository;
        $this->template = $template;
    }

    public function makeField(SettingsFieldDto $settingsFieldDto, ?string $id = null, ?string $name = null): string
    {
        if ($settingsFieldDto->dataType->is(SettingsFieldDataTypeEnum::BOOLEAN())) {
            return $this->makeBooleanField($settingsFieldDto, $id, $name);
        }

        if ($settingsFieldDto->dataType->is(SettingsFieldDataTypeEnum::INFORMATION())) {
            return $this->makeInformationField($settingsFieldDto, $id, $name);
        }

        if ($settingsFieldDto->dataType->is(SettingsFieldDataTypeEnum::SELECT())) {
            return $this->makeSelectField($settingsFieldDto, $id, $name);
        }

        if ($settingsFieldDto->dataType->is(SettingsFieldDataTypeEnum::SYSTEM_SERVICE_GROUP())) {
            return $this->makeSystemServiceGroupField($settingsFieldDto, $id, $name);
        }

        if ($settingsFieldDto->dataType->is(SettingsFieldDataTypeEnum::TEXT())) {
            return $this->makeTextField($settingsFieldDto, $id, $name);
        }

        // TODO
        return 'Error';
    }

    public function makeFields(
        SettingsFieldDtoList $settingsFieldDtoList,
        ?SettingsFieldVisibilityEnum $excludedVisibility = null,
        ?string $idPrefix = null,
        ?string $namePrefix = null
    ): string {
        $preSortedList = [];

        foreach ($settingsFieldDtoList->list as $settingsField) {
            if ($excludedVisibility !== null && $excludedVisibility == $settingsField->visibility) {
                continue;
            }

            $preSortedList[$settingsField->position][] = $settingsField;
        }
        ksort($preSortedList);

        $sortedList = [];

        foreach ($preSortedList as $fieldsWithSamePosition) {
            foreach ($fieldsWithSamePosition as $field) {
                $sortedList[] = $field;
            }
        }

        $html = [];

        foreach ($sortedList as $settingsField) {
            $id = $idPrefix ? $idPrefix . '-' : '';
            $id .= $settingsField->formFieldCollectionName ? $settingsField->formFieldCollectionName . '-' . $settingsField->key : $settingsField->key;
            $name = $namePrefix ?? '';

            if ($namePrefix !== '' && $settingsField->formFieldCollectionName) {
                $name .= '[' . $settingsField->formFieldCollectionName . '][' . $settingsField->key . ']';
            } elseif ($settingsField->formFieldCollectionName) {
                $name .= $settingsField->formFieldCollectionName . '[' . $settingsField->key . ']';
            } elseif ($namePrefix !== '') {
                $name .= '[' . $settingsField->key . ']';
            } else {
                $name .= $settingsField->key;
            }

            $html[] = $this->makeField($settingsField, $id, $name);
        }

        return implode('', $html);
    }

    private function makeBooleanField(SettingsFieldDto $settingsFieldDto, ?string $id = null, ?string $name = null)
    {
        return $this->template->getEngine()->render(
            'system/boolean.html.twig',
            array_merge(
                (array) $settingsFieldDto,
                [
                    'id' => $id ?? ($settingsFieldDto->formFieldCollectionName ? $settingsFieldDto->formFieldCollectionName . '-' . $settingsFieldDto->key : $settingsFieldDto->key),
                    'name' => $name ?? ($settingsFieldDto->formFieldCollectionName ? $settingsFieldDto->formFieldCollectionName . '[' . $settingsFieldDto->key . ']' : $settingsFieldDto->key),
                ],
            ),
        );
    }

    private function makeInformationField(SettingsFieldDto $settingsFieldDto, ?string $id = null, ?string $name = null)
    {
        return $this->template->getEngine()->render(
            'system/information.html.twig',
            array_merge(
                (array) $settingsFieldDto,
                [
                    'id' => $id ?? ($settingsFieldDto->formFieldCollectionName ? $settingsFieldDto->formFieldCollectionName . '-' . $settingsFieldDto->key : $settingsFieldDto->key),
                    'name' => $name ?? ($settingsFieldDto->formFieldCollectionName ? $settingsFieldDto->formFieldCollectionName . '[' . $settingsFieldDto->key . ']' : $settingsFieldDto->key),
                ],
            ),
        );
    }

    private function makeSelectField(SettingsFieldDto $settingsFieldDto, ?string $id = null, ?string $name = null)
    {
        if (isset($settingsFieldDto->translation->values)) {
            foreach ($settingsFieldDto->values->list as $key => $keyValueDto) {
                $translatedValue = Searcher::findObject($settingsFieldDto->translation->values->list, 'key', $keyValueDto->key);
                $settingsFieldDto->values->list[$key]->value = $translatedValue->value ?? $keyValueDto->value; // If no translation exists, the default value is used.
            }

            // Order by value naturally
            usort($settingsFieldDto->values->list, function (KeyValueDto $a, KeyValueDto $b) {
                return strnatcmp($a->value, $b->value);
            });
        }

        return $this->template->getEngine()->render(
            'system/select.html.twig',
            array_merge(
                (array) $settingsFieldDto,
                [
                    'id' => $id ?? ($settingsFieldDto->formFieldCollectionName ? $settingsFieldDto->formFieldCollectionName . '-' . $settingsFieldDto->key : $settingsFieldDto->key),
                    'name' => $name ?? ($settingsFieldDto->formFieldCollectionName ? $settingsFieldDto->formFieldCollectionName . '[' . $settingsFieldDto->key . ']' : $settingsFieldDto->key),
                ],
            ),
        );
    }

    private function makeSystemServiceGroupField(SettingsFieldDto $settingsFieldDto, ?string $id = null, ?string $name = null)
    {
        $serviceGroupList = $this->serviceGroupRepository->getAllOfSelectedLanguage();
        $values = new KeyValueDtoList();

        foreach ($serviceGroupList as $serviceGroup) {
            $values->add(new KeyValueDto($serviceGroup->key, $serviceGroup->name));
        }

        $settingsFieldDto->values = $values;

        return $this->template->getEngine()->render(
            'system/system-service-group.html.twig',
            array_merge(
                (array) $settingsFieldDto,
                [
                    'id' => $id ?? ($settingsFieldDto->formFieldCollectionName ? $settingsFieldDto->formFieldCollectionName . '-' . $settingsFieldDto->key : $settingsFieldDto->key),
                    'name' => $name ?? ($settingsFieldDto->formFieldCollectionName ? $settingsFieldDto->formFieldCollectionName . '[' . $settingsFieldDto->key . ']' : $settingsFieldDto->key),
                ],
            ),
        );
    }

    private function makeTextField(SettingsFieldDto $settingsFieldDto, ?string $id = null, ?string $name = null)
    {
        return $this->template->getEngine()->render(
            'system/text.html.twig',
            array_merge(
                (array) $settingsFieldDto,
                [
                    'id' => $id ?? ($settingsFieldDto->formFieldCollectionName ? $settingsFieldDto->formFieldCollectionName . '-' . $settingsFieldDto->key : $settingsFieldDto->key),
                    'name' => $name ?? ($settingsFieldDto->formFieldCollectionName ? $settingsFieldDto->formFieldCollectionName . '[' . $settingsFieldDto->key . ']' : $settingsFieldDto->key),
                ],
            ),
        );
    }
}
