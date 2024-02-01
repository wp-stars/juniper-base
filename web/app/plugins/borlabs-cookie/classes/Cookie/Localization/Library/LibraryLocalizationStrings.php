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

namespace Borlabs\Cookie\Localization\Library;

use Borlabs\Cookie\Localization\LocalizationInterface;

use function Borlabs\Cookie\System\WordPressGlobalFunctions\_x;

final class LibraryLocalizationStrings implements LocalizationInterface
{
    /**
     * @return array<array<string>>
     */
    public static function get(): array
    {
        return [
            // Alert messages
            'alert' => [
                'copyrights' => _x(
                    'All copyrights, trademarks, and other intellectual property rights mentioned or displayed in this library belong to their respective owners. Unless explicitly stated, these entities are not affiliated, endorsed by, or in any way associated with us.',
                    'Backend / Library / Alert Message',
                    'borlabs-cookie',
                ),
                'disclaimer' => _x(
                    'We expressly disclaim any liability for the timeliness and accuracy of the data provided.',
                    'Backend / Library / Alert Message',
                    'borlabs-cookie',
                ),
                'installationFailed' => _x(
                    'Installation failed.',
                    'Backend / Library / Alert Message',
                    'borlabs-cookie',
                ),
                'libraryRefreshedSuccessfully' => _x(
                    'Library refreshed successfully.',
                    'Backend / Library / Alert Message',
                    'borlabs-cookie',
                ),
                'packageIsDeprecated' => _x(
                    'This package has been marked as deprecated and should be uninstalled.',
                    'Backend / Library / Alert Message',
                    'borlabs-cookie',
                ),
                'packageIsNotInstalled' => _x(
                    'The package is not installed.',
                    'Backend / Cloud Scan / Alert',
                    'borlabs-cookie',
                ),
                'packageNotFound' => _x(
                    'Package not found.',
                    'Backend / Library / Alert Message',
                    'borlabs-cookie',
                ),
                'successorPackageAvailable' => _x(
                    'Please use the successor package <a class="brlbs-cmpnt-link brlbs-cmpnt-link-with-icon" href="{{ link }}" target="_blank"><strong><em>{{ name }}</em></strong><span class="brlbs-cmpnt-external-link-icon"></span></a>.',
                    'Backend / Library / Alert Message',
                    'borlabs-cookie',
                ),
                'uninstallFailed' => _x(
                    'Uninstalling the <strong>{{ type }}</strong> <strong><em>{{ name }}</em></strong> failed.',
                    'Backend / Cloud Scan / Alert',
                    'borlabs-cookie',
                ),
                'uninstallFailedWithMessage' => _x(
                    'Uninstalling the <strong>{{ type }}</strong> <strong><em>{{ name }}</em></strong> failed: {{ message }}',
                    'Backend / Cloud Scan / Alert',
                    'borlabs-cookie',
                ),
                'uninstallSuccess' => _x(
                    'The package <strong><em>{{ name }}</em></strong> was successfully uninstalled.',
                    'Backend / Cloud Scan / Alert',
                    'borlabs-cookie',
                ),
            ],

            // Breadcrumbs
            'breadcrumb' => [
                'install' => _x(
                    'Package Installation',
                    'Backend / Library / Breadcrumb',
                    'borlabs-cookie',
                ),
                'module' => _x(
                    'Library',
                    'Backend / Library / Breadcrumb',
                    'borlabs-cookie',
                ),
                'reinstall' => _x(
                    'Package Reinstallation',
                    'Backend / Library / Breadcrumb',
                    'borlabs-cookie',
                ),
                'update' => _x(
                    'Package Update',
                    'Backend / Library / Breadcrumb',
                    'borlabs-cookie',
                ),
            ],

            // Buttons
            'button' => [
                'details' => _x(
                    'Details',
                    'Backend / Library / Button Title',
                    'borlabs-cookie',
                ),
                'goBackToLibrary' => _x(
                    'Go back to the library',
                    'Backend / Library / Button Title',
                    'borlabs-cookie',
                ),
                'goBackToScanResult' => _x(
                    'Go back to scan result',
                    'Backend / Library / Button Title',
                    'borlabs-cookie',
                ),
                'install' => _x(
                    '<translation-key id="Button-Install">Install</translation-key>',
                    'Backend / Library / Button Title',
                    'borlabs-cookie',
                ),
                'refreshLibrary' => _x(
                    'Refresh Library',
                    'Backend / Library / Button Title',
                    'borlabs-cookie',
                ),
                'reinstall' => _x(
                    '<translation-key id="Button-Reinstall">Reinstall</translation-key>',
                    'Backend / Library / Button Title',
                    'borlabs-cookie',
                ),
                'uninstall' => _x(
                    '<translation-key id="Button-Uninstall">Uninstall</translation-key>',
                    'Backend / Library / Button Title',
                    'borlabs-cookie',
                ),
                'update' => _x(
                    '<translation-key id="Button-Update">Update</translation-key>',
                    'Backend / Library / Button Title',
                    'borlabs-cookie',
                ),
            ],

            // Description List
            'descriptionList' => [
                'borlabsServiceUpdatedAt' => _x(
                    'Borlabs Service Modification Date',
                    'Backend / Library / Description List',
                    'borlabs-cookie',
                ),
                'installedAt' => _x(
                    'Installed at',
                    'Backend / Library / Description List',
                    'borlabs-cookie',
                ),
                'installedVersion' => _x(
                    'Installed Version',
                    'Backend / Library / Description List',
                    'borlabs-cookie',
                ),
                'latestVersion' => _x(
                    'Latest Version',
                    'Backend / Library / Description List',
                    'borlabs-cookie',
                ),
                'type' => _x(
                    'Type',
                    'Backend / Library / Description List',
                    'borlabs-cookie',
                ),
                'updatedAt' => _x(
                    'Updated at',
                    'Backend / Library / Description List',
                    'borlabs-cookie',
                ),
                'updateAvailable' => _x(
                    'Update available',
                    'Backend / Library / Description List',
                    'borlabs-cookie',
                ),
                'version' => _x(
                    'Version',
                    'Backend / Library / Description List',
                    'borlabs-cookie',
                ),
            ],

            // Fields
            'field' => [
                'compatibilityPatches' => _x(
                    '<translation-key id="Compatibility-Patches">Compatibility Patches</translation-key>',
                    'Backend / Library / Field',
                    'borlabs-cookie',
                ),
                'contentBlockers' => _x(
                    '<translation-key id="Content-Blockers">Content Blockers</translation-key>',
                    'Backend / Library / Field',
                    'borlabs-cookie',
                ),
                'install' => _x(
                    'Install',
                    'Backend / Library / Field',
                    'borlabs-cookie',
                ),
                'installPackage' => _x(
                    'To install the package, simply click the <translation-key id="Button-Install">Install</translation-key> button.',
                    'Backend / Library / Field',
                    'borlabs-cookie',
                ),
                'overwriteCode' => _x(
                    'Overwrite Code',
                    'Backend / Library / Field',
                    'borlabs-cookie',
                ),
                'overwriteTranslation' => _x(
                    'Overwrite Translation',
                    'Backend / Library / Field',
                    'borlabs-cookie',
                ),
                'packageListLastUpdate' => _x(
                    'Last Update',
                    'Backend / Library / Field',
                    'borlabs-cookie',
                ),
                'reinstall' => _x(
                    'Reinstall',
                    'Backend / Library / Field',
                    'borlabs-cookie',
                ),
                'reinstallPackage' => _x(
                    'To reinstall the package, simply click the <translation-key id="Button-Reinstall">Reinstall</translation-key> button.',
                    'Backend / Library / Field',
                    'borlabs-cookie',
                ),
                'scriptBlockers' => _x(
                    '<translation-key id="Script-Blockers">Script Blockers</translation-key>',
                    'Backend / Library / Field',
                    'borlabs-cookie',
                ),
                'services' => _x(
                    '<translation-key id="Services">Services</translation-key>',
                    'Backend / Library / Field',
                    'borlabs-cookie',
                ),
                'styleBlockers' => _x(
                    '<translation-key id="Style-Blockers">Style Blockers</translation-key>',
                    'Backend / Library / Field',
                    'borlabs-cookie',
                ),
                'uninstallPackage' => _x(
                    'To uninstall the package, simply click the <translation-key id="Button-Uninstall">Uninstall</translation-key> button.',
                    'Backend / Library / Field',
                    'borlabs-cookie',
                ),
                'updatePackage' => _x(
                    'To update the package, simply click the <translation-key id="Button-Update">Update</translation-key> button.',
                    'Backend / Library / Field',
                    'borlabs-cookie',
                ),
            ],

            // Headlines
            'headline' => [
                'configureLanguage' => _x(
                    'Configure Language',
                    'Backend / Library / Headline',
                    'borlabs-cookie',
                ),
                'contentBlocker' => _x(
                    '<translation-key id="Content-Blocker">Content Blocker</translation-key>',
                    'Backend / Library / Headline',
                    'borlabs-cookie',
                ),
                'description' => _x(
                    'Description',
                    'Backend / Library / Headline',
                    'borlabs-cookie',
                ),
                'detectedOnPages' => _x(
                    'Detected on pages',
                    'Backend / Library / Headline',
                    'borlabs-cookie',
                ),
                'followUp' => _x(
                    'Follow up',
                    'Backend / Library / Headline',
                    'borlabs-cookie',
                ),
                'installation' => _x(
                    'Installation',
                    'Backend / Library / Headline',
                    'borlabs-cookie',
                ),
                'packageComponents' => _x(
                    'Package Components',
                    'Backend / Library / Headline',
                    'borlabs-cookie',
                ),
                'packageInformation' => _x(
                    'Package Information',
                    'Backend / Library / Headline',
                    'borlabs-cookie',
                ),
                'preparation' => _x(
                    'Preparation',
                    'Backend / Library / Headline',
                    'borlabs-cookie',
                ),
                'refreshLibrary' => _x(
                    'Refresh Library',
                    'Backend / Library / Headline',
                    'borlabs-cookie',
                ),
                'reinstallation' => _x(
                    'Reinstallation',
                    'Backend / Library / Headline',
                    'borlabs-cookie',
                ),
                'service' => _x(
                    '<translation-key id="Service">Service</translation-key>',
                    'Backend / Library / Headline',
                    'borlabs-cookie',
                ),
                'settings' => _x(
                    'Settings',
                    'Backend / Library / Headline',
                    'borlabs-cookie',
                ),
                'uninstall' => _x(
                    'Uninstall',
                    'Backend / Library / Headline',
                    'borlabs-cookie',
                ),
                'uninstallPackage' => _x(
                    'Uninstall Package',
                    'Backend / Library / Headline',
                    'borlabs-cookie',
                ),
                'update' => _x(
                    'Update',
                    'Backend / Library / Headline',
                    'borlabs-cookie',
                ),
            ],

            // Hint
            'hint' => [
                'compatibilityPatches' => _x(
                    'The <translation-key id="Compatibility-Patches">Compatibility Patches</translation-key> are included in this package and are necessary for the proper functioning of this package. They are installed when you click the <translation-key id="Button-Install">Install</translation-key> / <translation-key id="Button-Reinstall">Reinstall</translation-key> / <translation-key id="Button-Update">Update</translation-key> button. You can locate them later using the displayed key in the <translation-key id="Compatibility-Patches">Compatibility Patches</translation-key> view (<translation-key id="Navigation-System">System</translation-key> &raquo; <translation-key id="Navigation-System-Compatibility-Patches">Compatibility Patches</translation-key>).',
                    'Backend / Library / Hint',
                    'borlabs-cookie',
                ),
                'contentBlockers' => _x(
                    'The <translation-key id="Content-Blockers">Content Blockers</translation-key> are included in this package and are necessary for the proper functioning of this package. They are installed when you click the <translation-key id="Button-Install">Install</translation-key> / <translation-key id="Button-Reinstall">Reinstall</translation-key> / <translation-key id="Button-Update">Update</translation-key> button. You can locate them later using the displayed name and key in the <translation-key id="Content-Blockers">Content Blockers</translation-key> view (<translation-key id="Navigation-Blockers">Blockers</translation-key> &raquo; <translation-key id="Navigation-Blockers-Content-Blockers">Content Blockers</translation-key> &raquo; <translation-key id="Navigation-Blockers-Content-Blockers-Manage">Manage</translation-key>).',
                    'Backend / Library / Hint',
                    'borlabs-cookie',
                ),
                'overwriteCodeContentBlocker' => _x(
                    'If enabled, the code (<translation-key id="Preview-Blocked-Content-Image">Image</translation-key>, <translation-key id="Preview-Blocked-Content-HTML">HTML</translation-key>, <translation-key id="Preview-Blocked-Content-CSS">CSS</translation-key>, <translation-key id="Global">Global</translation-key> and <translation-key id="Initialization">Initialization</translation-key>) will be overwritten with the code provided by the package. To retain your code modifications while only updating the component settings, you may disable this option.',
                    'Backend / Library / Hint',
                    'borlabs-cookie',
                ),
                'overwriteCodeService' => _x(
                    'If enabled, the code (<translation-key id="Opt-in-Code">Opt-in Code</translation-key>, <translation-key id="Opt-out-Code">Opt-out Code</translation-key> and <translation-key id="Fallback-Code">Fallback Code</translation-key>) will be overwritten with the code provided by the package. To retain your code modifications while only updating the component settings, you may disable this option.',
                    'Backend / Library / Hint',
                    'borlabs-cookie',
                ),
                'overwriteTranslation' => _x(
                    'If enabled, the text will be overwritten with the translation provided by the package. To retain your translation while only updating the component settings, you may disable this option.',
                    'Backend / Library / Hint',
                    'borlabs-cookie',
                ),
                'scriptBlockers' => _x(
                    'The <translation-key id="Script-Blockers">Script Blockers</translation-key> are included in this package and are necessary for the proper functioning of this package. They are installed when you click the <translation-key id="Button-Install">Install</translation-key> / <translation-key id="Button-Reinstall">Reinstall</translation-key> / <translation-key id="Button-Update">Update</translation-key> button. You can locate them later using the displayed name and key in the <translation-key id="Script-Blockers">Script Blockers</translation-key> view (<translation-key id="Navigation-Blockers">Blockers</translation-key> &raquo; <translation-key id="Navigation-Blockers-Script-Blockers">Script Blockers</translation-key>).',
                    'Backend / Library / Hint',
                    'borlabs-cookie',
                ),
                'services' => _x(
                    'The <translation-key id="Services">Services</translation-key> are included in this package and are necessary for the proper functioning of this package. They are installed when you click the <translation-key id="Button-Install">Install</translation-key> / <translation-key id="Button-Reinstall">Reinstall</translation-key> / <translation-key id="Button-Update">Update</translation-key> button. You can locate them later using the displayed name and key in the <translation-key id="Services">Services</translation-key> view (<translation-key id="Navigation-Consent-Management">Consent Management</translation-key> &raquo; <translation-key id="Navigation-Consent-Management-Services">Services</translation-key>).',
                    'Backend / Library / Hint',
                    'borlabs-cookie',
                ),
                'styleBlockers' => _x(
                    'The <translation-key id="Style-Blockers">Style Blockers</translation-key> are included in this package and are necessary for the proper functioning of this package. They are installed when you click the <translation-key id="Button-Install">Install</translation-key> / <translation-key id="Button-Reinstall">Reinstall</translation-key> / <translation-key id="Button-Update">Update</translation-key> button. You can locate them later using the displayed name and key in the <translation-key id="Style-Blockers">Style Blockers</translation-key> view (<translation-key id="Navigation-Blockers">Blockers</translation-key> &raquo; <translation-key id="Navigation-Blockers-Style-Blockers">Style Blockers</translation-key>).',
                    'Backend / Library / Hint',
                    'borlabs-cookie',
                ),
            ],

            // Navigation
            'navigation' => [
                'all' => _x(
                    'All',
                    'Backend / Library / Navigation',
                    'borlabs-cookie',
                ),
                'filter' => _x(
                    'Filter',
                    'Backend / Library / Navigation',
                    'borlabs-cookie',
                ),
                'compatibilityPatches' => _x(
                    '<translation-key id="Compatibility-Patches">Compatibility Patches</translation-key>',
                    'Backend / Library / Navigation',
                    'borlabs-cookie',
                ),
                'contentBlockers' => _x(
                    '<translation-key id="Content-Blockers">Content Blockers</translation-key>',
                    'Backend / Library / Navigation',
                    'borlabs-cookie',
                ),
                'installedPackages' => _x(
                    'Installed Packages',
                    'Backend / Library / Navigation',
                    'borlabs-cookie',
                ),
                'scriptBlockers' => _x(
                    '<translation-key id="Script-Blockers">Script Blockers</translation-key>',
                    'Backend / Library / Navigation',
                    'borlabs-cookie',
                ),
                'services' => _x(
                    '<translation-key id="Services">Services</translation-key>',
                    'Backend / Library / Navigation',
                    'borlabs-cookie',
                ),
                'styleBlockers' => _x(
                    '<translation-key id="Style-Blockers">Style Blockers</translation-key>',
                    'Backend / Library / Navigation',
                    'borlabs-cookie',
                ),
            ],

            // Placeholder
            'placeholder' => [
                'search' => _x(
                    'Search',
                    'Backend / Library / Input Placeholder',
                    'borlabs-cookie',
                ),
            ],

            // Tables
            'table' => [
                'componentType' => _x(
                    'Type',
                    'Backend / Library / Table Headline',
                    'borlabs-cookie',
                ),
                'name' => _x(
                    'Name',
                    'Backend / Library / Table Headline',
                    'borlabs-cookie',
                ),
                'status' => _x(
                    'Status',
                    'Backend / Library / Table Headline',
                    'borlabs-cookie',
                ),
            ],

            // Text
            'text' => [
                'confirmUninstallPackage' => _x(
                    'Are you sure you want to uninstall the package?',
                    'Backend / Library / Text',
                    'borlabs-cookie',
                ),
                'package' => _x(
                    'Package',
                    'Backend / Library / Text',
                    'borlabs-cookie',
                ),
                'recommended' => _x(
                    'Recommended',
                    'Backend / Library / Text',
                    'borlabs-cookie',
                ),
            ],
        ];
    }
}
