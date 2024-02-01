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

namespace Borlabs\Cookie\Controller\Admin\IabTcf;

use Borlabs\Cookie\Adapter\WpFunction;
use Borlabs\Cookie\Controller\Admin\ControllerInterface;
use Borlabs\Cookie\Controller\Admin\ExtendedRouteValidationInterface;
use Borlabs\Cookie\Dto\System\RequestDto;
use Borlabs\Cookie\Exception\GenericException;
use Borlabs\Cookie\Exception\TranslatedException;
use Borlabs\Cookie\Localization\GlobalLocalizationStrings;
use Borlabs\Cookie\Localization\IabTcf\IabTcfSettingsLocalizationStrings;
use Borlabs\Cookie\Support\Formatter;
use Borlabs\Cookie\Support\Sanitizer;
use Borlabs\Cookie\System\Config\DialogSettingsConfig;
use Borlabs\Cookie\System\Config\IabTcfConfig;
use Borlabs\Cookie\System\Config\WidgetConfig;
use Borlabs\Cookie\System\IabTcf\IabTcfService;
use Borlabs\Cookie\System\Language\Language;
use Borlabs\Cookie\System\Message\MessageManager;
use Borlabs\Cookie\System\Script\ScriptConfigBuilder;
use Borlabs\Cookie\System\Template\Template;
use Borlabs\Cookie\System\ThirdPartyCacheClearer\ThirdPartyCacheClearerManager;

final class IabTcfSettingsController implements ControllerInterface, ExtendedRouteValidationInterface
{
    public const CONTROLLER_ID = 'borlabs-cookie-iab-tcf-settings';

    private DialogSettingsConfig $dialogSettingsConfig;

    private GlobalLocalizationStrings $globalLocalizationStrings;

    private IabTcfConfig $iabTcfConfig;

    private IabTcfService $iabTcfService;

    private IabTcfSettingsLocalizationStrings $iabTcfSettingsLocalizationStrings;

    private Language $language;

    private MessageManager $messageManager;

    private ScriptConfigBuilder $scriptConfigBuilder;

    private Template $template;

    private ThirdPartyCacheClearerManager $thirdPartyCacheClearerManager;

    private WidgetConfig $widgetConfig;

    private WpFunction $wpFunction;

    public function __construct(
        DialogSettingsConfig $dialogSettingsConfig,
        GlobalLocalizationStrings $globalLocalizationStrings,
        IabTcfConfig $iabTcfConfig,
        IabTcfService $iabTcfService,
        IabTcfSettingsLocalizationStrings $iabTcfSettingsLocalizationStrings,
        Language $language,
        MessageManager $messageManager,
        ScriptConfigBuilder $scriptConfigBuilder,
        Template $template,
        ThirdPartyCacheClearerManager $thirdPartyCacheClearerManager,
        WidgetConfig $widgetConfig,
        WpFunction $wpFunction
    ) {
        $this->dialogSettingsConfig = $dialogSettingsConfig;
        $this->globalLocalizationStrings = $globalLocalizationStrings;
        $this->iabTcfConfig = $iabTcfConfig;
        $this->iabTcfService = $iabTcfService;
        $this->iabTcfSettingsLocalizationStrings = $iabTcfSettingsLocalizationStrings;
        $this->language = $language;
        $this->messageManager = $messageManager;
        $this->scriptConfigBuilder = $scriptConfigBuilder;
        $this->template = $template;
        $this->thirdPartyCacheClearerManager = $thirdPartyCacheClearerManager;
        $this->widgetConfig = $widgetConfig;
        $this->wpFunction = $wpFunction;
    }

    public function downloadGvl(): string
    {
        try {
            $this->iabTcfService->updateGlobalVendorListFile();
            $this->iabTcfService->updatePurposeTranslationFiles();
            $this->iabTcfService->updateVendors();

            $this->messageManager->success(
                $this->iabTcfSettingsLocalizationStrings::get()['alert']['downloadGvlSuccessfully'],
            );
        } catch (TranslatedException $exception) {
            $this->messageManager->error($exception->getTranslatedMessage());
        } catch (GenericException $exception) {
            $this->messageManager->error($exception->getMessage());
        }

        return $this->viewOverview();
    }

    public function route(RequestDto $request): ?string
    {
        $action = $request->postData['action'] ?? $request->getData['action'] ?? '';

        if ($action === 'downloadGvl') {
            return $this->downloadGvl();
        }

        if ($action === 'save') {
            return $this->save($request->postData);
        }

        return $this->viewOverview();
    }

