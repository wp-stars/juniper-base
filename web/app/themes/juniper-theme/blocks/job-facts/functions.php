<?php

add_action(
    'wp_enqueue_scripts', function () {
	if ( ! has_block( 'acf/job-facts' ) ) {
		return;
	}

	$time       = time();
	$theme_path = get_template_directory_uri();

	$style_file_path = $theme_path . '/blocks/job-facts/style.css';
	$script_file_path = $theme_path . '/blocks/job-facts/script.js';

	if ( empty( file_get_contents( $style_file_path ) ) ) {
		return;
	}

	wp_enqueue_style('job-facts-css', $theme_path . '/blocks/job-facts/style.css', array(), $time, 'all');

	if ( empty( file_get_contents( $style_file_path ) ) ) {
		return;
	}

	wp_enqueue_script('job-facts-js', $theme_path . '/blocks/job-facts/script.js', array(), $time, true);
}
);

add_filter(
    'timber/acf-gutenberg-blocks-data/job-facts',
    function ( $context ) {
        return $context;
    }
);
