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
			), rand( 0, 9999 ), true );
			wp_localize_script( 'dashboardBlockFrontendScript', 'filterData', $attributes );
        }
    }
);

add_filter(
    'timber/acf-gutenberg-blocks-data/filter',
    function ( $context ) {
        $post_type = $context['fields']['post_type'];
        $data_arr = wps_get_filter_posts( $post_type, new stdClass(), 1);

        $data_arr['style'] = $context['fields']['style'];
        $data_arr['restUrl'] = get_rest_url();

        $context['data'] = json_encode($data_arr);
        return $context;
    }
);

function acf_load_post_type_field_choices( $field ) {
    // Reset choices
    $field['choices'] = get_post_types();
    
    return $field;
    
}

add_filter('acf/load_field/name=post_type', 'acf_load_post_type_field_choices');

add_action( 'rest_api_init', function () {
    register_rest_route( 'wps/v1', '/projects', array(
        'methods' => 'GET',
        'callback' => 'wps_filter_callback',
    ) );
  } 
);

function wps_filter_callback() {
    $project_categories = !empty($_GET['project_category']) ? explode(',', $_GET['project_category']) : [];
    $taxonomies = new stdClass();
    $taxonomies->projectcategory = $project_categories;

    $page = !empty($_GET['page']) ? intval($_GET['page']) : 1;

    return wps_get_filter_posts( 'projects', $taxonomies, $page );
}

function wps_get_filter_posts( $post_type, $taxonomies, $page ) {
    $data_arr = array();
    $tax_query = array();
    if(!empty($taxonomies->projectcategory)) {
        $tax_query[] = array(
            'taxonomy'  => 'project_category',
            'field'     => 'term_id',
            'terms'     => array_map(function ($val) { return intval($val); }, $taxonomies->projectcategory),
            'operator'  => 'IN'
        );
    }
    
    $initial_posts = new WP_Query(
        array(
            'post_type' => 'projects',
            'paged' => $page,
            'tax_query' => $tax_query
        )
    );


    foreach ($initial_posts->posts as $post) {
        $post->fields = get_fields($post);
        $post->excerpt = get_the_excerpt($post);
        $post->terms = get_the_terms($post, 'project_category') ? get_the_terms($post, 'project_category') : [];
    }

    $data_arr['posts'] = $initial_posts->posts;

    $terms = get_terms( 
        array(
            'taxonomy'   => 'project_category',
            'hide_empty' => false,
        )
    );

    foreach ($terms as $term) {
        $term->fields = get_fields($term);
    }

    $data_arr['terms'] = $terms;

    return $data_arr;
}


