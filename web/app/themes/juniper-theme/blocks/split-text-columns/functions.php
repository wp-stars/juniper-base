<?php

add_action(
    'wp_enqueue_scripts', function () {
	if ( ! has_block( 'acf/split-text-columns' ) ) {
		return;
	}

	$time       = time();
	$theme_path = get_template_directory_uri();

	$style_file_path = $theme_path . '/blocks/split-text-columns/style.css';
	$script_file_path = $theme_path . '/blocks/split-text-columns/script.js';

	if ( empty( file_get_contents( $script_file_path ) ) ) {
		return;
	}

	wp_enqueue_style('split-text-columns-css', $theme_path . '/blocks/split-text-columns/style.css', array(), $time, 'all');

	if ( empty( file_get_contents( $style_file_path ) ) ) {
		return;
	}

	wp_enqueue_script('split-text-columns-js', $theme_path . '/blocks/split-text-columns/script.js', array(), $time, true);
}
);

add_filter(
    'timber/acf-gutenberg-blocks-data/split-text-columns',
    function ( $context ) {
        foreach ($context['fields']['items'] as $key => $item) {
            if($context['fields']['items'][$key]['image']) {
                $context['fields']['items'][$key]['image'] = wp_get_attachment_image( $context['fields']['items'][$key]['image'], 'medium', array('height' => 100, 'width' => 100));
            }
        }
        return $context;
    }
);
