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

namespace Borlabs\Cookie\ApiClient;

use Borlabs\Cookie\Adapter\WpFunction;
use Borlabs\Cookie\ApiClient\Transformer\InstallationPackageTransformer;
use Borlabs\Cookie\ApiClient\Transformer\PackageListTransformer;
use Borlabs\Cookie\Dto\Package\InstallationPackageDto;
use Borlabs\Cookie\DtoList\Package\PackageDtoList;
use Borlabs\Cookie\Exception\ApiClient\PackageApiClientException;
use Borlabs\Cookie\HttpClient\HttpClient;
use Borlabs\Cookie\System\License\License;

final class PackageApiClient
{
    public const API_URL = 'https://service.borlabs.io/api/v1';

    private HttpClient $httpClient;

    private InstallationPackageTransformer $installationPackageTransformer;

    private License $license;

    private PackageListTransformer $packageListTransformer;

    private WpFunction $wpFunction;

    public function __construct(
        HttpClient $httpClient,
        InstallationPackageTransformer $installationPackageTransformer,
        License $license,
        PackageListTransformer $packageListTransformer,
        WpFunction $wpFunction
    ) {
        $this->httpClient = $httpClient;
        $this->installationPackageTransformer = $installationPackageTransformer;
        $this->license = $license;
        $this->packageListTransformer = $packageListTransformer;
        $this->wpFunction = $wpFunction;
    }

    public function requestPackage(string $packageKey): InstallationPackageDto
    {
        $licenseDto = $this->license->get();
        $serviceResponse = $this->httpClient->get(
            self::API_URL . '/package/' . $packageKey,
            (object) [
                'backendUrl' => $this->wpFunction->getSiteUrl(),
                'frontendUrl' => $this->wpFunction->getHomeUrl(),
                'licenseKey' => $licenseDto->licenseKey,
                'version' => BORLABS_COOKIE_VERSION,
            ],
        );

        if ($serviceResponse->success === false) {
            throw new PackageApiClientException($serviceResponse->messageCode);
        }

        return $this->installationPackageTransformer->toDto($serviceResponse->data);
    }

    public function requestPackages(): PackageDtoList
    {
        $serviceResponse = $this->httpClient->get(
            self::API_URL . '/packages',
            (object) [
                'showExperimentalPackages' => defined('BORLABS_COOKIE_DEV_MODE_SHOW_EXPERIMENTAL_PACKAGES') && constant('BORLABS_COOKIE_DEV_MODE_SHOW_EXPERIMENTAL_PACKAGES'),
            ],
        );

        if ($serviceResponse->success === false) {
            throw new PackageApiClientException($serviceResponse->messageCode);
        }

        return $this->packageListTransformer->toDto($serviceResponse->data);
    }
}
