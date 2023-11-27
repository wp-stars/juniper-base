<?php

add_action('wp_enqueue_scripts', function() {
	if (has_block('acf/code-snippet')) {
	$time = time();
	$theme_path = get_template_directory_uri();

		wp_enqueue_style('code-snippet-css', $theme_path . '/blocks/code-snippet/style.css', array(), $time, 'all');
		wp_enqueue_script('code-snippet-js', $theme_path . '/blocks/code-snippet/script.js', array(), $time, true);
		wp_enqueue_style('code-snippet-css-prism', $theme_path . '/blocks/code-snippet/style-prism.css', array(), $time, 'all');
		wp_enqueue_script('code-snippet-js-prism', $theme_path . '/blocks/code-snippet/script-prism.js', array(), $time, true);
	}
});

 add_filter(

 	'timber/acf-gutenberg-blocks-data/code-snippet',

 	function( $context ) {
 	return $context;
 });
