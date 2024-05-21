<?php
/**
 * LimeSoda Gutenberg Blocks
 * Handles registration of assets for project specific Gutenberg blocks
 * and block overrides for existing blocks (core & third party)
 *
 * @link https://limesoda.com
 * @since 1.0.0
 * @package Limesoda\\Astra_Child\\Blocks
 */

namespace WPS\Blocks;

use WP_Block_Type_Registry;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

if (!defined('THEME_DIR')) {
    define('THEME_DIR', get_template_directory() . '/');
}

if (!defined('THEME_URI')) {
    define('THEME_URI', get_template_directory_uri() . '/');
}

/**
 * Gutenberg_Blocks
 */
if (!class_exists('Gutenberg_Blocks')) {

	/**
	 * Gutenberg_Blocks
	 *
	 * @since 1.0.0
	 * @package Limesoda\\Astra_Child\\Blocks
	 * @author LIMESODA Team Undefined <support-wordpress@limesoda.com>
	 */
	class Gutenberg_Blocks {

		/**
		 * Set class instance
		 *
		 * @var $instance
		 */
		private static $instance;

		/**
		 * Block List
		 *
		 * @var array $blocks
		 */
		public static array $blocks = [];

		/**
		 * Additional gutenberg block dependencies
		 *
		 * @var array $additional_block_dependencies
		 */
		private static array $additional_block_dependencies = [];

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
			// Skip block registration if Gutenberg is not enabled/merged.
			if (!function_exists('register_block_type')) {
				return;
			}

			$this->set_blocks();

			// Register block category.
			add_filter('block_categories_all', [$this, 'add_block_category']);

			// Register assets for all blocks.
			add_action('init', [$this, 'register_block_assets']);

			// Enqueue assets for frontend (frontend.js, frontend.css, index.css).
			add_action('wp_enqueue_scripts', [$this, 'enqueue_block_override_frontend_assets'], 50);

			// Enqueue assets for backend (admin.css, index.js, index.css).
			add_action('enqueue_block_editor_assets', [$this, 'enqueue_block_override_editor_assets']);

			// Load string translations for all blocks.
			add_action('init', [$this, 'load_script_translations']);
		}

		/**
		 * Registers additional Gutenberg block dependencies for a page
		 * NOTE: Registers only block assets registered by this class (<theme>/ls-blocks)
		 * Registering of other blocks from core / plugin block assets is highly depending on the respective implementation
		 *
		 * @param array $blocks to register.
		 */
		public function register_additional_block_dependencies(array $blocks = []) {
			$blocks = array_merge(self::$additional_block_dependencies, $blocks);
			self::$additional_block_dependencies = $blocks;
		}

		/**
		 * Add a block category for "LimeSoda" if it doesn't exist already.
		 *
		 * @param array $categories Array of block categories.
		 * @return array
		 */
		public function add_block_category(array $categories): array {
			$category_slugs = wp_list_pluck($categories, 'slug');

			return in_array('ls', $category_slugs, true) ? $categories : array_merge(
				$categories,
				[
					[
						'slug' => 'ls',
						'title' => __('LimeSoda', 'iwgplating'),
						'icon' => null,
					],
				]
			);
		}

		/**
		 * Set all blocks that need to be registered by gutenberg
		 *
		 * Block assets must be placed inside <theme>/ls-blocks/<slug>/
		 * Entry points: JavaScript - index.js; Style - index.css; Admin Style - admin.css
		 * Make sure to include the entry points in the webpack config
		 *
		 * options for <theme>/ls-blocks/<slug>/index.php return array:
		 * 'slug': name of block
		 * 'localized_vars': variables available in JS
		 * 'group': (optional) block group slug. defaults to 'ls'
		 * 'render_callback' (optional) if set registers render callback
		 *
		 * @return void
		 */
		public function set_blocks() {
			self::$blocks = array_map(
				function($dir) {
					return include trailingslashit($dir) . 'index.php';
				},
				glob(THEME_DIR . 'ls-blocks/*', GLOB_ONLYDIR)
			);
		}

		/**
		 * Get a list of all blocks
		 *
		 * @return array
		 */
		public function get_blocks(): array {
			return self::$blocks;
		}

		/**
		 * Register assets for all blocks
		 *
		 * Registers all block assets so that they can be enqueued through Gutenberg in
		 * the corresponding context.
		 *
		 * Registers assets for custom blocks for WordPress to load in the respective context
		 *
		 * Passes translations to JavaScript.
		 *
		 * @return void
		 */
		public function register_block_assets() {
			foreach (self::$blocks as $block) {
				$group = $block['group'] ?? 'ls';
				if ($group === 'ls') {
					$block_dirname = $group . '-' . $block['slug'];
					$block_uri = trailingslashit(THEME_URI . 'ls-blocks/' . $block_dirname);
					$block_dir = trailingslashit(THEME_DIR . 'ls-blocks/' . $block_dirname);

					$asset_handler = [
						'editor_script' => ['block-editor-script-' . $block_dirname, 'index', 'js'],
						'editor_style' => ['block-editor-style-' . $block_dirname, 'editor', 'css'],
						'script' => ['block-frontend-script-' . $block_dirname, 'frontend', 'js'],
						'style' => ['block-frontend-style-' . $block_dirname, 'index', 'css'],
					];

					foreach ($asset_handler as $asset) {
						if (file_exists($block_dir . $asset[1] . '.' . $asset[2])) {
							$asset_file = include $block_dir . $asset[1] . '.asset.php';

							if ($asset[2] === 'js') {
								wp_register_script(
									$asset[0],
									$block_uri . $asset[1] . '.' . $asset[2],
									$asset_file['dependencies'],
									$asset_file['version'],
									true
								);
								wp_localize_script(
									$asset[0],
									'wpVars',
									$block['localized_vars']
								);
								wp_set_script_translations($asset[0], 'example', THEME_DIR . 'languages');
							} elseif ($asset[2] === 'css') {
								wp_register_style(
									$asset[0],
									$block_uri . $asset[1] . '.' . $asset[2],
									[],
									filemtime(THEME_DIR . 'ls-blocks/' . $block_dirname . '/index.css')
								);
							}
						}
					}

					// Register assets for custom blocks.
					if (!WP_Block_Type_Registry::get_instance()->is_registered(trailingslashit($group) . $block['slug'])) {
						register_block_type(
							trailingslashit(__DIR__) . $group . '-' . $block['slug'],
							[
								'editor_script' => 'block-editor-script-' . $block_dirname,
								'editor_style' => 'block-editor-style-' . $block_dirname,
								'script' => 'block-frontend-script-' . $block_dirname,
								'style' => 'block-frontend-style-' . $block_dirname,
								'render_callback' => array_key_exists('render_callback', $block) ? $block['render_callback'] : null,
							]
						);
						if (file_exists($block_dir . 'frontend.css')) {
							wp_enqueue_style(
								'block-frontend-additional-style-' . $block_dirname,
								THEME_URI . 'ls-blocks/' . $block_dirname . '/frontend.css',
								[],
								filemtime(THEME_DIR . 'ls-blocks/' . $block_dirname . '/frontend.css')
							);
						}
					}
				}
			}
		}

		/**
		 * Enqueue block frontend assets for block overrides
		 * files: index.css, frontend.js
		 *
		 * @return void
		 */
		public function enqueue_block_override_frontend_assets() {
			foreach (self::$blocks as $block) {
				$group = $block['group'] ?? 'ls';
				// Only enqueue assets for block overrides. custom blocks are enqueued automatically with register_block_assets.

				if ($group !== 'ls') {
					$block_dirname = $group . '-' . $block['slug'];
					$has_block = has_block(trailingslashit($group) . $block['slug']) || in_array(trailingslashit($group) . $block['slug'], self::$additional_block_dependencies);

					if ($has_block || $block_dirname === 'core-group' || $block_dirname === 'core-paragraph') {
						$asset_file = include THEME_DIR . '/ls-blocks/' . $block_dirname . '/index.asset.php';

						wp_enqueue_script(
							'block-frontend-script-' . $block_dirname,
							THEME_URI . 'ls-blocks/' . $block_dirname . '/index.js',
							$asset_file['dependencies'],
							$asset_file['version'],
							true
						);
						wp_localize_script(
							'block-frontend-script-' . $block_dirname,
							'wpVars',
							$block['localized_vars']
						);
						wp_enqueue_style(
							'block-frontend-style-' . $block_dirname,
							THEME_URI . 'ls-blocks/' . $block_dirname . '/index.css',
							[],
							filemtime(THEME_DIR . 'ls-blocks/' . $block_dirname . '/index.css'),
						);
					}
				}
			}
		}

		/**
		 * Enqueue block editor assets for block overrides
		 * files: index.js, index.css, admin.css
		 *
		 * @return void
		 */
		public function enqueue_block_override_editor_assets() {
			foreach (self::$blocks as $block) {
				$group = $block['group'] ?? 'ls';

				// Only enqueue assets for block overrides. custom blocks are enqueued automatically with register_block_assets.
				if ($group !== 'ls') {
					$dir = $group . '-' . $block['slug'];
					$asset_file = include THEME_DIR . '/ls-blocks/' . $dir . '/index.asset.php';

					wp_enqueue_script(
						'block-frontend-script-' . $dir,
						THEME_URI . 'ls-blocks/' . $dir . '/index.js',
						$asset_file['dependencies'],
						$asset_file['version'],
						true
					);
					wp_enqueue_script(
						'block-editor-script-' . $dir,
						THEME_URI . 'ls-blocks/' . $dir . '/edit.js',
						$asset_file['dependencies'],
						$asset_file['version'],
						true
					);
					wp_localize_script(
						'block-frontend-script-' . $dir,
						'wpVars',
						$block['localized_vars']
					);
					wp_localize_script(
						'block-editor-script-' . $dir,
						'wpVars',
						$block['localized_vars']
					);
					wp_enqueue_style(
						'block-frontend-style-' . $dir,
						THEME_URI . 'ls-blocks/' . $dir . '/index.css',
						[],
						filemtime(THEME_DIR . 'ls-blocks/' . $dir . '/index.css')
					);
					wp_enqueue_style(
						'block-editor-style-' . $dir,
						THEME_URI . 'ls-blocks/' . $dir . '/index.css',
						[],
						filemtime(THEME_DIR . 'ls-blocks/' . $dir . '/index.css')
					);
				}
			}
		}

		/**
		 * Load script translations for blocks
		 *
		 * @return void
		 */
		public function load_script_translations() {
			foreach (self::$blocks as $block) {
				$group = $block['group'] ?? 'ls';
				$script_handle = 'block-editor-script-' . $group . '-' . $block['slug'];

				if (function_exists('wp_set_script_translations') && wp_script_is($script_handle, 'registered')) {
					wp_set_script_translations(
						$script_handle,
						'example',
						THEME_DIR . 'languages'
					);
				}
			}
		}
	}
}

Gutenberg_Blocks::get_instance();
