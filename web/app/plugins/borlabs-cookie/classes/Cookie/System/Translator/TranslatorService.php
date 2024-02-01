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

namespace Borlabs\Cookie\System\Translator;

use Borlabs\Cookie\ApiClient\TranslatorApiClient;
use Borlabs\Cookie\Dto\Translator\TargetLanguageDto;
use Borlabs\Cookie\DtoList\System\KeyValueDtoList;
use Borlabs\Cookie\DtoList\Translator\LanguageSpecificKeyValueDtoList;
use Borlabs\Cookie\DtoList\Translator\TargetLanguageEnumDtoList;
use Borlabs\Cookie\Enum\Translator\SourceLanguageEnum;
use Borlabs\Cookie\Enum\Translator\TargetLanguageEnum;
use Borlabs\Cookie\Exception\ApiClient\TranslatorApiClientException;

final class TranslatorService
{
    private TranslatorApiClient $translatorApiClient;

    public function __construct(TranslatorApiClient $translatorApiClient)
    {
        $this->translatorApiClient = $translatorApiClient;
    }

    public function translate(
        string $sourceLanguage,
        array $targetLanguages,
        KeyValueDtoList $sourceTexts
    ): ?LanguageSpecificKeyValueDtoList {
        $sourceLanguage = strtoupper(substr($sourceLanguage, 0, 2));

        if (SourceLanguageEnum::hasValue($sourceLanguage) === false) {
            return null;
        }

        if (count($sourceTexts->list) === 0) {
            return null;
        }

        $list = new TargetLanguageEnumDtoList();

        foreach ($targetLanguages as $languageCode) {
            $languageCode = strtoupper(substr($languageCode, 0, 2));

            if (TargetLanguageEnum::hasKey($languageCode) === true) {
                $list->add(new TargetLanguageDto(TargetLanguageEnum::fromKey($languageCode)));
            }
        }

        $targetLanguages = $list;

        try {
            $translations = $this->translatorApiClient->translate(
                SourceLanguageEnum::fromValue($sourceLanguage),
                $targetLanguages,
                $sourceTexts,
            );
        } catch (TranslatorApiClientException $e) {
            return null;
        }

        return $translations;
    }
}
