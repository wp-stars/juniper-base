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

namespace Borlabs\Cookie\System\CloudScan;

use Borlabs\Cookie\Adapter\WpFunction;
use Borlabs\Cookie\ApiClient\CloudScanApiClient;
use Borlabs\Cookie\Dto\Adapter\WpGetPagesArgumentDto;
use Borlabs\Cookie\Dto\Adapter\WpGetPostsArgumentDto;
use Borlabs\Cookie\Dto\Adapter\WpGetPostTypeArgumentDto;
use Borlabs\Cookie\Dto\CloudScan\ScanResponseDto;
use Borlabs\Cookie\Dto\System\ExternalFileDto;
use Borlabs\Cookie\Enum\CloudScan\CloudScanStatusEnum;
use Borlabs\Cookie\Enum\CloudScan\CloudScanTypeEnum;
use Borlabs\Cookie\Exception\System\LicenseExpiredException;
use Borlabs\Cookie\Model\CloudScan\CloudScanCookieModel;
use Borlabs\Cookie\Model\CloudScan\CloudScanExternalResourceModel;
use Borlabs\Cookie\Model\CloudScan\CloudScanModel;
use Borlabs\Cookie\Model\CloudScan\CloudScanSuggestionModel;
use Borlabs\Cookie\Repository\CloudScan\CloudScanCookieRepository;
use Borlabs\Cookie\Repository\CloudScan\CloudScanExternalResourceRepository;
use Borlabs\Cookie\Repository\CloudScan\CloudScanRepository;
use Borlabs\Cookie\Repository\CloudScan\CloudScanSuggestionRepository;
use Borlabs\Cookie\Support\Sanitizer;
use Borlabs\Cookie\System\Config\DialogSettingsConfig;
use Borlabs\Cookie\System\FileSystem\FileManager;
use Borlabs\Cookie\System\License\License;
use DateTime;
use Exception;

class CloudScanService
{
    private const FALLBACK_CONTACT_KEYWORD_DATABASE = [
        'contact',
        'kontakt',
    ];

    private CloudScanApiClient $cloudScanApiClient;

    private CloudScanCookieRepository $cloudScanCookieRepository;

    private CloudScanExternalResourceRepository $cloudScanExternalResourceRepository;

    private CloudScanRepository $cloudScanRepository;

    private CloudScanSuggestionRepository $cloudScanSuggestionRepository;

    private DialogSettingsConfig $dialogSettingsConfig;

    private FileManager $fileManager;

    private License $license;

    private WpFunction $wpFunction;

    public function __construct(
        CloudScanApiClient $cloudScanApiClient,
        CloudScanCookieRepository $cloudScanCookieRepository,
        CloudScanExternalResourceRepository $cloudScanExternalResourceRepository,
        CloudScanRepository $cloudScanRepository,
        CloudScanSuggestionRepository $cloudScanSuggestionRepository,
        DialogSettingsConfig $dialogSettingsConfig,
        FileManager $fileManager,
        License $license,
        WpFunction $wpFunction
    ) {
        $this->cloudScanApiClient = $cloudScanApiClient;
        $this->cloudScanCookieRepository = $cloudScanCookieRepository;
        $this->cloudScanExternalResourceRepository = $cloudScanExternalResourceRepository;
        $this->cloudScanRepository = $cloudScanRepository;
        $this->cloudScanSuggestionRepository = $cloudScanSuggestionRepository;
        $this->dialogSettingsConfig = $dialogSettingsConfig;
        $this->fileManager = $fileManager;
        $this->license = $license;
        $this->wpFunction = $wpFunction;
    }

    public function checkUnfinishedScans()
    {
        $scansOfStatusAnalyzing = $this->cloudScanRepository->getAllOfStatus(CloudScanStatusEnum::SCANNING());

        foreach ($scansOfStatusAnalyzing as $unfinishedScan) {
            try {
                $this->syncScanResult($unfinishedScan->id);
            } catch (Exception $e) {
                // Note: ignore
            }
        }

        $scansOfStatusAnalyzing = $this->cloudScanRepository->getAllOfStatus(CloudScanStatusEnum::ANALYZING());

        foreach ($scansOfStatusAnalyzing as $unfinishedScan) {
            try {
                $this->syncScanResult($unfinishedScan->id);
            } catch (Exception $e) {
                // Note: ignore
            }
        }
    }

