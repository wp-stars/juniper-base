<?php

add_action(
    'wp_enqueue_scripts', function () {
        if (has_block('acf/jumbotron')) {
            $time = time();
            $theme_path = get_template_directory_uri();

            wp_enqueue_style('jumbotron-css', $theme_path . '/blocks/jumbotron/style.css', array(), $time, 'all');
            wp_enqueue_script('jumbotron-js', $theme_path . '/blocks/jumbotron/script.js', array(), $time, true);
        }
    }
);

add_filter(
    'timber/acf-gutenberg-blocks-data/jumbotron',
    function ( $context ) {

        if(!empty($context['fields']['title'])) $context['title'] = $context['fields']['title'];

        if(!empty($context['fields']['background_image'])) $context['jumbotron_bg_image'] = wp_get_attachment_image_url($context['fields']['background_image'], 'full');

        return $context;
    }
);
