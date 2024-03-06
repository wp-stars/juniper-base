<?php

add_action(
    'wp_enqueue_scripts', function () {
        if (has_block('acf/filter')) {
            $time = time();
            $theme_path = get_template_directory_uri();

            wp_enqueue_style('filter-css', $theme_path . '/blocks/filter/style.css', array(), $time, 'all');
            wp_enqueue_script('filter-js', $theme_path . '/blocks/filter/script.js', array(), $time, true);

            $attributes = [];
            wp_enqueue_script( 'dashboardBlockFrontendScript', $theme_path . '/blocks/filter/build/frontend.js', array(
				'wp-blocks',
				'wp-element',
				'wp-editor',
				'wp-api',
				'wp-element',
				'wp-i18n',
				'wp-polyfill',
				'wp-api-fetch'
			), $time, true );
			wp_localize_script( 'dashboardBlockFrontendScript', 'filterData', $attributes );
        }
    }
);

add_filter(
    'timber/acf-gutenberg-blocks-data/filter',
    function ( $context ) {
        $post_type = $context['fields']['post_type'];

        $filter_options = $context['fields']['filter_options'];
        foreach ($context['fields']['filter_options'] as $key => $option) {
            $tax = get_taxonomy($option['filter_choices']);
            $context['fields']['filter_options'][$key]['label'] = $tax->label;
            $context['fields']['filter_options'][$key]['name'] = $tax->name;

            $terms = get_terms(array(
                'taxonomy' => $option['filter_choices'],
                'hide_empty' => false, // Set to true if you want to exclude terms with no posts.
            ));
            $context['fields']['filter_options'][$key]['tax_options'] = $terms;
        }

        $data_arr = wps_get_filter_posts( $post_type, $taxonomies = [], [], 1);

        $post_type = get_post_type_object( $context['fields']['post_type'] );
        $data_arr['postName'] = $post_type->labels->name;
        $data_arr['postType'] = $context['fields']['post_type'];
        $data_arr['restUrl'] = get_rest_url();
        $data_arr['filterOptions'] = $context['fields']['filter_options'];

        $context['data'] = json_encode($data_arr);
        return $context;
    }
);

function juniper_excerpt_length( $length ) {
    return 20;
}
add_filter( 'excerpt_length', 'juniper_excerpt_length', 999 );

function acf_load_post_type_field_choices( $field ) {
    // Reset choices
    $field['choices'] = get_post_types();
    
    return $field;
    
}
add_filter('acf/load_field/name=post_type', 'acf_load_post_type_field_choices');

function acf_load_taxonomy_field_choices( $field ) {
    // Reset choices
    $taxonomies = get_taxonomies([], 'objects', 'and');
    $choices = [];
    foreach ($taxonomies as $key => $value) {
        $choices[$key] = $value->label;
    }

    $field['choices'] = $choices;
    
    return $field;
}
add_filter('acf/load_field/name=filter_choices', 'acf_load_taxonomy_field_choices');

add_action( 'rest_api_init', function () {
    register_rest_route( 'wps/v1', '/data', array(
        'methods' => 'GET',
        'callback' => 'wps_filter_callback',
        'permission_callback' => '__return_true',
    ) );
  } 
);

function wps_filter_callback() {
    $rawTaxonomies = !empty($_GET['taxonomies']) ? $_GET['taxonomies'] : "[]";

    // URL decode the parameter
    $decodedTaxonomies = urldecode($rawTaxonomies);

    // Remove extra slashes
    $decodedTaxonomies = stripslashes($decodedTaxonomies);
    // Decode the JSON string
    $decodedTaxonomies = json_decode($decodedTaxonomies);

    $page = !empty($_GET['page']) ? intval($_GET['page']) : 1;
    $post_type = !empty($_GET['post_type']) ? $_GET['post_type'] : '';

    return wps_get_filter_posts($post_type, $decodedTaxonomies, $page);
}


function wps_get_filter_posts( $post_type, $taxonomies, $page ) {
    $data_arr = array();
    $tax_query = array();
    if(count($taxonomies)) {
        foreach ($taxonomies as $key => $taxonomy) {
            $tax_query[] = array(
                'taxonomy'  => $taxonomy->name,
                'field'     => 'term_id',
                'terms'     => array_map(function ($val) { return intval($val); }, $taxonomy->value),
                'operator'  => 'IN'
            );
        }
    }

    $args =  array(
        'post_type' => $post_type,
        'paged' => $page
    );

    if(count($tax_query)) {
        $args['tax_query'] = $tax_query;
    }
    
    $filter_query = new WP_Query($args);

    $post_arr = array();
    foreach ($filter_query->posts as $post) {
        $post_obj = new stdClass();
        $post_obj->ID = $post->ID;
        $post_obj->fields = get_fields($post);
        $post_obj->excerpt = wp_trim_excerpt('', $post);
        // $post_obj->terms = get_the_terms($post, $taxonomy) ? get_the_terms($post, $taxonomy) : [];
        $post_obj->post_title = $post->post_title;
        $post_obj->post_name = $post->post_name;
        $post_obj->featured_image = get_the_post_thumbnail_url($post);
        $post_obj->link = get_permalink($post);

        $post_arr[] = $post_obj;
    }

    $data_arr['posts'] = $post_arr;
    $data_arr['maxNumPages'] = $filter_query->max_num_pages;

    return $data_arr;
}