    /**
     * @throws \Borlabs\Cookie\Exception\ApiClient\CloudScanApiClientException
     * @throws \Borlabs\Cookie\Exception\System\LicenseExpiredException
     * @throws \Borlabs\Cookie\Exception\UnexpectedRepositoryOperationException
     */
    public function createScan(
        array $urls,
        CloudScanTypeEnum $scanType,
        ?string $httpAuthUsername = null,
        ?string $httpAuthPassword = null
    ): CloudScanModel {
        if (!$this->license->isLicenseValid()) {
            throw new LicenseExpiredException('licenseExpiredFeatureNotAvailable');
        }

        $cloudScanResponse = $this->cloudScanApiClient->requestScanCreation(
            $this->getActivePlugins(),
            $this->getActiveThemes(),
            $urls,
            $scanType,
            $httpAuthUsername,
            $httpAuthPassword,
        );

        $cloudScanModel = new CloudScanModel();
        $cloudScanModel->createdAt = new DateTime();
        $cloudScanModel->externalId = $cloudScanResponse->id;
        $cloudScanModel->pages = $cloudScanResponse->pages;
        $cloudScanModel->status = $cloudScanResponse->status;
        $cloudScanModel->type = $cloudScanResponse->type;

        return $this->cloudScanRepository->insert($cloudScanModel);
    }

    /**
     * @throws \Borlabs\Cookie\Exception\ApiClient\CloudScanApiClientException
     */
    public function downloadKeywordDatabase(): void
    {
        if ($this->license->get() === null) {
            return;
        }

        $attachment = $this->cloudScanApiClient->requestScannerPageSelectionKeywordsAttachmentData();
        $this->fileManager->storeExternalFileGlobally(new ExternalFileDto($attachment->downloadUrl), 'scanner-page-selection-keywords-v1.json');
    }

    public function getActivePlugins(): array
    {
        $installedPlugins = [];
        $plugins = $this->wpFunction->getPlugins();

        foreach ($plugins as $pluginPath => $plugin) {
            $installedPlugins[] = (object) [
                'slug' => dirname($pluginPath),
                'textDomain' => $plugin['TextDomain'],
            ];
        }

        return $installedPlugins;
    }

    public function getActiveThemes(): array
    {
        $installedPlugins = [];
        $themes = $this->wpFunction->getWpThemes();

        foreach ($themes as $themeSlug => $theme) {
            $installedPlugins[] = (object) [
                'slug' => $themeSlug,
                'textDomain' => $theme->get('TextDomain'),
            ];
        }

        return $installedPlugins;
    }

    public function getContactKeywordDatabase(): array
    {
        $database = $this->getKeywordDatabase();

        if ($database === null || !isset($database->contact)) {
            return self::FALLBACK_CONTACT_KEYWORD_DATABASE;
        }

        return $database->contact;
    }

    public function getKeywordDatabase(): ?object
    {
        $file = $this->fileManager->getGloballyStoredFileContent('scanner-page-selection-keywords-v1.json');

        if ($file === null) {
            try {
                $this->downloadKeywordDatabase();
            } catch (Exception $e) {
                // Note: ignore because we have fallback
            }
            $file = $this->fileManager->getGloballyStoredFileContent('scanner-page-selection-keywords-v1.json');

            if ($file === null) {
                return null;
            }
        }

        return json_decode($file, false);
    }

