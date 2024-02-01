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

namespace Borlabs\Cookie\Controller\Admin\Dashboard;

use Borlabs\Cookie\Adapter\WpFunction;
use Borlabs\Cookie\Controller\Admin\ControllerInterface;
use Borlabs\Cookie\Controller\Admin\ExtendedRouteValidationInterface;
use Borlabs\Cookie\Dto\System\RequestDto;
use Borlabs\Cookie\Enum\System\AutomaticUpdateEnum;
use Borlabs\Cookie\Localization\Dashboard\DashboardLocalizationStrings;
use Borlabs\Cookie\Localization\GlobalLocalizationStrings;
use Borlabs\Cookie\Support\Formatter;
use Borlabs\Cookie\System\Config\PluginConfig;
use Borlabs\Cookie\System\Dashboard\ChartDataService;
use Borlabs\Cookie\System\Dashboard\NewsService;
use Borlabs\Cookie\System\Language\Language;
use Borlabs\Cookie\System\License\License;
use Borlabs\Cookie\System\Message\MessageManager;
use Borlabs\Cookie\System\Option\Option;
use Borlabs\Cookie\System\SystemCheck\SystemCheck;
use Borlabs\Cookie\System\Telemetry\TelemetryService;
use Borlabs\Cookie\System\Template\Template;
use Borlabs\Cookie\System\Updater\Updater;

/**
 * Class DashboardController.
 */
final class DashboardController implements ControllerInterface, ExtendedRouteValidationInterface
{
    public const CONTROLLER_ID = 'borlabs-cookie';

    private ChartDataService $chartDataService;

    private GlobalLocalizationStrings $globalLocalizationStrings;

    private Language $language;

    private License $license;

    private MessageManager $messageManager;

    private NewsService $newsService;

    private Option $option;

    private PluginConfig $pluginConfig;

    private SystemCheck $systemCheck;

    private TelemetryService $telemetryService;

    private Template $template;

    private Updater $updater;

    private WpFunction $wpFunction;

    public function __construct(
        ChartDataService $chartDataService,
        GlobalLocalizationStrings $globalLocalizationStrings,
        Language $language,
        License $license,
        MessageManager $messageManager,
        NewsService $newsService,
        Option $option,
        PluginConfig $pluginConfig,
        SystemCheck $systemCheck,
        TelemetryService $telemetryService,
        Template $template,
        Updater $updater,
        WpFunction $wpFunction
    ) {
        $this->chartDataService = $chartDataService;
        $this->globalLocalizationStrings = $globalLocalizationStrings;
        $this->language = $language;
        $this->license = $license;
        $this->messageManager = $messageManager;
        $this->newsService = $newsService;
        $this->option = $option;
        $this->pluginConfig = $pluginConfig;
        $this->systemCheck = $systemCheck;
        $this->telemetryService = $telemetryService;
        $this->template = $template;
        $this->updater = $updater;
        $this->wpFunction = $wpFunction;
    }

    public function route(RequestDto $request): ?string
    {
        $action = $request->postData['action'] ?? $request->getData['action'] ?? '';

        if ($action === 'switch-telemetry-status') {
            return $this->switchTelemetryStatus($request->postData, $request->getData);
        }

        if ($action === 'save') {
            return $this->save($request->postData);
        }

        return $this->viewOverview($request->postData);
    }

    public function save(array $postData): string
    {
        $pluginConfig = $this->pluginConfig->get();
        $pluginConfig->automaticUpdate = AutomaticUpdateEnum::hasValue($postData['automaticUpdate'] ?? '')
            ? AutomaticUpdateEnum::fromValue($postData['automaticUpdate']) : AutomaticUpdateEnum::AUTO_UPDATE_NONE();
        $pluginConfig->enableDebugLogging = (bool) ($postData['enableDebugLogging'] ?? false);
        $this->pluginConfig->save($pluginConfig);
        $this->updater->handleAutomaticUpdateStatus();

        $this->messageManager->success($this->globalLocalizationStrings::get()['alert']['savedSuccessfully']);

        return $this->viewOverview($postData);
    }

    public function switchTelemetryStatus(array $postData, array $getData): string
    {
        $telemetryStatus = $postData['telemetryStatus']
            ?? ($getData['telemetryStatus'] ?? false);
        $this->option->setGlobal('TelemetryStatus', (bool) $telemetryStatus);

        if ($telemetryStatus) {
            $this->telemetryService->sendTelemetryData();
        }

        return $this->viewOverview($postData);
    }

