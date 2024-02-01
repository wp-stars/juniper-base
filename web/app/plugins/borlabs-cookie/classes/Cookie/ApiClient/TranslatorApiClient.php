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

use Borlabs\Cookie\ApiClient\Transformer\LanguageSpecificKeyValueListTransformer;
use Borlabs\Cookie\DtoList\System\KeyValueDtoList;
use Borlabs\Cookie\DtoList\Translator\LanguageSpecificKeyValueDtoList;
use Borlabs\Cookie\DtoList\Translator\TargetLanguageEnumDtoList;
use Borlabs\Cookie\Enum\Translator\SourceLanguageEnum;
use Borlabs\Cookie\Exception\ApiClient\TranslatorApiClientException;
use Borlabs\Cookie\HttpClient\HttpClientInterface;
use Borlabs\Cookie\System\License\License;

final class TranslatorApiClient
{
    public const API_URL = 'https://service.borlabs.io/api/v1';

    private HttpClientInterface $httpClient;

    private LanguageSpecificKeyValueListTransformer $languageSpecificKeyValueListTransformer;

    private License $license;

    public function __construct(
        HttpClientInterface $httpClient,
        LanguageSpecificKeyValueListTransformer $languageSpecificKeyValueListTransformer,
        License $license
    ) {
        $this->httpClient = $httpClient;
        $this->languageSpecificKeyValueListTransformer = $languageSpecificKeyValueListTransformer;
        $this->license = $license;
    }

    /**
     * @throws \Borlabs\Cookie\Exception\ApiClient\TranslatorApiClientException
     */
    public function translate(
        SourceLanguageEnum $sourceLanguage,
        TargetLanguageEnumDtoList $targetLanguages,
        KeyValueDtoList $sourceTexts
    ): LanguageSpecificKeyValueDtoList {
        $licenseData = $this->license->get();
        $serviceResponse = $this->httpClient->post(
            self::API_URL . '/translate',
            (object) [
                'licenseKey' => !empty($licenseData->licenseKey) ? $licenseData->licenseKey : '',
                'sourceLanguage' => $sourceLanguage->value,
                'sourceTexts' => array_column($sourceTexts->list, 'value'),
                'targetLanguages' => array_column(array_column($targetLanguages->list, 'targetLanguageEnum'), 'value'),
                'version' => BORLABS_COOKIE_VERSION,
            ],
        );

        if ($serviceResponse->success === false) {
            throw new TranslatorApiClientException($serviceResponse->messageCode);
        }

        return $this->languageSpecificKeyValueListTransformer->toDto($serviceResponse->data, $sourceTexts);
    }
}
