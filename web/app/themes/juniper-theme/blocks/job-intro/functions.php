<?php

add_action(
    'wp_enqueue_scripts', function () {
	if ( ! has_block( 'acf/job-intro' ) ) {
		return;
	}

	$time       = time();
	$theme_path = get_template_directory_uri();

	$style_file_path = $theme_path . '/blocks/job-intro/style.css';
	$script_file_path = $theme_path . '/blocks/job-intro/script.js';

	if ( empty( file_get_contents( $style_file_path ) ) ) {
		return;
	}

	wp_enqueue_style('job-intro-css', $theme_path . '/blocks/job-intro/style.css', array(), $time, 'all');

	if ( empty( file_get_contents( $script_file_path ) ) ) {
		return;
	}

	wp_enqueue_script('job-intro-js', $theme_path . '/blocks/job-intro/script.js', array(), $time, true);
}
);

add_filter(
    'timber/acf-gutenberg-blocks-data/job-intro',
    function ( $context ) {
        return $context;
    }
);
