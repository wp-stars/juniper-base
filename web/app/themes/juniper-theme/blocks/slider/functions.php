<?php

add_action(
    'wp_enqueue_scripts', function () {
	if ( ! has_block( 'acf/slider' ) ) {
		return;
	}

	$time       = time();
	$theme_path = get_template_directory_uri();

	$style_file_path = $theme_path . '/blocks/slider/style.css';
	$script_file_path = $theme_path . '/blocks/slider/script.js';

	if ( empty( file_get_contents( $style_file_path ) ) ) {
		return;
	}

	wp_enqueue_style( 'slider-css', $theme_path . '/blocks/slider/style.css', [], $time, 'all');

	if ( empty( file_get_contents( $script_file_path ) ) ) {
		return;
	}

	wp_enqueue_script( 'slider-js', $theme_path . '/blocks/slider/script.js', [], $time, true);
}
);

add_filter(
    'timber/acf-gutenberg-blocks-data/slider',
    function ( $context ) {
        if($context['fields']['style'] === 'simple') {
            foreach ($context['fields']['simple_slider_items'] as $key => $item) {
                $context['fields']['simple_slider_items'][$key]['slide_image'] = wp_get_attachment_image( $context['fields']['simple_slider_items'][$key]['slide_image'], 'medium', [ 'height' => 100, 'width' => 100 ] );
            }
        }

        if( $context['fields']['style'] !== 'blog' ) {
			if ( ! $context['fields']['blog_slider_items'] ) {
		        $blog_query = new WP_Query( [
			                                    'post_type'      => 'post',
			                                    'posts_per_page' => 3,
		                                    ] );

		        $blog_items = $blog_query->posts;
	        } else {
		        $blog_items = $context['fields']['blog_slider_items'];
	        }
	        foreach ( $blog_items as $key => $item ) {
		        if ( ! isset( $context['fields']['blog_slider_items'][ $key ] ) )
			        $context['fields']['blog_slider_items'][ $key ] = new stdClass();

		        $context['fields']['blog_slider_items'][ $key ]->post_title = get_the_title( $item );

		        $context['fields']['blog_slider_items'][ $key ]->slide_image = get_the_post_thumbnail( $item, 'large' );

		        $context['fields']['blog_slider_items'][ $key ]->link = get_the_permalink( $item );

		        $context['fields']['blog_slider_items'][ $key ]->author = get_the_author_meta( 'display_name', $item->post_author );

		        $context['fields']['blog_slider_items'][ $key ]->date = get_the_date( 'd.m.Y', $item );

		        $context['fields']['blog_slider_items'][ $key ]->excerpt = get_the_excerpt( $item );

	        }
        }

	    $context['instance'] = uniqid();
        return $context;
    }
);
