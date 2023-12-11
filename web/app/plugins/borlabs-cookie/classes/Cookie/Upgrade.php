<?php
/*
 * ----------------------------------------------------------------------
 *
 *                          Borlabs Cookie
 *                    developed by Borlabs GmbH
 *
 * ----------------------------------------------------------------------
 *
 * Copyright 2018-2022 Borlabs GmbH. All rights reserved.
 * This file may not be redistributed in whole or significant part.
 * Content of this file is protected by international copyright laws.
 *
 * ----------------- Borlabs Cookie IS NOT FREE SOFTWARE -----------------
 *
 * @copyright Borlabs GmbH, https://borlabs.io
 * @author Benjamin A. Bornschein
 *
 */

namespace BorlabsCookie\Cookie;

use autoptimizeCache;
use BorlabsCookie\Cookie\Backend\CSS;
use BorlabsCookie\Cookie\Frontend\Services\Ezoic;
use BorlabsCookie\Cookie\Frontend\Services\EzoicMarketing;
use BorlabsCookie\Cookie\Frontend\Services\EzoicPreferences;
use BorlabsCookie\Cookie\Frontend\Services\EzoicStatistics;
use GlobIterator;

class Upgrade
{
    private static $instance;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private $currentBlogId = '';

    private $versionUpgrades
        = [
            'upgradeVersion_2_1_0' => '2.1.0',
            'upgradeVersion_2_1_8' => '2.1.8',
            'upgradeVersion_2_1_9' => '2.1.9',
            'upgradeVersion_2_1_13' => '2.1.13',
            'upgradeVersion_2_2_0' => '2.2.0',
            'upgradeVersion_2_2_2' => '2.2.2',
            'upgradeVersion_2_2_3' => '2.2.3',
            'upgradeVersion_2_2_6' => '2.2.6',
            'upgradeVersion_2_2_9' => '2.2.9',
            'upgradeVersion_2_2_29' => '2.2.29',
            'upgradeVersion_2_2_43' => '2.2.43',
            'upgradeVersion_2_2_44' => '2.2.44',
            'upgradeVersion_2_2_45' => '2.2.45',
            'upgradeVersion_2_2_46_1' => '2.2.46.1',
            'upgradeVersion_2_2_47' => '2.2.47',
            'upgradeVersion_2_2_49' => '2.2.49',
            'upgradeVersion_2_2_50' => '2.2.50',
            'upgradeVersion_2_2_56' => '2.2.56',
            'upgradeVersion_2_2_57' => '2.2.57',
            'upgradeVersion_2_2_61' => '2.2.61',
            'upgradeVersion_2_2_62' => '2.2.62',
            'upgradeVersion_2_2_63' => '2.2.63',
            'upgradeVersion_2_2_64' => '2.2.64',
            'upgradeVersion_2_2_65' => '2.2.65',
            'upgradeVersion_2_2_66' => '2.2.66',
            'upgradeVersion_2_2_67' => '2.2.67',
        ];

    public function __construct()
    {
    }

    public function __clone()
    {
        trigger_error('Cloning is not allowed.', E_USER_ERROR);
    }

    public function __wakeup()
    {
        trigger_error('Unserialize is forbidden.', E_USER_ERROR);
    }

    /**
     * clearCache function.
     */
    public function clearCache()
    {
        Log::getInstance()->info(__METHOD__, 'Clear cache after upgrade');

        // Borlabs Cookie - CSS
        if (file_exists(WP_CONTENT_DIR . '/cache/borlabs-cookie/')) {
            $iterator = new GlobIterator(WP_CONTENT_DIR . '/cache/borlabs-cookie/borlabs-cookie_*.css');

            if ($iterator->count()) {
                foreach ($iterator as $fileInfo) {
                    if (is_writable($fileInfo->getPathname())) {
                        unlink($fileInfo->getPathname());
                    }
                }
            }
        }

        // Autoptimize
        if (class_exists('\autoptimizeCache')) {
            Log::getInstance()->info(__METHOD__, 'Clear cache of Autoptimize');

            autoptimizeCache::clearall();
        }

        // Borlabs Cache
        if (class_exists('\Borlabs\Cache\Frontend\Garbage')) {
            Log::getInstance()->info(__METHOD__, 'Clear cache of Borlabs Cache');

            \Borlabs\Cache\Frontend\Garbage::getInstance()->clearStylesPreCacheFiles();

            \Borlabs\Cache\Frontend\Garbage::getInstance()->clearCache();
        }

        // WP Fastest Cache
        if (function_exists('wpfc_clear_all_cache')) {
            Log::getInstance()->info(__METHOD__, 'Clear cache of WP Fastest Cache');

            wpfc_clear_all_cache(true);
        }

        // WP Rocket
        if (function_exists('rocket_clean_domain')) {
            Log::getInstance()->info(__METHOD__, 'Clear cache of WP Rocket');

            rocket_clean_domain();
        }

        // WP Super Cache
        if (function_exists('wp_cache_clean_cache')) {
            global $file_prefix;

            if (isset($file_prefix)) {
                Log::getInstance()->info(__METHOD__, 'Clear cache of WP Super Cache');

                wp_cache_clean_cache($file_prefix);
            }
        }

        update_option('BorlabsCookieClearCache', false, 'no');

        Log::getInstance()->info(__METHOD__, 'Cache cleared');
    }

    /**
     * getVersionUpgrades function.
     */
    public function getVersionUpgrades()
    {
        return $this->versionUpgrades;
    }

