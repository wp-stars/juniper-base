<?php
/**
 * LimeSoda Shortcode for product slider
 * Displays cards in a slider
 *
 * @author        LimeSoda
 * @copyright    Copyright (c) 2020, LimeSoda
 * @link        https://limesoda.com/
 * @package Limesoda\\Astra_Child\\Shortcodes\\Productslider
 */

namespace Limesoda\Astra_Child\Shortcodes\Productslider;

use const Limesoda\Astra_Child\THEME_DIR;
use const Limesoda\Astra_Child\THEME_URI;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

if (!class_exists('Productslider')) {

	/**
	 * LS_SHORTCODE_PRODUCT_SLIDER
	 *
	 * @since 1.0.0
	 * @author LIMESODA Team Undefined <support-wordpress@limesoda.com>
	 */
	class Productslider {

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

		private static string $slug = 'productslider';

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
			if ((is_singular('post')) || (is_singular('products')) || (has_shortcode($post->post_content, self::$slug))) {
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
		 * Creates markup for product slider shortcode
		 *
		 * @return string html markup
		 */
		public function shortcode_markup(): string {

			$html = '<div class="ls-shortcode-product-slider">';
			$html .= '<div class="ls-shortcode-product-slider__container swiper"><div class="swiper-wrapper">';

			$query = new \WP_Query([
				'post_type' => 'products',
				'post_status' => 'publish',
				'posts_per_page' => 10,
			]);
			if ($query->have_posts()) {
				while ($query->have_posts()) {
					$query->the_post();
					$title = get_the_title();
					$categories = [];

					$product_categories = get_terms([
						'taxonomy' => 'product-categories',
						'object_ids' => get_the_ID(),
					]);
					if ($product_categories) {
						foreach ($product_categories as $category) {
							$categories[] = $category->name;

						}
					}

					$metal_and_accessories = get_terms([
						'taxonomy' => 'metal_and_accessories',
						'object_ids' => get_the_ID(),
					]);
					if ($metal_and_accessories) {
						foreach ($metal_and_accessories as $category) {
							$categories[] = $category->name;
						}
					}

					$colors = get_terms([
						'taxonomy' => 'colors',
						'object_ids' => get_the_ID(),
					]);
					if ($colors) {
						foreach ($colors as $category) {
							if ($category->parent !== 0) {
								$categories[] = $category->name;
							}
						}
					}

					$new = get_field('product_new');
					$link = get_the_permalink();
					$media = '';
					$gallery = get_field('gallery');
					if ($gallery) {
						$media = $gallery[0]['sizes']['medium'];
						$media_alt = $gallery[0]['alt'];
					}
					$html .= $this->single_post_card($title, $categories, $new, $link, $media, $media_alt);
				}
				wp_reset_postdata();
			}
			$html .= '</div></div><div class="product-slider-swiper-button-prev swiper-button-prev"></div><div class="product-slider-swiper-button-next swiper-button-next"></div>';

			$html .= '</div>';

			return $html;
		}

		/**
		 * Single Post Card
		 * Displays a single card element for a product
		 *
		 * @param string $title title of product.
		 * @param array $categories categories of product.
		 * @param string $new new flag of product.
		 * @param string $link link to product.
		 * @param int $media product card media.
		 * @param string $media_alt alt text for card media.
		 * @return string generated html
		 */
		public function single_post_card($title, $categories, $new, $link, $media, $media_alt): string {
			$html = '<div class="ls-product-slider-post-card swiper-slide">';
			$html .= '<a href="' . $link . '" title="' . __('Go to product', 'iwgplating') . $title . '">';
			$html .= '<div class="ls-post-card__image">';
			if ($new === true) {
				$html .= '<span class="ls-post-card__new">' . __('NEW', 'iwgplating') . '</span>';
			}
			if (isset($media)) {
				$html .= '<div class="ls-post-card__media"><figure><img src="' . $media . '" alt="' . $media_alt . '"></figure></div>';
			}
			$html .= '</div>';

			$html .= '<div class="ls-post-card__text_content">';

			$html .= '<div class="ls-post-card__headline"><h4>' . $title . '</h4></div>';
			if ($categories) {
				$html .= '<div class="ls-post-card__categories">';
				$category_count = count($categories);
				foreach ($categories as $key => $category) {
					$html .= '<span class="ls-post-card__post_category">' . $category;
					if ($key !== $category_count - 1) {
						$html .= ' | ';
					}
					$html .= '</span>';
				}
				$html .= '</div>';
			}

			$html .= '</div></a></div>';

			return $html;
		}
	}
}
return Productslider::get_instance();
