<?php
/**
 * WPS Shortcode handling
 *
 * @link https://wp-stars.com
 * @since 1.0.0
 * @package WPS\Shortcodes
 */

namespace WPS\Shortcodes;

use const WPS\THEME_DIR;

/**
 *  Autoload all shortcodes
 */
function autoload_shortcodes() {
	foreach (glob(THEME_DIR . 'shortcodes/*', GLOB_ONLYDIR) as $dir) {
		require_once trailingslashit($dir) . 'index.php';
	}
}

autoload_shortcodes();
