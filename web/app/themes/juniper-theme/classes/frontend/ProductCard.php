<?php


namespace frontend;

require_once __DIR__ . '/ProductCardData.php';

class ProductCard {

	public function __construct() {
		add_shortcode( 'wps_get_product_card', [ $this, 'product_card_html' ] );

		//        header('Content-Type: text/html; charset=utf-8');
	}



	public function product_card_html( $atts ): string {
		$product_id = $atts['product_id'];

		$product_card = ProductCardData::generate_on_product_id( $product_id, $atts['encoding']);

		return $product_card->render();
	}

}


