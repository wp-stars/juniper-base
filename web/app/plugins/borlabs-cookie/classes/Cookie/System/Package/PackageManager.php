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

namespace Borlabs\Cookie\System\Package;

use Borlabs\Cookie\Adapter\WpFunction;
use Borlabs\Cookie\ApiClient\PackageApiClient;
use Borlabs\Cookie\ApiClient\Transformer\PackageTransformer;
use Borlabs\Cookie\Dto\Package\InstallationStatusDto;
use Borlabs\Cookie\DtoList\Package\InstallationStatusDtoList;
use Borlabs\Cookie\Enum\Package\InstallationStatusEnum;
use Borlabs\Cookie\Exception\System\LicenseExpiredException;
use Borlabs\Cookie\Model\Package\PackageModel;
use Borlabs\Cookie\Repository\Package\PackageRepository;
use Borlabs\Cookie\System\License\License;
use Borlabs\Cookie\System\Log\Log;
use Borlabs\Cookie\System\Option\Option;
use Borlabs\Cookie\System\Package\PackageManagerComponent\CompatibilityPatchComponent;
use Borlabs\Cookie\System\Package\PackageManagerComponent\ContentBlockerComponent;
use Borlabs\Cookie\System\Package\PackageManagerComponent\ProviderComponent;
use Borlabs\Cookie\System\Package\PackageManagerComponent\ScriptBlockerComponent;
use Borlabs\Cookie\System\Package\PackageManagerComponent\ServiceComponent;
use Borlabs\Cookie\System\Package\PackageManagerComponent\StyleBlockerComponent;
use Borlabs\Cookie\System\Script\ScriptConfigBuilder;
use Borlabs\Cookie\System\Style\StyleBuilder;
use Borlabs\Cookie\System\ThirdPartyCacheClearer\ThirdPartyCacheClearerManager;
use DateTime;

class PackageManager
{
    private CompatibilityPatchComponent $compatibilityPatchComponent;

    private ContentBlockerComponent $contentBlockerComponent;

    private License $license;

    private Log $log;

    private Option $option;

    private PackageApiClient $packageApiClient;

    private PackageRepository $packageRepository;

    private PackageTransformer $packageTransformer;

    private ProviderComponent $providerComponent;

    private ScriptBlockerComponent $scriptBlockerComponent;

    private ScriptConfigBuilder $scriptConfigBuilder;

    private ServiceComponent $serviceComponent;

    private StyleBlockerComponent $styleBlockerComponent;

    private StyleBuilder $styleBuilder;

    private ThirdPartyCacheClearerManager $thirdPartyCacheClearerManager;

    private WpFunction $wpFunction;

    public function __construct(
        CompatibilityPatchComponent $compatibilityPatchComponent,
        ContentBlockerComponent $contentBlockerComponent,
        License $license,
        Log $log,
        Option $option,
        PackageApiClient $packageApiClient,
        PackageRepository $packageRepository,
        PackageTransformer $packageTransformer,
        ProviderComponent $providerComponent,
        ScriptBlockerComponent $scriptBlockerComponent,
        ScriptConfigBuilder $scriptConfigBuilder,
        ServiceComponent $serviceComponent,
        StyleBlockerComponent $styleBlockerComponent,
        StyleBuilder $styleBuilder,
        ThirdPartyCacheClearerManager $thirdPartyCacheClearerManager,
        WpFunction $wpFunction
    ) {
        $this->compatibilityPatchComponent = $compatibilityPatchComponent;
        $this->contentBlockerComponent = $contentBlockerComponent;
        $this->license = $license;
        $this->log = $log;
        $this->option = $option;
        $this->packageApiClient = $packageApiClient;
        $this->packageRepository = $packageRepository;
        $this->packageTransformer = $packageTransformer;
        $this->providerComponent = $providerComponent;
        $this->scriptBlockerComponent = $scriptBlockerComponent;
        $this->scriptConfigBuilder = $scriptConfigBuilder;
        $this->serviceComponent = $serviceComponent;
        $this->styleBlockerComponent = $styleBlockerComponent;
        $this->styleBuilder = $styleBuilder;
        $this->thirdPartyCacheClearerManager = $thirdPartyCacheClearerManager;
        $this->wpFunction = $wpFunction;
    }

