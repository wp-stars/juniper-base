<?php

add_action( 'wp_enqueue_scripts', function () {
	if ( ! has_block( 'acf/customer-requirements' ) ) {
		return;
	}

	$time       = time();
	$theme_path = get_template_directory_uri();

	$style_file_path = $theme_path . '/blocks/customer-requirements/style.css';
	$script_file_path = $theme_path . '/blocks/customer-requirements/script.js';

	if ( empty( file_get_contents( $style_file_path ) ) ) {
		return;
	}

	wp_enqueue_style( 'customer-requirements-css', $theme_path . '/blocks/customer-requirements/style.css', [], $time, 'all' );

	if ( empty( file_get_contents( $script_file_path ) ) ) {
		return;
	}

	wp_enqueue_script( 'customer-requirements-js', $theme_path . '/blocks/customer-requirements/script.js', [], $time, true );
} );

add_filter(

	'timber/acf-gutenberg-blocks-data/customer-requirements',

	function ( $context ) {
		return $context;
	} );
