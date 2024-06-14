<?php

namespace Blocks\Filter;

require_once __DIR__ . '/prebuildCache/PrebuildCache.php';

use blocks\filter\prebuildCache\PrebuildCache;
use stdClass;
use WP_Post;

add_action(
	'wp_enqueue_scripts', function () {
	if ( ! has_block( 'acf/filter' ) ) {
		return;
	}

	$time       = time();
	$theme_path = get_template_directory_uri();

	wp_enqueue_style( 'filter-css', $theme_path . '/blocks/filter/style.css', [], $time, 'all' );
	wp_enqueue_script( 'filter-js', $theme_path . '/blocks/filter/script.js', [], $time, true );

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

		// INFO: when removing 'hide_empty' as a argument, then the return turns into an object
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

add_action( 'wp_ajax_nopriv_getPostFilter', 'Blocks\Filter\wps_filter_callback' );
add_action( 'wp_ajax_getPostFilter', 'Blocks\Filter\wps_filter_callback' );
function wps_filter_callback(): void {
	$page      = $_GET['page'] ? intval( $_GET['page'] ) : 1;
	$post_type = $_GET['post_type'] ?: '';

	$post_data = wps_get_filter_posts( $post_type, $page );

	wp_send_json_success( $post_data, 200 );
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

function wps_get_filter_posts( $post_type, $page = 0, $per_page = 6 ): array {

	$currentLang   = apply_filters( 'wpml_current_language', null );
	$cachingToken  = 'wps_filter_cache_' . $currentLang;
	$cachedData    = get_transient( $cachingToken );
	$cacheDuration = HOUR_IN_SECONDS;

	if ( isset( $_GET['nocache'] ) && $_GET['nocache'] == 1 ) {
		delete_transient( $cachingToken );
		$cachedData = null;
	}

	if ( empty( $cachedData ) ) {
		$product_ids = wps_get_filter_post_ids( $post_type );

		if ( ! empty( $product_ids ) ) {
			// set transient for 1 hour

			set_transient( $cachingToken, $product_ids, $cacheDuration );
		}
	} else {
		// use the cached Data if available
		$product_ids = $cachedData;
	}

	//	$products_to_load = array_splice( $product_ids, $page * $per_page, $per_page);

	$post_arr = array_map( 'Blocks\Filter\map_post_to_filter_post_obj', $product_ids );

	return array_filter( $post_arr );
}

/**
 * @param mixed $post
 *
 * @return stdClass
 */
function map_post_to_filter_post_obj( $post_id, $reload_cache = false ): stdClass {
	try {
		return PrebuildCache::get_instance()->get_prebuild( $post_id, $reload_cache );
	} catch ( \Exception $e ) {
		return json_decode( PrebuildCache::get_instance()->generate_prebuild_json( $post_id ) );
	}
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


