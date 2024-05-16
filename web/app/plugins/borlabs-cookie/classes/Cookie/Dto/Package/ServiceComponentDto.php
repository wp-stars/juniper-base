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

namespace Borlabs\Cookie\Dto\Package;

use Borlabs\Cookie\Dto\AbstractDto;
use Borlabs\Cookie\DtoList\Package\LanguageSpecificSettingsFieldDtoList;

class ServiceComponentDto extends AbstractDto
{
    public string $key;

    public ?LanguageSpecificSettingsFieldDtoList $languageSpecificSetupSettingsFieldsList = null;

    public string $name;

    public function __construct($key, $name, ?LanguageSpecificSettingsFieldDtoList $languageSpecificListOfSetupSettingsFields = null)
    {
        $this->key = $key;
        $this->name = $name;
        $this->languageSpecificSetupSettingsFieldsList = $languageSpecificListOfSetupSettingsFields;
    }
}
