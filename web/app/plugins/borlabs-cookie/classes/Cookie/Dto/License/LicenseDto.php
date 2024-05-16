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

namespace Borlabs\Cookie\Dto\License;

use Borlabs\Cookie\Dto\AbstractDto;
use Borlabs\Cookie\DtoList\System\KeyValueDtoList;

/**
 * The **LicenseDto** class is used as a typed object that is passed within the system.
 *
 * The object contains license information.
 *
 * @see \Borlabs\Cookie\Dto\License\LicenseDto::$licenseKey
 * @see \Borlabs\Cookie\Dto\License\LicenseDto::$licenseMeta
 * @see \Borlabs\Cookie\Dto\License\LicenseDto::$licenseName
 * @see \Borlabs\Cookie\Dto\License\LicenseDto::$licenseSalt
 * @see \Borlabs\Cookie\Dto\License\LicenseDto::$licenseType
 * @see \Borlabs\Cookie\Dto\License\LicenseDto::$licenseValidUntil
 * @see \Borlabs\Cookie\Dto\License\LicenseDto::$siteSalt
 */
final class LicenseDto extends AbstractDto
{
    public string $licenseKey;

    public KeyValueDtoList $licenseMeta;

    public string $licenseName;

    public string $licenseSalt;

    public string $licenseType;

    /**
     * The date until which this license is eligible to receive updates and access services. Format: Y-m-d; Example: 1970-12-31.
     */
    public string $licenseValidUntil;

    public string $siteSalt;

    /**
     * LicenseDto constructor.
     *
     * @param string          $licenseKey        The license key
     * @param KeyValueDtoList $licenseMeta       Meta information of the license key
     * @param string          $licenseName       The name of the license
     * @param string          $licenseSalt       HMAC salt for this license
     * @param string          $licenseType       The type of the license
     * @param string          $licenseValidUntil The date until which this license is eligible to receive updates and access
     *                                           services. Format: Y-m-d; Example: 1970-12-31
     * @param string          $siteSalt          HMAC salt for this website
     */
    public function __construct(
        string $licenseKey,
        KeyValueDtoList $licenseMeta,
        string $licenseName,
        string $licenseSalt,
        string $licenseType,
        string $licenseValidUntil,
        string $siteSalt
    ) {
        $this->licenseKey = $licenseKey;
        $this->licenseMeta = $licenseMeta;
        $this->licenseName = $licenseName;
        $this->licenseSalt = $licenseSalt;
        $this->licenseType = $licenseType;
        $this->licenseValidUntil = $licenseValidUntil;
        $this->siteSalt = $siteSalt;
    }
}
