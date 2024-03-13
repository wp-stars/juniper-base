<?php

/**
 * WPS BMD API
 *
 * @package           PluginPackage
 * @author            wp-stars
 * @copyright         2019 wp-stars gmbh
 *
 * @wordpress-plugin
 * Plugin Name:       WPS BMD API
 * Plugin URI:        https://wp-stars.com
 * Description:       Connects Woocommerce to BMDs
 * Version:           1.0.0
 * Requires PHP:      8.2
 * Author:            wp-stars gmbh
 * Author URI:        https://wp-stars.com
 * Text Domain:       wps-bmd-api
 */

require_once __DIR__ . '/classes/Plugin.php';
require_once __DIR__ . '/classes/Exporter.php';

new \wps\bmd\Plugin();