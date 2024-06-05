<?php

namespace Blocks\Filter;

use stdClass;
use WP_Post;

add_action(
	'wp_enqueue_scripts', function () {
	if ( has_block( 'acf/filter' ) ) {
		$time       = time();
		$theme_path = get_template_directory_uri();

		wp_enqueue_style( 'filter-css', $theme_path . '/blocks/filter/style.css', [], $time, 'all' );
		wp_enqueue_script( 'filter-js', $theme_path . '/blocks/filter/script.js', [], $time, true );

		$attributes = [];
		wp_enqueue_script( 'filterBlock', $theme_path . '/blocks/filter/build/frontend.js', [
			'wp-blocks',
			'wp-element',
			'wp-editor',
			'wp-api',
			'wp-element',
			'wp-i18n',
			'wp-polyfill',
			'wp-api-fetch',
		],                 $time, true );
		wp_localize_script( 'filterBlock', 'filterData', $attributes );
	}
}
);

add_filter( 'timber/acf-gutenberg-blocks-data/filter', function ( $context ) {
	$post_type = $context['fields']['post_type'];

	$filter_options = $context['fields']['filter_options'] ?: [];

	$current_language = apply_filters( 'wpml_current_language', null );

	foreach ( $filter_options as $key => $option ) {
		$tax                                                  = get_taxonomy( $option['filter_choices'] );
		$context['fields']['filter_options'][ $key ]['label'] = $tax->label;
		$context['fields']['filter_options'][ $key ]['name']  = $tax->name;

		$terms = get_terms( [
			                    'taxonomy'   => $option['filter_choices'],
			                    'hide_empty' => false, // Set to true if you want to exclude terms with no posts.
		                    ] );

		$context['fields']['filter_options'][ $key ]['tax_options'] = $terms;
	}

	$data_arr = wps_get_filter_posts( $post_type, 1 );

	$post_type                 = get_post_type_object( $context['fields']['post_type'] );
	$data_arr['postName']      = $post_type->labels->name;
	$data_arr['postType']      = $context['fields']['post_type'];
	$data_arr['restUrl']       = get_rest_url();
	$data_arr['filterOptions'] = $context['fields']['filter_options'];
	$data_arr['title']         = __( $context['fields']['title'], 'text-domain' );

	$data_arr['sample_available'] = $context['fields']['sample_available'];
	$data_arr['online_available'] = $context['fields']['online_available'];

	$context['data'] = json_encode( $data_arr );

	return $context;
} );

function juniper_excerpt_length( $length ) {
	return 20;
}

add_filter( 'excerpt_length', 'Blocks\Filter\juniper_excerpt_length', 999 );

function acf_load_post_type_field_choices( $field ) {
	// Reset choices
	$field['choices'] = get_post_types();

	return $field;

}

add_filter( 'acf/load_field/name=post_type', 'Blocks\Filter\acf_load_post_type_field_choices' );

function acf_load_taxonomy_field_choices( $field ) {
	// Reset choices
	$taxonomies = get_taxonomies( [], 'objects', 'and' );
	$choices    = [];
	foreach ( $taxonomies as $key => $value ) {
		$choices[ $key ] = $value->label;
	}

	$field['choices'] = $choices;

	return $field;
}

add_filter( 'acf/load_field/name=filter_choices', 'Blocks\Filter\acf_load_taxonomy_field_choices' );
//
//add_action( 'rest_api_init', function () {
//	register_rest_route( 'wps/v1', '/data', [
//		'methods'             => 'GET',
//		'callback'            => 'wps_filter_callback',
//		'permission_callback' => '__return_true',
//	] );}
//);

function wps_filter_callback() {
	$rawTaxonomies = ! empty( $_GET['taxonomies'] ) ? $_GET['taxonomies'] : "[]";

	// URL decode the parameter
	$decodedTaxonomies = urldecode( $rawTaxonomies );

	// Remove extra slashes
	$decodedTaxonomies = stripslashes( $decodedTaxonomies );
	// Decode the JSON string
	$decodedTaxonomies = json_decode( $decodedTaxonomies );

	$page      = ! empty( $_GET['page'] ) ? intval( $_GET['page'] ) : 1;
	$post_type = ! empty( $_GET['post_type'] ) ? $_GET['post_type'] : '';
	$search    = ! empty( $_GET['search'] ) ? $_GET['search'] : '';

	$posts = wps_get_filter_posts( $post_type, $page );

	return [];
//	return $posts;
}

