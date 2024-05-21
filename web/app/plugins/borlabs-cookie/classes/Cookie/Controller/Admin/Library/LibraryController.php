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

namespace Borlabs\Cookie\Controller\Admin\Library;

use Borlabs\Cookie\Adapter\WpFunction;
use Borlabs\Cookie\Controller\Admin\ControllerInterface;
use Borlabs\Cookie\Controller\Admin\ExtendedRouteValidationInterface;
use Borlabs\Cookie\Dto\System\RequestDto;
use Borlabs\Cookie\Enum\Package\ComponentTypeEnum;
use Borlabs\Cookie\Enum\Package\InstallationStatusEnum;
use Borlabs\Cookie\Exception\GenericException;
use Borlabs\Cookie\Exception\TranslatedException;
use Borlabs\Cookie\Localization\CloudScan\CloudScanDetailsLocalizationStrings;
use Borlabs\Cookie\Localization\GlobalLocalizationStrings;
use Borlabs\Cookie\Localization\Library\LibraryLocalizationStrings;
use Borlabs\Cookie\Repository\CloudScan\CloudScanSuggestionRepository;
use Borlabs\Cookie\Repository\ContentBlocker\ContentBlockerRepository;
use Borlabs\Cookie\Repository\Expression\BinaryOperatorExpression;
use Borlabs\Cookie\Repository\Expression\ContainsLikeLiteralExpression;
use Borlabs\Cookie\Repository\Expression\LiteralExpression;
use Borlabs\Cookie\Repository\Expression\ModelFieldNameExpression;
use Borlabs\Cookie\Repository\Expression\NullExpression;
use Borlabs\Cookie\Repository\Package\PackageRepository;
use Borlabs\Cookie\Repository\Service\ServiceRepository;
use Borlabs\Cookie\Support\Sanitizer;
use Borlabs\Cookie\Support\Searcher;
use Borlabs\Cookie\System\Language\Language;
use Borlabs\Cookie\System\Message\MessageManager;
use Borlabs\Cookie\System\Package\PackageManager;
use Borlabs\Cookie\System\Script\BorlabsCookieGlobalsService;
use Borlabs\Cookie\System\Template\Template;
use DateTime;

final class LibraryController implements ControllerInterface, ExtendedRouteValidationInterface
{
    public const CONTROLLER_ID = 'borlabs-cookie-library';

    private BorlabsCookieGlobalsService $borlabsCookieGlobalsService;

    private CloudScanSuggestionRepository $cloudScanSuggestionRepository;

    private ContentBlockerRepository $contentBlockerRepository;

    private GlobalLocalizationStrings $globalLocalizationStrings;

    private Language $language;

    private LibraryLocalizationStrings $libraryLocalizationStrings;

    private MessageManager $messageManager;

    private PackageManager $packageManager;

    private PackageRepository $packageRepository;

    private ServiceRepository $serviceRepository;

    private Template $template;

    private WpFunction $wpFunction;

    public function __construct(
        BorlabsCookieGlobalsService $borlabsCookieGlobalsService,
        CloudScanSuggestionRepository $cloudScanSuggestionRepository,
        ContentBlockerRepository $contentBlockerRepository,
        GlobalLocalizationStrings $globalLocalizationStrings,
        Language $language,
        LibraryLocalizationStrings $libraryLocalizationStrings,
        MessageManager $messageManager,
        PackageManager $packageManager,
        PackageRepository $packageRepository,
        ServiceRepository $serviceRepository,
        Template $template,
        WpFunction $wpFunction
    ) {
        $this->borlabsCookieGlobalsService = $borlabsCookieGlobalsService;
        $this->cloudScanSuggestionRepository = $cloudScanSuggestionRepository;
        $this->contentBlockerRepository = $contentBlockerRepository;
        $this->globalLocalizationStrings = $globalLocalizationStrings;
        $this->language = $language;
        $this->libraryLocalizationStrings = $libraryLocalizationStrings;
        $this->messageManager = $messageManager;
        $this->packageManager = $packageManager;
        $this->packageRepository = $packageRepository;
        $this->serviceRepository = $serviceRepository;
        $this->template = $template;
        $this->wpFunction = $wpFunction;
    }

    public function refresh(RequestDto $request): string
    {
        try {
            $this->packageManager->updatePackageList();
            $this->messageManager->success($this->libraryLocalizationStrings::get()['alert']['libraryRefreshedSuccessfully']);
        } catch (TranslatedException $exception) {
            $this->messageManager->error($exception->getTranslatedMessage());
        } catch (GenericException $exception) {
            $this->messageManager->error($exception->getMessage());
        }

        return $this->viewOverview($request);
    }

