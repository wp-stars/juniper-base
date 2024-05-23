<?php
/**
 * LimeSoda Shortcode
 *
 * @link https://limesoda.com
 * @since 1.0.0
 * @package Limesoda\\Astra_Child\\Shortcodes\\Metalprices
 */

namespace Limesoda\Astra_Child\Shortcodes\Metalprices;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

if (!class_exists('Metalprices')) {

	/**
	 * Metal Prices
	 *
	 * @since 1.0.0
	 * @package Limesoda\\Astra_Child\\Shortcodes\\Metalprices
	 * @author LIMESODA Team Undefined <support-wordpress@limesoda.com>
	 */
	class Metalprices {

		/**
		 * Set class instance
		 *
		 * @var $instance
		 */
		private static $instance;

		/**
		 * Set shortcode slug name
		 *
		 * @var string
		 */
		private static string $slug = 'metalprices';

		/**
		 * Localized Vars
		 *
		 * @var array
		 */
		private static array $localized_vars = [];

		/**
		 * Initiator
		 *
		 * @return object initialized object of class.
		 */
		public static function get_instance() {
			if (!isset(self::$instance)) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 */
		public function __construct() {
			add_action(
				'init',
				function () {
					add_shortcode(self::$slug, [$this, 'shortcode_markup']);
				}
			);
			add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
		}

		/**
		 * Creates markup for shortcode
		 *
		 * @param array|string $attributes shortcode attributes.
		 * @return string html markup
		 */
		public function shortcode_markup($attributes = []): string {
			return '<div id="react-metalprices"></div>';
		}

		/**
		 * Enqueue Assets for Shortcode
		 *
		 * @return void
		 */
		public function enqueue_scripts() {
			global $post;

            if (!defined('THEME_DIR')) {
                define('THEME_DIR', get_template_directory() . '/');
            }

            if (!defined('THEME_URI')) {
                define('THEME_URI', get_template_directory_uri() . '/');
            }

			if (has_shortcode($post->post_content, self::$slug)) {

				$asset_file = include THEME_DIR . 'shortcodes/' . self::$slug . '/index.asset.php';
				wp_enqueue_style(
					'ls_shortcode_style_' . self::$slug,
					THEME_URI . 'shortcodes/' . self::$slug . '/index.css',
					[],
					filemtime(THEME_DIR . 'shortcodes/' . self::$slug . '/index.css')
				);
				wp_enqueue_script(
					'ls_shortcode_script_' . self::$slug,
					THEME_URI . 'shortcodes/' . self::$slug . '/index.js',
					$asset_file['dependencies'],
					$asset_file['version'],
					true
				);

				$wp_vars = [
					'metals' => [
						['key' => 'gold', 'label' => __('Gold', 'iwgplating'), 'number' => 79, 'short' => 'Au', 'unit' => '€/g'],
						['key' => 'silver', 'label' => __('Silver', 'iwgplating'), 'number' => 47, 'short' => 'Ag', 'unit' => '€/kg'],
						['key' => 'platin', 'label' => __('Platin', 'iwgplating'), 'number' => 78, 'short' => 'Pt', 'unit' => '€/g'],
						['key' => 'palladium', 'label' => __('Palladium', 'iwgplating'), 'number' => 46, 'short' => 'Pd', 'unit' => '€/g'],
						['key' => 'rhodium', 'label' => __('Rhodium', 'iwgplating'), 'number' => 45, 'short' => 'Rh', 'unit' => '€/g'],
						['key' => 'ruthenium', 'label' => __('Ruthenium', 'iwgplating'), 'number' => 44, 'short' => 'Ru', 'unit' => '€/g'],
					],
					'ranges' => [
						['key' => 'day', 'label' => __('Daily value', 'iwgplating'), 'days' => 1],
						['key' => 'week', 'label' => __('Weekly value', 'iwgplating'), 'days' => 7],
						['key' => 'month', 'label' => __('Monthly value', 'iwgplating'), 'days' => 30],
						['key' => 'year', 'label' => __('Annual value', 'iwgplating'), 'days' => 365],
					],
				];

				wp_localize_script(
					'ls_shortcode_script_' . self::$slug,
					'wpVars',
					array_merge(self::$localized_vars, $wp_vars)
				);
			}
		}
	}
}
return Metalprices::get_instance();
