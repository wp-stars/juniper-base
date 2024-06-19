<?php

add_action( 'wp_enqueue_scripts', function () {
	if ( ! has_block( 'acf/faq' ) ) {
		return;
	}

	$time       = time();
	$theme_path = get_template_directory_uri();

	$style_file_path = $theme_path . '/blocks/faq/style.css';
	$script_file_path = $theme_path . '/blocks/faq/script.js';

	if ( empty( file_get_contents( $style_file_path ) ) ) {
		return;
	}

	wp_enqueue_style( 'faq-css', $theme_path . '/blocks/faq/style.css', [], $time, 'all' );

	if ( empty( file_get_contents( $script_file_path ) ) ) {
		return;
	}

	wp_enqueue_script( 'faq-js', $theme_path . '/blocks/faq/script.js', [], $time, true );
} );

add_filter(

	'timber/acf-gutenberg-blocks-data/faq',

	function ( $context ) {

		$context['fields']['dark_mode'] ? $context['dark_mode'] = 'dark' : $context['dark_mode'] = '';

		return $context;
	} );
