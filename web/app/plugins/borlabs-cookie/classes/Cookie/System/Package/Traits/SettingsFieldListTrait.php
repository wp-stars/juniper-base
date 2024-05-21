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

namespace Borlabs\Cookie\System\Package\Traits;

use Borlabs\Cookie\DtoList\System\SettingsFieldDtoList;

// TODO Relocate this trait
trait SettingsFieldListTrait
{
    public function migrateSettingsFieldValues(SettingsFieldDtoList $defaultSettings, SettingsFieldDtoList $customSettings): SettingsFieldDtoList
    {
        foreach ($defaultSettings->list as $key => $defaultSettingsField) {
            foreach ($customSettings->list as $customSettingsField) {
                if ($defaultSettingsField->key === $customSettingsField->key) {
                    $defaultSettings->list[$key]->value = $customSettingsField->value;

                    // Ensure that a required field with a default value does not have an empty value field
                    if ($defaultSettings->list[$key]->isRequired && empty($customSettingsField->value) && !empty($defaultSettings->list[$key]->defaultValue)) {
                        $defaultSettings->list[$key]->value = $defaultSettings->list[$key]->defaultValue;
                    }
                }
            }
        }

        return $defaultSettings;
    }

    public function updateSettingsValuesFromFormFields(SettingsFieldDtoList $settings, array $formFieldValues): SettingsFieldDtoList
    {
        foreach ($settings->list as $key => $settingsField) {
            if (isset($formFieldValues[$settingsField->key])) {
                $settings->list[$key]->value = $formFieldValues[$settingsField->key];
            }
        }

        return $settings;
    }
}
