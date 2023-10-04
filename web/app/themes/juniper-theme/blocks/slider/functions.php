<?php

add_action(
    'wp_enqueue_scripts', function () {
        if (has_block('acf/slider')) {
            $time = time();
            $theme_path = get_template_directory_uri();

            wp_enqueue_script('slider-swiper', 'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js', array(), $time, false);
            wp_enqueue_style('slider-css', $theme_path . '/blocks/slider/style.css', array(), $time, 'all');
            wp_enqueue_style('slider-swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css', array(), $time, 'all');

            
            wp_enqueue_script('slider-js', $theme_path . '/blocks/slider/script.js', array(), $time, true);
            wp_enqueue_script('slider-simple', $theme_path . '/blocks/slider/simple-script.js', array(), $time, true);
            
        }
    }
);

add_filter(
    'timber/acf-gutenberg-blocks-data/slider',
    function ( $context ) {
        if($context['fields']['style'] === 'simple') {
            foreach ($context['fields']['simple_slider_items'] as $key => $item) {
                $context['fields']['simple_slider_items'][$key]['slide_image'] = wp_get_attachment_image( $context['fields']['simple_slider_items'][$key]['slide_image'], 'medium', array('height' => 100, 'width' => 100));
            }
        }

        if($context['fields']['style'] === 'blog') {
            foreach ($context['fields']['blog_slider_items'] as $key => $item) {
                $context['fields']['blog_slider_items'][$key]->slide_image = get_the_post_thumbnail($item, 'medium');

                $context['fields']['blog_slider_items'][$key]->link = get_the_permalink($item);

                $context['fields']['blog_slider_items'][$key]->author = get_the_author_meta('display_name', $item->post_author);

                $context['fields']['blog_slider_items'][$key]->date = get_the_date('d.m.Y', $item);

                $context['fields']['blog_slider_items'][$key]->excerpt = get_the_excerpt($item);

            }
        }

        $context['instance'] = uniqid();
        return $context;
    }
);
