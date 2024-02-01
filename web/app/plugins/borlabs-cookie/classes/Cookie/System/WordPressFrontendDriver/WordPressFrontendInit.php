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
use Borlabs\Cookie\System\CompatibilityPatch\CompatibilityPatchManager;
use Borlabs\Cookie\System\Config\DialogSettingsConfig;
use Borlabs\Cookie\System\Config\GeneralConfig;
use Borlabs\Cookie\System\ContentBlocker\ContentBlockerManager;
use Borlabs\Cookie\System\CookieBlocker\CookieBlockerService;
use Borlabs\Cookie\System\Dialog\Dialog;
use Borlabs\Cookie\System\Language\Language;
use Borlabs\Cookie\System\LocalScanner\LocalScanner;
use Borlabs\Cookie\System\LocalScanner\ScanRequestService;
use Borlabs\Cookie\System\ScriptBlocker\ScriptBlockerManager;
use Borlabs\Cookie\System\Shortcode\ShortcodeHandler;
use Borlabs\Cookie\System\StyleBlocker\StyleBlockerManager;

final class WordPressFrontendInit
{
    private CompatibilityPatchManager $compatibilityPatchManager;

    private ContentBlockerManager $contentBlockerManager;

    private ControllerManager $controllerManager;

    private CookieBlockerService $cookieBlockerService;

    private Dialog $dialog;

    private DialogSettingsConfig $dialogSettingsConfig;

    private GeneralConfig $generalConfig;

    private HtmlOutputManager $htmlOutputManager;

    private Language $language;

    private LocalScanner $localScanner;

    private OutputBufferManager $outputBufferManager;

    private ScanRequestService $scanRequestService;

    private ScriptBlockerManager $scriptBlockerManager;

    private ShortcodeHandler $shortcodeHandler;

    private StyleBlockerManager $styleBlockerManager;

    private WordPressFrontendResources $wordPressFrontendResources;

    private WpFunction $wpFunction;

    public function __construct(
        CompatibilityPatchManager $compatibilityPatchManager,
        ContentBlockerManager $contentBlockerManager,
        ControllerManager $controllerManager,
        CookieBlockerService $cookieBlockerService,
        Dialog $dialog,
        DialogSettingsConfig $dialogSettingsConfig,
        GeneralConfig $generalConfig,
        HtmlOutputManager $htmlOutputManager,
        Language $language,
        LocalScanner $localScanner,
        OutputBufferManager $outputBufferManager,
        ScanRequestService $scanRequestService,
        ScriptBlockerManager $scriptBlockerManager,
        ShortcodeHandler $shortCodeHandler,
        StyleBlockerManager $styleBlockerManager,
        WordPressFrontendResources $wordPressFrontendResources,
        WpFunction $wpFunction
    ) {
        $this->compatibilityPatchManager = $compatibilityPatchManager;
        $this->contentBlockerManager = $contentBlockerManager;
        $this->controllerManager = $controllerManager;
        $this->cookieBlockerService = $cookieBlockerService;
        $this->dialog = $dialog;
        $this->dialogSettingsConfig = $dialogSettingsConfig;
        $this->generalConfig = $generalConfig;
        $this->htmlOutputManager = $htmlOutputManager;
        $this->language = $language;
        $this->localScanner = $localScanner;
        $this->outputBufferManager = $outputBufferManager;
        $this->scanRequestService = $scanRequestService;
        $this->scriptBlockerManager = $scriptBlockerManager;
        $this->shortcodeHandler = $shortCodeHandler;
        $this->styleBlockerManager = $styleBlockerManager;
        $this->wordPressFrontendResources = $wordPressFrontendResources;
        $this->wpFunction = $wpFunction;
    }

