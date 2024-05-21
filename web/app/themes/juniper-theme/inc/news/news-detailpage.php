<?php
/**
 * LimeSoda News Detail Page
 * Detail Page of CPT "news"
 *
 * @package IWGPlating
 * @author LimeSoda
 * @copyright Copyright (c) 2020, LimeSoda
 * @link https://limesoda.com/
 */

namespace WPS\News\Detailpage;

/** Enqueue News Detail Page Styles and Scripts */
function enqueue_post_assets() {
	if (!is_singular('post')) {
		return;
	}

    if (!defined('THEME_DIR')) {
        define('THEME_DIR', get_template_directory() . '/');
    }

    if (!defined('THEME_URI')) {
        define('THEME_URI', get_template_directory_uri() . '/');
    }

	wp_enqueue_style(
		'news-detailpage-styles',
		THEME_URI . 'assets/css/news-detailpage.css',
		[],
		filemtime(THEME_DIR . 'assets/css/news-detailpage.css'),
	);

	wp_enqueue_style(
		'social-sharing-styles',
		THEME_URI . 'assets/css/social-sharing.css',
		[],
		filemtime(THEME_DIR . 'assets/css/social-sharing.css'),
	);

	$asset_file = include THEME_DIR . '/index.asset.php';
	wp_register_script(
		'social-sharing-scripts',
		THEME_URI . 'assets/js/social-sharing.js',
		$asset_file['dependencies'],
		$asset_file['version'],
		true
	);
	wp_enqueue_script(
		'social-sharing-scripts'
	);

}
add_action('wp_enqueue_scripts', '\WPS\News\Detailpage\enqueue_post_assets');

/** Breadcrumbs */
add_action('astra_primary_content_top', function() {
	if (is_singular('post')) {
		echo do_shortcode('[astra_breadcrumb]');
	}
});

/**
 * Disable Standard Post Title
 *
 * @return void
 */
function disable_post_title() {
	$post_types = ['post'];

	// bail early if the current post type if not the one we want to customize.
	if (!in_array(get_post_type(), $post_types, true)) {
		return;
	}

	// Disable title.
	add_filter('astra_the_title_enabled', '__return_false');
	// Disable featured image.
	add_filter('astra_featured_image_enabled', '__return_false');

}
add_action('wp', '\WPS\News\Detailpage\disable_post_title');

/**
 * Disable standard the content
 *
 * @param string $the_content disable the content.
 * @return mixed|void
 */
function disable_the_content($the_content) {
	$post_types = ['post'];

	// bail early if the current post type if not the one we want to customize.
	if (!in_array(get_post_type(), $post_types, true)) {
		return $the_content;
	} else {
		$current_id = get_the_ID();
		$date = get_the_date('F Y');
		$image = get_the_post_thumbnail($current_id, 'full');
		$title = get_the_title();
		$categories = get_the_category();
		$downloads = get_field('news_downloads');
		$html = '<section class="ls-newsdetail-test">';
		$html .= '<div class="ls-newsdetail_header">';
		$html .= '<div class="ls-newsdetail_header__metadata">';
		$html .= '<p class="ls-newsdetail_header__date">' . $date . ' | </p>';
		$html .= '<p class="ls-newsdetail-header__category">';
		foreach ($categories as $category) {
			$html .= $category->name . ' ';
		}
		$html .= '</p></div>';
		$html .= '<h1 class="ls-newsdetail_header__headline">' . $title . '</h1>';
		$html .= '<div class="ls-newsdetail_header__heading">';
		$html .= '<div class="ls-newsdetail_header__image">' . $image . '</div>';
		if (isset($categories) && count($categories) > 0 ) {
			$html .= '<div class="ls-newsdetail__icons">';
			foreach ($categories as $cat) {
				$cat_icon = get_field('newscategory_file_upload', $cat);
                if($cat_icon) {
                    $cat_icon_url = $cat_icon['url'];
                    $cat_icon_alt = $cat_icon['alt'];
                    $cat_icon_title = $cat_icon['title'];

                    if (isset($cat->slug)) {
                        $html .= '<div class="ls-newsdetail__category-icon">';
                        $html .= '<img src="' . $cat_icon_url . '" alt="' . $cat_icon_alt . '" alt="' . $cat_icon_title . '">';
                        $html .= '</div>';
                    }
                }
			}
			$html .= '</div>';
		}
		$html .= '</div></div>';

		$html .= '<div class="ls-newsdetail_main">';
		$html .= '<div class="ls-newsdetail_content">' . $the_content . '</div>';
		$html .= '<div class="ls-newsdetail_sidebar">';
		$html .= '<div class="ls-social_share">';
		$html .= '<p class="is-style-label-medium">' . __('Share it', 'iwgplating') . '</p>';
		$html .= '<div class="wp-block-button"><a id="ls-mail-share-button" class="wp-icons icon-contact"></a></div>';
		$html .= '<div class="wp-block-button"><a id="ls-facebook-share-button" class="wp-icons icon-fb"></a></div>';
        global $wp;
        $current_url = add_query_arg( $wp->query_vars, home_url( $wp->request ) );
        $title = urlencode( get_the_title() );
		$html .= '<div class="wp-block-button"><a id="ls-linkedin-share-button" target="_blank" href="https://www.linkedin.com/shareArticle?mini=true&url='. $current_url .'&title=' . $title . '" class="wp-icons icon-linkedin"></a></div>';
		$html .= '</div>';

		if ($downloads && count($downloads) > 0 ) {
			$html .= '<div class="ls-newsdetail__downloads"><hr>';
			$html .= '<p class="is-style-label-medium">' . __('Downloads', 'iwgplating') . '</p>';
			$html .= '<ul>';
			foreach ($downloads as $download) {
				$download_url = $download['news_file_upload']['url'];
				$download_name = $download['news_file_upload']['filename'];
				$html .= '<li class="ls-newsdetail__downloads_item"><a href="' . $download_url . '" target="_blank">' . $download_name . '</a></li>';
			}
			$html .= '</ul></div>';
		}
		$html .= '</div></div></section>';
		return $html;
	}
}
add_filter('the_content', '\WPS\News\Detailpage\disable_the_content');
