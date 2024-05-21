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

use Borlabs\Cookie\ApiClient\Transformer\AttachmentTransformer;
use Borlabs\Cookie\Dto\Attachment\AttachmentDto;
use Borlabs\Cookie\Exception\ApiClient\GeoIpApiClientException;
use Borlabs\Cookie\HttpClient\HttpClient;
use Borlabs\Cookie\System\License\License;

final class GeoIpApiClient
{
    public const API_URL = 'https://service.borlabs.io/api/v1';

    private AttachmentTransformer $attachmentTransformer;

    private HttpClient $httpClient;

    private License $license;

    public function __construct(
        AttachmentTransformer $attachmentTransformer,
        HttpClient $httpClient,
        License $license
    ) {
        $this->attachmentTransformer = $attachmentTransformer;
        $this->httpClient = $httpClient;
        $this->license = $license;
    }

    /**
     * @throws \Borlabs\Cookie\Exception\ApiClient\GeoIpApiClientException
     */
    public function requestGeoIpDatabaseAttachmentData(): AttachmentDto
    {
        $licenseData = $this->license->get();
        $serviceResponse = $this->httpClient->get(
            self::API_URL . '/attachments/geo-ip-country',
            (object) [
                'licenseKey' => $licenseData->licenseKey,
                'version' => BORLABS_COOKIE_VERSION,
            ],
            $licenseData->licenseSalt,
        );

        if ($serviceResponse->success !== true) {
            throw new GeoIpApiClientException($serviceResponse->messageCode);
        }

        return $this->attachmentTransformer->toDto($serviceResponse->data);
    }
}
