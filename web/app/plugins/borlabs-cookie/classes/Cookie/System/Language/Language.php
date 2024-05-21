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

use Borlabs\Cookie\Adapter\Polylang;
use Borlabs\Cookie\Adapter\TranslatePress;
use Borlabs\Cookie\Adapter\Weglot;
use Borlabs\Cookie\Adapter\WpFunction;
use Borlabs\Cookie\Adapter\Wpml;
use Borlabs\Cookie\Container\Container;
use Borlabs\Cookie\Dto\System\KeyValueDto;
use Borlabs\Cookie\Dto\System\RequestDto;
use Borlabs\Cookie\DtoList\System\KeyValueDtoList;
use Borlabs\Cookie\Support\Transformer;
use Borlabs\Cookie\System\Language\Traits\LanguageTrait;
use Borlabs\Cookie\System\Log\Log;
use LogicException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Singleton class Language.
 *
 * The **Language** class decides if and which multi-language strategy should be used.
 * If a multi-language strategy is used, the class forwards calls to the chosen strategy.
 *
 * @see \Borlabs\Cookie\System\Language\Language::getCurrentLanguageCode()
 * @see \Borlabs\Cookie\System\Language\Language::getDefaultLanguage()
 * @see \Borlabs\Cookie\System\Language\Language::getLanguageList()
 * @see \Borlabs\Cookie\System\Language\Language::getLanguageName()
 * @see \Borlabs\Cookie\System\Language\Language::getSelectedLanguageCode()
 * @see \Borlabs\Cookie\System\Language\Language::handleLanguageSwitchRequest()
 * @see \Borlabs\Cookie\System\Language\Language::init()
 * @see \Borlabs\Cookie\System\Language\Language::isInitialized()
 * @see \Borlabs\Cookie\System\Language\Language::isMultilanguagePluginActive()
 * @see \Borlabs\Cookie\System\Language\Language::loadBlogLanguage()
 * @see \Borlabs\Cookie\System\Language\Language::loadTextDomain()
 * @see \Borlabs\Cookie\System\Language\Language::setInitializationSignal()
 * @see \Borlabs\Cookie\System\Language\Language::unloadBlogLanguage()
 */
final class Language
{
    use LanguageTrait;

    public const LANGUAGE_COOKIE = 'borlabs-cookie-language';

    public const LANGUAGE_FOLDER = '/languages';

    private $container;

    private $initializationSignal = false;

    private $isInitialized = false;

    private Log $log;

    private $multilanguageContext;

    private $selectedLanguageCode;

    private WpFunction $wpFunction;

    /**
     * Language constructor.
     *
     * The constructor selects the strategy of which multi-language adapter to use if a multi-language plugin is active.
     */
    public function __construct(
        Container $container,
        Log $log,
        WpFunction $wpFunction
    ) {
        $this->container = $container;
        $this->log = $log;
        $this->wpFunction = $wpFunction;
    }

    public function determineLanguageCodeLength(string $languageCode): string
    {
        return $this->ignoreISO639_1()
            ? $languageCode
            : substr(
                $languageCode,
                0,
                2,
            );
    }

    /**
     * This method returns the current language code. If no current language code can be detected, the default language
     * code is used.
     * If no multi-language strategy is used, this method returns `null`.
     */
    public function getCurrentLanguageCode(): string
    {
        $this->ensureInitializationSignalWasGiven();

        $languageCode = $this->getDefaultLanguage();

        if (!is_null($this->multilanguageContext)) {
            $languageCode = $this->multilanguageContext->getCurrentLanguageCode() ?? $languageCode;

            $this->log->debug('Current language code: ' . $languageCode, [
                'requestUri' => $_SERVER['REQUEST_URI'],
            ]);
        }

        $finalLanguageCode = $this->determineLanguageCodeLength($languageCode);
        $this->log->debug('Current language code after determining the code length: ' . $finalLanguageCode);

        return $finalLanguageCode;
    }

    /**
     * This methode returns the default language code of the blog.
     */
    public function getDefaultLanguage(): string
    {
        return $this->determineLanguageCodeLength(BORLABS_COOKIE_DEFAULT_LANGUAGE);
    }

