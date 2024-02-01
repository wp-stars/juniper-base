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

namespace Borlabs\Cookie\System\Language;

use Borlabs\Cookie\DtoList\System\KeyValueDtoList;

/**
 * Class MultilanguageContext.
 *
 * The **MultilanguageContext** class forwards calls to the chosen multi-language strategy.
 *
 * @see \Borlabs\Cookie\System\Language\MultilanguageContext::getCurrentLanguageCode()
 * @see \Borlabs\Cookie\System\Language\MultilanguageContext::getLanguageList()
 * @see \Borlabs\Cookie\System\Language\MultilanguageContext::getLanguageName()
 */
final class MultilanguageContext
{
    /**
     * The property contains the instance of the selected strategy.
     *
     * @var \Borlabs\Cookie\System\Language\MultilanguageInterface
     */
    private $strategy;

    public function __construct(MultilanguageInterface $strategy)
    {
        $this->strategy = $strategy;
    }

    /**
     * This method returns the current language code. If no current language code can be detected, the default language
     * code is used.
     */
    public function getCurrentLanguageCode(): ?string
    {
        return $this->strategy->getCurrentLanguageCode();
    }

    /**
     * This method returns a {@see \Borlabs\Cookie\DtoList\System\KeyValueDtoList} object with the available languages. The
     * `key` contains the language code and the `value` contains the name of the language.
     */
    public function getLanguageList(): KeyValueDtoList
    {
        return $this->strategy->getLanguageList();
    }

    /**
     * This method returns the name of the passed language code.
     */
    public function getLanguageName(string $languageCode): ?string
    {
        return $this->strategy->getLanguageName($languageCode);
    }
}
