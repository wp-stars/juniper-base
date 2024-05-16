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

namespace Borlabs\Cookie\System\Config;

use Borlabs\Cookie\Adapter\WpFunction;
use Borlabs\Cookie\Dto\Config\GeneralDto;
use Borlabs\Cookie\Enum\Cookie\SameSiteEnum;
use Borlabs\Cookie\System\Language\Language;
use Borlabs\Cookie\System\Option\Option;

/**
 * @extends AbstractConfigManagerWithLanguage<GeneralDto>
 */
final class GeneralConfig extends AbstractConfigManagerWithLanguage
{
    /**
     * Name of `config_name` where the configuration will be stored. The name is automatically extended with a language
     * code.
     */
    public const CONFIG_NAME = 'GeneralConfig';

    /**
     * The property is set by {@see \Borlabs\Cookie\System\Config\AbstractConfigManagerWithLanguage}.
     */
    public static ?GeneralDto $baseConfigDto = null;

    private WpFunction $wpFunction;

    public function __construct(
        Language $language,
        Option $option,
        WpFunction $wpFunction
    ) {
        parent::__construct($language, $option);

        $this->wpFunction = $wpFunction;
    }

    /**
     * Returns an {@see \Borlabs\Cookie\Dto\Config\GeneralDto} object with all properties set to the default
     * values.
     */
    public function defaultConfig(): GeneralDto
    {
        $siteUrlInfo = parse_url($this->wpFunction->getHomeUrl());
        $defaultConfig = new GeneralDto();
        $defaultConfig->cookieDomain = $siteUrlInfo['host'];
        $defaultConfig->cookiePath = !empty($siteUrlInfo['path']) ? $siteUrlInfo['path'] : $defaultConfig->cookiePath;
        $defaultConfig->cookieSameSite = SameSiteEnum::LAX();
        $defaultConfig->pluginUrl = BORLABS_COOKIE_PLUGIN_URL;

        return $defaultConfig;
    }

    /**
     * This method returns the {@see \Borlabs\Cookie\Dto\Config\GeneralDto} object with all properties for the
     * language specified when calling the {@see \Borlabs\Cookie\System\Config\GeneralConfig::load()} method.
     */
    public function get(): GeneralDto
    {
        $this->ensureConfigWasInitialized();

        return self::$baseConfigDto;
    }

    /**
     * Returns the {@see \Borlabs\Cookie\Dto\Config\GeneralDto} object of the specified language.
     * If no configuration is found for the language, the default settings are used.
     */
    public function load(string $languageCode): GeneralDto
    {
        return $this->_load($languageCode);
    }

    /**
     * Saves the configuration of the specified language.
     */
    public function save(GeneralDto $config, string $languageCode): bool
    {
        return $this->_save($config, $languageCode);
    }
}
