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

namespace Borlabs\Cookie\System\Installer\ServiceGroup;

use Borlabs\Cookie\Adapter\WpDb;
use Borlabs\Cookie\Dto\System\AuditDto;
use Borlabs\Cookie\Repository\ServiceGroup\ServiceGroupRepository;
use Borlabs\Cookie\System\Language\Language;

final class ServiceGroupSeeder
{
    private Language $language;

    private ServiceGroupDefaultEntries $serviceGroupDefaultEntries;

    private ServiceGroupRepository $serviceGroupRepository;

    private WpDb $wpdb;

    public function __construct(
        Language $language,
        ServiceGroupDefaultEntries $serviceGroupDefaultEntries,
        ServiceGroupRepository $serviceGroupRepository,
        WpDb $wpdb
    ) {
        $this->language = $language;
        $this->serviceGroupDefaultEntries = $serviceGroupDefaultEntries;
        $this->serviceGroupRepository = $serviceGroupRepository;
        $this->wpdb = $wpdb;
    }

    public function run(string $prefix = ''): AuditDto
    {
        if (empty($prefix)) {
            $prefix = $this->wpdb->prefix;
        }

        // Sets the prefix to be used by the repository manager.
        $this->serviceGroupRepository->overwriteTablePrefix($prefix);
        $language = $this->language->getSelectedLanguageCode();
        $defaultServiceGroups = $this->serviceGroupDefaultEntries->getDefaultEntries();

        foreach ($defaultServiceGroups as $model) {
            // Test if service group exists
            $result = $this->serviceGroupRepository->find([
                'key' => $model->key,
                'language' => $language,
            ]);

            if (!empty($result[0]->id)) {
                continue;
            }

            // Try to add
            $newModel = $this->serviceGroupRepository->insert($model);

            if (empty($newModel)) {
                // Reset prefix
                $this->serviceGroupRepository->overwriteTablePrefix();

                return new AuditDto(false);
            }
        }

        // Reset prefix
        $this->serviceGroupRepository->overwriteTablePrefix();

        return new AuditDto(true);
    }
}
