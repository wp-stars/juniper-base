<?php
/**
 * LimeSoda Shortcode for news archive
 * Displays cards for all news articles
 *
 * @author        LimeSoda
 * @copyright    Copyright (c) 2020, LimeSoda
 * @link        https://limesoda.com/
 * @package Limesoda\\Astra_Child\\Shortcodes\\newsarchive
 */

namespace Limesoda\Astra_Child\Shortcodes\Newsarchive;

use const Limesoda\Astra_Child\THEME_DIR;
use const Limesoda\Astra_Child\THEME_URI;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('Newsarchive')) {

    /**
     * LS_SHORTCODE_NEWS_ARCHIVE
     *
     * @since 1.0.0
     * @author LIMESODA Team Undefined <support-wordpress@limesoda.com>
     */
    class Newsarchive {

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

        private static string $slug = 'newsarchive';

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
            add_action('wp_ajax_nopriv_ls_shortcodes_news_archive_filter_posts', [$this, 'ajax_load_filtered_posts_by_category']);
            add_action('wp_ajax_ls_shortcodes_news_archive_filter_posts', [$this, 'ajax_load_filtered_posts_by_category']);
            add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        }

        /**
         * Enqueue Assets for Shortcode
         *
         * @return void
         */
        public function enqueue_scripts() {
            global $post;
            $categories = get_categories();

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
                wp_localize_script(
                    'ls_shortcode_script_' . self::$slug,
                    'reactVars',
                    [
                        'postID' => $post->ID,
                    ]
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
         * Creates markup for blog archive shortcode
         *
         * @param array|string $attributes shortcode attributes.
         * @return string html markup
         */
        public function shortcode_markup($attributes = []): string {
            $html = '<div class="ls-shortcode-news-archive"><div class="ls-shortcode-news-archive__inner">';
            $html .= $this->filter_by_category();
            $html .= '<div class="ls-shortcode-news-archive__grid">';
            $count = get_option('posts_per_page');
            $paged = get_query_var('paged') ? get_query_var('paged') : 1;
            $offset = ($paged - 1) * $count;

            $query = new \WP_Query([
                'post_type' => 'post',
                'post_status' => 'publish',
                'posts_per_page' => $count,
                'paged' => $paged,
                'offset' => $offset,
            ]);

            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $title = get_the_title();
                    $date = get_the_date('F Y');
                    $categories = get_the_category();
                    $categories_arr = [];
                    foreach ($categories as $key => $category) {
                        if($key < 2) {
                            $categories_arr[$category->slug] = $category->name;
                        }
                    }
                    $excerpt = get_the_excerpt();
                    $link = get_the_permalink();
                    $media = get_the_post_thumbnail(null, 200);
                    $cat_icon = get_field('newscategory_file_upload', $categories[0]);
                    $cat_icon_url = $cat_icon['url'];
                    $cat_icon_alt = $cat_icon['alt'];
                    $cat_icon_title = $cat_icon['title'];

                    $html .= $this->single_post_card($title, $date, $categories_arr, $excerpt, $link, $media, $cat_icon_url, $cat_icon_alt, $cat_icon_title);
                }

                wp_reset_postdata(); // reset postdata to original page query to prevent single posts from secondary query to override page settings
            }
            $html .= '</div></div></div>';

            // load more button
            $visibility_class = '';
            if ($query->found_posts <= $query->post_count) {
                $visibility_class = 'ls-shortcode-news-archive__load-more--hidden';
            }
            $html .= '<a class="ls-shortcode-news-archive__load-more__link ' . $visibility_class . '">';
            $html .= __('Load more', 'iwgplating');
            $html .= '</a>';

            return $html;
        }

        /**
         * Filter Blog by Categories
         *
         * @return string
         */
        public function filter_by_category(): string {
            $all_categories = get_categories();

            $html = '<div class="ls-shortcode-newsarchive__filter"><strong>' . __('Filter by topic', 'iwgplating') . '</strong><select class="ls-shortcode-news-archive__filter__mobile">';
            $html .= '<option value="-1" class="ls-shortcode-news-archive__filter__link" data-category="-1">' . __('All', 'iwgplating') . '</option>';

            foreach ($all_categories as $category) {
                if ($category->count > 0) {
                    $html .= '<option value="' . $category->term_id . '" class="ls-shortcode-news-archive__filter__link" data-category="' . $category->term_id . '">' . $category->cat_name . '</option>';
                }
            }

            $html .= '</select></div>';
            return $html;
        }

        /**
         * Single Post Card
         * Displays a single card element for a blog post
         *
         * @param string $title title of blog post.
         * @param string $date date of blog post.
         * @param string $excerpt excerpt of blog post.
         * @param string $link link to post.
         * @param int $media id of card media.
         * @param int $cat_icon_url url of category icon.
         * @param int $cat_icon_alt alt of category icon.
         * @param int $cat_icon_title title of category icon.
         * @return string generated html
         */
        public function single_post_card($title, $date, $category_arr, $excerpt, $link, $media, $cat_icon_url, $cat_icon_alt, $cat_icon_title): string {
            $html = '<div class="ls-post-card">';
            if (isset($media)) {
                $html .= '<div class="ls-post-card__header">';
            }
            if (isset($media)) {
                $html .= '<div class="ls-post-card__media">' . $media . '</div>';
            }
            if (isset($media)) {
                $html .= '</div>';
            }
            $html .= '<div class="ls-post-card__content"><a href="' . $link . '" title="Go to the article ' . $title . '">';
            $category_str = implode(", ", $category_arr);
            $html .= '<div class="ls-post-card__metadata"><p class="ls-post-card__date">' . $date . ' | </p><p class="ls-post-card__category">' . $category_str . '</p></div>';
            $html .= '<div class="ls-post-card__headline"><h4>' . $title . '</h4></div>';
            if (has_excerpt()) {
                $html .= '<p class="ls-post-card__excerpt">' . $excerpt . '</p>';
            }
            $html .= '<strong class="ls-post-card__readmore">' . __('Read More', 'iwgplating') . '</strong></div></a></div>';

            return $html;
        }

        /**
         * Load filtered posts from category with ajax
         * depending on $categoryId and query $offset
         *
         * @return void
         */
        public function ajax_load_filtered_posts_by_category(): string {
            header('Access-Control-Allow-Origin: *');
            $response = [
                'error' => true,
                'posts' => '',
                'moreAvailable' => false,
            ];

            $category_id = !wp_verify_nonce($_REQUEST['_wpnonce'], 'my-nonce') ? $_POST['categoryId'] : null;
            $offset = !wp_verify_nonce($_REQUEST['_wpnonce'], 'my-nonce') ? $_POST['offset'] : 0;

            if (!empty($category_id)) {
                $html = '';

                $args = [
                    'posts_per_page' => get_option('posts_per_page'),
                    'post_type' => 'post',
                    'post_status' => 'publish',
                    'offset' => $offset,
                ];

                if ($category_id > 0) {
                    $args['category__in'] = $category_id;
                }
                $query = new \WP_Query($args);

                while ($query->have_posts()) {
                    $query->the_post();
                    $title = get_the_title();
                    $date = get_the_date('F Y');
                    $categories = get_the_category();
                    $categories_arr = [];
                    foreach ($categories as $key => $category) {
                        if($key < 2) {
                            $categories_arr[$category->slug] = $category->name;
                        }
                    }
                    $excerpt = get_the_excerpt();
                    $link = get_the_permalink();
                    $media = get_the_post_thumbnail(null, 200);
                    $cat_icon = get_field('newscategory_file_upload', $categories[0]);
                    $cat_icon_url = $cat_icon['url'];
                    $cat_icon_alt = $cat_icon['alt'];
                    $cat_icon_title = $cat_icon['title'];
                    $html .= $this->single_post_card($title, $date, $categories_arr, $excerpt, $link, $media, $cat_icon_url, $cat_icon_alt, $cat_icon_title);
                }

                $response['error'] = false;
                $response['posts'] = $html;
                $response['query'] = $query;

                if ($query->found_posts > ($offset + $query->post_count)) {
                    $response['moreAvailable'] = true;
                }
                echo json_encode($response);
                wp_die();
            }

            echo json_encode($response);
            wp_die();
        }
    }
}
return Newsarchive::get_instance();
