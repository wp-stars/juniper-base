<?php

add_action( 'wp_enqueue_scripts', function () {
	if ( ! has_block( 'acf/employee-quote' ) ) {
		return;
	}

	$time       = time();
	$theme_path = get_template_directory_uri();

	$style_file_path = $theme_path . '/blocks/employee-quote/style.css' ;
	$script_file_path = $theme_path . '/blocks/employee-quote/script.js';

	if ( empty( file_get_contents( $style_file_path ) ) ) {
		return;
	}

	wp_enqueue_style( 'employee-quote-css', $theme_path . '/blocks/employee-quote/style.css', [], $time, 'all' );

	if ( empty( file_get_contents( $script_file_path ) ) ) {
		return;
	}

	wp_enqueue_script( 'employee-quote-js', $theme_path . '/blocks/employee-quote/script.js', [], $time, true );
} );

add_filter(

	'timber/acf-gutenberg-blocks-data/employee-quote',

	function ( $context ) {
		return $context;
	} );