    /**
     * @param string $type Possible values: homepage, selection_of_sites_per_post_type, custom
     *
     * @return array|object[]
     */
    public function getListOfPagesByType(
        string $type,
        bool $enableCustomScanUrl,
        ?string $scanPageUrl = null,
        ?string $customScanUrls = null
    ): array {
        if ($type === 'homepage') {
            return [
                $this->getHomepage(),
            ];
        }

        if ($type === 'selection_of_sites_per_post_type') {
            return $this->getSelectionOfSitesPerPostType();
        }

        if ($type === 'custom') {
            if (!$enableCustomScanUrl && $scanPageUrl !== null) {
                return [
                    (object) [
                        'url' => $scanPageUrl,
                    ],
                ];
            }

            if ($enableCustomScanUrl && $customScanUrls !== null) {
                return array_map(
                    fn ($url) => (object) ['url' => $url],
                    Sanitizer::hostList($customScanUrls, true),
                );
            }
        }

        // Fallback
        return [
            $this->getHomepage(),
        ];
    }

    /**
     * @throws \Borlabs\Cookie\Exception\ApiClient\CloudScanApiClientException
     * @throws \Borlabs\Cookie\Exception\UnexpectedRepositoryOperationException
     */
    public function syncScanResult(int $id): ?CloudScanModel
    {
        /** @var null|CloudScanModel $cloudScan */
        $cloudScan = $this->cloudScanRepository->findById($id);

        if ($cloudScan === null) {
            // TODO: exception
            return null;
        }

        $scanResponse = $this->cloudScanApiClient->getScan($cloudScan->externalId);

        if ($cloudScan->status->isValue(CloudScanStatusEnum::FINISHED)) {
            return $cloudScan;
        }

        $cloudScan->pages = $scanResponse->pages;
        $cloudScan->status = $scanResponse->status;
        $this->cloudScanRepository->update($cloudScan);
        $this->handleCookies($cloudScan, $scanResponse);
        $this->handleExternalResources($cloudScan, $scanResponse);
        $this->handleSuggestions($cloudScan, $scanResponse);

        return $cloudScan;
    }

    private function ensureUrlIsUnique(array $urls)
    {
        $uniqueUrls = [];
        $urlList = [];

        foreach ($urls as $url) {
            $urlList[$url->url] = $url->url;
        }

        foreach ($urlList as $url) {
            $uniqueUrls[] = (object) ['url' => $url];
        }

        return $uniqueUrls;
    }

    private function getHomepage(): object
    {
        return (object) [
            'url' => $this->wpFunction->getHomeUrl(),
        ];
    }

    private function getLikelyContactPage(): ?object
    {
        $pageSelectionKeywords = $this->getContactKeywordDatabase();

        $pages = $this->wpFunction->getPages(new WpGetPagesArgumentDto());

        foreach ($pageSelectionKeywords as $pageSelectionKeyword) {
            foreach ($pages as $page) {
                if (strtolower($page->post_name) === $pageSelectionKeyword
                    || strtolower($page->post_title) === $pageSelectionKeyword) {
                    return (object) [
                        'url' => $this->wpFunction->getPermalink($page->ID),
                    ];
                }
            }
        }

        return null;
    }

    private function getOldestAndNewestPageOfEachPosttype(): array
    {
        $pages = [];
        $postTypeArgument = new WpGetPostTypeArgumentDto();
        $postTypeArgument->public = true;
        $postTypes = $this->wpFunction->getPostTypes($postTypeArgument);

        foreach ($postTypes as $postType) {
            $archiveLink = $this->wpFunction->getPostTypeArchiveLink($postType);

            if ($archiveLink !== null) {
                $pages[] = (object) [
                    'url' => $archiveLink,
                ];
            }

            $postArgument = new WpGetPostsArgumentDto();
            $postArgument->numberPosts = 1;
            $postArgument->postType = [$postType];
            $postArgument->order = 'ASC';
            $oldestPost = $this->wpFunction->getPosts($postArgument);

            if (count($oldestPost) === 1) {
                $pages[] = (object) [
                    'url' => $this->wpFunction->getPermalink($oldestPost[0]->ID),
                ];

                $postArgument = new WpGetPostsArgumentDto();
                $postArgument->numberPosts = 1;
                $postArgument->postType = [$postType];
                $postArgument->order = 'DESC';
                $newestPost = $this->wpFunction->getPosts($postArgument);

                if ($newestPost[0]->ID !== $oldestPost[0]->ID) {
                    $pages[] = (object) [
                        'url' => $this->wpFunction->getPermalink($newestPost[0]->ID),
                    ];
                }
            }
        }

        return $pages;
    }