    public function route(RequestDto $request): ?string
    {
        $id = (int) ($request->postData['id'] ?? $request->getData['id'] ?? -1);
        $suggestionId = $request->postData['suggestionId'] ?? $request->getData['suggestionId'] ?? null;

        if ($suggestionId !== null) {
            $suggestionId = (int) $suggestionId;
        }

        $action = $request->postData['action'] ?? $request->getData['action'] ?? '';

        if ($action === 'details') {
            return $this->viewDetails($id, $request, $suggestionId);
        }

        if ($action === 'refresh') {
            return $this->refresh($request);
        }

        if ($action === 'uninstall') {
            return $this->uninstall($request, $id);
        }

        return $this->viewOverview($request);
    }

    public function uninstall(RequestDto $request, int $id): string
    {
        $package = $this->packageRepository->findById($id);

        if ($package === null) {
            $this->messageManager->error($this->libraryLocalizationStrings->get()['alert']['packageNotFound']);

            return $this->viewOverview($request);
        }

        if ($package->installedAt === null) {
            $this->messageManager->error($this->libraryLocalizationStrings->get()['alert']['packageIsNotInstalled']);

            return $this->viewOverview($request);
        }

        $languages = $this->language->getLanguageList();
        $config = [
            'language' => [],
        ];

        foreach ($languages->list as $language) {
            $config['language'][$language->key] = '1';
        }

        try {
            $uninstallStatusEntries = $this->packageManager->uninstall($package, $config);
            $failed = false;

            foreach ($uninstallStatusEntries as $uninstallStatusEntry) {
                if ($uninstallStatusEntry->status->is(InstallationStatusEnum::fromValue(InstallationStatusEnum::FAILURE))) {
                    $failed = true;

                    if ($uninstallStatusEntry->failureMessage !== null) {
                        $this->messageManager->error($this->libraryLocalizationStrings->get()['alert']['uninstallFailedWithMessage'], [
                            'type' => $uninstallStatusEntry->componentType->getDescription(),
                            'name' => $uninstallStatusEntry->name,
                            'message' => $uninstallStatusEntry->failureMessage,
                        ]);
                    } else {
                        $this->messageManager->error($this->libraryLocalizationStrings->get()['alert']['uninstallFailed'], [
                            'type' => $uninstallStatusEntry->componentType->getDescription(),
                            'name' => $uninstallStatusEntry->name,
                        ]);
                    }
                }
            }

            if (!$failed) {
                $this->messageManager->success($this->libraryLocalizationStrings->get()['alert']['uninstallSuccess'], [
                    'name' => $package->name,
                ]);
            }

            return $this->viewOverview($request);
        } catch (TranslatedException $exception) {
            $this->messageManager->error($exception->getTranslatedMessage());
        } catch (GenericException $exception) {
            $this->messageManager->error($exception->getMessage());
        }

        return $this->viewOverview($request);
    }

    public function validate(RequestDto $request, string $nonce, bool $isValid): bool
    {
        if (isset($request->postData['action'])
            && in_array($request->postData['action'], ['refresh',], true)
            && $this->wpFunction->wpVerifyNonce(self::CONTROLLER_ID . '-' . $request->postData['action'], $nonce)
        ) {
            $isValid = true;
        }

        if (isset($request->getData['action'], $request->getData['id'])
            && in_array($request->getData['action'], ['uninstall'], true)
            && $this->wpFunction->wpVerifyNonce(self::CONTROLLER_ID . '-' . $request->getData['id'] . '-' . $request->getData['action'], $nonce)
        ) {
            $isValid = true;
        }

        return $isValid;
    }

    public function viewDetails(int $id, RequestDto $request, ?int $suggestionId = null): string
    {
        // Check if package exists
        $package = $this->packageRepository->findById($id);

        if ($package === null) {
            $this->messageManager->error(LibraryLocalizationStrings::get()['alert']['packageNotFound']);

            return $this->viewOverview($request);
        }

        $templateData = [];
        $templateData['controllerId'] = self::CONTROLLER_ID;
        $templateData['localized'] = LibraryLocalizationStrings::get();
        $templateData['localized']['showScan'] = CloudScanDetailsLocalizationStrings::get();
        $templateData['localized']['global'] = $this->globalLocalizationStrings::get();
        $templateData['data']['package'] = $package;

        foreach ($templateData['data']['package']->components->contentBlockers->list as &$componentData) {
            $this->updateComponentSettings($componentData, $this->contentBlockerRepository, 'settingsFields');
        }

        foreach ($templateData['data']['package']->components->services->list as &$componentData) {
            $this->updateComponentSettings($componentData, $this->serviceRepository, 'settingsFields');
        }

        $templateData['data']['languages'] = $this->language->getLanguageList();
        $templateData['data']['selectedLanguages'] = array_column($this->language->getLanguageList()->list, 'key', 'key');
        $templateData['data']['componentTypes'] = array_column(ComponentTypeEnum::getAll(), 'description', 'value');
        $this->borlabsCookieGlobalsService->addProperty('componentTypes', array_column(ComponentTypeEnum::getAll(), 'description', 'value'));

        if ($suggestionId !== null) {
            $templateData['data']['suggestion'] = $this->cloudScanSuggestionRepository->findById($suggestionId);
        } else {
            $templateData['data']['suggestion'] = null;
        }

        if ($package->isDeprecated && $package->borlabsServicePackageSuccessorKey !== '') {
            $successorPackage = $this->packageRepository->getByPackageKey($package->borlabsServicePackageSuccessorKey);

            $this->messageManager->error($this->libraryLocalizationStrings->get()['alert']['packageIsDeprecated']);

            if ($successorPackage !== null) {
                $this->messageManager->info($this->libraryLocalizationStrings->get()['alert']['successorPackageAvailable'], [
                    'link' => $this->wpFunction->wpNonceUrl(
                        '?page=borlabs-cookie-library&action=details&id=' . $successorPackage->id,
                        self::CONTROLLER_ID . '-' . $successorPackage->id . '-details',
                    ),
                    'name' => $successorPackage->name,
                ]);
            }
        }

        return $this->template->getEngine()->render('library/details-package.html.twig', $templateData);
    }