    public function upgradeVersion_2_1_0()
    {
        global $wpdb;

        // Update tables
        $tableNameCookies = $wpdb->prefix . 'borlabs_cookie_cookies';
        $tableNameCookieGroups = $wpdb->prefix . 'borlabs_cookie_groups'; // ->prefix contains base_prefix + blog id
        $tableNameContentBlocker = $wpdb->prefix
            . 'borlabs_cookie_content_blocker'; // ->prefix contains base_prefix + blog id

        if (Install::getInstance()->checkIfTableExists($tableNameCookies)) {
            $wpdb->query('ALTER TABLE `' . $tableNameCookies . '` MODIFY `language` varchar(16);');
        }

        if (Install::getInstance()->checkIfTableExists($tableNameCookieGroups)) {
            $wpdb->query('ALTER TABLE `' . $tableNameCookieGroups . '` MODIFY `language` varchar(16);');
        }

        if (Install::getInstance()->checkIfTableExists($tableNameContentBlocker)) {
            $wpdb->query('ALTER TABLE `' . $tableNameContentBlocker . '` MODIFY `language` varchar(16);');
        }

        // Add new table
        $charsetCollate = $wpdb->get_charset_collate();
        $tableNameScriptBlocker = $wpdb->prefix
            . 'borlabs_cookie_script_blocker'; // ->prefix contains base_prefix + blog id

        $sqlCreateTableScriptBlocker = Install::getInstance()
            ->getCreateTableStatementScriptBlocker(
                $tableNameScriptBlocker,
                $charsetCollate
            );

        $wpdb->query($sqlCreateTableScriptBlocker);

        // Add user capabilities
        Install::getInstance()->addUserCapabilities();

        update_option('BorlabsCookieVersion', '2.1.0', 'no');
    }

    public function upgradeVersion_2_1_13()
    {
        global $wpdb;

        // Change cookie log table
        $tableName = $wpdb->prefix . 'borlabs_cookie_consent_log';

        if (Install::getInstance()->checkIfTableExists($tableName)) {
            // Check if key exists
            $checkOldKey = $wpdb->query(
                '
                SHOW
                    INDEXES
                FROM
                    `' . $tableName . "`
                WHERE
                    `Key_name` = 'is_latest'
            "
            );

            if ($checkOldKey) {
                // Remove key
                $wpdb->query(
                    '
                    ALTER TABLE
                        `' . $tableName . '`
                    DROP INDEX
                        `is_latest`
                '
                );
            }

            // Add new key
            $checkNewKey = $wpdb->query(
                '
                SHOW
                    INDEXES
                FROM
                    `' . $tableName . "`
                WHERE
                    `Key_name` = 'uid'
            "
            );

            if (!$checkNewKey) {
                // Add key
                $wpdb->query(
                    '
                    ALTER TABLE
                        `' . $tableName . '`
                    ADD KEY
                        `uid` (`uid`, `is_latest`)
                '
                );
            }
        }

        // Change column of cookie_expiry
        $tableNameCookies = $wpdb->prefix . 'borlabs_cookie_cookies';

        if (Install::getInstance()->checkIfTableExists($tableNameCookies)) {
            $wpdb->query(
                '
                ALTER TABLE
                    ' . $tableNameCookies . "
                MODIFY
                    `cookie_expiry` varchar(255) NOT NULL DEFAULT ''
            "
            );
        }

        update_option('BorlabsCookieVersion', '2.1.13', 'no');
    }

    public function upgradeVersion_2_1_8()
    {
        // Update Multilanguage
        $languageCodes = [];

        // Polylang
        if (defined('POLYLANG_VERSION')) {
            $polylangLanguages = get_terms('language', ['hide_empty' => false]);

            if (!empty($polylangLanguages)) {
                foreach ($polylangLanguages as $languageData) {
                    if (!empty($languageData->slug) && is_string($languageData->slug)) {
                        $languageCodes[$languageData->slug] = $languageData->slug;
                    }
                }
            }
        }

        // WPML
        if (defined('ICL_LANGUAGE_CODE')) {
            $wpmlLanguages = apply_filters('wpml_active_languages', null, []);

            if (!empty($wpmlLanguages)) {
                foreach ($wpmlLanguages as $languageData) {
                    if (!empty($languageData['code'])) {
                        $languageCodes[$languageData['code']] = $languageData['code'];
                    }
                }
            }
        }

        if (!empty($languageCodes)) {
            foreach ($languageCodes as $languageCode) {
                // Load config
                Config::getInstance()->loadConfig($languageCode);

                // Save CSS
                CSS::getInstance()->save($languageCode);

                // Update style version
                $styleVersion = get_option('BorlabsCookieStyleVersion_' . $languageCode, 1);
                $styleVersion = (int) $styleVersion + 1;

                update_option('BorlabsCookieStyleVersion_' . $languageCode, $styleVersion, false);
            }
        } else {
            // Load config
            Config::getInstance()->loadConfig();

            // Save CSS
            CSS::getInstance()->save();
        }

        update_option('BorlabsCookieVersion', '2.1.8', 'no');
    }

    public function upgradeVersion_2_1_9()
    {
        global $wpdb;

        $charsetCollate = $wpdb->get_charset_collate();
        $tableNameScriptBlocker = $wpdb->prefix
            . 'borlabs_cookie_script_blocker'; // ->prefix contains base_prefix + blog id

        // Check if Script Blocker table is wrong schema
        $columnStatus = Install::getInstance()->checkIfColumnExists(
            $tableNameScriptBlocker,
            'content_blocker_id'
        );

        if ($columnStatus === true) {
            // Fix Script Blocker Table
            $wpdb->query('DROP TABLE IF EXISTS `' . $tableNameScriptBlocker . '`');

            $sqlCreateTableScriptBlocker = Install::getInstance()
                ->getCreateTableStatementScriptBlocker(
                    $tableNameScriptBlocker,
                    $charsetCollate
                );

            $wpdb->query($sqlCreateTableScriptBlocker);
        }

        update_option('BorlabsCookieVersion', '2.1.9', 'no');
    }

