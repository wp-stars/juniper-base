<?php

add_action('wp_enqueue_scripts', function() {
	if (has_block('acf/contact-us')) {
	$time = time();
	$theme_path = get_template_directory_uri();

		wp_enqueue_style('contact-us-css', $theme_path . '/blocks/contact-us/style.css', array(), $time, 'all');
		wp_enqueue_script('contact-us-js', $theme_path . '/blocks/contact-us/script.js', array(), $time, true);
	}
});

 add_filter(

 	'timber/acf-gutenberg-blocks-data/contact-us',

 	function( $context ) {
		// var_dump($context['fields']['image']['sizes']['medium']);
 	return $context;
 });
