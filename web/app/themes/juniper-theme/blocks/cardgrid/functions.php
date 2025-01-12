<?php

add_action(
    'wp_enqueue_scripts', function () {
        if (has_block('acf/cardgrid')) {
            $time = time();
            $theme_path = get_template_directory_uri();

            wp_enqueue_style('cardgrid-css', $theme_path . '/blocks/cardgrid/style.css', array(), $time, 'all');
            wp_enqueue_script('cardgrid-js', $theme_path . '/blocks/cardgrid/script.js', array(), $time, true);
        }
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