    public function upgradeVersion_2_2_0()
    {
        global $wpdb;

        // Update tables
        $tableNameCookies = $wpdb->prefix . 'borlabs_cookie_cookies';

        if (Install::getInstance()->checkIfTableExists($tableNameCookies)) {
            $wpdb->query(
                '
                ALTER TABLE
                    `' . $tableNameCookies . '`
                MODIFY
                    `cookie_name` TEXT NOT NULL
            '
            );

            $wpdb->query(
                '
                ALTER TABLE
                    `' . $tableNameCookies . '`
                MODIFY
                    `cookie_expiry` TEXT NOT NULL
            '
            );
        }

        update_option('BorlabsCookieVersion', '2.2.0', 'no');
    }

    public function upgradeVersion_2_2_2()
    {
        global $wpdb;

        Log::getInstance()->info(__METHOD__, 'Update Ezoic setup');

        $tableNameCookies = $wpdb->prefix . 'borlabs_cookie_cookies';

        if (Install::getInstance()->checkIfTableExists($tableNameCookies)) {
            $wpdb->query(
                '
                UPDATE
                    `' . $tableNameCookies . "`
                SET
                    `fallback_js` = '" . esc_sql(
                    Ezoic::getInstance()->getDefault()['fallbackJS']
                ) . "'
                WHERE
                    `service` = 'Ezoic'
            "
            );

            $wpdb->query(
                '
                UPDATE
                    `' . $tableNameCookies . "`
                SET
                    `settings` = 'a:2:{s:25:\"blockCookiesBeforeConsent\";s:1:\"0\";s:10:\"prioritize\";s:1:\"1\";}'
                WHERE
                    `service` = 'EzoicMarketing'
                    OR
                    `service` = 'EzoicPreferences'
                    OR
                    `service` = 'EzoicStatistics'
            "
            );
        }

        // Update Multilanguage
        $languageCodes = [];

        // Polylang
        if (defined('POLYLANG_VERSION')) {
            $polylangLanguages = get_terms('language', ['hide_empty' => false]);

            if (!empty($polylangLanguages)) {
                foreach ($polylangLanguages as $languageData) {
                    if (!empty($languageData->slug) && is_string($languageData->slug)) {
                        $languageCodes[$languageData->slug] = $languageData->slug;
                    }
                }
            }
        }

        // WPML
        if (defined('ICL_LANGUAGE_CODE')) {
            $wpmlLanguages = apply_filters('wpml_active_languages', null, []);

            if (!empty($wpmlLanguages)) {
                foreach ($wpmlLanguages as $languageData) {
                    if (!empty($languageData['code'])) {
                        $languageCodes[$languageData['code']] = $languageData['code'];
                    }
                }
            }
        }

        if (!empty($languageCodes)) {
            foreach ($languageCodes as $languageCode) {
                Log::getInstance()
                    ->info(__METHOD__, 'Update CSS of language {languageCode}', ['languageCode' => $languageCode]);

                // Load config
                Config::getInstance()->loadConfig($languageCode);

                // Save CSS
                CSS::getInstance()->save($languageCode);

                // Update style version
                $styleVersion = get_option('BorlabsCookieStyleVersion_' . $languageCode, 1);
                $styleVersion = (int) $styleVersion + 1;

                update_option('BorlabsCookieStyleVersion_' . $languageCode, $styleVersion, false);
            }
        } else {
            Log::getInstance()->info(__METHOD__, 'Update CSS');

            // Load config
            Config::getInstance()->loadConfig();

            // Save CSS
            CSS::getInstance()->save();
        }

