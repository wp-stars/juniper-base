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


        $context["fields"]["dark_mode"] == true ?
        $context["dark_mode"] = "dark" : $context["dark_mode"] = "light";

        if($context["fields"]["layout"] == "Text-Left"){
            $context["reverse"] = "order-first ml-[auto]";
        }
        else if($context["fields"]["layout"] == "Text-Right"){
            $context["reverse"] = "order-last mr-[auto] ";
        }

        if($context["fields"]["layout"] == "Fullwidth-Overlay"){
            $context["overlay"] = "overlay";
        }
        else if($context["fields"]["layout"] == "Fullwidth-Textbox"){
            $context["textbox"] = "textbox";
        }
        
      
    
        return $context;
    }
);
