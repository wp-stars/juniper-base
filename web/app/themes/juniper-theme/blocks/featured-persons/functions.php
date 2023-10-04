<?php

add_action(
    'wp_enqueue_scripts', function () {
        if (has_block('acf/featured-persons')) {
            $time = time();
            $theme_path = get_template_directory_uri();

            wp_enqueue_style('featured-persons-css', $theme_path . '/blocks/featured-persons/style.css', array(), $time, 'all');
            wp_enqueue_script('featured-persons-js', $theme_path . '/blocks/featured-persons/script.js', array(), $time, true);

            wp_enqueue_script('slider-swiper', 'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js', array(), $time, false);
            wp_enqueue_style('slider-swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css', array(), $time, 'all');

        }
    }
);

add_filter(
    'timber/acf-gutenberg-blocks-data/featured-persons',
    function ( $context ) {
        return $context;
    }
);
