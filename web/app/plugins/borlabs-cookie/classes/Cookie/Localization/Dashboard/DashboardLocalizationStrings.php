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

namespace Borlabs\Cookie\Localization\Dashboard;

use Borlabs\Cookie\Localization\LocalizationInterface;

use function Borlabs\Cookie\System\WordPressGlobalFunctions\_x;

/**
 * The **DashboardLocalizationStrings** class contains various localized strings.
 *
 * @see \Borlabs\Cookie\Localization\Dashboard\DashboardLocalizationStrings::get()
 */
final class DashboardLocalizationStrings implements LocalizationInterface
{
    /**
     * @return array<array<string>>
     */
    public static function get(): array
    {
        return [
            // Alert messages
            'alert' => [
                'noChartData' => _x(
                    'No data available yet. Please try again in a few hours.',
                    'Backend / Dashboard / Alert Message',
                    'borlabs-cookie',
                ),
            ],

            // Breadcrumbs
            'breadcrumb' => [
                'module' => _x(
                    'Dashboard',
                    'Backend / Dashboard / Breadcrumb',
                    'borlabs-cookie',
                ),
            ],

            // Buttons
            'button' => [
                'chartData7Days' => _x(
                    '7 Days',
                    'Backend / Dashboard / Button Title',
                    'borlabs-cookie',
                ),
                'chartData30Days' => _x(
                    '30 Days',
                    'Backend / Dashboard / Button Title',
                    'borlabs-cookie',
                ),
                'chartDataToday' => _x(
                    'Today',
                    'Backend / Dashboard / Button Title',
                    'borlabs-cookie',
                ),
                'chartDataServices30Days' => _x(
                    '30 Days by Service',
                    'Backend / Dashboard / Button Title',
                    'borlabs-cookie',
                ),
                'improveBorlabsCookieClickedTheWrongButton' => _x(
                    'No, I do not want to help',
                    'Backend / Dashboard / Button Title',
                    'borlabs-cookie',
                ),
                'improveBorlabsCookieGoodHuman' => _x(
                    '<span class="brlbs-cmpnt-heart-icon"></span><span>Yes, I would like to help</span>',
                    'Backend / Dashboard / Button Title',
                    'borlabs-cookie',
                ),
            ],

            // Field
            'field' => [
                'automaticUpdate' => _x(
                    'Automatic Update',
                    'Backend / Dashboard / Field',
                    'borlabs-cookie',
                ),
                'enableDebugLogging' => _x(
                    'Enable Debug Logging',
                    'Backend / Dashboard / Field',
                    'borlabs-cookie',
                ),
                'improveBorlabsCookie' => _x(
                    'Improve Borlabs Cookie',
                    'Backend / Dashboard / Field',
                    'borlabs-cookie',
                ),
            ],

            // Headlines
            'headline' => [
                'acknowledgement' => _x(
                    'Acknowledgement',
                    'Backend / Dashboard / Headline',
                    'borlabs-cookie',
                ),
                'contributors' => _x(
                    'Contributors',
                    'Backend / Dashboard / Headline',
                    'borlabs-cookie',
                ),
                'cookieVersion' => _x(
                    '<span>Statistics</span> <small>-</small> <small>Cookie Version {{ cookieVersion }}</small>',
                    'Backend / Dashboard / Headline',
                    'borlabs-cookie',
                ),
                'improveBorlabsCookie' => _x(
                    'Improve Borlabs Cookie',
                    'Backend / Dashboard / Headline',
                    'borlabs-cookie',
                ),
                'news' => _x(
                    'News',
                    'Backend / Dashboard / Headline',
                    'borlabs-cookie',
                ),
                'pluginUpdatesAndDebugging' => _x(
                    'Plugin Updates &amp; Debugging',
                    'Backend / Dashboard / Headline',
                    'borlabs-cookie',
                ),
                'quickStart' => _x(
                    'Quick Start',
                    'Backend / Dashboard / Headline',
                    'borlabs-cookie',
                ),
            ],

            // Hint
            'hint' => [
                'automaticUpdateA' => _x(
                    'You can choose how <translation-key id="Borlabs-Cookie">Borlabs Cookie</translation-key> updates automatically. Select from one of the three available options.<br><br>',
                    'Backend / Dashboard / Hint',
                    'borlabs-cookie',
                ),
                'automaticUpdateB' => _x(
                    '<translation-key id="All-versions">All versions</translation-key>: All updates are installed automatically.<br><br>',
                    'Backend / Dashboard / Hint',
                    'borlabs-cookie',
                ),
                'automaticUpdateC' => _x(
                    '<translation-key id="Minor-versions-only">Minor versions only</translation-key>: Only minor versions are installed automatically.',
                    'Backend / Dashboard / Hint',
                    'borlabs-cookie',
                ),
                'automaticUpdateD' => _x(
                    'A standard version number follows the format 1.0.3.0, where the segments represent <translation-key id="MAJOR">MAJOR</translation-key>, <translation-key id="MINOR">MINOR</translation-key>, <translation-key id="PATCH">PATCH</translation-key>, and <translation-key id="HOTFIX">HOTFIX</translation-key>, respectively.<br><br>',
                    'Backend / Dashboard / Hint',
                    'borlabs-cookie',
                ),
                'automaticUpdateE' => _x(
                    '<translation-key id="No-automatic-update">No automatic update</translation-key>: Automatic updates are disabled; you must update manually.',
                    'Backend / Dashboard / Hint',
                    'borlabs-cookie',
                ),
                'enableDebugLogging' => _x(
                    'This setting should only be activated when urgently required. When activated, it generates a large number of log entries that can affect the performance of the website. The log entries can be found under <translation-key id="Navigation-System">System</translation-key> &raquo; <translation-key id="Navigation-System-Logs">Logs</translation-key>.',
                    'Backend / Dashboard / Hint',
                    'borlabs-cookie',
                ),
            ],

            // Tables
            'table' => [
            ],

            // Text
            'text' => [
                'developerAndInfrastructure' => _x(
                    'Development &amp; Infrastructure',
                    'Backend / Dashboard / Text',
                    'borlabs-cookie',
                ),
                'furtherContributors' => _x(
                    'Further Contributors',
                    'Backend / Dashboard / Text',
                    'borlabs-cookie',
                ),
                'here' => _x(
                    'here',
                    'Backend / Dashboard / Text',
                    'borlabs-cookie',
                ),
                'inMemoryOfSergiiKovalenko' => _x(
                    'In memory of Sergii Kovalenko.',
                    'Backend / Dashboard / Text',
                    'borlabs-cookie',
                ),
                'improveBorlabsCookieA' => _x(
                    'Help us improve <translation-key id="Borlabs-Cookie">Borlabs Cookie</translation-key> by providing us with non-personal information about your website.',
                    'Backend / Dashboard / Text',
                    'borlabs-cookie',
                ),
                'improveBorlabsCookieB' => _x(
                    'For more information about what data we need and for what purpose, <a class="brlbs-cmpnt-link brlbs-cmpnt-link-with-icon" href="%s" rel="nofollow noreferrer" target="_blank"><span>click here</span><span class="brlbs-cmpnt-external-link-icon"></span></a>.',
                    'Backend / Dashboard / Text',
                    'borlabs-cookie',
                ),
                'localization' => _x(
                    'Localization',
                    'Backend / Dashboard / Text',
                    'borlabs-cookie',
                ),
                'quickStartDone' => _x(
                    'The dialog should now appear on your website. Open your website in incognito/private mode for testing. If you have any questions, please visit our <a class="brlbs-cmpnt-link brlbs-cmpnt-link-with-icon" href="%s" rel="nofollow noreferrer" target="_blank"><span>Knowledge Base</span><span class="brlbs-cmpnt-external-link-icon"></span></a>.',
                    'Backend / Dashboard / Text',
                    'borlabs-cookie',
                ),
                'quickStartStepA' => _x(
                    'Please disable your caching plugin if it is currently enabled. Once you have completed the setup of <translation-key id="Borlabs-Cookie">Borlabs Cookie</translation-key>, you may re-enable it.',
                    'Backend / Dashboard / Text',
                    'borlabs-cookie',
                ),
                'quickStartStepB' => _x(
                    'Update the default provider data under <translation-key id="Navigation-Consent-Management">Consent Management</translation-key> &raquo; <a class="brlbs-cmpnt-link" href="?page=borlabs-cookie-provider"><translation-key id="Navigation-Consent-Management-Providers">Providers</translation-key></a> &raquo; <translation-key id="Owner-of-this-website">Owner of this website</translation-key>. Please update the data by adding your name and address, and ensure to include the URL for the <translation-key id="Privacy-Url">Privacy URL</translation-key>.',
                    'Backend / Dashboard / Text',
                    'borlabs-cookie',
                ),
                'quickStartStepC' => _x(
                    'Click on <translation-key id="Navigation-Dialog-Widget">Dialog &amp; Widget</translation-key> &raquo; <translation-key id="Navigation-Dialog-Widget-Dialog">Dialog</translation-key> &raquo; <a class="brlbs-cmpnt-link" href="?page=borlabs-cookie-dialog-settings"><translation-key id="Navigation-Dialog-Widget-Dialog-Settings">Settings</translation-key></a> and select your <translation-key id="Privacy-Page">Privacy Page</translation-key> and <translation-key id="Imprint-Page">Imprint Page</translation-key>. Add both pages to <translation-key id="Hide-Dialog-on-Pages">Hide Dialog on Pages</translation-key> too.',
                    'Backend / Dashboard / Text',
                    'borlabs-cookie',
                ),
                'quickStartStepD' => _x(
                    'Enable <translation-key id="Borlabs-Cookie">Borlabs Cookie</translation-key> under <a class="brlbs-cmpnt-link" href="?page=borlabs-cookie-settings"><translation-key id="Navigation-Settings">Settings</translation-key></a> &raquo; <translation-key id="Borlabs-Cookie-Status">Borlabs Cookie Status</translation-key>.',
                    'Backend / Dashboard / Text',
                    'borlabs-cookie',
                ),
                'quickStartStepE' => _x(
                    'Use our scanner to check which cookies are set on your website and which services are used. Click on <a class="brlbs-cmpnt-link" href="?page=borlabs-cookie-cloud-scan"><translation-key id="Navigation-Scanner">Scanner</translation-key></a> &raquo; <translation-key id="Button-Add-New">Add New</translation-key> &raquo; <translation-key id="Button-Create-Scan">Create scan</translation-key> to add a new scan job. The <translation-key id="Scan-type">Scan type</translation-key> should be set to <translation-key id="Setup">Setup</translation-key>. ',
                    'Backend / Dashboard / Text',
                    'borlabs-cookie',
                ),
                'quickStartStepF' => _x(
                    'After you have used the scanner and installed suggested packages, create a new scan job and set <translation-key id="Scan-type">Scan type</translation-key> to <translation-key id="Audit">Audit</translation-key>. This scan type is used to verify that your website does not load cookies or external resources without obtaining the appropriate consent.',
                    'Backend / Dashboard / Text',
                    'borlabs-cookie',
                ),
                'quickStartStepG' => _x(
                    'Explore our <a class="brlbs-cmpnt-link" href="?page=borlabs-cookie-library"><translation-key id="Navigation-Library">Library</translation-key></a> to have an overview of all our available packages. Here you can find templates for <translation-key id="Services">Services</translation-key> (e.g. Google Analytics) or <translation-key id="Content-Blockers">Content Blockers</translation-key> (e.g. YouTube) and much more.',
                    'Backend / Dashboard / Text',
                    'borlabs-cookie',
                ),
                'quickStartStepH' => _x(
                    'If you place ads on your website, you may need to enable the IAB TCF under <translation-key id="Navigation-Consent-Management">Consent Management</translation-key> &raquo; <translation-key id="Navigation-Consent-Management-IAB-TCF">IAB TCF</translation-key> &raquo; <a class="brlbs-cmpnt-link" href="?page=borlabs-cookie-iab-tcf-settings"><translation-key id="Navigation-Consent-Management-IAB-TCF-Settings">Settings</translation-key></a> &raquo; <translation-key id="IAB-TCF-Status">IAB TCF Status</translation-key>. And then configure vendors under <translation-key id="Navigation-Consent-Management">Consent Management</translation-key> &raquo; <translation-key id="Navigation-Consent-Management-IAB-TCF">IAB TCF</translation-key> &raquo; <a class="brlbs-cmpnt-link" href="?page=borlabs-cookie-iab-tcf-vendor"><translation-key id="Navigation-Consent-Management-IAB-TCF-Manage-Vendors">Manage Vendors</translation-key></a> &raquo; <translation-key id="Configure-Vendors">Configure Vendors</translation-key>.',
                    'Backend / Dashboard / Text',
                    'borlabs-cookie',
                ),
                'quickStartStepI' => _x(
                    'You can customize the dialog texts under <translation-key id="Navigation-Dialog-Widget">Dialog &amp; Widget</translation-key> &raquo; <translation-key id="Navigation-Dialog-Widget-Dialog">Dialog</translation-key> &raquo; <a class="brlbs-cmpnt-link" href="?page=borlabs-cookie-dialog-localization"><translation-key id="Navigation-Dialog-Widget-Dialog-Localization">Localization</translation-key></a>. The visual appearance (colors, spacing, etc.) can be customized under <translation-key id="Navigation-Dialog-Widget">Dialog &amp; Widget</translation-key> &raquo; <translation-key id="Navigation-Dialog-Widget-Dialog">Dialog</translation-key> &raquo; <a class="brlbs-cmpnt-link" href="?page=borlabs-cookie-dialog-appearance"><translation-key id="Navigation-Dialog-Widget-Dialog-Appearance">Appearance</translation-key></a>. Everything else like layout, position, buttons, etc. you can define under <translation-key id="Navigation-Dialog-Widget">Dialog &amp; Widget</translation-key> &raquo; <translation-key id="Navigation-Dialog-Widget-Dialog">Dialog</translation-key> &raquo; <a class="brlbs-cmpnt-link" href="?page=borlabs-cookie-dialog-settings"><translation-key id="Navigation-Dialog-Widget-Dialog-Settings">Settings</translation-key></a>.',
                    'Backend / Dashboard / Text',
                    'borlabs-cookie',
                ),
                'thankYou' => _x(
                    'Thank you!',
                    'Backend / Dashboard / Text',
                    'borlabs-cookie',
                ),
            ],

            // URL
            'url' => [
                'improveBorlabsCookie' => _x(
                    'https://borlabs.io/borlabs-cookie/telemetry/',
                    'Backend / Dashboard / URL',
                    'borlabs-cookie',
                ),
                'knowledgeBase' => _x(
                    'https://borlabs.io/support/?utm_source=Borlabs+Cookie&utm_medium=Dashboard+Link&utm_campaign=Analysis',
                    'Backend / Dashboard / URL',
                    'borlabs-cookie',
                ),
                'quickStartVideo' => _x(
                    'https://cdn-public.borlabs.io/videos/en/borlabs-cookie-3-0-setup.mp4',
                    'Backend / Dashboard / URL',
                    'borlabs-cookie',
                ),
            ],
        ];
    }
}
