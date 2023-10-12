<?php

add_action(
    'wp_enqueue_scripts', function () {
        if (has_block('acf/decorator-image')) {
            $time = time();
            $theme_path = get_template_directory_uri();

            wp_enqueue_style('decorator-image-css', $theme_path . '/blocks/decorator-image/style.css', array(), $time, 'all');
            wp_enqueue_script('decorator-image-js', $theme_path . '/blocks/decorator-image/script.js', array(), $time, true);
        }
    }
);

add_filter(
    'timber/acf-gutenberg-blocks-data/decorator-image',
    function ( $context ) {
        return $context;
    }
);
