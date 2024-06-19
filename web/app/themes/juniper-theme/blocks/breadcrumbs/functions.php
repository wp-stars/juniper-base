<?php

add_action(
    'wp_enqueue_scripts', function () {
	if ( ! has_block( 'acf/breadcrumbs' ) ) {
		return;
	}
	$time       = time();
	$theme_path = get_template_directory_uri();

	$style_file_path = $theme_path . '/blocks/breadcrumbs/style.css';
	$script_file_path =  $theme_path . '/blocks/breadcrumbs/script.js';

	$style_file_content = file_get_contents( $style_file_path );

	if ( empty( $style_file_content ) ) {
		return;
	}

	unset( $style_file_content );

	wp_enqueue_style('breadcrumbs-css', $style_file_path, array(), $time, 'all');

	$script_file_content = file_get_contents( $script_file_path );

	if ( empty( $script_file_content ) ) {
		return;
	}

	unset( $script_file_content );

	wp_enqueue_script('breadcrumbs-js',$script_file_path , array(), $time, true);
}
);

add_filter(
    'timber/acf-gutenberg-blocks-data/breadcrumbs',
    function ( $context ) {
        // title and URL of current page
        $page_title = get_the_title();
        $page_url = get_permalink();

        // title and URL of parent page
        $parent_page_title = $context['parent_page_title'];
        $parent_page_url = $context['parent_page_url'];
        if (is_page() && $post = get_post()) {
            $parent_page_id = $post->post_parent;
            if ($parent_page_id) {
                $parent_page = get_post($parent_page_id);
                $parent_page_title = get_the_title($parent_page);
                $parent_page_url = get_permalink($parent_page);
            }
        }

        $context['page_title']          = $page_title;
        $context['page_url']            = $page_url;
        $context['parent_page_title']   = $parent_page_title;
        $context['parent_page_url']     = $parent_page_url;

        return $context;
    }
);

