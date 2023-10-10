<?php

add_action(
    'wp_enqueue_scripts', function () {
        if (has_block('acf/locationlist')) {
            $time = time();
            $theme_path = get_template_directory_uri();

            wp_enqueue_style('locationlist-css', $theme_path . '/blocks/locationlist/style.css', array(), $time, 'all');
            wp_enqueue_script('locationlist-js', $theme_path . '/blocks/locationlist/script.js', array(), $time, true);
        }
    }
);

add_filter(
    'timber/acf-gutenberg-blocks-data/locationlist',
    function ( $context ) {

        $context['maps_api_key'] = get_field('google_maps_api_key', 'option');
        return $context;
    }
);






