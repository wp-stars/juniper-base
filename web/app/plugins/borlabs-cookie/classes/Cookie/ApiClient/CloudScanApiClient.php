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
use Borlabs\Cookie\ApiClient\Transformer\AttachmentTransformer;
use Borlabs\Cookie\ApiClient\Transformer\CloudScanTransformer;
use Borlabs\Cookie\Dto\Attachment\AttachmentDto;
use Borlabs\Cookie\Dto\CloudScan\ScanResponseDto;
use Borlabs\Cookie\Enum\CloudScan\CloudScanTypeEnum;
use Borlabs\Cookie\Exception\ApiClient\CloudScanApiClientException;
use Borlabs\Cookie\HttpClient\HttpClient;
use Borlabs\Cookie\System\License\License;

class CloudScanApiClient
{
    public const API_URL = 'https://service.borlabs.io/api/v1';

    private AttachmentTransformer $attachmentTransformer;

    private CloudScanTransformer $cloudScanTransformer;

    private HttpClient $httpClient;

    private License $license;

    private WpFunction $wpFunction;

    public function __construct(
        AttachmentTransformer $attachmentTransformer,
        CloudScanTransformer $cloudScanTransformer,
        HttpClient $httpClient,
        License $license,
        WpFunction $wpFunction
    ) {
        $this->attachmentTransformer = $attachmentTransformer;
        $this->cloudScanTransformer = $cloudScanTransformer;
        $this->httpClient = $httpClient;
        $this->license = $license;
        $this->wpFunction = $wpFunction;
    }

    /**
     * @throws \Borlabs\Cookie\Exception\ApiClient\CloudScanApiClientException
     */
    public function getScan(string $id): ScanResponseDto
    {
        $licenseDto = $this->license->get();
        $serviceResponse = $this->httpClient->get(
            self::API_URL . '/scans/' . $id,
            (object) [
                'backendUrl' => $this->wpFunction->getSiteUrl(),
                'frontendUrl' => $this->wpFunction->getHomeUrl(),
                'licenseKey' => $licenseDto->licenseKey ?? '',
                'product' => BORLABS_COOKIE_SLUG,
                'version' => BORLABS_COOKIE_VERSION,
            ],
            !empty($licenseDto->licenseSalt) ? $licenseDto->licenseSalt : '',
        );

        if ($serviceResponse->success === false) {
            throw new CloudScanApiClientException($serviceResponse->messageCode);
        }

        if (!isset($serviceResponse->data)) {
            throw new CloudScanApiClientException('invalidResponse');
        }

        return $this->cloudScanTransformer->toDto($serviceResponse->data);
    }

    /**
     * @throws CloudScanApiClientException
     */
    public function requestScanCreation(
        array $installedPlugins,
        array $installedThemes,
        array $urls,
        CloudScanTypeEnum $scanType,
        ?string $httpAuthUsername = null,
        ?string $httpAuthPassword = null
    ): ScanResponseDto {
        $licenseDto = $this->license->get();

        $serviceResponse = $this->httpClient->post(
            self::API_URL . '/scans',
            (object) [
                'backendUrl' => $this->wpFunction->getSiteUrl(),
                'basicAuth' => isset($httpAuthUsername, $httpAuthPassword) ? [
                    'password' => $httpAuthPassword,
                    'username' => $httpAuthUsername,
                ] : null,
                'frontendUrl' => $this->wpFunction->getHomeUrl(),
                'installedPlugins' => $installedPlugins,
                'installedThemes' => $installedThemes,
                'licenseKey' => $licenseDto->licenseKey ?? '',
                'product' => BORLABS_COOKIE_SLUG,
                'type' => $scanType->value,
                'urls' => $urls,
                'version' => BORLABS_COOKIE_VERSION,
            ],
            !empty($licenseDto->licenseSalt) ? $licenseDto->licenseSalt : '',
        );

        if ($serviceResponse->success === false) {
            throw new CloudScanApiClientException($serviceResponse->messageCode ?? null);
        }

        if (!isset($serviceResponse->data)) {
            throw new CloudScanApiClientException('invalidResponse');
        }

        return $this->cloudScanTransformer->toDto($serviceResponse->data);
    }

    /**
     * @throws \Borlabs\Cookie\Exception\ApiClient\CloudScanApiClientException
     */
    public function requestScannerPageSelectionKeywordsAttachmentData(): AttachmentDto
    {
        $licenseDto = $this->license->get();
        $serviceResponse = $this->httpClient->get(
            self::API_URL . '/attachments/scanner-page-selection-keywords-v1',
            (object) [
                'backendUrl' => $this->wpFunction->getSiteUrl(),
                'frontendUrl' => $this->wpFunction->getHomeUrl(),
                'licenseKey' => $licenseDto->licenseKey ?? '',
                'product' => BORLABS_COOKIE_SLUG,
                'version' => BORLABS_COOKIE_VERSION,
            ],
            !empty($licenseDto->licenseSalt) ? $licenseDto->licenseSalt : '',
        );

        if ($serviceResponse->success !== true) {
            throw new CloudScanApiClientException($serviceResponse->messageCode);
        }

        return $this->attachmentTransformer->toDto($serviceResponse->data);
    }
}
