<?php

add_action('wp_enqueue_scripts', function() {
	if ( ! has_block( 'acf/location-gender-hours' ) ) {
		return;
	}
	$time       = time();
	$theme_path = get_template_directory_uri();

	$style_file_path = $theme_path . '/blocks/location-gender-hours/style.css';
	$script_file_path = $theme_path . '/blocks/location-gender-hours/script.js';

	if ( empty( file_get_contents( $style_file_path ) ) ) {
		return;
	}

	wp_enqueue_style('location-gender-hours-css', $theme_path . '/blocks/location-gender-hours/style.css', array(), $time, 'all');

	if ( empty( file_get_contents( $script_file_path ) ) ) {
		return;
	}

	wp_enqueue_script('location-gender-hours-js', $theme_path . '/blocks/location-gender-hours/script.js', array(), $time, true);
});

 add_filter(

 	'timber/acf-gutenberg-blocks-data/location-gender-hours',

 	function( $context ) {
 	return $context;
 });