    public function save(array $postData): string
    {
        $iabTcfConfig = $this->iabTcfConfig->get();
        $iabTcfConfig->hostnamesForConsentAddition = Sanitizer::hostList($postData['hostnamesForConsentAddition'] ?? []);
        $iabTcfConfig->iabTcfStatus = (bool) ($postData['iabTcfStatus'] ?? false);
        $iabTcfConfig->compactLayout = (bool) ($postData['compactLayout'] ?? false);

        // Save config for other languages
        $languages = array_keys(
            array_filter(
                $postData['languages']['configuration'] ?? [],
                fn ($checked) => $checked === '1',
            ),
        );

        foreach ($languages as $languageCode) {
            $this->iabTcfConfig->save($iabTcfConfig, $languageCode);

            if ($iabTcfConfig->iabTcfStatus) {
                $this->enableShowBorlabsCookieBranding($languageCode);
                $this->enableShowWidgetSetting($languageCode);
            }

            $this->scriptConfigBuilder->updateJavaScriptConfigFileAndIncrementConfigVersion(
                $languageCode,
            );
        }

        // Save config for this language. The save routine also updates the current language object.
        $this->iabTcfConfig->save($iabTcfConfig, $this->language->getSelectedLanguageCode());

        if ($iabTcfConfig->iabTcfStatus) {
            try {
                $this->iabTcfService->updateGlobalVendorListFile();
                $this->iabTcfService->updatePurposeTranslationFiles();
                $this->iabTcfService->updateVendors();
                $this->enableShowBorlabsCookieBranding($this->language->getSelectedLanguageCode());
                $this->enableShowWidgetSetting($this->language->getSelectedLanguageCode());
            } catch (TranslatedException $exception) {
                $this->messageManager->error($exception->getTranslatedMessage());
            } catch (GenericException $exception) {
                $this->messageManager->error($exception->getMessage());
            }
        }

        $this->scriptConfigBuilder->updateJavaScriptConfigFileAndIncrementConfigVersion(
            $this->language->getSelectedLanguageCode(),
        );
        $this->thirdPartyCacheClearerManager->clearCache();

        $this->messageManager->success($this->globalLocalizationStrings::get()['alert']['savedSuccessfully']);

        return $this->viewOverview();
    }

    public function validate(RequestDto $request, string $nonce, bool $isValid): bool
    {
        /*
         * The button to download the Global Vendor List (GVL) is in the same form as the settings,
         * so it shares the nonce of the "save" action.
         */
        if (in_array($request->postData['action'] ?? '', ['downloadGvl'], true)
            && $this->wpFunction->wpVerifyNonce(self::CONTROLLER_ID . '-save', $nonce)
        ) {
            $isValid = true;
        }

        return $isValid;
    }

    public function viewOverview(): string
    {
        $templateData = [];
        $templateData['controllerId'] = self::CONTROLLER_ID;
        $templateData['localized'] = $this->iabTcfSettingsLocalizationStrings::get();
        $templateData['localized']['global'] = $this->globalLocalizationStrings::get();
        $templateData['languages'] = $this->language->getLanguageList();
        $templateData['data'] = (array) $this->iabTcfConfig->get();
        $templateData['data']['gvlImported'] = $this->iabTcfService->isGlobalVendorListDownloaded();
        $lastSuccessfulCheckWithApiTimestamp = $this->iabTcfService->getLastSuccessfulCheckWithApiTimestamp();
        $templateData['data']['gvlLastSuccessfulCheckWithApiFormattedTime']
            = $lastSuccessfulCheckWithApiTimestamp === null
            ? '-' : Formatter::timestamp($lastSuccessfulCheckWithApiTimestamp);
        $thirdPartyHostnamesForConsentAddition = $this->wpFunction->applyFilter(
            'borlabsCookie/scriptBuilder/iabTcf/modifyHostnamesForConsentAddition',
            [],
        );
        $templateData['data']['thirdPartyHostnamesForConsentAddition'] = Sanitizer::hostArray($thirdPartyHostnamesForConsentAddition);

        return $this->template->getEngine()->render(
            'iab-tcf/iab-tcf-settings/iab-tcf-settings.html.twig',
            $templateData,
        );
    }

    private function enableShowBorlabsCookieBranding(string $languageCode)
    {
        // When utilizing the IAB TCF, enabling the Borlabs Cookie branding including the CMP id is mandatory as it's part of the requirements.
        $dialogSettingsConfig = $this->dialogSettingsConfig->get();
        $dialogSettingsConfig->showBorlabsCookieBranding = true;
        $this->dialogSettingsConfig->save($dialogSettingsConfig, $languageCode);
    }

    private function enableShowWidgetSetting(string $languageCode)
    {
        // When utilizing the IAB TCF, enabling the widget is mandatory as it's part of the requirements.
        $widgetConfig = $this->widgetConfig->get();
        $widgetConfig->show = true;
        $this->widgetConfig->save($widgetConfig, $languageCode);
    }
}
