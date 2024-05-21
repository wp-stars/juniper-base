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

namespace Borlabs\Cookie\Validator\License;

use Borlabs\Cookie\Localization\License\LicenseLocalizationStrings;
use Borlabs\Cookie\System\Message\MessageManager;
use Borlabs\Cookie\Validator\Validator;

final class LicenseValidator
{
    private Validator $validator;

    public function __construct(
        MessageManager $message
    ) {
        $this->validator = new Validator($message, true);
    }

    public function isValid(array $postData): bool
    {
        $localization = LicenseLocalizationStrings::get();
        $this->validator->isNotEmptyString($localization['field']['licenseKey'], $postData['licenseKey'] ?? '');

        return $this->validator->isValid();
    }
}
