<?php

add_action(
    'wp_enqueue_scripts', function () {
        if (has_block('acf/tabs')) {
            $time = time();
            $theme_path = get_template_directory_uri();

            wp_enqueue_style('tabs-css', $theme_path . '/blocks/tabs/style.css', array(), $time, 'all');
            wp_enqueue_script('tabs-js', $theme_path . '/blocks/tabs/script.js', array(), $time, true);
        }
    }
);

add_filter(
    'timber/acf-gutenberg-blocks-data/tabs',
    function ( $context ) {
        $context['instance'] = uniqid();
        return $context;
    }
);
