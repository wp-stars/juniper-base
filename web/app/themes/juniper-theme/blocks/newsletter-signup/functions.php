<?php

add_action(
    'wp_enqueue_scripts', function () {
	if ( ! has_block( 'acf/newsletter-signup' ) ) {
		return;
	}

	$time       = time();
	$theme_path = get_template_directory_uri();

	$style_file_path = $theme_path . '/blocks/newsletter-signup/style.css';
	$script_file_path = $theme_path . '/blocks/newsletter-signup/script.js';

	if ( empty( file_get_contents( $style_file_path ) ) ) {
		return;
	}

	wp_enqueue_style('newsletter-signup-css', $theme_path . '/blocks/newsletter-signup/style.css', array(), $time, 'all');

	if ( empty( file_get_contents( $script_file_path ) ) ) {
		return;
	}

	wp_enqueue_script('newsletter-signup-js', $theme_path . '/blocks/newsletter-signup/script.js', array(), $time, true);
}
);

add_filter(
    'timber/acf-gutenberg-blocks-data/newsletter-signup',
    function ( $context ) {
        return $context;
    }
);
