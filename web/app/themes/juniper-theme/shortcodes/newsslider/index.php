<?php
/**
 * LimeSoda Shortcode for news slider
 * Displays cards in a slider
 *
 * @author        LimeSoda
 * @copyright    Copyright (c) 2020, LimeSoda
 * @link        https://limesoda.com/
 * @package Limesoda\\Astra_Child\\Shortcodes\\Newsslider
 */

namespace WPS\Shortcodes\Newsslider;

use const WPS\THEME_DIR;
use const WPS\THEME_URI;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

if (!class_exists('Newsslider')) {

	/**
	 * LS_SHORTCODE_NEWS_SLIDER
	 *
	 * @since 1.0.0
	 * @author LIMESODA Team Undefined <support-wordpress@limesoda.com>
	 */
	class Newsslider {

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

		private static string $slug = 'newsslider';

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
		 * Creates markup for news slider shortcode
		 *
		 * @param array|string $attributes shortcode attributes.
		 * @return string html markup
		 */
		public function shortcode_markup($attributes = []): string {
            $current_id = get_the_ID();
			$cat = $attributes['cat'] ?? null;
			$nr_of_posts = $attributes['number_of_posts'] ?? null;
			$count = $nr_of_posts;

			$html = '<div class="ls-shortcode-news">';
			if (is_singular('post')) {
				$html .= '<h4 class="ls-shortcode-news__headline">' . __('You might also be interested in', 'iwgplating') . '</h4>';
			}
			$html .= '<div class="ls-shortcode-news-slider swiper"><div class="swiper-wrapper">';

			if (is_front_page()) {
				$query = new \WP_Query([
					'post_type' => 'post',
					'post_status' => 'publish',
					'posts_per_page' => $count,
                    'post__not_in' => array($current_id),
					'meta_query' => [
						[
							'key' => 'news_new',
							'value' => '1',
							'compare' => '=',
						],
					],
				]);
			} else if (isset($cat)) {
				$query = new \WP_Query([
					'post_type' => 'post',
					'post_status' => 'publish',
					'posts_per_page' => $count,
                    'post__not_in' => array($current_id),
					'category_name' => $cat,
					'tax_query' => [
						[
							'taxonomy' => 'category',
							'field' => 'slug',
							'terms' => $cat,
						],
					],
				]);
			} else {
				$query = new \WP_Query([
					'post_type' => 'post',
					'post_status' => 'publish',
					'posts_per_page' => $count,
                    'post__not_in' => array($current_id),
				]);
			}

			if ($query->have_posts()) {
				while ($query->have_posts()) {
					$query->the_post();
					$title = get_the_title();
					$date = get_the_date('F Y');
					$categories = get_the_category();
					$category = $categories[0];
					$new = get_field('news_new');
					$excerpt = get_the_excerpt();
					$link = get_the_permalink();
					$media = get_the_post_thumbnail(null, 200);
					$html .= $this->single_post_card($title, $date, $categories[0], $new, $excerpt, $link, $media);
				}

				wp_reset_postdata(); // reset postdata to original page query to prevent single posts from secondary query to override page settings
			}
			$html .= '</div></div><div class="news-slider-swiper-button-prev swiper-button-prev"></div><div class="news-slider-swiper-button-next swiper-button-next"></div>';

			$html .= '</div>';

			return $html;
		}

		/**
		 * Single Post Card
		 * Displays a single card element for a news post
		 *
		 * @param string $title title of news post.
		 * @param string $date date of news post.
		 * @param string $category category of news post.
		 * @param string $new new flag of news post.
		 * @param string $excerpt excerpt of news post.
		 * @param string $link link to post.
		 * @param int $media id of card media.
		 * @return string generated html
		 */
		public function single_post_card($title, $date, $category, $new, $excerpt, $link, $media): string {
			$html = '<div class="ls-news-slider-post-card swiper-slide">';
			if (isset($media) && isset($category)) {
				$html .= '<div class="ls-post-card__header">';
			}
			if ($new === true) {
					$html .= '<span class="ls-post-card__new">' . __('NEW', 'iwgplating') . '</span>';
			}
			if (isset($media)) {
				$html .= '<div class="ls-post-card__media">' . $media . '</div>';
			}
			if (isset($category)) {
				$html .= '<div class="ls-post-card__categpory-icon">';
				$cat_icon = get_field('newscategory_file_upload', $category);
                if($cat_icon) {
                    $cat_icon_url = $cat_icon['url'];
                    $cat_icon_alt = $cat_icon['alt'];
                    $cat_icon_title = $cat_icon['title'];
                    $html .= '<img src="' . $cat_icon_url . '" alt="' . $cat_icon_alt . '" alt="' . $cat_icon_title . '">';
                }

				$html .= '</div>';
			}
			if (isset($media) && isset($category)) {
				$html .= '</div>';
			}
			$html .= '<div class="ls-post-card__content"><a href="' . $link . '" title="Go to the article ' . $title . '">';
			$html .= '<div class="ls-post-card__metadata"><p class="ls-post-card__date">' . $date . ' | </p><p class="ls-post-card__category">' . $category->name . '</p></div>';
			$html .= '<div class="ls-post-card__headline"><h4>' . $title . '</h4></div>';
			if (has_excerpt()) {
				$html .= '<p class="ls-post-card__excerpt">' . $excerpt . '</p>';
			}
			$html .= '<strong class="ls-post-card__readmore">' . __('Read more', 'iwgplating') . '</strong></div></a></div>';

			return $html;
		}
	}
}
return Newsslider::get_instance();
