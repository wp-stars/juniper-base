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

namespace Borlabs\Cookie\System\WordPressFrontendDriver;

use Borlabs\Cookie\Adapter\WpFunction;
use Borlabs\Cookie\Repository\Service\ServiceRepository;
use Borlabs\Cookie\System\Config\IabTcfConfig;
use Borlabs\Cookie\System\FileSystem\CacheFolder;
use Borlabs\Cookie\System\Language\Language;
use Borlabs\Cookie\System\Option\Option;
use Borlabs\Cookie\System\ResourceEnqueuer\ResourceEnqueuer;
use Borlabs\Cookie\System\Script\FallbackCodeManager;
use Borlabs\Cookie\System\Script\ScriptConfigBuilder;
use Borlabs\Cookie\System\Style\StyleBuilder;

class WordPressFrontendResources
{
    public const BORLABS_COOKIE_HANDLES = [
        'borlabs-cookie-config',
        'borlabs-cookie-core',
        'borlabs-cookie-prioritize',
        'borlabs-cookie-stub',
    ];

    private CacheFolder $cacheFolder;

    private FallbackCodeManager $fallbackCodeManager;

    private IabTcfConfig $iabTcfConfig;

    private Language $language;

    private Option $option;

    private ResourceEnqueuer $resourceEnqueuer;

    private ScriptConfigBuilder $scriptConfigBuilder;

    private ServiceRepository $serviceRepository;

    private StyleBuilder $styleBuilder;

    private WpFunction $wpFunction;

    public function __construct(
        CacheFolder $cacheFolder,
        FallbackCodeManager $fallbackCodeManager,
        IabTcfConfig $iabTcfConfig,
        Language $language,
        Option $option,
        ResourceEnqueuer $resourceEnqueuer,
        ScriptConfigBuilder $scriptConfigBuilder,
        ServiceRepository $serviceRepository,
        StyleBuilder $styleBuilder,
        WpFunction $wpFunction
    ) {
        $this->cacheFolder = $cacheFolder;
        $this->fallbackCodeManager = $fallbackCodeManager;
        $this->iabTcfConfig = $iabTcfConfig;
        $this->language = $language;
        $this->option = $option;
        $this->resourceEnqueuer = $resourceEnqueuer;
        $this->scriptConfigBuilder = $scriptConfigBuilder;
        $this->serviceRepository = $serviceRepository;
        $this->styleBuilder = $styleBuilder;
        $this->wpFunction = $wpFunction;
    }

    /**
     * Prevent Cloudflare Rocket Loader from loading JavaScript files from Borlabs Cookie asynchronously.
     *
     * @param mixed $tag
     * @param mixed $handle
     */
    public function addAttributeNoCloudflareAsync($tag, $handle): string
    {
        return $this->addAttributeIfRequired($tag, $handle, 'data-cfasync', 'false');
    }

    /**
     * Prevent WP Rocket from minimizing the JavaScript files of Borlabs Cookie.
     *
     * @param mixed $tag
     * @param mixed $handle
     */
    public function addAttributeNoMinify($tag, $handle): string
    {
        return $this->addAttributeIfRequired($tag, $handle, 'data-no-minify', '1');
    }

    /**
     * Prevent LiteSpeed Cache from optimizing the JavaScript files of Borlabs Cookie.
     *
     * @param mixed $tag
     * @param mixed $handle
     */
    public function addAttributeNoOptimize($tag, $handle): string
    {
        return $this->addAttributeIfRequired($tag, $handle, 'data-no-optimize', '1');
    }

    public function outputHeadCode(): void
    {
        echo $this->fallbackCodeManager->getFallbackCodes();
    }

    public function registerFooterResources(): void
    {
        if (defined('REST_REQUEST') && $this->wpFunction->applyFilter('borlabsCookie/frontendResources/disabledOnRestRequest', true)) {
            return;
        }

        $languageCode = $this->language->getCurrentLanguageCode();

        $dependencies = [
            'borlabs-cookie-config',
        ];
        $borlabsCookieJSFile = 'typescript/frontend/borlabs-cookie.ts';
        $manifest = json_decode(file_get_contents(BORLABS_COOKIE_PLUGIN_PATH . '/assets/manifest.json', true), true);

        if ($this->iabTcfConfig->get()->iabTcfStatus) {
            $borlabsCookieJSFile = 'typescript/frontend/borlabs-cookie-iabtcf.ts';
            $dependencies[] = 'borlabs-cookie-stub';
        }

        $this->resourceEnqueuer->enqueueScript('core', 'assets/' . $manifest[$borlabsCookieJSFile]['file'], $dependencies, null, true);
    }

