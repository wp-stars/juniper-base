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
use Borlabs\Cookie\Dto\Telemetry\TelemetryDto;
use Borlabs\Cookie\HttpClient\HttpClient;
use Borlabs\Cookie\System\License\License;

final class TelemetryApiClient
{
    public const API_URL = 'https://service.borlabs.io/api/v1';

    private HttpClient $httpClient;

    private License $license;

    private WpFunction $wpFunction;

    public function __construct(
        HttpClient $httpClient,
        License $license,
        WpFunction $wpFunction
    ) {
        $this->httpClient = $httpClient;
        $this->license = $license;
        $this->wpFunction = $wpFunction;
    }

    public function sendTelemetryData(TelemetryDto $telemetryData): void
    {
        $licenseDto = $this->license->get();

        $this->httpClient->post(
            self::API_URL . '/telemetry',
            (object) [
                'backendUrl' => $this->wpFunction->getSiteUrl(),
                'frontendUrl' => $this->wpFunction->getHomeUrl(),
                'licenseKey' => $licenseDto->licenseKey,
                'telemetryData' => $telemetryData,
                'version' => BORLABS_COOKIE_VERSION,
            ],
        );
    }
}
