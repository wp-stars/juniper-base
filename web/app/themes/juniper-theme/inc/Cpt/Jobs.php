<?php

/**
 * Jobs
 * Handles Jobs in Backend
 *
 * @package IWGPlating
 * @author WP-Stars
 * @copyright Copyright (c) 2024, WP-Stars
 * @link https://wp-stars.com/
 */


namespace Juniper\Cpt;

class Jobs
{
    public string $cpt_slug;
    public string $cpt_name;

    public function __construct()
    {
        $this->cpt_slug = substr('jobs', 0, 20);
        $this->cpt_name = substr('Jobs', 0, 20);

        add_action('init', array($this, 'register_custom_cpt'));
        add_action('acf/init', [$this, 'register_custom_fields']);
    }

    /**
     * Register custom post type & taxonomies for Jobs
     *
     * @return void
     */
    public function register_custom_cpt() {
        $labels = [
            'name' => __('Jobs', 'iwgplating'),
            'singular_name' => __('Job', 'iwgplating'),
            'menu_name' => __('Jobs', 'iwgplating'),
            'all_items' => __('All Jobs', 'iwgplating'),
            'add_new' => __('Add new', 'iwgplating'),
            'add_new_item' => __('Add new Job', 'iwgplating'),
            'edit_item' => __('Edit Job', 'iwgplating'),
            'new_item' => __('Add Job', 'iwgplating'),
            'view_item' => __('Show Job', 'iwgplating'),
            'view_items' => __('Show Jobs', 'iwgplating'),
            'search_items' => __('Search Jobs', 'iwgplating'),
            'not_found' => __('No Jobs found', 'iwgplating'),
            'not_found_in_trash' => __('No Jobs found in trash', 'iwgplating'),
            'archives' => __('Jobs archives', 'iwgplating'),
            'insert_into_item' => __('Insert into Jobs', 'iwgplating'),
            'uploaded_to_this_item' => __('Upload to these Jobs', 'iwgplating'),
            'filter_items_list' => __('Filter Jobs list', 'iwgplating'),
            'items_list_navigation' => __('Jobs list navigation', 'iwgplating'),
            'items_list' => __('Jobs list', 'iwgplating'),
            'attributes' => __('Jobs attributes', 'iwgplating'),
            'name_admin_bar' => __('Jobs', 'iwgplating'),
            'item_published' => __('Jobs published', 'iwgplating'),
            'item_published_privately' => __('Jobs published privately.', 'iwgplating'),
            'item_reverted_to_draft' => __('Jobs reverted to draft.', 'iwgplating'),
            'item_scheduled' => __('Jobs scheduled', 'iwgplating'),
            'item_updated' => __('Jobs updated.', 'iwgplating'),
        ];

        $cpt_args = [
            'label' => __('Jobs', 'iwgplating'),
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
            'taxonomies' => ['job_categories'],
            'rewrite' => false,
            'query_var' => true,
            'menu_position' => 6,
            'menu_icon' => 'dashicons-businessperson',
            'show_in_graphql' => false,
            'supports' => ['title', 'category', 'author', 'revisions'],
        ];
        register_post_type('jobs', $cpt_args);

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

        // Register 'Job Category' taxonomy.
        $job_categories_args = array_merge(
            [
                'hierarchical' => true,
                'labels' => [
                    'name' => __('Job Categories', 'iwgplating'),
                    'singular_name' => __('Job Category', 'iwgplating'),
                    'menu_name' => __('Job Categories', 'iwgplating'),
                ],
                'rewrite' => ['slug' => 'job_categories'],
            ],
            $taxonomy_args
        );

        register_taxonomy(
            'job-categories',
            'jobs',
            $job_categories_args,
        );
    }

    /**
     * Add and register custom fields for "Jobs"
     *
     * @return void
     */
    public function register_custom_fields() {
        if (function_exists('acf_add_local_field_group')) {
            acf_add_local_field_group([
                'key' => 'jobs_custom_fields',
                'title' => 'Job Details',
                'fields' => [
                    [
                        'key' => 'field_job_destination',
                        'label' => __('City, state, country', 'iwgplating'),
                        'name' => 'job_destination',
                        'type' => 'text',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => [
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ],
                        'default_value' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                        'maxlength' => '',
                        'translations' => 'translate',
                    ],
                    [
                        'key' => 'field_employment_type',
                        'label' => __('Employment Type', 'iwgplating'),
                        'name' => 'job_employment_type',
                        'type' => 'text',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => [
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ],
                        'default_value' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                        'maxlength' => '',
                        'translations' => 'translate',
                    ],
                    [
                        'key' => 'field_job_description',
                        'label' => __('Job Description', 'iwgplating'),
                        'name' => 'job_description',
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
                        'key' => 'field_email',
                        'label' => __('E-Mail Adress (optional)', 'iwgplating'),
                        'name' => 'e-mail',
                        'type' => 'email',
                        'instructions' => 'Standardmäßig ist die E-Mail-Adresse " office@iwgplating.at" beim "Jetzt bewerben" Button hinterlegt. Wird hier eine E-Mail Adresse hinterlegt, wird diese verwendet.',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => [
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ],
                        'default_value' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                        'translations' => 'copy_once',
                    ],
                ],
                'location' => [
                    [
                        [
                            'param' => 'post_type',
                            'operator' => '==',
                            'value' => 'jobs',
                        ],
                    ],
                ],
                'menu_order' => 0,
                'position' => 'normal',
                'style' => 'default',
                'label_placement' => 'top',
                'instruction_placement' => 'label',
                'hide_on_screen' => [
                    0 => 'the_content',
                ],
                'active' => true,
                'description' => '',
                'show_in_rest' => 0,
            ]);
        }
    }
}