    /**
     * This method returns a {@see \Borlabs\Cookie\DtoList\System\KeyValueDtoList} object with the available languages. The
     * `key` contains the language code and the `value` contains the name of the language.
     */
    public function getLanguageList(): KeyValueDtoList
    {
        $this->ensureInitializationSignalWasGiven();
        $defaultList = new KeyValueDtoList([
            new KeyValueDto(
                $this->getCurrentLanguageCode(),
                strtoupper($this->getLanguageName($this->getCurrentLanguageCode())),
            ),
        ]);

        if (!is_null($this->multilanguageContext)) {
            $languageList = $this->multilanguageContext->getLanguageList();
            // Sort list by language name
            $languageList->list = Transformer::naturalSortArrayByObjectProperty($languageList->list, 'value');
        }

        return $languageList ?? $defaultList;
    }

    /**
     * This method returns the name of the passed language code.
     * If no multi-language strategy is used, this method returns the default language code.
     */
    public function getLanguageName(string $languageCode): string
    {
        $this->ensureInitializationSignalWasGiven();

        if (!is_null($this->multilanguageContext)) {
            return $this->multilanguageContext->getLanguageName($languageCode) ?? $this->getDefaultLanguage();
        }

        return $this->getDefaultLanguage();
    }

    /**
     * This method returns the selected language code of the plugin's backend. The returned language code of this
     * method is used for settings or entries where a language code is required for language dependent storage.
     */
    public function getSelectedLanguageCode(): string
    {
        return $this->selectedLanguageCode;
    }

    /**
     * This method returns the language code of the currently logged in user.
     */
    public function getUserLanguageCode(): string
    {
        $languageCode = $this->wpFunction->getUserLocale();

        if (!empty($languageCode)) {
            return $this->determineLanguageCodeLength($languageCode);
        }

        return $this->getDefaultLanguage();
    }

    /**
     * This method handles the update of the language cookie, after the language was switched via the backend of the
     * plugin.
     */
    public function handleLanguageSwitchRequest(): void
    {
        /** @var RequestDto $currentRequest */
        $currentRequest = $this->container->get('currentRequest');

        if (
            isset($currentRequest->getData[self::LANGUAGE_COOKIE])
            && $this->isValidLanguageCode($currentRequest->getData[self::LANGUAGE_COOKIE])
        ) {
            setcookie(self::LANGUAGE_COOKIE, $currentRequest->getData[self::LANGUAGE_COOKIE], ['httponly' => true]);
            $this->selectedLanguageCode = $currentRequest->getData[self::LANGUAGE_COOKIE];
        }
    }

    /**
     * This method is called by a WordPress driver and determines if and which multi-language strategy should be used.
     *
     * @see \Borlabs\Cookie\System\WordPressAdminDriver\WordPressAdminInit
     * @see \Borlabs\Cookie\System\WordPressFrontendDriver\WordPressFrontendInit
     */
    public function init()
    {
        $this->ensureInitializationSignalWasGiven();

        if ($this->isInitialized === true) {
            throw new LogicException('The initialization was already executed.', E_USER_ERROR);
        }

        /*
         * Detect multi-language plugin.
         * Since Polylang fakes the existence of the WPML API, we need to stop detecting WPML when Polylang is active.
         * As soon as a strategy is detected, the detection is stopped.
         */
        $polylang = new Polylang($this->container, $this->wpFunction);

        if ($polylang->isActive()) {
            $this->multilanguageContext = new MultilanguageContext($polylang);

            $this->isInitialized = true;
            $this->log->debug('Polylang is active.');
        }

        if ($this->isInitialized === false) {
            $weglot = new Weglot();

            if ($weglot->isActive()) {
                $this->multilanguageContext = new MultilanguageContext($weglot);

                $this->isInitialized = true;
                $this->log->debug('Weglot is active.');
            }
        }

        if ($this->isInitialized === false) {
            $wpml = new Wpml($this->wpFunction);

            if ($wpml->isActive()) {
                $this->multilanguageContext = new MultilanguageContext($wpml);

                $this->isInitialized = true;
                $this->log->debug('WPML is active.');
            }
        }

        if ($this->isInitialized === false) {
            $translatePress = new TranslatePress();

            if ($translatePress->isActive()) {
                $this->multilanguageContext = new MultilanguageContext($translatePress);

                $this->isInitialized = true;
                $this->log->debug('TranslatePress is active.');
            }
        }

        if (isset($_COOKIE[self::LANGUAGE_COOKIE]) && $this->isValidLanguageCode($_COOKIE[self::LANGUAGE_COOKIE])) {
            $this->selectedLanguageCode = $_COOKIE[self::LANGUAGE_COOKIE];
        } else {
            // Fallback when no language was selected, the "current" or "default" (WordPress blog language) language code is used.
            $this->selectedLanguageCode = $this->getCurrentLanguageCode();
        }
    }