        update_option('BorlabsCookieClearCache', true, 'no');
        update_option('BorlabsCookieVersion', '2.2.2', 'no');
        Log::getInstance()->info(__METHOD__, 'Upgrade complete');
    }

    public function upgradeVersion_2_2_29()
    {
        global $wpdb;

        $tableNameCookies = $wpdb->prefix . 'borlabs_cookie_cookies';

        if (Install::getInstance()->checkIfTableExists($tableNameCookies)) {
            $wpdb->query(
                '
                UPDATE
                    `' . $tableNameCookies . "`
                SET
                    `privacy_policy_url` = 'https://wiki.osmfoundation.org/wiki/Privacy_Policy'
                WHERE
                    `privacy_policy_url` = 'https://wiki.osmfoundation.org/wiki/Privacy_Politik'
            "
            );
        }

        $tableNameCookies = $wpdb->prefix . 'borlabs_cookie_content_blocker';

        if (Install::getInstance()->checkIfTableExists($tableNameCookies)) {
            $wpdb->query(
                '
                UPDATE
                    `' . $tableNameCookies . "`
                SET
                    `privacy_policy_url` = 'https://wiki.osmfoundation.org/wiki/Privacy_Policy'
                WHERE
                    `privacy_policy_url` = 'https://wiki.osmfoundation.org/wiki/Privacy_Politik'
            "
            );
        }

        update_option('BorlabsCookieClearCache', true, 'no');
        update_option('BorlabsCookieVersion', '2.2.29', 'no');
        Log::getInstance()->info(__METHOD__, 'Upgrade complete');
    }

    public function upgradeVersion_2_2_3()
    {
        global $wpdb;

        Log::getInstance()->info(__METHOD__, 'Update Ezoic setup');

        $tableNameCookies = $wpdb->prefix . 'borlabs_cookie_cookies';

        if (Install::getInstance()->checkIfTableExists($tableNameCookies)) {
            $wpdb->query(
                '
                UPDATE
                    `' . $tableNameCookies . "`
                SET
                    `settings` = 'a:2:{s:25:\"blockCookiesBeforeConsent\";s:1:\"0\";s:10:\"prioritize\";s:1:\"1\";}',
                    `opt_in_js` = '" . esc_sql(
                    Ezoic::getInstance()->getDefault()['optInJS']
                ) . "',
                    `fallback_js` = ''
                WHERE
                    `service` = 'Ezoic'
            "
            );

            $wpdb->query(
                '
                UPDATE
                    `' . $tableNameCookies . "`
                SET
                    `settings` = 'a:2:{s:25:\"blockCookiesBeforeConsent\";s:1:\"0\";s:10:\"prioritize\";s:1:\"1\";}',
                    `opt_out_js` = '" . esc_sql(
                    EzoicMarketing::getInstance()->getDefault()['optOutJS']
                ) . "'
                WHERE
                    `service` = 'EzoicMarketing'
            "
            );

            $wpdb->query(
                '
                UPDATE
                    `' . $tableNameCookies . "`
                SET
                    `settings` = 'a:2:{s:25:\"blockCookiesBeforeConsent\";s:1:\"0\";s:10:\"prioritize\";s:1:\"1\";}',
                    `opt_out_js` = '" . esc_sql(
                    EzoicPreferences::getInstance()->getDefault()['optOutJS']
                ) . "'
                WHERE
                    `service` = 'EzoicPreferences'
            "
            );

            $wpdb->query(
                '
                UPDATE
                    `' . $tableNameCookies . "`
                SET
                    `settings` = 'a:2:{s:25:\"blockCookiesBeforeConsent\";s:1:\"0\";s:10:\"prioritize\";s:1:\"1\";}',
                    `opt_out_js` = '" . esc_sql(
                    EzoicStatistics::getInstance()->getDefault()['optOutJS']
                ) . "'
                WHERE
                    `service` = 'EzoicStatistics'
            "
            );
        }

        update_option('BorlabsCookieClearCache', true, 'no');
        update_option('BorlabsCookieVersion', '2.2.3', 'no');
        Log::getInstance()->info(__METHOD__, 'Upgrade complete');
    }

    public function upgradeVersion_2_2_43()
    {
        global $wpdb;

        // Change povider column length
        $tableNameCookies = $wpdb->prefix . 'borlabs_cookie_cookies';

        if (Install::getInstance()->checkIfTableExists($tableNameCookies)) {
            $wpdb->query(
                '
                ALTER TABLE
                    ' . $tableNameCookies . "
                MODIFY
                    `provider` varchar(255) NOT NULL DEFAULT ''
            "
            );
        }

        // Update address of cookies
        $tableNameCookies = $wpdb->prefix . 'borlabs_cookie_cookies';

        if (Install::getInstance()->checkIfTableExists($tableNameCookies)) {
            // Ezoic
            $wpdb->query(
                '
                UPDATE
                    `' . $tableNameCookies . "`
                SET
                    `provider` = '" . esc_sql(
                    'Ezoic Inc, 6023 Innovation Way 2nd Floor, Carlsbad, CA 92009, USA'
                ) . "'
                WHERE
                    `service` = 'Ezoic'
                    OR
                    `service` = 'EzoicMarketing'
                    OR
                    `service` = 'EzoicPreferences'
                    OR
                    `service` = 'EzoicStatistics'
            "
            );
            // Facebook
            $wpdb->query(
                '
                UPDATE
                    `' . $tableNameCookies . "`
                SET
                    `provider` = '" . esc_sql('Meta Platforms Ireland Limited, 4 Grand Canal Square, Dublin 2, Ireland')
                . "'
                WHERE
                    `service` = 'FacebookPixel'
                    OR
                    `cookie_id` = 'facebook'
                    OR
                    `cookie_id` = 'instagram'
            "
            );
            // Google
            $wpdb->query(
                '
                UPDATE
                    `' . $tableNameCookies . "`
                SET
                    `provider` = '" . esc_sql('Google Ireland Limited, Gordon House, Barrow Street, Dublin 4, Ireland')
                . "'
                WHERE
                    `service` = 'GoogleAds'
                    OR
                    `service` = 'GoogleAdSense'
                    OR
                    `service` = 'GoogleAnalytics'
                    OR
                    `service` = 'GoogleTagManager'
                    OR
                    `service` = 'GoogleTagManagerConsent'
                    OR
                    `cookie_id` = 'googlemaps'
                    OR
                    `cookie_id` = 'youtube'
            "
            );
            // Hotjar
            $wpdb->query(
                '
                UPDATE
                    `' . $tableNameCookies . "`
                SET
                    `provider` = '"
                . esc_sql(
                    'Hotjar Ltd., Dragonara Business Centre, 5th Floor, Dragonara Road, Paceville St Julian\'s STJ 3141 Malta'
                ) . "'
                WHERE
                    `service` = 'Hotjar'
            "
            );
            // HubSpot
            $wpdb->query(
                '
                UPDATE
                    `' . $tableNameCookies . "`
                SET
                    `provider` = '" . esc_sql('HubSpot Inc., 25 First Street, 2nd Floor, Cambridge, MA 02141, USA') . "'
                WHERE
                    `service` = 'HubSpot'
            "
            );
            // OpenStreetMap
            $wpdb->query(
                '
                UPDATE
                    `' . $tableNameCookies . "`
                SET
                    `provider` = '"
                . esc_sql(
                    'Openstreetmap Foundation, St John’s Innovation Centre, Cowley Road, Cambridge CB4 0WS, United Kingdom'
                ) . "'
                WHERE
                    `cookie_id` = 'openstreetmap'
            "
            );
            // Tidio
            $wpdb->query(
                '
                UPDATE
                    `' . $tableNameCookies . "`
                SET
                    `provider` = '" . esc_sql('Tidio LLC, 220C Blythe Road, London W14 0HH, United Kingdom') . "'
                WHERE
                    `service` = 'Tidio'
            "
            );
            // Twitter
            $wpdb->query(
                '
                UPDATE
                    `' . $tableNameCookies . "`
                SET
                    `provider` = '"
                . esc_sql(
                    'Twitter International Company, One Cumberland Place, Fenian Street, Dublin 2, D02 AX07, Ireland'
                ) . "'
                WHERE
                    `cookie_id` = 'twitter'
            "
            );
            // Userlike
            $wpdb->query(
                '
                UPDATE
                    `' . $tableNameCookies . "`
                SET
                    `provider` = '" . esc_sql('Userlike UG, Probsteigasse 44-46, 50670 Köln') . "'
                WHERE
                    `service` = 'Userlike'
            "
            );
            // Vimeo
            $wpdb->query(
                '
                UPDATE
                    `' . $tableNameCookies . "`
                SET
                    `provider` = '" . esc_sql('Vimeo Inc., 555 West 18th Street, New York, New York 10011, USA') . "'
                WHERE
                    `cookie_id` = 'vimeo'
            "
            );
        }

        update_option('BorlabsCookieClearCache', true, 'no');
        update_option('BorlabsCookieVersion', '2.2.43', 'yes');
        Log::getInstance()->info(__METHOD__, 'Upgrade complete');
    }

    public function upgradeVersion_2_2_44()
    {
        $languageCodes = $this->getLanguageCodes();

        if (!empty($languageCodes)) {
            foreach ($languageCodes as $languageCode) {
                Log::getInstance()
                    ->info(__METHOD__, 'Update CSS of language {languageCode}', ['languageCode' => $languageCode]);

                // Load config
                Config::getInstance()->loadConfig($languageCode);
                // Save CSS
                CSS::getInstance()->save($languageCode);
                // Update style version
                $styleVersion = get_option('BorlabsCookieStyleVersion_' . $languageCode, 1);
                $styleVersion = (int) $styleVersion + 1;
                update_option('BorlabsCookieStyleVersion_' . $languageCode, $styleVersion, false);
            }
        } else {
            Log::getInstance()->info(__METHOD__, 'Update CSS');
            // Load config
            Config::getInstance()->loadConfig();
            // Save CSS
            CSS::getInstance()->save();
        }

        update_option('BorlabsCookieClearCache', true, 'no');
        update_option('BorlabsCookieVersion', '2.2.44', 'yes');
        Log::getInstance()->info(__METHOD__, 'Upgrade complete');
    }

    public function upgradeVersion_2_2_45()
    {
        $languageCodes = $this->getLanguageCodes();

        if (!empty($languageCodes)) {
            foreach ($languageCodes as $languageCode) {
                Log::getInstance()
                    ->info(__METHOD__, 'Update CSS of language {languageCode}', ['languageCode' => $languageCode]);

                // Load config
                Config::getInstance()->loadConfig($languageCode);
                // Save CSS
                CSS::getInstance()->save($languageCode);
                // Update style version
                $styleVersion = get_option('BorlabsCookieStyleVersion_' . $languageCode, 1);
                $styleVersion = (int) $styleVersion + 1;
                update_option('BorlabsCookieStyleVersion_' . $languageCode, $styleVersion, false);
            }
        } else {
            Log::getInstance()->info(__METHOD__, 'Update CSS');
            // Load config
            Config::getInstance()->loadConfig();
            // Save CSS
            CSS::getInstance()->save();
        }

        update_option('BorlabsCookieClearCache', true, 'no');
        update_option('BorlabsCookieVersion', '2.2.45', 'yes');
        Log::getInstance()->info(__METHOD__, 'Upgrade complete');
    }

    public function upgradeVersion_2_2_46_1()
    {
        global $wpdb;

        // Add new table
        $tableNameStatistics = $wpdb->prefix . 'borlabs_cookie_statistics';

        if (Install::getInstance()->checkIfTableExists($tableNameStatistics) === false) {
            $charsetCollate = $wpdb->get_charset_collate();
            $sqlCreateTableStatistics = Install::getInstance()->getCreateTableStatementStatistics($tableNameStatistics, $charsetCollate);

            $wpdb->query($sqlCreateTableStatistics);
        }

        $languageCodes = $this->getLanguageCodes();

        if (!empty($languageCodes)) {
            foreach ($languageCodes as $languageCode) {
                Log::getInstance()->info(__METHOD__, 'Update CSS: ' . $languageCode);
                $updatedConfig = Config::getInstance()->loadConfig($languageCode);
                $updatedConfig['cookieBoxWidgetColor'] = '#0063e3';
                $updatedConfig['cookieBoxShowWidget'] = false;
                $updatedConfig['cookieBoxWidgetPosition'] = 'bottom-left';
                $updatedConfig['reloadAfterOptOut'] = true;
                $updatedConfig['cookieBoxIndividualSettingsBtnColor'] = '#000';
                $updatedConfig['cookieBoxIndividualSettingsBtnHoverColor'] = '#262626';
                $updatedConfig['cookieBoxIndividualSettingsBtnTxtColor'] = '#fff';
                $updatedConfig['cookieBoxIndividualSettingsBtnHoverTxtColor'] = '#fff';
                update_option('BorlabsCookieConfig_' . $languageCode, $updatedConfig, 'no');
                // Load config
                Config::getInstance()->loadConfig($languageCode);
                // Save CSS
                CSS::getInstance()->save($languageCode);
            }
        } else {
            $configs = $this->getConfigs();

            foreach ($configs as $optionName => $languageCode) {
                $updatedConfig = Config::getInstance()->loadConfig($languageCode);
                $updatedConfig['cookieBoxWidgetColor'] = '#0063e3';
                $updatedConfig['cookieBoxShowWidget'] = false;
                $updatedConfig['cookieBoxWidgetPosition'] = 'bottom-left';
                $updatedConfig['reloadAfterOptOut'] = true;
                $updatedConfig['cookieBoxIndividualSettingsBtnColor'] = '#000';
                $updatedConfig['cookieBoxIndividualSettingsBtnHoverColor'] = '#262626';
                $updatedConfig['cookieBoxIndividualSettingsBtnTxtColor'] = '#fff';
                $updatedConfig['cookieBoxIndividualSettingsBtnHoverTxtColor'] = '#fff';
                update_option('BorlabsCookieConfig_' . $languageCode, $updatedConfig, 'no');
                // Load config
                Config::getInstance()->loadConfig($languageCode);
                // Save CSS
                CSS::getInstance()->save($languageCode);
            }
        }

        update_option('BorlabsCookieClearCache', true, 'no');
        update_option('BorlabsCookieVersion', '2.2.46.1', 'yes');

        // Migration as last to avoid multiple migrations due to timeouts
        $tableCookieConsentLog = $wpdb->prefix . 'borlabs_cookie_consent_log';
        $cookieVersion = get_site_option('BorlabsCookieCookieVersion', 1);
        $consentCount = $wpdb->get_var('
            SELECT
                   COUNT(*)
            FROM
                 `' . $tableCookieConsentLog . "`
            WHERE
                `is_latest` = 1
                AND
                `cookie_version` = '" . esc_sql($cookieVersion) . "'
                AND
                `stamp` >= NOW() - INTERVAL 30 DAY
           ");
        // Limit migration
        if ((int) $consentCount > 100000) {
            $consentCount = 100000;
        }
        $chunkSize = 5000;
        $chunks = ceil($consentCount / $chunkSize);

        for ($i = 0; $i < $chunks; ++$i) {
            $consentsLogs = $wpdb->get_results(
                '
                SELECT
                    `consents`,
                    `stamp`
                FROM
                    `' . $tableCookieConsentLog . "`
                WHERE
                    `is_latest` = 1
                    AND
                    `cookie_version` = '" . esc_sql($cookieVersion) . "'
                ORDER BY
                    `stamp` DESC
                LIMIT
                    " . ($i * $chunkSize) . ', ' . $chunkSize . '
            '
            );

            if ($consentsLogs === null) {
                Log::getInstance()->error(__METHOD__, 'Consent migration failed');

                break;
            }
            $values = [];

            foreach ($consentsLogs as $consentsLog) {
                $consentData = unserialize($consentsLog->consents);
                $serviceGroups = array_keys($consentData);

                foreach ($serviceGroups as $serviceGroup) {
                    array_push($values, "('" . $serviceGroup . "','" . $consentsLog->stamp . "')");
                }
            }

            if (count($values) > 0) {
                $tableStatistics = $wpdb->prefix . 'borlabs_cookie_statistics';
                $result = $wpdb->query(
                    'INSERT INTO `' . $tableStatistics . '` (`service_group`, `stamp`) VALUES ' . implode(',', $values)
                );

                if ($result === false) {
                    Log::getInstance()->error(__METHOD__, 'Consent migration failed');

                    break;
                }
            }
        }

        Log::getInstance()->info(__METHOD__, 'Upgrade complete');
    }

    public function upgradeVersion_2_2_47()
    {
        global $wpdb;

        // Add new key
        $tableStatistics = $wpdb->prefix . 'borlabs_cookie_statistics';

        if (Install::getInstance()->checkIfTableExists($tableStatistics) === true) {
            $checkNewKey = $wpdb->query('
                SHOW
                    INDEXES
                FROM
                    `' . $tableStatistics . '`
                WHERE
                    `Key_name` = \'service_group_stamp\'
            ');

            if (!$checkNewKey) {
                // Add key
                $wpdb->query(
                    '
                    ALTER TABLE
                        `' . $tableStatistics . '`
                    ADD KEY
                        `service_group_stamp` (`stamp`, `service_group`)
                '
                );
            }
        }

        $languageCodes = $this->getLanguageCodes();

        if (!empty($languageCodes)) {
            foreach ($languageCodes as $languageCode) {
                Log::getInstance()->info(__METHOD__, 'Update CSS of language {languageCode}', ['languageCode' => $languageCode]);
                // Load config
                Config::getInstance()->loadConfig($languageCode);
                // Save CSS
                CSS::getInstance()->save($languageCode);
            }
        } else {
            $configs = $this->getConfigs();

            foreach ($configs as $optionName => $languageCode) {
                Log::getInstance()->info(__METHOD__, 'Update CSS of language {languageCode}', ['languageCode' => $languageCode]);
                // Load config
                Config::getInstance()->loadConfig($languageCode);
                // Save CSS
                CSS::getInstance()->save($languageCode);
            }
        }

        update_option('BorlabsCookieClearCache', true, 'no');
        update_option('BorlabsCookieVersion', '2.2.47', 'yes');
        Log::getInstance()->info(__METHOD__, 'Upgrade complete');
    }

    public function upgradeVersion_2_2_49()
    {
        update_option('BorlabsCookieClearCache', true, 'no');
        update_option('BorlabsCookieVersion', '2.2.49', 'yes');
        Log::getInstance()->info(__METHOD__, 'Upgrade complete');
    }

    public function upgradeVersion_2_2_50()
    {
        global $wpdb;

        $tableNameStatistics = $wpdb->prefix . 'borlabs_cookie_statistics';
        $columnStatus = Install::getInstance()->checkIfColumnExists(
            $tableNameStatistics,
            'id'
        );

        if ($columnStatus === false) {
            $wpdb->query('ALTER TABLE `' . $tableNameStatistics . '` ADD `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);');
        }

        update_option('BorlabsCookieClearCache', true, 'no');
        update_option('BorlabsCookieVersion', '2.2.50', 'yes');
        Log::getInstance()->info(__METHOD__, 'Upgrade complete');
    }

    public function upgradeVersion_2_2_56()
    {
        update_option('BorlabsCookieClearCache', true, 'yes');
        update_option('BorlabsCookieVersion', '2.2.56', 'yes');
        Log::getInstance()->info(__METHOD__, 'Upgrade complete');
    }

    public function upgradeVersion_2_2_57()
    {
        global $wpdb;

        $charsetCollate = $wpdb->get_charset_collate();
        $tableName = $wpdb->prefix
            . 'borlabs_cookie_statistics'; // ->prefix contains base_prefix + blog id

        // Check if Statistic table is wrong schema
        $columnStatus = Install::getInstance()->checkIfColumnExists($tableName, 'script_blocker_id');

        if ($columnStatus === true) {
            // Fix Statistics table
            $wpdb->query('DROP TABLE IF EXISTS `' . $tableName . '`');

            $sqlCreateTableStatistics = Install::getInstance()->getCreateTableStatementStatistics(
                $tableName,
                $charsetCollate
            );

            $wpdb->query($sqlCreateTableStatistics);
        }

        update_option('BorlabsCookieClearCache', true, 'yes');
        update_option('BorlabsCookieVersion', '2.2.57', 'yes');
        Log::getInstance()->info(__METHOD__, 'Upgrade complete');
    }

    public function upgradeVersion_2_2_6()
    {
        global $wpdb;

        Log::getInstance()->info(__METHOD__, 'Update Ezoic setup');

        $tableNameCookies = $wpdb->prefix . 'borlabs_cookie_cookies';

        if (Install::getInstance()->checkIfTableExists($tableNameCookies)) {
            $wpdb->query(
                '
                UPDATE
                    `' . $tableNameCookies . "`
                SET
                    `settings` = 'a:2:{s:25:\"blockCookiesBeforeConsent\";s:1:\"0\";s:10:\"prioritize\";s:1:\"1\";}',
                    `opt_in_js` = '" . esc_sql(
                    Ezoic::getInstance()->getDefault()['optInJS']
                ) . "',
                    `fallback_js` = ''
                WHERE
                    `service` = 'Ezoic'
            "
            );

            $wpdb->query(
                '
                UPDATE
                    `' . $tableNameCookies . "`
                SET
                    `settings` = 'a:2:{s:25:\"blockCookiesBeforeConsent\";s:1:\"0\";s:10:\"prioritize\";s:1:\"1\";}',
                    `opt_in_js` = '" . esc_sql(
                    EzoicMarketing::getInstance()->getDefault()['optInJS']
                ) . "',
                    `opt_out_js` = '" . esc_sql(
                    EzoicMarketing::getInstance()->getDefault()['optOutJS']
                ) . "'
                WHERE
                    `service` = 'EzoicMarketing'
            "
            );

            $wpdb->query(
                '
                UPDATE
                    `' . $tableNameCookies . "`
                SET
                    `settings` = 'a:2:{s:25:\"blockCookiesBeforeConsent\";s:1:\"0\";s:10:\"prioritize\";s:1:\"1\";}',
                    `opt_in_js` = '" . esc_sql(
                    EzoicPreferences::getInstance()->getDefault()['optInJS']
                ) . "',
                    `opt_out_js` = '" . esc_sql(
                    EzoicPreferences::getInstance()->getDefault()['optOutJS']
                ) . "'
                WHERE
                    `service` = 'EzoicPreferences'
            "
            );

            $wpdb->query(
                '
                UPDATE
                    `' . $tableNameCookies . "`
                SET
                    `settings` = 'a:2:{s:25:\"blockCookiesBeforeConsent\";s:1:\"0\";s:10:\"prioritize\";s:1:\"1\";}',
                    `opt_in_js` = '" . esc_sql(
                    EzoicStatistics::getInstance()->getDefault()['optInJS']
                ) . "',
                    `opt_out_js` = '" . esc_sql(
                    EzoicStatistics::getInstance()->getDefault()['optOutJS']
                ) . "'
                WHERE
                    `service` = 'EzoicStatistics'
            "
            );
        }

        update_option('BorlabsCookieClearCache', true, 'no');
        update_option('BorlabsCookieVersion', '2.2.6', 'no');
        Log::getInstance()->info(__METHOD__, 'Upgrade complete');
    }

    public function upgradeVersion_2_2_61()
    {
        update_option('BorlabsCookieClearCache', true, 'yes');
        update_option('BorlabsCookieVersion', '2.2.61', 'yes');
        Log::getInstance()->info(__METHOD__, 'Upgrade complete');
    }

    public function upgradeVersion_2_2_62()
    {
        update_option('BorlabsCookieClearCache', true, 'yes');
        update_option('BorlabsCookieVersion', '2.2.62', 'yes');
        Log::getInstance()->info(__METHOD__, 'Upgrade complete');
    }

    public function upgradeVersion_2_2_63()
    {
        update_option('BorlabsCookieClearCache', true, 'yes');
        update_option('BorlabsCookieVersion', '2.2.63', 'yes');
        Log::getInstance()->info(__METHOD__, 'Upgrade complete');
    }

    public function upgradeVersion_2_2_64()
    {
        update_option('BorlabsCookieClearCache', true, 'yes');
        update_option('BorlabsCookieVersion', '2.2.64', 'yes');
        Log::getInstance()->info(__METHOD__, 'Upgrade complete');
    }

    public function upgradeVersion_2_2_65()
    {
        update_option('BorlabsCookieClearCache', true, 'yes');
        update_option('BorlabsCookieVersion', '2.2.65', 'yes');
        Log::getInstance()->info(__METHOD__, 'Upgrade complete');
    }

    public function upgradeVersion_2_2_66()
    {
        update_option('BorlabsCookieClearCache', true, 'yes');
        update_option('BorlabsCookieVersion', '2.2.66', 'yes');
        Log::getInstance()->info(__METHOD__, 'Upgrade complete');
    }

    public function upgradeVersion_2_2_67()
    {
        update_option('BorlabsCookieClearCache', true, 'yes');
        update_option('BorlabsCookieVersion', '2.2.67', 'yes');
        Log::getInstance()->info(__METHOD__, 'Upgrade complete');
    }

    public function upgradeVersion_2_2_9()
    {
        // Update Multilanguage
        $languageCodes = [];

        // Polylang
        if (defined('POLYLANG_VERSION')) {
            $polylangLanguages = get_terms('language', ['hide_empty' => false]);

            if (!empty($polylangLanguages)) {
                foreach ($polylangLanguages as $languageData) {
                    if (!empty($languageData->slug) && is_string($languageData->slug)) {
                        $languageCodes[$languageData->slug] = $languageData->slug;
                    }
                }
            }
        }

        // WPML
        if (defined('ICL_LANGUAGE_CODE')) {
            $wpmlLanguages = apply_filters('wpml_active_languages', null, []);

            if (!empty($wpmlLanguages)) {
                foreach ($wpmlLanguages as $languageData) {
                    if (!empty($languageData['code'])) {
                        $languageCodes[$languageData['code']] = $languageData['code'];
                    }
                }
            }
        }

        if (!empty($languageCodes)) {
            foreach ($languageCodes as $languageCode) {
                Log::getInstance()
                    ->info(__METHOD__, 'Update CSS of language {languageCode}', ['languageCode' => $languageCode]);

                // Load config
                Config::getInstance()->loadConfig($languageCode);

                // Save CSS
                CSS::getInstance()->save($languageCode);

                // Update style version
                $styleVersion = get_option('BorlabsCookieStyleVersion_' . $languageCode, 1);
                $styleVersion = (int) $styleVersion + 1;

                update_option('BorlabsCookieStyleVersion_' . $languageCode, $styleVersion, false);
            }
        } else {
            Log::getInstance()->info(__METHOD__, 'Update CSS');

            // Load config
            Config::getInstance()->loadConfig();

            // Save CSS
            CSS::getInstance()->save();
        }

        update_option('BorlabsCookieClearCache', true, 'no');
        update_option('BorlabsCookieVersion', '2.2.9', 'no');

        Log::getInstance()->info(__METHOD__, 'Upgrade complete');
    }

    private function getConfigs()
    {
        global $wpdb;

        $configs = [];
        $allConfigs = $wpdb->get_results('
            SELECT
                `option_name`
            FROM
                `' . $wpdb->options . '`
            WHERE
                `option_name` LIKE \'BorlabsCookieConfig_%\'
        ');

        foreach ($allConfigs as $optionData) {
            $configs[$optionData->option_name] = str_replace('BorlabsCookieConfig_', '', $optionData->option_name);
        }

        return $configs;
    }

    private function getLanguageCodes()
    {
        $languageCodes = [];

        // Polylang
        if (defined('POLYLANG_VERSION')) {
            $polylangLanguages = get_terms('language', ['hide_empty' => false]);

            if (!empty($polylangLanguages)) {
                foreach ($polylangLanguages as $languageData) {
                    if (!empty($languageData->slug) && is_string($languageData->slug)) {
                        $languageCodes[$languageData->slug] = $languageData->slug;
                    }
                }
            }
        }

        // WPML
        if (defined('ICL_LANGUAGE_CODE')) {
            $wpmlLanguages = apply_filters('wpml_active_languages', null, []);

            if (!empty($wpmlLanguages)) {
                foreach ($wpmlLanguages as $languageData) {
                    if (!empty($languageData['code'])) {
                        $languageCodes[$languageData['code']] = $languageData['code'];
                    }
                }
            }
        }

        // Weglot
        if (function_exists('weglot_get_original_language') && function_exists('weglot_get_destination_languages')) {
            $originalLanguageCode = weglot_get_original_language();
            $languageCodes = array_merge($languageCodes, [
                $originalLanguageCode => $originalLanguageCode,
            ]);

            foreach (weglot_get_destination_languages() as $destination) {
                $languageCodes = array_merge($languageCodes, [
                    $destination['language_to'] => $destination['language_to'],
                ]);
            }
        }

        return $languageCodes;
    }
}
