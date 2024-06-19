<?php

add_action(
    'wp_enqueue_scripts', function () {
	if ( ! has_block( 'acf/applynow' ) ) {
		return;
	}

	$time       = time();
	$theme_path = get_template_directory_uri();

	$style_file_path = $theme_path . '/blocks/applynow/style.css';
	$script_file_path = $theme_path . '/blocks/applynow/script.js';

	$style_file_content = file_get_contents( $style_file_path );

	if ( empty( $style_file_content ) ) {
		return;
	}

	unset( $style_file_content );

	wp_enqueue_style('applynow-css', $theme_path . '/blocks/applynow/style.css', array(), $time, 'all');

	$scriptFile_content = file_get_contents( $script_file_path );

	if ( empty( $scriptFile_content ) ) {
		return;
	}

	unset( $scriptFile_content );

	wp_enqueue_script('applynow-js', $theme_path . '/blocks/applynow/script.js', array(), $time, true);
}
);

add_filter(
    'timber/acf-gutenberg-blocks-data/applynow',
    function ( $context ) {
        return $context;
    }
);
