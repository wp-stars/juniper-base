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

namespace Borlabs\Cookie\System\Installer\ContentBlocker;

use Borlabs\Cookie\Adapter\WpDb;
use Borlabs\Cookie\Dto\System\AuditDto;
use Borlabs\Cookie\Repository\ContentBlocker\ContentBlockerRepository;
use Borlabs\Cookie\System\Language\Language;

final class ContentBlockerSeeder
{
    private ContentBlockerDefaultEntries $contentBlockerDefaultEntries;

    private ContentBlockerRepository $contentBlockerRepository;

    private Language $language;

    private WpDb $wpdb;

    public function __construct(
        ContentBlockerDefaultEntries $contentBlockerDefaultEntries,
        ContentBlockerRepository $contentBlockerRepository,
        Language $language,
        WpDb $wpdb
    ) {
        $this->contentBlockerDefaultEntries = $contentBlockerDefaultEntries;
        $this->contentBlockerRepository = $contentBlockerRepository;
        $this->language = $language;
        $this->wpdb = $wpdb;
    }

    public function run(string $prefix = ''): AuditDto
    {
        if (empty($prefix)) {
            $prefix = $this->wpdb->prefix;
        }

        // Sets the prefix to be used by the repository manager.
        $this->contentBlockerRepository->overwriteTablePrefix($prefix);

        $language = $this->language->getSelectedLanguageCode();
        $defaultContentBlocker = $this->contentBlockerDefaultEntries->getDefaultEntries();

        foreach ($defaultContentBlocker as $model) {
            // Test if content blocker exists
            $result = $this->contentBlockerRepository->find([
                'key' => $model->key,
                'language' => $language,
            ]);

            if (!empty($result[0]->id)) {
                continue;
            }

            // Try to add
            $newModel = $this->contentBlockerRepository->insert($model);

            if (empty($newModel)) {
                return new AuditDto(false);
            }
        }

        // Reset prefix
        $this->contentBlockerRepository->overwriteTablePrefix();

        return new AuditDto(true);
    }
}