    private function getSelectionOfSitesPerPostType(): array
    {
        $pages = $this->getOldestAndNewestPageOfEachPosttype();
        $likelyContactPage = $this->getLikelyContactPage();

        if ($likelyContactPage !== null) {
            $pages[] = $likelyContactPage;
        }
        $pages[] = $this->getHomepage();

        if ($this->dialogSettingsConfig->get()->imprintPageUrl !== '') {
            $pages[] = (object) ['url' => $this->dialogSettingsConfig->get()->imprintPageUrl];
        }

        if ($this->dialogSettingsConfig->get()->privacyPageUrl !== '') {
            $pages[] = (object) ['url' => $this->dialogSettingsConfig->get()->privacyPageUrl];
        }

        return $this->ensureUrlIsUnique($pages);
    }

    private function handleCookies(CloudScanModel $cloudScan, ScanResponseDto $scanResponseDto): void
    {
        if (count($scanResponseDto->cookies->list) <= 0) {
            return;
        }

        $existingCookies = $this->cloudScanCookieRepository->getByCloudScan($cloudScan);

        foreach ($existingCookies as $existingCookie) {
            $this->cloudScanCookieRepository->delete($existingCookie);
        }

        foreach ($scanResponseDto->cookies->list as $cookie) {
            $cookieModel = new CloudScanCookieModel();
            $cookieModel->borlabsServicePackageKey = $cookie->packageKey;
            $cookieModel->cloudScanId = $cloudScan->id;
            $cookieModel->examples = $cookie->examples;
            $cookieModel->hostname = $cookie->hostname;
            $cookieModel->lifetime = $cookie->lifetime;
            $cookieModel->name = $cookie->name;
            $cookieModel->path = $cookie->path;

            $this->cloudScanCookieRepository->insert($cookieModel);
        }
    }

    private function handleExternalResources(CloudScanModel $cloudScan, ScanResponseDto $scanResponseDto): void
    {
        if (count($scanResponseDto->externalResources->list) <= 0) {
            return;
        }

        $existingExternalResources = $this->cloudScanExternalResourceRepository->getByCloudScan($cloudScan);

        foreach ($existingExternalResources as $existingExternalResource) {
            $this->cloudScanExternalResourceRepository->delete($existingExternalResource);
        }

        foreach ($scanResponseDto->externalResources->list as $externalResource) {
            $externalResourceModel = new CloudScanExternalResourceModel();
            $externalResourceModel->borlabsServicePackageKey = $externalResource->packageKey;
            $externalResourceModel->cloudScanId = $cloudScan->id;
            $externalResourceModel->examples = $externalResource->examples;
            $externalResourceModel->hostname = $externalResource->hostname;

            $this->cloudScanExternalResourceRepository->insert($externalResourceModel);
        }
    }

    private function handleSuggestions(CloudScanModel $cloudScan, ScanResponseDto $scanResponseDto): void
    {
        if (count($scanResponseDto->suggestions->list) <= 0) {
            return;
        }

        $existingSuggestions = $this->cloudScanSuggestionRepository->getByCloudScan($cloudScan);

        foreach ($existingSuggestions as $existingSuggestion) {
            $this->cloudScanSuggestionRepository->delete($existingSuggestion);
        }

        foreach ($scanResponseDto->suggestions->list as $suggestion) {
            $suggestionModel = new CloudScanSuggestionModel();
            $suggestionModel->borlabsServicePackageKey = $suggestion->packageKey;
            $suggestionModel->cloudScanId = $cloudScan->id;
            $suggestionModel->pages = $suggestion->pages;

            $this->cloudScanSuggestionRepository->insert($suggestionModel);
        }
    }
}
