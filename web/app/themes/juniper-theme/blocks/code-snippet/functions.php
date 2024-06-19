<?php

add_action( 'wp_enqueue_scripts', function () {
	if ( ! has_block( 'acf/code-snippet' ) ) {
		return;
	}

	$time       = time();
	$theme_path = get_template_directory_uri();

	$style_file_path = $theme_path . '/blocks/code-snippet/style-prism.css';
	$script_file_path = $theme_path . '/blocks/code-snippet/script-prism.js';

	if ( empty( file_get_contents( $style_file_path ) ) ) {
		return;
	}

	wp_enqueue_style( 'code-snippet-css-prism', $style_file_path, [], $time, 'all' );

	if ( empty( file_get_contents( $script_file_path ) ) ) {
		return;
	}

	wp_enqueue_script( 'code-snippet-js-prism', $script_file_path, [], $time, true );
} );

add_filter(

	'timber/acf-gutenberg-blocks-data/code-snippet',

	function ( $context ) {
		return $context;
	} );
