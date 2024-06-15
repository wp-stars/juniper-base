<?php

namespace frontend;

use frontend\ProductCard\MockedProductCardRenderer;
use frontend\ProductCard\ProductCardRenderer;

require_once __DIR__ . '/ProductCard/CardRenderer.php';
require_once __DIR__ . '/ProductCard/ProductCardRenderer.php';
require_once __DIR__ . '/ProductCard/MockedProductCardRenderer.php';

class ProductCard {

	public function __construct() {
		add_shortcode( 'wps_get_product_card', [ $this, 'product_card_html' ] );

		//        header('Content-Type: text/html; charset=utf-8');
	}



	public function product_card_html( $atts ): string {
		$product_id = $atts['product_id'];

		$product_card = ProductCardRenderer::generate( $product_id, $atts['encoding']);

		return $product_card->render();
	}

	public function product_card_mock_html(): string {
		$mock_product_card = MockedProductCardRenderer::generate( null, '');

		return $mock_product_card->render();
	}

}


