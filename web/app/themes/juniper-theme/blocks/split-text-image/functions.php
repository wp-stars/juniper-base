<?php

add_action(
    'wp_enqueue_scripts', function () {
	if ( ! has_block( 'acf/split-text-image' ) ) {
		return;
	}

	$time       = time();
	$theme_path = get_template_directory_uri();

	$style_file_path = $theme_path . '/blocks/split-text-image/style.css';
	$script_file_path = $theme_path . '/blocks/split-text-image/script.js';

	if ( empty( file_get_contents( $style_file_path ) ) ) {
		return;
	}

	wp_enqueue_style('split-text-image-css', $theme_path . '/blocks/split-text-image/style.css', array(), $time, 'all');

	if ( empty( file_get_contents( $script_file_path ) ) ) {
		return;
	}

	wp_enqueue_script('split-text-image-js', $theme_path . '/blocks/split-text-image/script.js', array(), $time, true);
}
);

add_filter(
    'timber/acf-gutenberg-blocks-data/split-text-image',
    function ( $context ) {

      $context["fields"]["reverse_order"] == true ?
            $context["reverse"] = "first" : $context["reverse"] = "last";
    
      $context["fields"]["dark_mode"] == true ?
            $context["dark_mode"] = "dark" : $context["dark_mode"] = "";
        return $context;
    }
);