    public function getLastSuccessfulCheckWithApiTimestamp(): int
    {
        return (int) $this->option->get('PackageListLastUpdate', null)->value;
    }

    public function getPackageUpdateCount(): int
    {
        return count($this->packageRepository->getUpdatablePackages());
    }

    /**
     * @throws \Borlabs\Cookie\Exception\ApiClient\PackageApiClientException
     * @throws \Borlabs\Cookie\Exception\UnexpectedRepositoryOperationException
     */
    public function install(PackageModel $localPackage, ?array $componentSettings = null): ?InstallationStatusDtoList
    {
        if (!$this->license->isLicenseValid()) {
            throw new LicenseExpiredException('licenseExpiredFeatureNotAvailable');
        }

        // Get the package data
        $borlabsServiceInstallationPackage = $this->packageApiClient->requestPackage($localPackage->borlabsServicePackageKey);
        $languages = [];

        if (isset($componentSettings['language'])) {
            $languages = array_keys($componentSettings['language'], '1', true);
        }

        if (
            (
                count($borlabsServiceInstallationPackage->data->components->contentBlockers)
                || count($borlabsServiceInstallationPackage->data->components->services)
            )
            && count($languages) === 0
        ) {
            $this->log->error(
                'No language selected but package contains content blocker or service.',
                ['packageId' => $localPackage->id, 'componentSettings' => $componentSettings],
            );

            return null;
        }

        $installationStatusList = new InstallationStatusDtoList(null);
        $notInstalledPackages = $this->packageRepository->getNotInstalledPackages();

        foreach ($languages as $languageCode) {
            $this->handleComponentContentBlockers(
                $borlabsServiceInstallationPackage->data->components->contentBlockers,
                $borlabsServiceInstallationPackage->data->key,
                $languageCode,
                $componentSettings['settingsForLanguage'][$languageCode]['contentBlocker'] ?? null,
                $localPackage,
                $notInstalledPackages,
                $installationStatusList,
            );

            $this->handleComponentServices(
                $borlabsServiceInstallationPackage->data->components->services,
                $borlabsServiceInstallationPackage->data->key,
                $languageCode,
                $componentSettings['settingsForLanguage'][$languageCode]['service'] ?? null,
                $localPackage,
                $notInstalledPackages,
                $installationStatusList,
            );

            // Set Service association for Content Blocker
            foreach ($borlabsServiceInstallationPackage->data->components->contentBlockers as $contentBlocker) {
                $this->contentBlockerComponent->setServiceAssociationIfRequired($contentBlocker, $languageCode);
            }

            $this->scriptConfigBuilder->updateJavaScriptConfigFileAndIncrementConfigVersion(
                $languageCode,
            );
            $this->styleBuilder->updateCssFileAndIncrementStyleVersion(
                $this->wpFunction->getCurrentBlogId(),
                $languageCode,
            );
        }

        $this->handleComponentCompatibilityPatches(
            $borlabsServiceInstallationPackage->data->components->compatibilityPatches,
            $borlabsServiceInstallationPackage->data->key,
            $localPackage,
            $notInstalledPackages,
            $installationStatusList,
        );

        $this->handleComponentScriptBlockers(
            $borlabsServiceInstallationPackage->data->components->scriptBlockers,
            $borlabsServiceInstallationPackage->data->key,
            $localPackage,
            $notInstalledPackages,
            $installationStatusList,
        );

        $this->handleComponentStyleBlockers(
            $borlabsServiceInstallationPackage->data->components->styleBlockers,
            $borlabsServiceInstallationPackage->data->key,
            $localPackage,
            $notInstalledPackages,
            $installationStatusList,
        );

        if ($localPackage->installedAt === null) {
            $localPackage->installedAt = new DateTime();
        }

        $localPackage->borlabsServicePackageVersion = $this->packageTransformer->transformToVersionNumberDto($borlabsServiceInstallationPackage->data->version);
        $localPackage->updatedAt = new DateTime();
        $localPackage->version = $this->packageTransformer->transformToVersionNumberDto($borlabsServiceInstallationPackage->data->version);
        $this->packageRepository->update($localPackage);

        if (count($installationStatusList->list) === 0) {
            $this->log->error(
                'No components installed.',
                [
                    'packageId' => $localPackage->id,
                    'componentSettings' => $componentSettings, ],
            );
        }

        $this->thirdPartyCacheClearerManager->clearCache();

        return $installationStatusList;
    }