function wps_get_filter_post_ids( $post_type ): array {
	global $wpdb;

	$translation_exsist = table_exists( 'icl_translations' );

	$current_language_code = apply_filters('wpml_current_language', null);

	if($translation_exsist) {
		$translation_post_type = 'post_' . $post_type;

		$query = "SELECT ID FROM $wpdb->posts WHERE 
                            ID IN (SELECT element_id FROM `wp_icl_translations` WHERE language_code = %s AND element_type = %s) 
                          AND ID NOT IN (SELECT object_id FROM wp_term_relationships WHERE term_taxonomy_id IN (SELECT term_taxonomy_id FROM wp_term_taxonomy WHERE term_id IN (SELECT term_id FROM wp_terms WHERE slug = 'exclude-from-catalog')))
                          AND post_status = 'publish'
                        ORDER BY post_date DESC";

		$replacements = [$current_language_code, $translation_post_type];

	} else {
		$query = "SELECT ID FROM $wpdb->posts WHERE post_type = %s AND ID NOT IN (SELECT object_id FROM wp_term_relationships WHERE term_taxonomy_id IN (SELECT term_taxonomy_id FROM wp_term_taxonomy WHERE term_id IN (SELECT term_id FROM wp_terms WHERE slug = 'exclude-from-catalog'))) AND post_status = 'publish' ORDER BY post_date DESC";

		$replacements = [$post_type];
	}

	$query = $wpdb->prepare($query, $replacements);

	error_log($query);

	return $wpdb->get_col($query);
}

/**
 * @param $table_name
 *
 * @return bool
 */
function table_exists( $table_name ): bool {
	global $wpdb;

	$existing_tables = $wpdb->get_col('SHOW TABLES');

	return in_array( $wpdb->prefix . $table_name, $existing_tables );
}

function wps_get_filter_posts( $post_type, $page ) {

	$currentLang   = apply_filters( 'wpml_current_language', null );
	$cachingToken  = 'wps_filter_cache_' . $currentLang;
	$cachedData    = get_transient( $cachingToken );
	$cacheDuration = HOUR_IN_SECONDS;

	if ( isset( $_GET['nocache'] ) && $_GET['nocache'] == 1 ) {
		delete_transient( $cachingToken );
		$cachedData = null;
	}

	if ( empty($cachedData) ) {
		$product_ids = wps_get_filter_post_ids($post_type );

		if ( !empty($product_ids) ) {
			// set transient for 1 hour

			set_transient( $cachingToken, $product_ids, $cacheDuration );
		}
	} else {
		// use the cached Data if available
		$product_ids = $cachedData;
	}

	$posts = array_map('get_post', $product_ids);

	$post_arr = array_map('Blocks\Filter\map_post_to_filter_post_obj', $posts);

	$data_arr['posts']       = $post_arr;

	return $data_arr;
}

/**
 * @param mixed $post
 *
 * @return stdClass
 */
function map_post_to_filter_post_obj( WP_Post $post ): stdClass {
	$fields = get_fields( $post->ID );

	$post_obj     = new stdClass();
	$post_obj->ID = $post->ID;
	//		$post_obj->fields               = get_fields(json_encode($post));
	$post_obj->excerpt           = htmlspecialchars( wp_trim_excerpt( '', $post ) );
	$post_obj->post_title        = htmlspecialchars( $post->post_title );
	$post_obj->post_name         = htmlspecialchars( $post->post_name );
	$post_obj->date              = htmlspecialchars( $post->post_date );
	$post_obj->featured_image    = htmlspecialchars( get_the_post_thumbnail_url( $post ) );
	$post_obj->link              = htmlspecialchars( get_permalink( $post ) );
	$post_obj->price             = (int) ( wc_get_product( $post->ID ) )->get_regular_price();
	$post_obj->subheadline       = htmlspecialchars( $fields['wps_sp_subheadline'] ?? '' );
	$post_obj->description_title = htmlspecialchars( $fields['wps_sp_description_title'] ?? '' );
	$post_obj->description_text  = htmlspecialchars( $fields['wps_sp_description_text'] ?? '' );
	//		$post_obj->features_text        = htmlspecialchars( $fields['wps_sp_features_text'] ?? '' );
	//		$post_obj->areas_of_application = htmlspecialchars( $fields['wps_sp_areas_of_application_text'] ?? '' );

	$taxonomies = get_post_taxonomies( $post );

	$taxonomy_data = [];
	foreach ( $taxonomies as $taxonomy ) {
		$terms = get_the_terms( $post, $taxonomy );
		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$taxonomy_data[ $taxonomy ][] = [
					'term_id' => $term->term_id,
					'name'    => $term->name,
					'slug'    => $term->slug
				];
			}
		}
	}

	$post_obj->taxonomies = $taxonomy_data;

	$terms        = get_the_terms( $post, 'product_type' );
	$product_type = $terms && ! is_wp_error( $terms )
		? $terms[0]->name
		: '';

	$post_obj->product_type = $product_type;

	$rendered_card = do_shortcode( "[wps_get_product_card product_id='{$post->ID}' encoding='ISO-8859-1']" );

	$encoded_html = base64_encode( $rendered_card );

	$post_obj->html = $encoded_html;

	return $post_obj;
}

function display_woocommerce_notices_on_add_to_cart( $content ) {
	// Prepend your custom paragraph

	// Ensure WooCommerce notices are displayed on the next page load
	if ( isset( $_GET['add-to-cart'] ) && is_numeric( $_GET['add-to-cart'] ) ) {
		$woocommerce_message = wc_print_notices();
		// Combine the custom paragraph with the original content
		$new_content = $woocommerce_message . $content;

		return $new_content;
	} else {
		return $content;
	}

}

add_filter( 'the_content', 'Blocks\Filter\display_woocommerce_notices_on_add_to_cart' );


