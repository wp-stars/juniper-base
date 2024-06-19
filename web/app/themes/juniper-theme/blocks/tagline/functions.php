<?php

add_action(
    'wp_enqueue_scripts', function () {
	if ( ! has_block( 'acf/tagline' ) ) {
		return;
	}

	$time       = time();
	$theme_path = get_template_directory_uri();

	$style_file_path = $theme_path . '/blocks/tagline/style.css';
	$script_file_path = $theme_path . '/blocks/tagline/script.js';

	if ( empty( file_get_contents( $style_file_path ) ) ) {
		return;
	}

	wp_enqueue_style('tagline-css', $theme_path . '/blocks/tagline/style.css', array(), $time, 'all');

	if ( empty( file_get_contents( $script_file_path ) ) ) {
		return;
	}

	wp_enqueue_script('tagline-js', $theme_path . '/blocks/tagline/script.js', array(), $time, true);
}
);

add_filter(
    'timber/acf-gutenberg-blocks-data/tagline',
    function ( $context ) {
        return $context;
    }
);
