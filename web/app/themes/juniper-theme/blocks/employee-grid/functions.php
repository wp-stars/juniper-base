<?php

add_action(
	'wp_enqueue_scripts', function () {
	if ( ! has_block( 'acf/employee-grid' ) ) {
		return;
	}

	$time       = time();
	$theme_path = get_template_directory_uri();

	$style_file_path = $theme_path . '/blocks/employee-grid/style.css';
	$script_file_path = $theme_path . '/blocks/employee-grid/script.js';

	if ( empty( file_get_contents( $style_file_path ) ) ) {
		return;
	}

	wp_enqueue_style( 'employee-grid-css', $theme_path . '/blocks/employee-grid/style.css', [], $time, 'all' );

	if ( empty( file_get_contents( $script_file_path ) ) ) {
		return;
	}

	wp_enqueue_script( 'employee-grid-js', $theme_path . '/blocks/employee-grid/script.js', [], $time, true );
}
);

add_filter(
	'timber/acf-gutenberg-blocks-data/employee-grid',
	function ( $context ) {
		$context['title'] = "Employees";

		return $context;
	}
);