    /**
     * Currently uninstall only works for all languages.
     *
     * @throws \Borlabs\Cookie\Exception\UnexpectedRepositoryOperationException
     *
     * @return InstallationStatusDto[]
     */
    public function uninstall(PackageModel $package, ?array $componentSettings = null): array
    {
        $package = $this->packageRepository->findById($package->id, [
            'contentBlockers',
            'scriptBlockers',
            'services',
            'styleBlockers',
            'compatibilityPatches',
            'providers',
        ]);
        $notInstalledPackageses = $this->packageRepository->getNotInstalledPackages();

        $languages = array_keys($componentSettings['language'], '1', true);

        /** @var array<int, InstallationStatusDto> $uninstallationStatusList */
        $uninstallationStatusList = [];

        foreach ($languages as $languageCode) {
            // Uninstall content blockers
            foreach ($package->contentBlockers as $contentBlocker) {
                if ($contentBlocker->language === $languageCode) {
                    foreach ($this->contentBlockerComponent->uninstall($package, $contentBlocker, $notInstalledPackageses, false) as $uninstallationStatusListItem) {
                        $uninstallationStatusList[] = $uninstallationStatusListItem;
                    }
                }
            }

            // Uninstall services
            foreach ($package->services as $service) {
                if ($service->language === $languageCode) {
                    foreach ($this->serviceComponent->uninstall($package, $service, $notInstalledPackageses, false) as $uninstallationStatusListItem) {
                        $uninstallationStatusList[] = $uninstallationStatusListItem;
                    }
                }
            }

            // Uninstall providers
            foreach ($package->providers as $provider) {
                if ($provider->language === $languageCode) {
                    $uninstallationStatusList[] = $this->providerComponent->uninstall($package, $provider->id);
                }
            }
        }

        // Uninstall script blockers
        foreach ($package->scriptBlockers as $scriptBlocker) {
            $uninstallationStatusList[] = $this->scriptBlockerComponent->uninstall(
                $package,
                $scriptBlocker,
                $notInstalledPackageses,
            );
        }

        // Uninstall style blockers
        foreach ($package->styleBlockers as $styleBlocker) {
            $uninstallationStatusList[] = $this->styleBlockerComponent->uninstall(
                $package,
                $styleBlocker,
                $notInstalledPackageses,
            );
        }

        // Uninstall content blockers
        foreach ($package->compatibilityPatches as $compatibilityPatch) {
            $uninstallationStatusList[] = $this->compatibilityPatchComponent->uninstall(
                $package,
                $compatibilityPatch,
                $notInstalledPackageses,
            );
        }

        $failed = false;

        foreach ($uninstallationStatusList as $uninstallationStatusListItem) {
            if ($uninstallationStatusListItem->status->is(InstallationStatusEnum::FAILURE())) {
                $failed = true;
            }
        }

        // Update JavaScript config and CSS files
        foreach ($languages as $languageCode) {
            $this->scriptConfigBuilder->updateJavaScriptConfigFileAndIncrementConfigVersion(
                $languageCode,
            );
            $this->styleBuilder->updateCssFileAndIncrementStyleVersion(
                $this->wpFunction->getCurrentBlogId(),
                $languageCode,
            );
        }

        $this->thirdPartyCacheClearerManager->clearCache();

        if (!$failed) {
            $package->installedAt = null;
            $package->updatedAt = new DateTime();
            $this->packageRepository->update($package);
        }

        return $uninstallationStatusList;
    }

