<?php

namespace Juniper\Cpt;

class Projects {
	public string $cpt_slug;
	public string $cpt_name;

	public function __construct() {
		$this->cpt_slug = substr( 'projects', 0, 20 );
		$this->cpt_name = substr( 'Projects', 0, 20 );

		add_action( 'init', array( $this, 'register_taxonomy' ) );
		add_action( 'init', array( $this, 'register_custom_cpt' ) );
		add_action( 'rest_api_init',  array( $this, 'create_api_projects_acf_fields') );


	}

	public function register_custom_cpt() {
		register_post_type(
			$this->cpt_slug,
			array(
				'labels'      => array(
					'name'          => __( $this->cpt_name ),
					'singular_name' => __( $this->cpt_name, ),
				),
				'public'      => true,
				'has_archive' => false,
				'show_in_rest' => true,
				'rewrite'     => array( 
					'slug' => 'projekte', 
					'with_front' => false 
				),
			)
		);
	}

	public function register_taxonomy() {
		$labels = array(
			'name'              => _x( 'Project Categories', 'taxonomy general name' ),
			'singular_name'     => _x( 'Project Category', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Project Categories' ),
			'all_items'         => __( 'All Project Categories' ),
			'parent_item'       => __( 'Parent Project Categories' ),
			'parent_item_colon' => __( 'Parent Project Categories:' ),
			'edit_item'         => __( 'Edit Project Category' ),
			'update_item'       => __( 'Update Project Category' ),
			'add_new_item'      => __( 'Add New Project Category' ),
			'new_item_name'     => __( 'New Project Category Name' ),
			'menu_name'         => __( 'Project Category' ),
		);
		$args   = array(
			'hierarchical'      => true, // make it hierarchical (like categories)
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'show_in_rest' 		=> true,
			'rewrite'           => [ 'slug' => 'project_category' ],
		);
		register_taxonomy( 'project_category', [ 'projects' ], $args );
	}

	public function create_api_projects_acf_fields() {

		register_rest_field( 'data', 'fields', array(
				'get_callback'    	  => 'get_post_meta_for_api',
				'schema'              => null,
			)
		);
	}

	function get_fields_for_api( $object ) {
		//get the id of the post object array
		$post_id = $object['id'];

		//return the post meta
		return get_fields( $post_id );
	}

}
