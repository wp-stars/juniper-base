<?php

add_action(
    'wp_enqueue_scripts', function () {

        if (has_block('acf/split-text-image')) {
            $time = time();
            $theme_path = get_template_directory_uri();

            wp_enqueue_style('split-text-image-css', $theme_path . '/blocks/split-text-image/style.css', array(), $time, 'all');
            wp_enqueue_script('split-text-image-js', $theme_path . '/blocks/split-text-image/script.js', array(), $time, true);
        }
    }
);

add_filter(
    'timber/acf-gutenberg-blocks-data/split-text-image',
    function ( $context ) {

      $context["fields"]["reverse_order"] == true ?
            $context["reverse"] = "first" : $context["reverse"] = "last";
    
      $context["fields"]["dark_mode"] == true ?
            $context["dark_mode"] = "dark" : $context["dark_mode"] = "";
        return $context;
    }
);