    /**
     * @throws \Borlabs\Cookie\Exception\ApiClient\PackageApiClientException
     * @throws \Borlabs\Cookie\Exception\UnexpectedRepositoryOperationException
     */
    public function updatePackageList(): void
    {
        $this->packageRepository->removeNotInstalledPackages();
        $packages = $this->packageApiClient->requestPackages();

        foreach ($packages->list as $packageDto) {
            $package = $this->packageRepository->getByPackageKey($packageDto->key);

            if ($package !== null) {
                $packageDto->packageModel->id = $package->id;
                $packageDto->packageModel->installedAt = $package->installedAt ?? null;
                $packageDto->packageModel->updatedAt = $package->updatedAt ?? null;
                $packageDto->packageModel->version = $package->version;
                $this->packageRepository->update($packageDto->packageModel);
            } else {
                $this->packageRepository->insert($packageDto->packageModel);
            }
        }

        $this->option->set('PackageListLastUpdate', (string) time());
    }

    private function handleComponentCompatibilityPatches(
        array $borlabsServiceComponentCompatibilityPatches,
        string $borlabsServicePackageKey,
        PackageModel $localPackage,
        array $notInstalledPackages,
        InstallationStatusDtoList &$installationStatusList
    ) {
        // Install or update compatibility patch
        foreach ($borlabsServiceComponentCompatibilityPatches as $compatibilityPatch) {
            $installationStatusList->add(
                $this->compatibilityPatchComponent->install(
                    $compatibilityPatch,
                    $borlabsServicePackageKey,
                ),
            );
        }

        // Uninstall compatibility patch that are no longer part of the package
        foreach ($localPackage->compatibilityPatches as $localPackageCompatibilityPatch) {
            $canBeUninstalled = true;

            foreach ($borlabsServiceComponentCompatibilityPatches as $compatibilityPatch) {
                if ($localPackageCompatibilityPatch->key === $compatibilityPatch->key) {
                    $canBeUninstalled = false;

                    break;
                }
            }

            if ($canBeUninstalled) {
                $installationStatusList->add(
                    $this->compatibilityPatchComponent->uninstall(
                        $localPackage,
                        $localPackageCompatibilityPatch,
                        $notInstalledPackages,
                    ),
                );
            }
        }
    }

    private function handleComponentContentBlockers(
        array $borlabsServiceComponentContentBlockers,
        string $borlabsServicePackageKey,
        string $languageCode,
        ?array $componentSettings,
        PackageModel $localPackage,
        array $notInstalledPackages,
        InstallationStatusDtoList &$installationStatusList
    ) {
        // Install or update content blocker
        foreach ($borlabsServiceComponentContentBlockers as $contentBlocker) {
            $installationStatusList->add(
                $this->contentBlockerComponent->install(
                    $contentBlocker,
                    $borlabsServicePackageKey,
                    $languageCode,
                    $componentSettings[$contentBlocker->key] ?? null,
                ),
            );
        }

        // Uninstall content blocker that are no longer part of the package
        foreach ($localPackage->contentBlockers as $localPackageContentBlocker) {
            $canBeUninstalled = true;

            foreach ($borlabsServiceComponentContentBlockers as $contentBlocker) {
                if ($localPackageContentBlocker->key === $contentBlocker->key) {
                    $canBeUninstalled = false;

                    break;
                }
            }

            if ($canBeUninstalled) {
                $statusEntries = $this->contentBlockerComponent->uninstall(
                    $localPackage,
                    $localPackageContentBlocker,
                    $notInstalledPackages,
                    true,
                );

                foreach ($statusEntries as $statusEntry) {
                    $installationStatusList->add($statusEntry);
                }
            }
        }
    }

