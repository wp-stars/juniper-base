<?php

add_action('wp_enqueue_scripts', function() {
	if (has_block('acf/location-gender-hours')) {
	$time = time();
	$theme_path = get_template_directory_uri();

		wp_enqueue_style('location-gender-hours-css', $theme_path . '/blocks/location-gender-hours/style.css', array(), $time, 'all');
		wp_enqueue_script('location-gender-hours-js', $theme_path . '/blocks/location-gender-hours/script.js', array(), $time, true);
	}
});

 add_filter(

 	'timber/acf-gutenberg-blocks-data/location-gender-hours',

 	function( $context ) {
 	return $context;
 });
