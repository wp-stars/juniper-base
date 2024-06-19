<?php

add_action(
    'wp_enqueue_scripts', function () {
	if ( ! has_block( 'acf/locationlist' ) ) {
		return;
	}

	$time       = time();
	$theme_path = get_template_directory_uri();

	$style_file_path = $theme_path . '/blocks/locationlist/style.css';
	$script_file_path = $theme_path . '/blocks/locationlist/script.js';

	if ( empty( file_get_contents( $style_file_path ) ) ) {
		return;
	}

	wp_enqueue_style( 'locationlist-css', $theme_path . '/blocks/locationlist/style.css', [], $time, 'all');

	if ( empty( file_get_contents( $script_file_path ) ) ) {
		return;
	}

	wp_enqueue_script( 'locationlist-js', $theme_path . '/blocks/locationlist/script.js', [], $time, true);
}
);

add_filter(
    'timber/acf-gutenberg-blocks-data/locationlist',
    function ( $context ) {

        $context['maps_api_key'] = get_field('google_maps_api_key', 'option');
        return $context;
    }
);






