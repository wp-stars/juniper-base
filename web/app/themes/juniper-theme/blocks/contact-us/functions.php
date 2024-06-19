<?php

add_action( 'wp_enqueue_scripts', function () {
	if ( ! has_block( 'acf/contact-us' ) ) {
		return;
	}
	$time       = time();
	$theme_path = get_template_directory_uri();

	$style_file_path = $theme_path . '/blocks/contact-us/style.css';
	$script_file_path = $theme_path . '/blocks/contact-us/script.js';

	if ( empty( file_get_contents( $style_file_path ) ) ) {
		return;
	}


	wp_enqueue_style( 'contact-us-css', $theme_path . '/blocks/contact-us/style.css', [], $time, 'all' );

	if ( empty( file_get_contents( $script_file_path ) ) ) {
		return;
	}

	wp_enqueue_script( 'contact-us-js', $theme_path . '/blocks/contact-us/script.js', [], $time, true );
} );

add_filter(

	'timber/acf-gutenberg-blocks-data/contact-us',

	function ( $context ) {
		// var_dump($context['fields']['image']['sizes']['medium']);
		return $context;
	} );
