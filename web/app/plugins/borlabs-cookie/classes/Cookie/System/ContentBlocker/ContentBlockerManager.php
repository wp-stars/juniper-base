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

namespace Borlabs\Cookie\System\ContentBlocker;

use Borlabs\Cookie\Adapter\WpFunction;
use Borlabs\Cookie\Model\ContentBlocker\ContentBlockerModel;
use Borlabs\Cookie\Repository\ContentBlocker\ContentBlockerRepository;
use Borlabs\Cookie\Support\Searcher;
use Borlabs\Cookie\System\Config\ContentBlockerSettingsConfig;
use Borlabs\Cookie\System\Language\Language;
use Borlabs\Cookie\System\LocalScanner\ScanRequestService;

final class ContentBlockerManager
{
    public const IFRAME_DETECTION_REGEX = '/<iframe.*?(?=<\/iframe>)<\/iframe>/is';

    private ContentBlockerSettingsConfig $contentBlockerGeneralConfig;

    private ContentBlockerRepository $contentBlockerRepository;

    /**
     * @var ContentBlockerModel[]
     */
    private array $contentBlockers = [];

    private array $exclusionList = [];

    private Language $language;

    private ScanRequestService $scanRequestService;

    private WpFunction $wpFunction;

    public function __construct(
        ContentBlockerSettingsConfig $contentBlockerGeneralConfig,
        ContentBlockerRepository $contentBlockerRepository,
        Language $language,
        ScanRequestService $scanRequestService,
        WpFunction $wpFunction
    ) {
        $this->contentBlockerGeneralConfig = $contentBlockerGeneralConfig;
        $this->contentBlockerRepository = $contentBlockerRepository;
        $this->language = $language;
        $this->scanRequestService = $scanRequestService;
        $this->wpFunction = $wpFunction;
    }

    public function detectIframes(string $htmlContent, $postId = null, $field = null): string
    {
        if ($this->wpFunction->isFeed() && $this->contentBlockerGeneralConfig->get()->removeIframesInFeeds) {
            return preg_replace(self::IFRAME_DETECTION_REGEX, '', $htmlContent);
        }

        return preg_replace_callback(
            self::IFRAME_DETECTION_REGEX,
            fn ($matches) => $this->handleIframeBlocking($matches[0]),
            $htmlContent,
        );
    }

    public function getContentBlockerByKey(string $key): ?ContentBlockerModel
    {
        return Searcher::findObject($this->contentBlockers, 'key', $key);
    }

    public function getContentBlockers(): array
    {
        return $this->contentBlockers;
    }

    public function handleContentBlocking(
        string $content,
        ?string $url = null,
        ?string $contentBlockerId = null,
        ?array $attributes = null
    ) {
        if (isset($url) && $this->isHostnameExcluded($url)) {
            return $content;
        }

        if ($this->wpFunction->isFeed() && $this->contentBlockerGeneralConfig->get()->removeIframesInFeeds) {
            return '';
        }

        /** @var ContentBlockerModel $contentBlocker */
        $contentBlocker = null;

        if (isset($contentBlockerId)) {
            $contentBlocker = Searcher::findObject($this->contentBlockers, 'key', $contentBlockerId);
        } elseif (isset($url)) {
            $contentBlocker = $this->determineContentBlockerByUrl($url);
        }

        // Fallback to default ContentBlocker
        if ($contentBlocker === null) {
            $contentBlocker = Searcher::findObject($this->contentBlockers, 'key', 'default');
        }

        // In case default ContentBlocker was disabled
        if ($contentBlocker === null) {
            return $content;
        }

        $contentBlocker = clone $contentBlocker;
        $attributes = array_merge($attributes ?? [], ['url' => $url,]);

        // Allow modification of the ContentBlocker model
        $contentBlocker = $this->wpFunction->applyFilter(
            'borlabsCookie/contentBlocker/blocking/afterDetermination/' . $contentBlocker->key,
            $contentBlocker,
            $attributes,
            $content,
        );
        // Allow modification of the content that is about to be blocked
        $content = $this->wpFunction->applyFilter(
            'borlabsCookie/contentBlocker/blocking/beforeBlocking/' . $contentBlocker->key,
            $content,
            $attributes,
            $contentBlocker,
        );

        $search = array_map(static fn ($value) => '{{ ' . $value . ' }}', array_column($contentBlocker->languageStrings->list, 'key'));
        $search[] = '{{ name }}';
        $search[] = '{{ previewImage }}';
        $search[] = '{{ serviceConsentButtonDisplayValue }}';
        $replace = array_column($contentBlocker->languageStrings->list, 'value');
        $replace[] = $contentBlocker->name;
        $replace[] = $contentBlocker->previewImage;
        $replace[] = isset($contentBlocker->serviceId) ? 'inherit' : 'none';

        $contentBlocker->previewHtml = str_replace($search, $replace, $contentBlocker->previewHtml);
        $encodedContent = base64_encode($content);

        $additionalHtmlAttributes = '';

        $content = <<<EOT
        <div class="brlbs-cmpnt-container brlbs-cmpnt-content-blocker" data-borlabs-cookie-content-blocker-id="{$contentBlocker->key}" data-borlabs-cookie-content="{$encodedContent}" {$additionalHtmlAttributes}>{$contentBlocker->previewHtml}</div>
EOT;

        // Allow modification of the content after blocking
        $content = $this->wpFunction->applyFilter(
            'borlabsCookie/contentBlocker/blocking/afterBlocking/' . $contentBlocker->key,
            $content,
            $attributes,
        );

        // Remove whitespace to avoid WordPress' automatic br- & p-tags
        return preg_replace('/[\s]+/mu', ' ', $content);
    }

