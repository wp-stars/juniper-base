<?php

add_action(
    'wp_enqueue_scripts', function () {
	if ( ! has_block( 'acf/cardgrid' ) ) {
		return;
	}

	$time       = time();
	$theme_path = get_template_directory_uri();

	$style_file_path = $theme_path . '/blocks/cardgrid/style.css';
	$script_file_path = $theme_path . '/blocks/cardgrid/script.js';

	if ( empty( file_get_contents( $script_file_path ) ) ) {
		return;
	}

	wp_enqueue_style('cardgrid-css', $style_file_path, array(), $time, 'all');

	if ( empty( file_get_contents( $style_file_path ) ) ) {
		return;
	}

	wp_enqueue_script('cardgrid-js', $script_file_path, array(), $time, true);
}
);

add_filter(
    'timber/acf-gutenberg-blocks-data/cardgrid',
    function ( $context ) {

        $context['card_grid_height'] = 432 * count($context['fields']['cards']) / 2;
        $context['half_card_total'] = count($context['fields']['cards']) / 2;


        $context['card_grid_rows'] = floor(count($context['fields']['cards']) / 2);

        return $context;
    }
);


