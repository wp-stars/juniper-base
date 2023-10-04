<?php

add_action('wp_enqueue_scripts', function() {
	if (has_block('acf/customer-requirements')) {
	$time = time();
	$theme_path = get_template_directory_uri();

		wp_enqueue_style('customer-requirements-css', $theme_path . '/blocks/customer-requirements/style.css', array(), $time, 'all');
		wp_enqueue_script('customer-requirements-js', $theme_path . '/blocks/customer-requirements/script.js', array(), $time, true);
	}
});

 add_filter(

 	'timber/acf-gutenberg-blocks-data/customer-requirements',

 	function( $context ) {
 	return $context;
 });
