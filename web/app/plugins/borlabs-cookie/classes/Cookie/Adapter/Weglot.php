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

namespace Borlabs\Cookie\Adapter;

use Borlabs\Cookie\Dto\System\KeyValueDto;
use Borlabs\Cookie\DtoList\System\KeyValueDtoList;
use Borlabs\Cookie\System\Language\MultilanguageInterface;
use Borlabs\Cookie\System\Language\Traits\LanguageTrait;

/**
 * Class Weglot.
 *
 * The **WPML** class is used as a strategy in the **MultilanguageContext** class.
 * The class acts as an adapter between the WPML API and the **Language** class.
 *
 * @see \Borlabs\Cookie\System\Language\Language
 * @see \Borlabs\Cookie\System\Language\MultilanguageContext
 * @see \Borlabs\Cookie\System\Language\MultilanguageInterface
 */
final class Weglot implements MultilanguageInterface
{
    use LanguageTrait;

    /**
     * This method returns the current language code. If no current language code can be detected, the default language
     * code is used.
     * If no language code can be detected, this method returns `null`.
     */
    public function getCurrentLanguageCode(): ?string
    {
        $weglotCurrentLanguage = weglot_get_current_language();

        return is_string($weglotCurrentLanguage) ? $weglotCurrentLanguage : null;
    }

    /**
     * This method returns the default language code, which MUST NOT be the current language code.
     * This method is used when no current language code can be detected or is *all*.
     * If no language code can be detected, this method returns `null`.
     */
    public function getDefaultLanguageCode(): ?string
    {
        $originalLanguage = weglot_get_original_language();

        return is_string($originalLanguage) ? $this->determineLanguageCodeLength($originalLanguage) : null;
    }

    /**
     * This method returns a {@see \Borlabs\Cookie\DtoList\System\KeyValueDtoList} with the available languages. The `name`
     * contains the language code and the `value` contains the name of the language.
     */
    public function getLanguageList(): KeyValueDtoList
    {
        $list = new KeyValueDtoList();
        $originalLanguage = weglot_get_languages_available()[weglot_get_original_language()];
        $list->add(
            new KeyValueDto(
                $this->determineLanguageCodeLength($originalLanguage->getInternalCode()),
                $originalLanguage->getLocalName(),
            ),
        );
        $destinationLanguages = array_column(weglot_get_destination_languages(), 'language_to');

        foreach ($destinationLanguages as $languageCode) {
            $languageData = weglot_get_languages_available()[$languageCode];
            $list->add(
                new KeyValueDto(
                    $this->determineLanguageCodeLength($languageData->getInternalCode()),
                    $languageData->getLocalName(),
                ),
            );
        }

        return $list;
    }

    /**
     * This method returns the name of the passed language code.
     * If no language name can be found, this method returns `null`.
     */
    public function getLanguageName(string $languageCode): ?string
    {
        // TODO: Needs testing in context of `determineLanguageCodeLength()`
        return weglot_get_languages_available()[$languageCode]->getLocalName();
    }

    /**
     * This method returns `true` if the corresponding multi-language plugin is active.
     */
    public function isActive(): bool
    {
        return function_exists('weglot_get_current_language');
    }
}
