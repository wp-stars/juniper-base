<?php

add_action(
	'wp_enqueue_scripts', function () {
	if ( ! has_block( 'acf/decorator-image' ) ) {
		return;
	}
	$time       = time();
	$theme_path = get_template_directory_uri();

	$style_file_path = $theme_path . '/blocks/decorator-image/style.css';
	$script_file_path = $theme_path . '/blocks/decorator-image/script.js';

	if ( empty( file_get_contents( $style_file_path ) ) ) {
		return;
	}

	wp_enqueue_style( 'decorator-image-css', $theme_path . '/blocks/decorator-image/style.css', [], $time, 'all' );

	if ( empty( file_get_contents( $script_file_path ) ) ) {
		return;
	}

	wp_enqueue_script( 'decorator-image-js', $theme_path . '/blocks/decorator-image/script.js', [], $time, true );
}
);

add_filter(
	'timber/acf-gutenberg-blocks-data/decorator-image',
	function ( $context ) {
		return $context;
	}
);
