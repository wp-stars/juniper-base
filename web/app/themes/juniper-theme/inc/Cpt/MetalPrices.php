<?php
/**
 * MetalPrices
 * Handles MetalPrices in Backend
 *
 * @package IWGPlating
 * @author WP-Stars
 * @copyright Copyright (c) 2024, WP-Stars
 * @link https://wp-stars.com/
 */


namespace Juniper\Cpt;

class MetalPrices
{
    public string $cpt_slug;
    public string $cpt_name;

    public function __construct()
    {
        $this->cpt_slug = substr('metalprices', 0, 20);
        $this->cpt_name = substr('Metal Prices', 0, 20);

        add_action('acf/init', [$this, 'register_custom_fields']);
        add_action('init', [$this, 'register_custom_post_type']);
        add_filter('wp_insert_post_data', [$this, 'auto_generate_post_title'], 10, 2);
    }

    /**
     * Generate Post Title of CPT
     *
     * @param array $data post data.
     * @param array $postarr post array.
     * @return mixed|string new title
     */
    public function auto_generate_post_title($data, $postarr) {
        if (isset($postarr['ID']) && $data['post_type'] === 'metalprices') {
            $date = new \DateTime($data['post_date']);
            $data['post_title'] = wp_date('d. F Y', $date->getTimestamp());
        }
        return $data;
    }

    /**
     * Register custom post type for Delivery Areas
     *
     * @return void
     */
    public function register_custom_post_type() {
        $labels = [
            'name' => __('Metal Prices', 'iwgplating'),
            'singular_name' => __('Metal Prices', 'iwgplating'),
            'menu_name' => __('Metal Prices', 'iwgplating'),
            'all_items' => __('All Metal Prices', 'iwgplating'),
            'add_new' => __('Add new', 'iwgplating'),
            'add_new_item' => __('Add new Metal Prices', 'iwgplating'),
            'edit_item' => __('Edit Metal Price', 'iwgplating'),
            'new_item' => __('Add Metal Prices', 'iwgplating'),
            'view_item' => __('Show Metal Price', 'iwgplating'),
            'view_items' => __('Show Metal Prices', 'iwgplating'),
            'search_items' => __('Search Metal Prices', 'iwgplating'),
            'not_found' => __('No Metal Prices found', 'iwgplating'),
            'not_found_in_trash' => __('No Metal Prices found in trash', 'iwgplating'),
            'archives' => __('Metal Price archives', 'iwgplating'),
            'insert_into_item' => __('Insert into Metal Prices', 'iwgplating'),
            'uploaded_to_this_item' => __('Upload to these Metal Prices', 'iwgplating'),
            'filter_items_list' => __('Filter Metal Prices list', 'iwgplating'),
            'items_list_navigation' => __('Metal Prices list navigation', 'iwgplating'),
            'items_list' => __('Metal Prices list', 'iwgplating'),
            'attributes' => __('Metal Prices attributes', 'iwgplating'),
            'name_admin_bar' => __('Metal Prices', 'iwgplating'),
            'item_published' => __('Metal Prices published', 'iwgplating'),
            'item_published_privately' => __('Metal Prices published privately.', 'iwgplating'),
            'item_reverted_to_draft' => __('Metal Prices reverted to draft.', 'iwgplating'),
            'item_scheduled' => __('Metal Prices scheduled', 'iwgplating'),
            'item_updated' => __('Metal Prices updated.', 'iwgplating'),
        ];

        $cpt_args = [
            'label' => __('Metal Prices', 'iwgplating'),
            'labels' => $labels,
            'description' => '',
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_rest' => true,
            'rest_base' => 'metalprices',
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
            'rewrite' => false,
            'query_var' => true,
            'menu_position' => 5,
            'menu_icon' => 'dashicons-money-alt',
            'show_in_graphql' => false,
            'supports' => [],
        ];
        register_post_type('metalprices', $cpt_args);
    }

    /**
     * Add and register custom fields for "Metal Prices"
     *
     * @return void
     */
    public function register_custom_fields() {
        if (function_exists('acf_add_local_field_group')) {
            acf_add_local_field_group([
                'key' => 'metalprices_custom_fields',
                'title' => 'Metal Prices',
                'fields' => [
                    [
                        'key' => 'field_metal_gold',
                        'label' => __('Gold', 'iwgplating'),
                        'name' => 'metalprice_gold',
                        'type' => 'number',
                        'required' => 0,
                        'prepend' => '€/g',
                    ],
                    [
                        'key' => 'field_metal_silver',
                        'label' => __('Silver', 'iwgplating'),
                        'name' => 'metalprice_silver',
                        'type' => 'number',
                        'required' => 0,
                        'prepend' => '€/kg',
                    ],
                    [
                        'key' => 'field_metal_platin',
                        'label' => __('Platin', 'iwgplating'),
                        'name' => 'metalprice_platin',
                        'type' => 'number',
                        'required' => 0,
                        'prepend' => '€/g',
                    ],
                    [
                        'key' => 'field_metal_palladium',
                        'label' => __('Palladium', 'iwgplating'),
                        'name' => 'metalprice_palladium',
                        'type' => 'number',
                        'required' => 0,
                        'prepend' => '€/g',
                    ],
                    [
                        'key' => 'field_metal_rhodium',
                        'label' => __('Rhodium', 'iwgplating'),
                        'name' => 'metalprice_rhodium',
                        'type' => 'number',
                        'required' => 0,
                        'prepend' => '€/g',
                    ],
                    [
                        'key' => 'field_metal_ruthenium',
                        'label' => __('Ruthenium', 'iwgplating'),
                        'name' => 'metalprice_ruthenium',
                        'type' => 'number',
                        'required' => 0,
                        'prepend' => '€/g',
                    ],
                ],
                'location' => [
                    [
                        [
                            'param' => 'post_type',
                            'operator' => '==',
                            'value' => 'metalprices',
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
                'show_in_rest' => true,
            ]);
        }
    }
}