    public function validate(RequestDto $request, string $nonce, bool $isValid): bool
    {
        if (
            isset($request->getData['action'], $request->getData['telemetryStatus'])
            && in_array($request->getData['action'], ['switch-telemetry-status',], true)
            && $this->wpFunction->wpVerifyNonce(self::CONTROLLER_ID . '-' . $request->getData['action'], $nonce)
        ) {
            $isValid = true;
        }

        if (isset($request->postData['action'])
            && in_array($request->postData['action'], ['chart-data', 'switch-telemetry-status',], true)
            && $this->wpFunction->wpVerifyNonce(self::CONTROLLER_ID . '-' . $request->postData['action'], $nonce)
        ) {
            $isValid = true;
        }

        return $isValid;
    }

    public function viewOverview(array $postData): string
    {
        $templateData = [];
        $templateData['controllerId'] = self::CONTROLLER_ID;
        $templateData['localized'] = DashboardLocalizationStrings::get();
        $templateData['localized']['global'] = GlobalLocalizationStrings::get();
        $templateData['enum']['automaticUpdateOptions'] = AutomaticUpdateEnum::getLocalizedKeyValueList();
        $templateData['language'] = $this->language->getSelectedLanguageCode();
        $templateData['data']['pluginConfig'] = $this->pluginConfig->get();
        $templateData['data']['news'] = $this->newsService->getNews();
        $templateData['data']['telemetryStatus'] = $this->option->getGlobal('TelemetryStatus', false)->value;
        $templateData['data']['displayTelemetryModal'] = $this->shouldTelemetryModalDisplayed();
        $timeRange = '30days';

        if (isset($postData['timeRange']) && $postData['timeRange'] === 'today') {
            $timeRange = 'today';
        } elseif (isset($postData['timeRange']) && $postData['timeRange'] === '7days') {
            $timeRange = '7days';
        } elseif (isset($postData['timeRange']) && $postData['timeRange'] === 'services30days') {
            $timeRange = 'services30days';
        }

        $chartData = $this->chartDataService->getChartData($timeRange);

        $templateData['data']['timeRange'] = $timeRange;
        $templateData['data']['jsonChartData'] = isset($chartData['datasets'][0]['data'][0]) ? true : false;
        $templateData['scriptTagChartData'] = '<script>var barChartData = ' . json_encode($chartData) . '; </script>';
        $templateData['localized']['headline']['cookieVersion'] = Formatter::interpolate(
            $templateData['localized']['headline']['cookieVersion'],
            [
                'cookieVersion' => $this->option->getGlobal('CookieVersion', 1)->value,
            ],
        );

        // Contains parsed template of system status section
        $templateData['template']['systemCheck'] = $this->systemCheck->systemCheckView();

        return $this->template->getEngine()->render(
            'dashboard/dashboard.html.twig',
            $templateData,
        );
    }

    private function shouldTelemetryModalDisplayed(): bool
    {
        $displayStatus = false;
        $telemetryStatus = $this->option->getGlobal('TelemetryStatus', false)->value;

        if ($telemetryStatus || !$this->license->isLicenseValid()) {
            return false;
        }

        $lastTimeTelemetryModalDisplayed = (int) $this->option->getGlobal('TelemetryModalDisplayed', 0)->value;
        $lastTimeTelemetryModalDisplayed7d = (int) $this->option->getGlobal('TelemetryModalDisplayed7d', 0)->value;
        $lastTimeTelemetryModalDisplayed14d = (int) $this->option->getGlobal('TelemetryModalDisplayed14d', 0)->value;

        if (
            $lastTimeTelemetryModalDisplayed === 0
            || $lastTimeTelemetryModalDisplayed < date('Ymd', strtotime('-3 months'))
        ) {
            $displayStatus = true;
            $this->option->setGlobal('TelemetryModalDisplayed', date('Ymd'));
            $this->option->setGlobal('TelemetryModalDisplayed7d', date('Ymd'));
            $this->option->setGlobal('TelemetryModalDisplayed14d', date('Ymd'));
        } elseif (
            $lastTimeTelemetryModalDisplayed7d === 0
            || $lastTimeTelemetryModalDisplayed7d < date('Ymd', strtotime('-7 days'))) {
            $displayStatus = true;
            $this->option->setGlobal('TelemetryModalDisplayed7d', date('Ymd', strtotime('+7 days')));
        } elseif (
            $lastTimeTelemetryModalDisplayed14d === 0
            || $lastTimeTelemetryModalDisplayed14d < date('Ymd', strtotime('-14 days'))) {
            $displayStatus = true;
            $this->option->setGlobal('TelemetryModalDisplayed14d', date('Ymd', strtotime('+14 days')));
        }

        return $displayStatus;
    }
}