    public function viewOverview(RequestDto $request): string
    {
        $postData = Sanitizer::requestData($request->postData);
        $getData = Sanitizer::requestData($request->getData);
        $searchTerm = $postData['searchTerm'] ?? $getData['borlabs-search-term'] ?? null;
        $filter = $postData['filter'] ?? $getData['borlabs-filter'] ?? null;
        $where = [];

        if ($searchTerm) {
            $where[] = new BinaryOperatorExpression(
                new ModelFieldNameExpression('name'),
                'LIKE',
                new ContainsLikeLiteralExpression(new LiteralExpression($searchTerm)),
            );
        }

        if ($filter && ComponentTypeEnum::hasValue($filter)) {
            $where[] = new BinaryOperatorExpression(
                new ModelFieldNameExpression('type'),
                '=',
                new LiteralExpression($filter),
            );
        } elseif ($filter === 'installed-packages') {
            $where[] = new BinaryOperatorExpression(
                new ModelFieldNameExpression('installedAt'),
                'IS NOT',
                new NullExpression(),
            );
        }

        if ($filter !== 'installed-packages') {
            $where[] = new BinaryOperatorExpression(
                new ModelFieldNameExpression('isDeprecated'),
                '=',
                new LiteralExpression('0'),
            );
        }

        $packages = $this->packageRepository->paginate(
            (int) ($getData['borlabs-page'] ?? 1),
            $where,
            [
                'isFeatured' => 'DESC',
                'name' => 'ASC',
            ],
            [],
            24,
            [
                'borlabs-filter' => $filter,
                'borlabs-search-term' => $searchTerm,
            ],
        );

        $templateData = [];
        $templateData['controllerId'] = self::CONTROLLER_ID;
        $templateData['localized'] = LibraryLocalizationStrings::get();
        $templateData['localized']['global'] = $this->globalLocalizationStrings::get();
        $templateData['isUpdateAvailable'] = count($this->packageRepository->getUpdatablePackages()) > 0;
        $templateData['data']['filter'] = $filter;
        $templateData['data']['searchTerm'] = $searchTerm;
        $templateData['data']['packages'] = $packages;
        $templateData['data']['componentTypes'] = array_column(ComponentTypeEnum::getAll(), 'description', 'value');
        $templateData['data']['packageListLastUpdate'] = $this->packageManager->getLastSuccessfulCheckWithApiTimestamp() ? (new DateTime())->setTimestamp($this->packageManager->getLastSuccessfulCheckWithApiTimestamp()) : null;

        return $this->template->getEngine()->render('library/overview-packages.html.twig', $templateData);
    }

    private function updateComponentSettings($componentData, $repository, $property): void
    {
        $components = $repository->getAllByKey($componentData->key);

        if ($components !== null) {
            foreach ($components as $component) {
                // Get the language
                $componentDataSettingsField = Searcher::findObject(
                    $componentData->languageSpecificSetupSettingsFieldsList->list ?? [],
                    'language',
                    $component->language,
                );

                // Check if the language is missing in the package
                if (!isset($componentDataSettingsField)) {
                    // Check if en language settings fields are available
                    $componentDataSettingsField = Searcher::findObject(
                        $componentData->languageSpecificSetupSettingsFieldsList->list ?? [],
                        'language',
                        'en',
                    );

                    if (!isset($componentDataSettingsField)) {
                        continue;
                    }

                    // Clone the english settings fields
                    $newLanguage = clone $componentDataSettingsField;
                    $newLanguage->language = $component->language;
                    $componentData->languageSpecificSetupSettingsFieldsList->add($newLanguage);

                    // Get the reference of the language object
                    $componentDataSettingsField = Searcher::findObject(
                        $componentData->languageSpecificSetupSettingsFieldsList->list ?? [],
                        'language',
                        $component->language,
                    );
                }

                foreach ($componentDataSettingsField->settingsFields->list as &$settingsField) {
                    foreach ($component->{$property}->list as $componentSettingsField) {
                        if ($settingsField->key === $componentSettingsField->key) {
                            $settingsField->value = $componentSettingsField->value;
                        }
                    }
                }
            }
        }
    }
}
