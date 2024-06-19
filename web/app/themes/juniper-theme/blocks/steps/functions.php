<?php

add_action('wp_enqueue_scripts', function() {
	if ( ! has_block( 'acf/steps' ) ) {
		return;
	}

	$time       = time();
	$theme_path = get_template_directory_uri();

	$style_file_path = $theme_path . '/blocks/steps/style.css';
	$script_file_path = $theme_path . '/blocks/steps/script.js';

	if ( empty( file_get_contents( $style_file_path ) ) ) {
		return;
	}

	wp_enqueue_style('steps-css', $theme_path . '/blocks/steps/style.css', array(), $time, 'all');

	if ( empty( file_get_contents( $script_file_path ) ) ) {
		return;
	}

	wp_enqueue_script('steps-js', $theme_path . '/blocks/steps/script.js', array(), $time, true);
});

 add_filter(

 	'timber/acf-gutenberg-blocks-data/steps',

 	function( $context ) {


		$context["fields"]["dark_mode"] == true ?
        $context["dark_mode"] = "dark" : $context["dark_mode"] = "";
		
 	return $context;
 });
