<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_GZDP_Theme_Shopkeeper extends WC_GZDP_Theme {

	public function __construct( $template ) {
		parent::__construct( $template );

		add_filter( 'woocommerce_gzd_shopmark_single_product_filters', array( $this, 'single_product_filters' ), 10 );
		add_filter( 'woocommerce_gzd_shopmark_single_product_defaults', array( $this, 'single_product_defaults' ), 10 );

		add_action( 'woocommerce_single_product_germanized_info', array( $this, 'remove_theme_hooks' ), 0 );
	}

	public function remove_theme_hooks() {
		remove_all_actions( 'woocommerce_single_product_germanized_info' );
	}

	public function has_custom_shopmarks() {
		return true;
	}

	public function single_product_defaults( $defaults ) {
		$count = 12;

		foreach ( $defaults as $type => $type_data ) {
			$defaults[ $type ]['default_filter']   = 'woocommerce_single_product_summary_single_price';
			$defaults[ $type ]['default_priority'] = $count++;
		}

		return $defaults;
	}

	public function single_product_filters( $filters ) {
		$filters['woocommerce_single_product_summary_single_price'] = array(
			'title'            => __( 'Shopkeeper - Price', 'woocommerce-germanized-pro' ),
			'number_of_params' => 1,
			'is_action'        => true,
		);

		$filters['woocommerce_single_product_summary_single_title'] = array(
			'title'            => __( 'Shopkeeper - Title', 'woocommerce-germanized-pro' ),
			'number_of_params' => 1,
			'is_action'        => true,
		);

		$filters['woocommerce_single_product_summary_single_excerpt'] = array(
			'title'            => __( 'Shopkeeper - Excerpt', 'woocommerce-germanized-pro' ),
			'number_of_params' => 1,
			'is_action'        => true,
		);

		return $filters;
	}
}
