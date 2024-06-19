<?php

add_action(
    'wp_enqueue_scripts', function () {
	if ( ! has_block( 'acf/taxonomy-landing-page-links' ) ) {
		return;
	}

	$time       = time();
	$theme_path = get_template_directory_uri();

	$style_file_path = $theme_path . '/blocks/taxonomy-landing-page-links/style.css';
	$script_file_path = $theme_path . '/blocks/taxonomy-landing-page-links/script.js';

	if ( empty( file_get_contents( $style_file_path ) ) ) {
		return;
	}

	wp_enqueue_style('taxonomy-landing-page-links-css', $theme_path . '/blocks/taxonomy-landing-page-links/style.css', array(), $time, 'all');

	if ( empty( file_get_contents( $script_file_path ) ) ) {
		return;
	}

	wp_enqueue_script('taxonomy-landing-page-links-js', $theme_path . '/blocks/taxonomy-landing-page-links/script.js', array(), $time, true);
}
);

add_filter(
    'timber/acf-gutenberg-blocks-data/taxonomy-landing-page-links',
    function ( $context ) {
        global $post;
        $terms = [];

        if($terms = get_the_terms($post->ID, $context['fields']['taxonomy'])) {
            foreach ($terms as $term) {
                $term->fields = get_fields($term);
                $term->link = get_term_link($term);
            }
        }

        $context['terms'] = $terms;

        return $context;
    }
);
