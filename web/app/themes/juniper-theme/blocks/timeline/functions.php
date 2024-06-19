<?php

add_action(
    'wp_enqueue_scripts', function () {
	if ( ! has_block( 'acf/timeline' ) ) {
		return;
	}

	$time       = time();
	$theme_path = get_template_directory_uri();

	$style_file_path = $theme_path . '/blocks/timeline/style.css';
	$script_file_path = $theme_path . '/blocks/timeline/script.js';

	if ( empty( file_get_contents( $style_file_path ) ) ) {
		return;
	}

	wp_enqueue_script('timeline-js', $theme_path . '/blocks/timeline/script.js', array(), $time, true);

	if ( empty( file_get_contents( $script_file_path ) ) ) {
		return;
	}

	wp_enqueue_style('timeline-css', $theme_path . '/blocks/timeline/style.css', array(), $time, 'all');
}
);

add_filter(
    'timber/acf-gutenberg-blocks-data/timeline',
    function ( $context ) {
        return $context;
    }
);