    public function register(): void
    {
        // Detect language and load text domain.
        $this->language->setInitializationSignal();
        $this->language->init();
        $this->language->loadTextDomain();

        // Register Frontend Controllers capable of handling the current request
        $this->controllerManager->init();

        // Disable Borlabs Cookie if a scan request requires it.
        if ($this->scanRequestService->noBorlabsCookie()) {
            // Hide shortcodes if Borlabs Cookie should be disabled for this request.
            $this->wpFunction->addShortcode('borlabs-cookie', function ($atts, $content = null) {
                return '';
            });

            return;
        }

        // Initialize Borlabs Cookie
        if (
            $this->generalConfig->get()->borlabsCookieStatus
            || (
                $this->generalConfig->get()->setupMode
                && ($this->wpFunction->currentUserCan('manage_borlabs_cookie') || $this->scanRequestService->isScanRequest())
            )
        ) {
            $this->localScanner->init();
            $this->compatibilityPatchManager->loadPatches();
            $this->contentBlockerManager->init();
            $this->scriptBlockerManager->init();
            $this->styleBlockerManager->init();
            $this->cookieBlockerService->init();

            // Register resources
            $this->wpFunction->addAction('wp_enqueue_scripts', [$this->wordPressFrontendResources, 'registerHeadResources']);
            $this->wpFunction->addAction('wp_head', [$this->wordPressFrontendResources, 'outputHeadCode']);
            $this->wpFunction->addAction('wp_footer', [$this->wordPressFrontendResources, 'registerFooterResources']);

            $this->wpFunction->addAction('template_redirect', [$this->outputBufferManager, 'startBuffering'], 19021987);
            $this->wpFunction->addFilter('script_loader_tag', [$this->scriptBlockerManager, 'blockHandle'], 999, 3);
            $this->wpFunction->addFilter('style_loader_tag', [$this->styleBlockerManager, 'blockHandle'], 999, 3);

            // Add Frontend Shortcodes Support
            $this->wpFunction->addShortcode('borlabs-cookie', [$this->shortcodeHandler, 'handle']);

            $this->wpFunction->addFilter('the_content', [$this->contentBlockerManager, 'detectIframes'], 100, 1);
            $this->wpFunction->addFilter('embed_oembed_html', [$this->contentBlockerManager, 'handleOembedBlocking'], 100, 4);
            $this->wpFunction->addFilter('render_block', [$this->contentBlockerManager, 'detectIframes'], 100, 1);
            $this->wpFunction->addFilter('widget_custom_html_content', [$this->contentBlockerManager, 'detectIframes'], 100, 1);
            $this->wpFunction->addFilter('widget_text_content', [$this->contentBlockerManager, 'detectIframes'], 100, 1);
            $this->wpFunction->addFilter('widget_block_content', [$this->contentBlockerManager, 'detectIframes'], 100, 1);
            $this->wpFunction->addFilter('script_loader_tag', [$this->wordPressFrontendResources, 'transformScriptTagsToModules'], 100, 2);
            $this->wpFunction->addFilter('script_loader_tag', [$this->wordPressFrontendResources, 'addAttributeNoCloudflareAsync'], 100, 2);
            $this->wpFunction->addFilter('script_loader_tag', [$this->wordPressFrontendResources, 'addAttributeNoMinify'], 100, 2);
            $this->wpFunction->addFilter('script_loader_tag', [$this->wordPressFrontendResources, 'addAttributeNoOptimize'], 100, 2);

            // Embed Cookie Box
            $this->wpFunction->addAction('wp_footer', [$this->dialog, 'output']);
            $this->wpFunction->addAction('wp_footer', [$this->htmlOutputManager, 'handle'], 19021987); // Late but not latest

            if ($this->dialogSettingsConfig->get()->showDialogOnLoginPage === true) {
                $this->wpFunction->addAction('login_enqueue_scripts', [$this->wordPressFrontendResources, 'registerHeadResources']);
                $this->wpFunction->addAction('login_head', [$this->wordPressFrontendResources, 'outputHeadCode']);
                $this->wpFunction->addAction('login_footer', [$this->wordPressFrontendResources, 'registerFooterResources']);
                $this->wpFunction->addAction('login_footer', [$this->dialog, 'output']);
            }
        }
    }
}
