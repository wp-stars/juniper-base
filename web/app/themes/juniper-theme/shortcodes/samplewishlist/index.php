<?php
/**
 * LimeSoda Shortcode for sample wishlist
 *
 * @author        LimeSoda
 * @copyright    Copyright (c) 2020, LimeSoda
 * @link        https://limesoda.com/
 * @package Limesoda\\Astra_Child\\Shortcodes\\SampleWishlist
 */

namespace Limesoda\Astra_Child\Shortcodes\SampleWishlist;

use const Limesoda\Astra_Child\THEME_DIR;
use const Limesoda\Astra_Child\THEME_URI;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

if (!class_exists('SampleWishlist')) {

	/**
	 * LS_SHORTCODE_SAMPLE_WISHLIST
	 *
	 * @since 1.0.0
	 * @author LIMESODA Team Undefined <support-wordpress@limesoda.com>
	 */
	class SampleWishlist {

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

		private static string $slug = 'samplewishlist';

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
		 * Enqueue Assets for Shortcode
		 *
		 * @return void
		 */
		public function enqueue_scripts() {
			global $post;
			if ((is_singular('post')) || (has_shortcode($post->post_content, self::$slug))) {
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
				wp_localize_script(
					'ls_shortcode_script_' . self::$slug,
					'wpVars',
					[
						'ajaxUrl' => admin_url('admin-ajax.php'),
					]
				);
			}
		}

		/**
		 * Creates markup for sample wishlist shortcode
		 *
		 * @return string html markup
		 */
		public function shortcode_markup(): string {
			$html = '<div class="ls-shortcode-sample-wishlist">';
			$html .= '<div class="ls-sample-wishlist__overview">';
			$html .= '<div class="ls-sample-wishlist__overview_heading">';
			$html .= '<h4><span>1</span>' . __('Your Sample Order', 'iwgplating') . '</h4>';
			$html .= '</div>';
			$html .= '<div class="ls-sample-wishlist__overview_table">';

			$html .= '<div class="ls-sample-wishlist__item_row ls-table-head ls-hide-on-mobile">';
			$html .= '<div class="ls-sample-wishlist__item_number"><span></span></div>';
			$html .= '<div class="ls-sample-wishlist__item_image"><img></div>';
			$html .= '<div class="ls-sample-wishlist__item_name">' . __('Product', 'iwgplating') . '</div>';
			$html .= '<div class="ls-sample-wishlist__item_button"><a></a></div>';
			$html .= '</div>';

			$items = explode('\",\"', trim($_COOKIE['wishlist'], '["\]'));
			for ($i = 0; $i < 3; $i++) {
				if ($items[ $i ]) {
					$html .= $this->get_row_markup($items[ $i ], $i + 1);
				} else {
					$html .= $this->get_row_markup(0, $i + 1);
				}
			}

			$html .= '</div>';
			$html .= '</div>';
			$html .= '<div class="ls-sample-wishlist__form">';
			$html .= '<div class="ls-sample-wishlist__form_heading">';
			$html .= '<h4><span>2</span>' . __('Shipping Address', 'iwgplating') . '</h4>';
			$html .= '</div>';
			$html .= '</div>';
			$html .= '</div>';

			return $html;
		}

		/**
		 * Wishlist Table Row
		 * Displays item in wishlist in a row
		 *
		 * @param int $id id of product.
		 * @param int $key index of element in array.
		 * @return string generated html
		 */
		public function get_row_markup($id, $key): string {
			if ($id !== 0) {
				$title = get_the_title($id);

				$color = '';
				$terms = get_terms([
					'taxonomy' => 'colors',
					'object_ids' => $id,
				]);
				if ($terms) {
					foreach ($terms as $term) {
						if ($term->parent !== 0) {
							$color .= '<span>' . $term->name . '</span>';
						}
					}
				}
				$link = get_the_permalink($id);

				$image = get_the_post_thumbnail_url($id);
				$thumbnail_id = get_post_thumbnail_id($id);
				$image_alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);
				if ($image === false) {
					$gallery = get_field('gallery', $id);
					if ($gallery) {
						$image = $gallery[0]['sizes']['thumbnail'];
						$image_alt = $gallery[0]['alt'];
					}
				}
				$class = 'ls-selected-sample';
			} else {
				$title = '----';
				$color = '----';
				$link = '';
				$image = '';
				$image_alt = '';
				$class = 'ls-empty-sample';
			}

			$html = '<div class="ls-sample-wishlist__item_row ' . $class . '">';
			$html .= '<div class="ls-sample-wishlist__item_number"><span>' . $key . '</span></div>';
			$html .= '<div class="ls-sample-wishlist__item_image"><img src="' . $image . '" alt="' . $image_alt . '"></div>';
			$html .= '<div class="ls-sample-wishlist__item_name"><span class="ls-hide-on-desktop">' . __('Product:', 'iwgplating') . ' </span><a href="' . $link . '"><span>' . $title . '</span></a></div>';
			$html .= '<div class="ls-sample-wishlist__item_button" data-id="' . $id . '"><a class="ls-remove-product">' . __('Remove Sample', 'iwgplating') . '</a><a class="ls-add-product" href="' . __('/en/productfinder', 'iwgplating') . '">' . __('Add', 'iwgplating') . '</a></div>';
			$html .= '</div>';

			return $html;
		}
	}
}
return SampleWishlist::get_instance();
