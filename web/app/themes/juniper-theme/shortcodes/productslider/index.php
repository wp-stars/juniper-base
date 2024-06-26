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

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

if (!class_exists('Productslider')) {

	class Productslider {

		private static $instance;
		private static string $slug = 'productslider';
		private static array $localized_vars = [];

		public static function get_instance() {
			if (!isset(self::$instance)) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		public function __construct() {
			add_action('init', function () {
				add_shortcode(self::$slug, [$this, 'shortcode_markup']);
			});
			add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
		}

		public function enqueue_scripts() {
			global $post;

			if (!defined('THEME_DIR')) {
				define('THEME_DIR', get_template_directory() . '/');
			}

			if (!defined('THEME_URI')) {
				define('THEME_URI', get_template_directory_uri() . '/');
			}

			if ((is_singular('post')) || (is_singular('product')) || (has_shortcode($post->post_content, self::$slug))) {
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

		public function shortcode_markup(): string {
			// Get the ID of the product to exclude
			$excluded_product = get_page_by_title('musterbestellung', OBJECT, 'product');
			$excluded_product_id = $excluded_product ? $excluded_product->ID : 0;

            $postNotIn = [13819, 13840];
            $postNotIn[] = $excluded_product_id;
            $postNotIn = array_unique($postNotIn);
            $postNotIn = implode(',', $postNotIn);

			$html = '<div class="ls-shortcode-product-grid container my-8">';
			$html .= '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 mb-10 gap-y-14 sm:gap-[42px] filter-grid flex flex-wrap">';

			$query = new \WP_Query([
				'post_type' => 'product',
				'post_status' => 'publish',
				'posts_per_page' => 3,
				'orderby' => 'date',
				'order' => 'DESC',
                'post__not_in' => [$postNotIn], // Exclude the specified product
			]);

			if ($query->have_posts()) {
				while ($query->have_posts()) {
					$query->the_post();
					$product_id = get_the_ID();
					$html .= '<div class="product-card">';
					$html .= do_shortcode("[wps_get_product_card product_id='$product_id']");
					$html .= '</div>';
				}
				wp_reset_postdata();
			} else {
				$html .= '<p>' . __('No products found', 'wps-juniper') . '</p>';
			}

			$html .= '</div>';
			$html .= '</div>';

            $html .= '<div class="container flex justify-center mb-6">';
            $html .= '<a href="' . get_permalink(wc_get_page_id( 'shop' )) . '" class="btn btn-accent font-semibold w-auto">' . __('Shop', 'wps-juniper') . '</a>';
            $html .= '</div>';

			return $html;
		}
	}
}
return Productslider::get_instance();
