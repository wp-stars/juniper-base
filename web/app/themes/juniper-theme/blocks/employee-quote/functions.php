<?php

add_action('wp_enqueue_scripts', function() {
	if (has_block('acf/employee-quote')) {
	$time = time();
	$theme_path = get_template_directory_uri();

		wp_enqueue_style('employee-quote-css', $theme_path . '/blocks/employee-quote/style.css', array(), $time, 'all');
		wp_enqueue_script('employee-quote-js', $theme_path . '/blocks/employee-quote/script.js', array(), $time, true);
	}
});

 add_filter(

 	'timber/acf-gutenberg-blocks-data/employee-quote',

 	function( $context ) {
 	return $context;
 });