    public function registerHeadResources(): void
    {
        if (defined('REST_REQUEST') && $this->wpFunction->applyFilter('borlabsCookie/frontendResources/disabledOnRestRequest', true)) {
            return;
        }

        $languageCode = $this->language->getCurrentLanguageCode();

        $configVersionOption = $this->option->get('ConfigVersion', 1, $languageCode);
        $configFilePath = $this->cacheFolder->getPath() . '/' . $this->scriptConfigBuilder->getConfigFileName($languageCode);

        // If JavaScript config file does not exist, try to create it on the fly
        if (file_exists($configFilePath) === false) {
            $this->scriptConfigBuilder->buildJavaScriptConfigFile($languageCode);
        }

        $this->resourceEnqueuer->enqueueScript(
            'config',
            $this->cacheFolder->getUrl() . '/' . $this->scriptConfigBuilder->getConfigFileName($languageCode),
            null,
            (string) $configVersionOption->value,
        );

        if ($this->iabTcfConfig->get()->iabTcfStatus) {
            $this->resourceEnqueuer->enqueueScript(
                'stub',
                'assets/javascript/' . 'borlabs-cookie-tcf-stub.min.js',
                null,
                (string) $configVersionOption->value,
            );
        }

        if (count($this->serviceRepository->getPrioritizedServices($languageCode))) {
            $this->resourceEnqueuer->enqueueScript(
                'prioritize',
                'assets/javascript/' . 'borlabs-cookie-prioritize.min.js',
                ['borlabs-cookie-config'],
                (string) $configVersionOption->value,
            );
        }

        // Avoid cached styles
        $blogId = $this->wpFunction->getCurrentBlogId();
        $styleVersionOption = $this->option->get('StyleVersion', 1, $languageCode);
        $cssFilePath = $this->cacheFolder->getPath() . '/' . $this->styleBuilder->getCssFileName($blogId, $languageCode);

        // If CSS file does not exist, try to create it on the fly
        if (file_exists($cssFilePath) === false) {
            $this->styleBuilder->buildCssFile($blogId, $languageCode);
        }

        // Check if DEV mode is active or CSS file is still missing
        if (
            defined('BORLABS_COOKIE_DEV_MODE_DISABLE_CSS_CACHING') && constant('BORLABS_COOKIE_DEV_MODE_DISABLE_CSS_CACHING') === true
            || file_exists($cssFilePath) === false
        ) {
            $manifest = json_decode(file_get_contents(BORLABS_COOKIE_PLUGIN_PATH . '/assets/manifest.json', true), true);
            $this->resourceEnqueuer->enqueueStyle('origin', 'assets/' . $manifest['scss/frontend/borlabs-cookie.scss']['file'], null, (string) $styleVersionOption->value);

            $inlineCss = $this->styleBuilder->getDialogCss();
            $inlineCss .= $this->styleBuilder->getWidgetVariableCss();
            $inlineCss .= $this->styleBuilder->getAnimationCss();
            $inlineCss .= $this->styleBuilder->getCustomCss();
            $inlineCss .= $this->styleBuilder->getContentBlockerCss($languageCode);
            $inlineCss = $this->styleBuilder->applyCssModifications($inlineCss);
            $this->wpFunction->wpAddInlineStyle('borlabs-cookie-origin', $inlineCss);

            return;
        }

        $this->resourceEnqueuer->enqueueStyle(
            'custom',
            $this->cacheFolder->getUrl() . '/' . $this->styleBuilder->getCssFileName($blogId, $languageCode),
            null,
            (string) $styleVersionOption->value,
        );
    }

    public function transformScriptTagsToModules($tag, $handle)
    {
        if (
            strpos($handle, 'borlabs-cookie-core') !== false
            || strpos($handle, 'borlabs-cookie-prioritize') !== false
            || strpos($handle, 'borlabs-cookie-stub') !== false
        ) {
            $scriptTypeMatches = [];
            preg_match('/type=["\']([^"\']*)["\']/', $tag, $scriptTypeMatches);
            $scriptType = !empty($scriptTypeMatches) && !empty($scriptTypeMatches[1]) ? strtolower($scriptTypeMatches[1]) : null;

            $tag = $scriptType
                ? preg_replace('/type=(["\'])([^"\']*)["\']/', 'type=$1module$1', $tag)
                : str_replace('<script', "<script type='module'", $tag);
        }

        return $tag;
    }

    private function addAttributeIfRequired($tag, $handle, $attributeName, $attributeValue): string
    {
        if ($this->shouldProcessTag($handle)) {
            return $this->addOrUpdateAttribute($tag, $attributeName, $attributeValue);
        }

        return $tag;
    }

    private function addOrUpdateAttribute($tag, $attributeName, $attributeValue): string
    {
        $pattern = '/' . preg_quote($attributeName) . '=["\']([^"\']*)["\']/';
        $matches = [];
        preg_match($pattern, $tag, $matches);

        if (!empty($matches)) {
            return preg_replace($pattern, $attributeName . '="' . $attributeValue . '"', $tag);
        }

        return str_replace('<script', "<script {$attributeName}=\"{$attributeValue}\"", $tag);
    }

    private function shouldProcessTag($handle): bool
    {
        foreach (self::BORLABS_COOKIE_HANDLES as $string) {
            if (strpos($handle, $string) !== false) {
                return true;
            }
        }

        return false;
    }
}
