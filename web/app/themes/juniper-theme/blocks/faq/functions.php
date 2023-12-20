<?php

add_action('wp_enqueue_scripts', function() {
	if (has_block('acf/faq')) {
	$time = time();
	$theme_path = get_template_directory_uri();

		wp_enqueue_style('faq-css', $theme_path . '/blocks/faq/style.css', array(), $time, 'all');
		wp_enqueue_script('faq-js', $theme_path . '/blocks/faq/script.js', array(), $time, true);
	}
});

 add_filter(

 	'timber/acf-gutenberg-blocks-data/faq',

 	function( $context ) {

		$context["fields"]["dark_mode"] == true ?
        $context["dark_mode"] = "dark" : $context["dark_mode"] = "";
		
 	return $context;
 });
