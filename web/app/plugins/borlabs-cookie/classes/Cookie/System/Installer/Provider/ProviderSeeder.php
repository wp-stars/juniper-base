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

namespace Borlabs\Cookie\System\Installer\Provider;

use Borlabs\Cookie\Adapter\WpDb;
use Borlabs\Cookie\Dto\System\AuditDto;
use Borlabs\Cookie\Repository\Provider\ProviderRepository;
use Borlabs\Cookie\System\Language\Language;

final class ProviderSeeder
{
    private Language $language;

    private ProviderDefaultEntries $providerDefaultEntries;

    private ProviderRepository $providerRepository;

    private WpDb $wpdb;

    public function __construct(
        Language $language,
        ProviderDefaultEntries $providerDefaultEntries,
        ProviderRepository $providerRepository,
        WpDb $wpdb
    ) {
        $this->language = $language;
        $this->providerDefaultEntries = $providerDefaultEntries;
        $this->providerRepository = $providerRepository;
        $this->wpdb = $wpdb;
    }

    public function run(string $prefix = ''): AuditDto
    {
        if (empty($prefix)) {
            $prefix = $this->wpdb->prefix;
        }

        // Sets the prefix to be used by the repository manager.
        $this->providerRepository->overwriteTablePrefix($prefix);
        $language = $this->language->getSelectedLanguageCode();
        $defaultProviders = $this->providerDefaultEntries->getDefaultEntries();

        foreach ($defaultProviders as $model) {
            // Test if provider exists
            $result = $this->providerRepository->find([
                'borlabsServiceProviderKey' => $model->borlabsServiceProviderKey,
                'language' => $language,
            ]);

            if (!empty($result[0]->id)) {
                continue;
            }

            // Try to add
            $newModel = $this->providerRepository->insert($model);

            if (empty($newModel)) {
                // Reset prefix
                $this->providerRepository->overwriteTablePrefix();

                return new AuditDto(false);
            }
        }

        // Reset prefix
        $this->providerRepository->overwriteTablePrefix();

        return new AuditDto(true);
    }
}
