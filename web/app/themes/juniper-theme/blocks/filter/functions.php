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

        $data_arr = wps_get_filter_posts( $post_type, $taxonomies = [], 1, '');

        $post_type = get_post_type_object( $context['fields']['post_type'] );
        $data_arr['postName'] = $post_type->labels->name;
        $data_arr['postType'] = $context['fields']['post_type'];
        $data_arr['restUrl'] = get_rest_url();
        $data_arr['filterOptions'] = $context['fields']['filter_options'];
        $data_arr['title'] = $context['fields']['title'];
        $data_arr['shop'] = $context['fields']['shop'];

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
    $search = !empty($_GET['search']) ? $_GET['search'] : '';

    return wps_get_filter_posts($post_type, $decodedTaxonomies, $page, $search);
}


function wps_get_filter_posts( $post_type, $taxonomies, $page, $search = '' ) {

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
        'paged' => $page,
        's' => $search
    );

    /*if ($exclude_product_type) {
        // Exclude products of specific product type
        $args['meta_query'] = array(
            array(
                'key' => '_product_type',
                'value' => $exclude_product_type,
                'compare' => '!='
            )
        );
    }*/

    if(count($tax_query)) {
        $args['tax_query'] = $tax_query;
    }

    $cachingToken = 'wps_filter_cache_' . md5(json_encode($args));
    $cachedData = get_transient($cachingToken);
    $cacheDuration = HOUR_IN_SECONDS;

    if(isset($_GET['nocache']) && $_GET['nocache'] == 1) {
        delete_transient($cachingToken);
    }

    if (false === $cachedData) {
        $filter_query = new WP_Query($args);

        // check for a valid query result
        if(!is_wp_error($filter_query) && $filter_query->have_posts()) {

            // set transient for 1 hour
            set_transient($cachingToken, $filter_query, $cacheDuration);
        }
    }else{
        // use the cached Data if available
        $filter_query = $cachedData;
    }

    $post_arr = array();
    foreach ($filter_query->posts as $post) {


    $fields = get_fields($post->ID);

        $post_obj = new stdClass();
        $post_obj->ID = $post->ID;
        // $post_obj->fields = get_fields(json_encode($post));
        $post_obj->excerpt = htmlspecialchars(wp_trim_excerpt('', $post));
        $post_obj->post_title = htmlspecialchars($post->post_title);
        $post_obj->post_name = htmlspecialchars($post->post_name);
        $post_obj->featured_image = htmlspecialchars(get_the_post_thumbnail_url($post));
        $post_obj->link = htmlspecialchars(get_permalink($post));
        $post_obj->price = (int)(wc_get_product( $post->ID ))->get_regular_price();
        $post_obj->subheadline = htmlspecialchars($fields['wps_sp_subheadline'] ?? '');
        $post_obj->description_title = htmlspecialchars($fields['wps_sp_description_title'] ?? '');
        $post_obj->description_text = htmlspecialchars($fields['wps_sp_description_text'] ?? '');
        $post_obj->features_text = htmlspecialchars($fields['wps_sp_features_text'] ?? '');
        $post_obj->areas_of_application = htmlspecialchars($fields['wps_sp_areas_of_application_text'] ?? '');

      
        $taxonomies = get_post_taxonomies($post);
       
        $taxonomy_data = array();
        foreach ($taxonomies as $taxonomy) {
            $terms = get_the_terms($post, $taxonomy);
            if ($terms && !is_wp_error($terms)) {
                foreach ($terms as $term) {
                    $taxonomy_data[$taxonomy][] = array(
                        'term_id' => $term->term_id,
                        'name' => $term->name,
                        'slug' => $term->slug
                    );
                }
            }
        }

        $post_obj->taxonomies = $taxonomy_data;
        

        $product_type = '';
        $terms = get_the_terms($post, 'product_type');
        if ($terms && !is_wp_error($terms)) {
            $product_type = $terms[0]->name;
        }
        $post_obj->product_type = $product_type;

        $encodedHtml = base64_encode(do_shortcode("[wps_get_product_card product_id='{$post->ID}']"));

        $post_obj->html = $encodedHtml;

        $post_arr[] = $post_obj;
    }

   
    $data_arr['posts'] = $post_arr;
    $data_arr['maxNumPages'] = $filter_query->max_num_pages;
   
    return $data_arr;
}


