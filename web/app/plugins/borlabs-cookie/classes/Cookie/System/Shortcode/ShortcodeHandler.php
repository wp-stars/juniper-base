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

namespace Borlabs\Cookie\System\Shortcode;

use Borlabs\Cookie\Localization\DefaultLocalizationStrings;
use Borlabs\Cookie\Repository\Service\ServiceRepository;
use Borlabs\Cookie\System\Config\ContentBlockerSettingsConfig;
use Borlabs\Cookie\System\ContentBlocker\ContentBlockerManager;

final class ShortcodeHandler
{
    private ContentBlockerSettingsConfig $contentBlockerGeneralConfig;

    private ContentBlockerManager $contentBlockerManager;

    private DefaultLocalizationStrings $defaultLocalizationStrings;

    private ServiceRepository $serviceRepository;

    public function __construct(
        ContentBlockerManager $contentBlockerManager,
        ContentBlockerSettingsConfig $contentBlockerGeneralConfig,
        DefaultLocalizationStrings $defaultLocalizationStrings,
        ServiceRepository $serviceRepository
    ) {
        $this->contentBlockerManager = $contentBlockerManager;
        $this->contentBlockerGeneralConfig = $contentBlockerGeneralConfig;
        $this->defaultLocalizationStrings = $defaultLocalizationStrings;
        $this->serviceRepository = $serviceRepository;
    }

    public function handle(array $atts, ?string $content = null): string
    {
        if (
            function_exists('is_feed') && is_feed()
            && $this->contentBlockerGeneralConfig->get()->removeIframesInFeeds == true
        ) {
            return '';
        }

        if (empty($atts['type'])) {
            return $content;
        }

        if ($atts['type'] === 'btn-consent-preferences') {
            return $this->handleTypeBtnConsentPreference($atts, $content);
        }

        if ($atts['type'] === 'btn-switch-consent') {
            return $this->handleTypeBtnSwitchConsent($atts, $content);
        }

        if ($atts['type'] === 'consent-history') {
            return $this->handleTypeConsentHistory($atts, $content);
        }

        if ($atts['type'] === 'content-blocker') {
            return $this->handleTypeContentBlocker($atts, $content);
        }

        if ($atts['type'] === 'service') {
            return $this->handleTypeService($atts, $content);
        }

        if ($atts['type'] === 'service-group') {
            return $this->handleTypeServiceGroup($atts, $content);
        }

        if ($atts['type'] === 'service-list') {
            return $this->handleTypeServiceList($atts, $content);
        }

        if ($atts['type'] === 'uid') {
            return $this->handleTypeUID($atts, $content);
        }

        return $content;
    }

    public function handleTypeBtnConsentPreference(array $atts, string $content): string
    {
        $title = $this->defaultLocalizationStrings->get()['shortcodes']['openConsentPreferences'];
        $type = 'button';

        if (!empty($atts['title'])) {
            $title = $atts['title'];
        }

        if (!empty($atts['element']) && $atts['element'] === 'link') {
            $type = 'link';
        }

        return '<span class="borlabs-cookie-open-dialog-preferences ' . ($type === 'button' ? 'brlbs-cmpnt-container' : '') . '" data-borlabs-cookie-title="' . $title . '" data-borlabs-cookie-type="' . $type . '" ></span>';
    }

    public function handleTypeBtnSwitchConsent(array $atts, string $content): string
    {
        if (empty($atts['id'])) {
            return $content;
        }

        $serviceModel = $this->serviceRepository->getByKey($atts['id']);

        if (empty($serviceModel)) {
            return $content;
        }

        $title = $serviceModel->name;

        if (!empty($atts['title'])) {
            $title = sprintf($atts['title'], $title);
        }

        return '<div data-borlabs-cookie-btn-switch-consent class="brlbs-cmpnt-container"
        data-borlabs-cookie-title="' . $title . '"
        data-borlabs-cookie-service-id="' . $serviceModel->key . '"></div>';
    }

    public function handleTypeConsentHistory(array $atts, string $content): string
    {
        return '<div class="brlbs-cmpnt-container" data-borlabs-cookie-consent-history></div>';
    }

    public function handleTypeContentBlocker(array $atts, string $content): string
    {
        $contentBlockerId = '';

        if (!empty($atts['id'])) {
            $contentBlockerId = $atts['id'];
        }

        $url = null;

        if (filter_var(trim($content), FILTER_VALIDATE_URL) !== false) {
            $url = trim($content);
            $content = (string) wp_oembed_get($content);
        } else {
            $content = do_shortcode($content);
        }

        return $this->contentBlockerManager->handleContentBlocking(
            $content,
            $url,
            $contentBlockerId,
            $atts,
        );
    }

    public function handleTypeService(array $atts, string $content): string
    {
        $serviceId = $atts['id'] ?? '';

        if ($serviceId === '') {
            return '<!-- Borlabs Cookie: id attribute missing -->';
        }

        $encodedContent = base64_encode($content);

        return <<<EOT
<span data-borlabs-cookie-service-id="{$serviceId}" data-borlabs-cookie-content="{$encodedContent}"></span>
EOT;
    }

    public function handleTypeServiceGroup(array $atts, string $content): string
    {
        $serviceGroupId = $atts['id'] ?? '';

        if ($serviceGroupId === '') {
            return '<!-- Borlabs Cookie: id attribute missing -->';
        }

        $encodedContent = base64_encode($content);

        return <<<EOT
<span data-borlabs-cookie-service-group-id="{$serviceGroupId}" data-borlabs-cookie-content="{$encodedContent}"></span>
EOT;
    }

    public function handleTypeServiceList(array $atts, string $content): string
    {
        return '<div class="brlbs-cmpnt-container" data-borlabs-cookie-service-list></div>';
    }

    public function handleTypeUID(array $atts, string $content): string
    {
        return '<span data-borlabs-cookie-user-uid></span>';
    }
}