    public function handleIframeBlocking(string $iframeTag): string
    {
        if (strpos($iframeTag, 'data-borlabs-cookie-do-not-block-iframe') !== false) {
            return $iframeTag;
        }

        // Replace data-src & data-lazy-src attributes with src attributes
        $iframeTag = str_replace(['data-src=','data-lazy-src=',], ['src=', 'src='], $iframeTag);

        $srcMatches = null;
        preg_match('/src=([\'"])(.+?)\1/i', $iframeTag, $srcMatches);

        if (empty($srcMatches[0]) || $srcMatches[2] === 'about:blank') {
            return $iframeTag;
        }

        return $this->handleContentBlocking($iframeTag, $srcMatches[2]);
    }

    public function handleOembedBlocking(string $content): string
    {
        if (preg_match('/<iframe.+?src=[\'"](.+?)[\'"].*?><\/iframe>/i', $content)) {
            return $this->handleIframeBlocking($content);
        }

        return $this->handleContentBlocking($content);
    }

    public function init()
    {
        $this->contentBlockers = [];
        $this->exclusionList = [];

        if ($this->scanRequestService->noContentBlockers()) {
            return;
        }

        $siteHost = strtolower(
            parse_url($this->wpFunction->getHomeUrl(), PHP_URL_HOST),
        );
        $this->exclusionList[$siteHost] = $siteHost;

        foreach ($this->contentBlockerGeneralConfig->get()->excludedHostnames as $exclusion) {
            $exclusion = strtolower($exclusion);
            $this->exclusionList[$exclusion] = $exclusion;
        }

        $contentBlockerModels = $this->contentBlockerRepository->find(
            ['language' => $this->language->getCurrentLanguageCode(),],
            [],
            [],
            ['contentBlockerLocations'],
        );

        foreach ($contentBlockerModels as $contentBlockerModel) {
            if ($contentBlockerModel->status === true) {
                if ($this->scanRequestService->noDefaultContentBlocker() && $contentBlockerModel->key === 'default') {
                    continue;
                }

                $this->contentBlockers[] = $contentBlockerModel;

                continue;
            }

            foreach ($contentBlockerModel->contentBlockerLocations as $contentBlockerLocation) {
                $exclusion = strtolower($contentBlockerLocation->hostname);
                $this->exclusionList[$exclusion] = $exclusion;
            }
        }
    }

    private function determineContentBlockerByUrl(string $url): ?ContentBlockerModel
    {
        $urlInfo = parse_url($url);
        $urlToCompare = strtolower($urlInfo['host'] ?? '') . ($urlInfo['path'] ?? '');
        $levensteinDistance = 0;
        $contentBlocker = null;

        foreach ($this->contentBlockers as $contentBlockerModel) {
            foreach ($contentBlockerModel->contentBlockerLocations  as $contentBlockerLocation) {
                $contentBlockerLocation = strtolower($contentBlockerLocation->hostname . '/' . ltrim($contentBlockerLocation->path, '/'));

                if (strpos($urlToCompare, $contentBlockerLocation) === false) {
                    continue;
                }

                $distance = levenshtein($urlToCompare, $contentBlockerLocation);

                if ($distance < $levensteinDistance || ($levensteinDistance === 0 && $contentBlocker === null)) {
                    $levensteinDistance = $distance;
                    $contentBlocker = $contentBlockerModel;
                }
            }
        }

        return $contentBlocker;
    }

    private function isHostnameExcluded(string $hostname): bool
    {
        $hostname = strtolower($hostname);

        foreach ($this->exclusionList as $exclusion) {
            if (strpos($hostname, $exclusion) !== false) {
                return true;
            }
        }

        return false;
    }
}