    /**
     * This method returns whether a multi-language strategy is used or not.
     * If the website does not use a multi-language plugin, this method returns `false`.
     */
    public function isMultilanguagePluginActive(): bool
    {
        $this->ensureInitializationSignalWasGiven();

        return !is_null($this->multilanguageContext);
    }

    public function isValidLanguageCode(string $languageCode): bool
    {
        $validLanguageCodes = array_column($this->getLanguageList()->list, 'key', 'key');

        return isset($validLanguageCodes[$languageCode]);
    }

    /**
     * This method loads the .mo file of the selected language.
     *
     * @see \Borlabs\Cookie\System\Language\Language::getSelectedLanguageCode()
     */
    public function loadBlogLanguage()
    {
        $languageCode = $this->getSelectedLanguageCode();
        $this->loadTextDomain($languageCode);
    }

    /**
     * This method loads the plugin textdomain.
     *
     * @see \Borlabs\Cookie\System\WordPressAdminDriver\WordPressAdminInit::__construct()
     */
    public function loadTextDomain(?string $languageCode = null): void
    {
        $languageFile = $this->findLanguageFile($languageCode ?? $this->getUserLanguageCode());

        if ($languageFile !== null) {
            $this->wpFunction->loadTextDomain('borlabs-cookie', BORLABS_COOKIE_PLUGIN_PATH . self::LANGUAGE_FOLDER . '/' . $languageFile);

            return;
        }

        if ($languageCode !== 'en') {
            $this->wpFunction->loadPluginTextdomain('borlabs-cookie', BORLABS_COOKIE_SLUG . self::LANGUAGE_FOLDER . '/');
        } else {
            // The default language is English, for which there is no .mo file, so we need to unload an already loaded .mo file.
            $this->wpFunction->unloadTextDomain('borlabs-cookie');
        }
    }

    /**
     * This method is called by a WordPress driver and sets the signal for the `init` method.
     *
     * @see \Borlabs\Cookie\System\WordPressAdminDriver\WordPressAdminInit
     * @see \Borlabs\Cookie\System\WordpressFrontendDriver\WordPressFrontendInit
     */
    public function setInitializationSignal(): void
    {
        $this->initializationSignal = true;
    }

    /**
     * This method unloads the .mo file and calls `loadTextDomain` to load the default language of the current user.
     *
     * @see \Borlabs\Cookie\System\Language\Language::loadTextDomain()
     */
    public function unloadBlogLanguage(): void
    {
        $this->wpFunction->unloadTextDomain('borlabs-cookie');
        $this->loadTextDomain();
    }

    /**
     * This method ensures that any language-related method can only be called after initialization.
     * This helps to avoid errors in development caused by unforeseen calls to language-related methods at runtime,
     * when information about the requested language cannot be determined at the time a method is called.
     */
    private function ensureInitializationSignalWasGiven(): void
    {
        if ($this->initializationSignal === false) {
            throw new LogicException('A language function was called before the initialization signal was given.', E_USER_ERROR);
        }
    }

    /**
     * This method tries to return the .mo file name of the requested language code.
     */
    private function findLanguageFile(string $languageCode): ?string
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(BORLABS_COOKIE_PLUGIN_PATH . self::LANGUAGE_FOLDER),
        );

        foreach ($iterator as $fileData) {
            $fileName = basename($fileData->getPathname());

            // If the language code consists of only two characters, we look for the .mo file that is closest to the language code.
            // As a fallback if the language code is only two characters long, the ignoreISO639_1 value is ignored.
            // This can be the case with multilingual plugins.
            if ($this->ignoreISO639_1() === false || strlen($languageCode) === 2) {
                if (strpos($fileName, 'borlabs-cookie-' . $languageCode) !== false && $fileData->getExtension() === 'mo') {
                    return $fileName;
                }
            } elseif ($fileName === 'borlabs-cookie-' . $languageCode . '.mo') {
                return $fileName;
            }
        }

        return null;
    }
}
