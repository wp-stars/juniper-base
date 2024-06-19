<?php

add_action(
    'wp_enqueue_scripts', function () {
	if ( ! has_block( 'acf/job-list' ) ) {
		return;
	}

	$time       = time();
	$theme_path = get_template_directory_uri();

	$style_file_path = $theme_path . '/blocks/job-list/style.css';
	$script_file_path = $theme_path . '/blocks/job-list/script.js';

	if ( empty( file_get_contents( $style_file_path ) ) ) {
		return;
	}

	wp_enqueue_style('job-list-css', $theme_path . '/blocks/job-list/style.css', array(), $time, 'all');

	if ( empty( file_get_contents( $script_file_path ) ) ) {
		return;
	}

	wp_enqueue_script('job-list-js', $theme_path . '/blocks/job-list/script.js', array(), $time, true);
}
);

add_filter(
    'timber/acf-gutenberg-blocks-data/job-list',
    function ( $context ) {

        $args = array(
            'post_type' => 'jobs',
        );

        $jobs = get_posts($args);

        foreach ($jobs as $job) {
            $job->permalink = get_permalink($job);
            $job->fields = get_fields($job);
        }

        $context['jobs'] = $jobs;

        return $context;
    }
);
