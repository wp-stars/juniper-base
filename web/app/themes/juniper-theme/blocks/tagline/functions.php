<?php

add_action(
    'wp_enqueue_scripts', function () {
        if (has_block('acf/tagline')) {
            $time = time();
            $theme_path = get_template_directory_uri();

            wp_enqueue_style('tagline-css', $theme_path . '/blocks/tagline/style.css', array(), $time, 'all');
            wp_enqueue_script('tagline-js', $theme_path . '/blocks/tagline/script.js', array(), $time, true);
        }
    }
);

add_filter(
    'timber/acf-gutenberg-blocks-data/tagline',
    function ( $context ) {
        return $context;
    }
);
