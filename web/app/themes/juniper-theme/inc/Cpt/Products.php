<?php
/**
 * LimeSoda MetalPrices
 * Handles Metal Prices in Backend
 *
 * @package IWGPlating
 * @author LimeSoda
 * @copyright Copyright (c) 2020, LimeSoda
 * @link https://limesoda.com/
 */

namespace Limesoda\Astra_Child\CustomPostTypes\LS_Products;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

/**
 * LS_Products
 */
if (!class_exists('LS_Products')) {
	/**
	 * LS_Products
	 */
	class LS_Products {
		/**
		 * Set class instance
		 *
		 * @var $instance
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @return object initialized object of class.
		 */
		public static function get_instance() {
			if (!isset(self::$instance)) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 */
		private function __construct() {
			add_action('acf/init', [$this, 'register_custom_fields']);
			add_action('init', [$this, 'register_custom_post_type']);
		}

		/**
		 * Register custom post type & taxonomies for Products
		 *
		 * @return void
		 */
		public function register_custom_post_type() {
			$labels = [
				'name' => __('Products', 'iwgplating'),
				'singular_name' => __('Product', 'iwgplating'),
				'menu_name' => __('Products', 'iwgplating'),
				'all_items' => __('All Products', 'iwgplating'),
				'add_new' => __('Add new', 'iwgplating'),
				'add_new_item' => __('Add new Product', 'iwgplating'),
				'edit_item' => __('Edit Product', 'iwgplating'),
				'new_item' => __('Add Product', 'iwgplating'),
				'view_item' => __('Show Product', 'iwgplating'),
				'view_items' => __('Show Products', 'iwgplating'),
				'search_items' => __('Search Products', 'iwgplating'),
				'not_found' => __('No Products found', 'iwgplating'),
				'not_found_in_trash' => __('No Products found in trash', 'iwgplating'),
				'archives' => __('Products archives', 'iwgplating'),
				'insert_into_item' => __('Insert into Products', 'iwgplating'),
				'uploaded_to_this_item' => __('Upload to these Products', 'iwgplating'),
				'filter_items_list' => __('Filter Products list', 'iwgplating'),
				'items_list_navigation' => __('Products list navigation', 'iwgplating'),
				'items_list' => __('Products list', 'iwgplating'),
				'attributes' => __('Products attributes', 'iwgplating'),
				'name_admin_bar' => __('Products', 'iwgplating'),
				'item_published' => __('Products published', 'iwgplating'),
				'item_published_privately' => __('Products published privately.', 'iwgplating'),
				'item_reverted_to_draft' => __('Products reverted to draft.', 'iwgplating'),
				'item_scheduled' => __('Products scheduled', 'iwgplating'),
				'item_updated' => __('Products updated.', 'iwgplating'),
			];

			$cpt_args = [
				'label' => __('Products', 'iwgplating'),
				'labels' => $labels,
				'description' => '',
				'public' => true,
				'publicly_queryable' => true,
				'show_ui' => true,
				'show_in_rest' => true,
				'rest_base' => '',
				'rest_controller_class' => 'WP_REST_Posts_Controller',
				'has_archive' => false,
				'show_in_menu' => true,
				'show_in_nav_menus' => true,
				'delete_with_user' => false,
				'exclude_from_search' => false,
				'capability_type' => 'post',
				'map_meta_cap' => true,
				'hierarchical' => false,
				'can_export' => false,
				'taxonomies' => ['colors', 'product_categories', 'usages', 'metal_and_accessories', 'applications'],
				'rewrite' => false,
				'query_var' => true,
				'menu_position' => 6,
				'menu_icon' => 'dashicons-star-empty',
				'show_in_graphql' => false,
				'supports' => ['title', 'editor', 'author', 'thumbnail', 'revisions'],
			];
			register_post_type('products', $cpt_args);

			$taxonomy_args = [
				'query_var' => true,
				'show_ui' => true,
				'show_in_rest' => true,
				'show_in_menu' => true,
				'show_in_nav_menus' => true,
				'show_admin_column' => true,
				'show_in_quick_edit' => true,
				'has_archive' => false,
			];

			// Register 'Product Category' taxonomy.
			$product_categories_args = array_merge(
				[
					'hierarchical' => true,
					'labels' => [
						'name' => __('Product Categories', 'iwgplating'),
						'singular_name' => __('Product Category', 'iwgplating'),
						'menu_name' => __('Product Categories', 'iwgplating'),
					],
					'rewrite' => ['slug' => 'product_categories'],
				],
				$taxonomy_args
			);

			register_taxonomy(
				'product-categories',
				'products',
				$product_categories_args,
			);

			// Register 'Usage' taxonomy.
			$usages_args = array_merge(
				[
					'hierarchical' => true,
					'labels' => [
						'name' => __('Usages', 'iwgplating'),
						'singular_name' => __('Usage', 'iwgplating'),
						'menu_name' => __('Usages', 'iwgplating'),
					],
					'rewrite' => ['slug' => 'usages'],
				],
				$taxonomy_args
			);

			register_taxonomy(
				'usages',
				'products',
				$usages_args,
			);

			// Register 'Metal & Accessories' taxonomy.
			$metal_and_accessories_args = array_merge(
				[
					'hierarchical' => true,
					'labels' => [
						'name' => __('Metal & Accessories', 'iwgplating'),
						'singular_name' => __('Metal & Accessory', 'iwgplating'),
						'menu_name' => __('Metal & Accessories', 'iwgplating'),
					],
					'rewrite' => ['slug' => 'metal_and_accessories'],
				],
				$taxonomy_args
			);

			register_taxonomy(
				'metal_and_accessories',
				'products',
				$metal_and_accessories_args,
			);

			// Register 'Colors' taxonomy.
			$colors_args = array_merge(
				[
					'hierarchical' => true,
					'labels' => [
						'name' => __('Colors', 'iwgplating'),
						'singular_name' => __('Color', 'iwgplating'),
						'menu_name' => __('Colors', 'iwgplating'),
					],
					'rewrite' => ['slug' => 'colors'],
				],
				$taxonomy_args
			);

			register_taxonomy(
				'colors',
				'products',
				$colors_args,
			);

			// Register 'Applications' taxonomy.
			$applications_args = array_merge(
				[
					'hierarchical' => true,
					'labels' => [
						'name' => __('Applications', 'iwgplating'),
						'singular_name' => __('Application', 'iwgplating'),
						'menu_name' => __('Applications', 'iwgplating'),
					],
					'rewrite' => ['slug' => 'applications'],
				],
				$taxonomy_args
			);

			register_taxonomy(
				'applications',
				'products',
				$applications_args,
			);
		}

		/**
		 * Add and register custom fields for "Metal Prices"
		 *
		 * @return void
		 */
		public function register_custom_fields() {
			if (function_exists('acf_add_local_field_group')) {
				acf_add_local_field_group([
					'key' => 'products_custom_fields',
					'title' => 'Product Details',
					'fields' => [
						[
							'key' => 'field_product_new',
							'label' => __('NEW', 'iwgplating'),
							'name' => 'product_new',
							'type' => 'true_false',
							'default_value' => 1,
							'ui' => 1,
							'ui_on_text' => '',
							'ui_off_text' => '',
							'wrapper' => [
								'width' => '50',
								'class' => '',
								'id' => '',
							],
						],
						[
							'key' => 'field_product-sample-button',
							'label' => __('Is product sample available?', 'iwgplating'),
							'name' => 'product_samplebutton',
							'type' => 'true_false',
							'default_value' => 1,
							'ui' => 1,
							'ui_on_text' => '',
							'ui_off_text' => '',
							'wrapper' => [
								'width' => '50',
								'class' => '',
								'id' => '',
							],
						],
						[
							'key' => 'field_product_description',
							'label' => __('Product Description', 'iwgplating'),
							'name' => 'product_description',
							'type' => 'wysiwyg',
							'tabs' => 'all',
							'toolbar' => 'full',
							'media_upload' => 1,
							'delay' => 0,
						],
						[
							'key' => 'field_properties_and_benefits',
							'label' => __('Properties & Benefits', 'iwgplating'),
							'name' => 'properties_and_benefits',
							'type' => 'wysiwyg',
							'tabs' => 'all',
							'toolbar' => 'full',
							'media_upload' => 1,
							'delay' => 0,
						],
						[
							'key' => 'field_area_of_application',
							'label' => __('Area of Application', 'iwgplating'),
							'name' => 'area_of_application',
							'type' => 'wysiwyg',
							'tabs' => 'all',
							'toolbar' => 'full',
							'media_upload' => 1,
							'delay' => 0,
						],
						[
							'key' => 'field_downloads',
							'label' => __('Downloads', 'iwgplating'),
							'name' => 'downloads',
							'type' => 'repeater',
							'layout' => 'table',
							'sub_fields' => [
								[
									'key' => 'field_file_upload',
									'label' => __('File Upload', 'iwgplating'),
									'name' => 'file_upload',
									'type' => 'file',
									'return_format' => 'array',
									'library' => 'all',
								],
							],
						],
						[
							'key' => 'field_Gallery',
							'label' => __('Gallery', 'iwgplating'),
							'name' => 'gallery',
							'type' => 'gallery',
							'return_format' => 'array',
							'preview_size' => 'medium',
							'insert' => 'append',
							'library' => 'all',
						],
					],
					'location' => [
						[
							[
								'param' => 'post_type',
								'operator' => '==',
								'value' => 'products',
							],
						],
					],
					'menu_order' => 0,
					'position' => 'normal',
					'style' => 'default',
					'label_placement' => 'top',
					'instruction_placement' => 'label',
					'hide_on_screen' => '',
					'active' => true,
					'description' => '',
					'show_in_rest' => 0,
				]);
				acf_add_local_field_group([
					'key' => 'group_628602ad7f2ad',
					'title' => 'Farbe',
					'fields' => [
						[
							'key' => 'field_628602eb01060',
							'label' => 'Farbe',
							'name' => 'tax_color',
							'type' => 'color_picker',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => [
								'width' => '',
								'class' => '',
								'id' => '',
							],
							'default_value' => '',
							'enable_opacity' => 0,
							'return_format' => 'string',
						],
					],
					'location' => [
						[
							[
								'param' => 'taxonomy',
								'operator' => '==',
								'value' => 'product-categories',
							],
						],
					],
					'menu_order' => 0,
					'position' => 'normal',
					'style' => 'default',
					'label_placement' => 'top',
					'instruction_placement' => 'label',
					'hide_on_screen' => '',
					'active' => true,
					'description' => '',
					'show_in_rest' => 0,
				]);
			}
		}
	}
}

LS_Products::get_instance();
