<?php
/**
 * WPS Shortcode handling
 *
 * @link https://wp-stars.com
 * @since 1.0.0
 * @package Shortcodes
 */

namespace Shortcodes;

use const THEME_DIR;

/**
 *  Autoload all shortcodes
 */
function autoload_shortcodes() {
	foreach (glob(THEME_DIR . 'shortcodes/*', GLOB_ONLYDIR) as $dir) {
		require_once trailingslashit($dir) . 'index.php';
	}
}

autoload_shortcodes();
