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
            $blog_items;
            if(!$context['fields']['blog_slider_items']) {
                $blog_query = new WP_Query( array(
                    'post_type' => 'post',
                    'posts_per_page' => 3,
                )); 

                $blog_items = $blog_query->posts;
            } else {
                $blog_items = $context['fields']['blog_slider_items'];
            }
            foreach ($blog_items as $key => $item) {
                $context['fields']['blog_slider_items'][$key]->post_title = get_the_title($item);

                $context['fields']['blog_slider_items'][$key]->slide_image = get_the_post_thumbnail($item, 'large');

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
