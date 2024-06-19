<?php

add_action(
    'wp_enqueue_scripts', function () {
	if ( ! has_block( 'acf/tabs' ) ) {
		return;
	}

	$time       = time();
	$theme_path = get_template_directory_uri();

	$style_file_path = $theme_path . '/blocks/tabs/style.css';
	$script_file_path = $theme_path . '/blocks/tabs/script.js';

	if ( empty( file_get_contents( $style_file_path ) ) ) {
		return;
	}

	wp_enqueue_style('tabs-css', $theme_path . '/blocks/tabs/style.css', array(), $time, 'all');

	if ( empty( file_get_contents( $script_file_path ) ) ) {
		return;
	}

	wp_enqueue_script('tabs-js', $theme_path . '/blocks/tabs/script.js', array(), $time, true);
}
);

add_filter(
    'timber/acf-gutenberg-blocks-data/tabs',
    function ( $context ) {
        $context['instance'] = uniqid();
        return $context;
    }
);
