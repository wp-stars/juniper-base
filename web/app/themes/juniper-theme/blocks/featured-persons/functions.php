<?php

add_action(
    'wp_enqueue_scripts', function () {
	if ( ! has_block( 'acf/featured-persons' ) ) {
		return;
	}

	$time       = time();
	$theme_path = get_template_directory_uri();

	$style_file_path = $theme_path . '/blocks/featured-persons/style.css';
	$script_file_path = $theme_path . '/blocks/featured-persons/script.js';

	if ( empty( file_get_contents( $style_file_path ) ) ) {
		return;
	}

	wp_enqueue_style( 'featured-persons-css', $style_file_path, [], $time );

	if ( empty( file_get_contents( $script_file_path ) ) ) {
		return;
	}

	wp_enqueue_script( 'featured-persons-js', $script_file_path, [], $time, true);
}
);

add_filter(
    'timber/acf-gutenberg-blocks-data/featured-persons',
    function ( $context ) {
        return $context;
    }
);
