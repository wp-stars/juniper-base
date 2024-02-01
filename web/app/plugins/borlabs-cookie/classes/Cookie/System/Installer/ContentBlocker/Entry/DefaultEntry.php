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

namespace Borlabs\Cookie\System\Installer\ContentBlocker\Entry;

use Borlabs\Cookie\Dto\System\KeyValueDto;
use Borlabs\Cookie\DtoList\System\KeyValueDtoList;
use Borlabs\Cookie\Localization\DefaultLocalizationStrings;
use Borlabs\Cookie\Model\ContentBlocker\ContentBlockerModel;
use Borlabs\Cookie\Repository\Provider\ProviderRepository;
use Borlabs\Cookie\System\ContentBlocker\ContentBlockerDefaultSettingsFieldManager;
use Borlabs\Cookie\System\Installer\DefaultEntryInterface;
use Borlabs\Cookie\System\Language\Language;

/**
 * Class DefaultEntry.
 */
final class DefaultEntry implements DefaultEntryInterface
{
    private ContentBlockerDefaultSettingsFieldManager $contentBlockerDefaultSettingsFields;

    private DefaultLocalizationStrings $defaultLocalizationStrings;

    private Language $language;

    private ProviderRepository $providerRepository;

    public function __construct(
        ContentBlockerDefaultSettingsFieldManager $contentBlockerDefaultSettingsFields,
        DefaultLocalizationStrings $defaultLocalizationStrings,
        Language $language,
        ProviderRepository $providerRepository
    ) {
        $this->contentBlockerDefaultSettingsFields = $contentBlockerDefaultSettingsFields;
        $this->defaultLocalizationStrings = $defaultLocalizationStrings;
        $this->language = $language;
        $this->providerRepository = $providerRepository;
    }

    public function getDefaultModel(): ContentBlockerModel
    {
        $provider = $this->providerRepository->getByBorlabsServiceProviderKey('unknown');
        $model = new ContentBlockerModel();
        $model->description = $this->defaultLocalizationStrings->get()['contentBlocker']['defaultDescription'];
        $model->key = 'default';
        $model->language = $this->language->getSelectedLanguageCode();
        $model->name = $this->defaultLocalizationStrings->get()['contentBlocker']['defaultName'];
        $model->previewHtml = <<<EOT
<div class="brlbs-cmpnt-cb-preset-a">
    <p class="brlbs-cmpnt-cb-description">{{ description }}</p>
    <div class="brlbs-cmpnt-cb-buttons">
        <a class="brlbs-cmpnt-cb-btn" href="#" data-borlabs-cookie-unblock role="button">{{ unblockButton }}</a>
        <a class="brlbs-cmpnt-cb-btn" href="#" data-borlabs-cookie-accept-service role="button" style="display: {{ serviceConsentButtonDisplayValue }}">{{ acceptServiceUnblockContent }}</a>
    </div>
    <a class="brlbs-cmpnt-cb-provider-toggle" href="#" data-borlabs-cookie-show-provider-information role="button">{{ moreInformation }}</a>
</div>
EOT;
        $model->languageStrings = new KeyValueDtoList([
            new KeyValueDto('acceptServiceUnblockContent', $this->defaultLocalizationStrings->get()['contentBlocker']['acceptServiceUnblockContent']),
            new KeyValueDto('description', $this->defaultLocalizationStrings->get()['contentBlocker']['description']),
            new KeyValueDto('moreInformation', $this->defaultLocalizationStrings->get()['contentBlocker']['moreInformation']),
            new KeyValueDto('unblockButton', $this->defaultLocalizationStrings->get()['contentBlocker']['unblockButton']),
        ]);
        $model->providerId = $provider->id;
        $model->settingsFields = $this->contentBlockerDefaultSettingsFields->get(
            $this->language->getSelectedLanguageCode(),
        );
        $model->status = true;
        $model->undeletable = true;

        return $model;
    }
}
