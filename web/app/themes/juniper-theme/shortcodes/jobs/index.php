<?php
/**
 * LimeSoda Shortcode for job archive
 * Displays cards for all job articles
 *
 * @author        LimeSoda
 * @copyright    Copyright (c) 2020, LimeSoda
 * @link        https://limesoda.com/
 * @package Limesoda\\Astra_Child\\Shortcodes\\job
 */

namespace Limesoda\Astra_Child\Shortcodes\Jobs;

use const Limesoda\Astra_Child\THEME_DIR;
use const Limesoda\Astra_Child\THEME_URI;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

if (!class_exists('Jobs')) {

	/**
	 * LS_SHORTCODE_JOBS
	 *
	 * @since 1.0.0
	 * @author LIMESODA Team Undefined <support-wordpress@limesoda.com>
	 */
	class Jobs {

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

		private static string $slug = 'jobs';

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
			if (has_shortcode($post->post_content, self::$slug)) {
				$asset_file = include THEME_DIR . 'shortcodes/' . self::$slug . '/index.asset.php';
				wp_enqueue_style(
					'ls_shortcode_style_' . self::$slug,
					THEME_URI . 'shortcodes/' . self::$slug . '/index.css',
					[],
					filemtime(THEME_DIR . 'shortcodes/' . self::$slug . '/index.css')
				);
			}
		}

		/**
		 * Creates markup for job shortcode
		 *
		 * @param array|string $attributes shortcode attributes.
		 * @return string html markup
		 */
		public function shortcode_markup($attributes = []): string {
			$html = '<div class="ls-shortcode-jobs"><div class="ls-shortcode-jobs__inner">';

			$count = get_option('posts_per_page');
			$paged = get_query_var('paged') ? get_query_var('paged') : 1;
			$offset = ($paged - 1) * $count;

			$query = new \WP_Query([
				'post_type' => 'jobs',
				'post_status' => 'publish',
				'posts_per_page' => $count,
				'paged' => $paged,
				'offset' => $offset,
			]);

			$count_posts = $query->found_posts;
			$html .= '<h3 class="ls-shortcode-jobs__headline">' . __('Jobs', 'iwgplating') . ' (' . $count_posts . ')</h3>';

			if ($query->have_posts()) {
				while ($query->have_posts()) {
					$query->the_post();
					$current_id = get_the_ID();
					$title = get_the_title();
					$destination = get_field('job_destination');
					$employment = get_field('job_employment_type');
					$current_category = '';
					$categories = wp_get_post_terms($current_id, 'job-categories');
					foreach ($categories as $category) {
						$current_category = $category->name;
						$current_cat_slug = $category->slug;
					}
					$category = $current_category;
					$category_slug = $current_cat_slug;
					$link = get_the_permalink();

					$html .= $this->single_post_card($title, $destination, $employment, $category, $category_slug, $link);
				}

				wp_reset_postdata(); // reset postdata to original page query to prevent single posts from secondary query to override page settings
			}
			$html .= '</div></div>';

			return $html;
		}

		/**
		 * Single Post Card
		 * Displays a single card element for a blog post
		 *
		 * @param string $title title of blog post.
		 * @param string $destination title of blog post.
		 * @param string $employment title of blog post.
		 * @param string $category category of blog post.
		 * @param string $category_slug category-slug of blog post.
		 * @param string $link link to post.
		 * @return string generated html
		 */
		public function single_post_card($title, $destination, $employment, $category, $category_slug, $link): string {
			$html = '<div class="ls-jobs-card ls-job-card-' . $category_slug . '"><a href="' . $link . '" title="Go to the article ' . $title . '">';
			if (isset($category_slug)) {
				$html .= '<div class="ls-jobs-card__category-icon">';
				if ($category_slug === 'chemicals') {
					$html .= '<i class="icon-chemicals"></i>';
				}
				if ($category_slug === 'plating-service') {
					$html .= '<i class="icon-lohngalvanik"></i>';
				}
				if ($category_slug === 'lab-solutions') {
					$html .= '<i class="icon-lab"></i>';
				}
				$html .= '</div>';
			}
			$html .= '<div class="ls-jobs-card__content">';
			$html .= '<div class="ls-jobs-card__metadata"><p class="ls-jobs-card__category is-style-label-small">' . $category . '</p></div>';
			$html .= '<div class="ls-jobs-card__headline"><h4>' . $title . '</h4></div>';
			$html .= '<div class="ls-jobs-card__info"><p class="ls-jobs-card__destination">' . $destination . '</p> | <p class="ls-jobs-card__employment">' . $employment . '</p></div>';
			$html .= '</div>';
			$html .= '<strong class="ls-jobs-card__readmore">' . __('Read More', 'iwgplating') . '</strong>';
			$html .= '</a></div>';
			return $html;
		}
	}
}
return Jobs::get_instance();