    private function handleComponentScriptBlockers(
        array $borlabsServiceComponentScriptBlockers,
        string $borlabsServicePackageKey,
        PackageModel $localPackage,
        array $notInstalledPackages,
        InstallationStatusDtoList &$installationStatusList
    ) {
        // Install or update script blocker
        foreach ($borlabsServiceComponentScriptBlockers as $scriptBlocker) {
            $installationStatusList->add(
                $this->scriptBlockerComponent->install(
                    $scriptBlocker,
                    $borlabsServicePackageKey,
                ),
            );
        }

        // Uninstall script blockers that are no longer part of the package
        foreach ($localPackage->scriptBlockers as $localPackageScriptBlocker) {
            $canBeUninstalled = true;

            foreach ($borlabsServiceComponentScriptBlockers as $scriptBlocker) {
                if ($localPackageScriptBlocker->key === $scriptBlocker->key) {
                    $canBeUninstalled = false;

                    break;
                }
            }

            if ($canBeUninstalled) {
                $installationStatusList->add(
                    $this->scriptBlockerComponent->uninstall(
                        $localPackage,
                        $localPackageScriptBlocker,
                        $notInstalledPackages,
                    ),
                );
            }
        }
    }

    private function handleComponentServices(
        array $borlabsServiceComponentServices,
        string $borlabsServicePackageKey,
        string $languageCode,
        ?array $componentSettings,
        PackageModel $localPackage,
        array $notInstalledPackages,
        InstallationStatusDtoList &$installationStatusList
    ) {
        // Install or update service
        foreach ($borlabsServiceComponentServices as $service) {
            $installationStatusList->add(
                $this->serviceComponent->install(
                    $service,
                    $borlabsServicePackageKey,
                    $languageCode,
                    $componentSettings[$service->key] ?? null,
                ),
            );
        }

        // Uninstall service that are no longer part of the package
        foreach ($localPackage->services as $localPackageService) {
            $canBeUninstalled = true;

            foreach ($borlabsServiceComponentServices as $service) {
                if ($localPackageService->key === $service->key) {
                    $canBeUninstalled = false;

                    break;
                }
            }

            if ($canBeUninstalled) {
                $statusEntries = $this->serviceComponent->uninstall(
                    $localPackage,
                    $localPackageService,
                    $notInstalledPackages,
                    true,
                );

                foreach ($statusEntries as $statusEntry) {
                    $installationStatusList->add($statusEntry);
                }
            }
        }
    }

    private function handleComponentStyleBlockers(
        array $borlabsServiceComponentStyleBlockers,
        string $borlabsServicePackageKey,
        PackageModel $localPackage,
        array $notInstalledPackages,
        InstallationStatusDtoList &$installationStatusList
    ) {
        // Install or update style blocker
        foreach ($borlabsServiceComponentStyleBlockers as $styleBlocker) {
            $installationStatusList->add(
                $this->styleBlockerComponent->install(
                    $styleBlocker,
                    $borlabsServicePackageKey,
                ),
            );
        }

        // Uninstall style blockers that are no longer part of the package
        foreach ($localPackage->styleBlockers as $localPackageStyleBlocker) {
            $canBeUninstalled = true;

            foreach ($borlabsServiceComponentStyleBlockers as $styleBlocker) {
                if ($localPackageStyleBlocker->key === $styleBlocker->key) {
                    $canBeUninstalled = false;

                    break;
                }
            }

            if ($canBeUninstalled) {
                $installationStatusList->add(
                    $this->styleBlockerComponent->uninstall(
                        $localPackage,
                        $localPackageStyleBlocker,
                        $notInstalledPackages,
                    ),
                );
            }
        }
    }
}
