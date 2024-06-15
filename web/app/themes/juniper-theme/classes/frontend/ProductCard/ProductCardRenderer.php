<?php

namespace frontend\ProductCard;

use WC_Product;

class ProductCardRenderer extends CardRenderer {

	public static function generate( $reference, $encoding ): ProductCardRenderer {
		$product = wc_get_product( $reference );

		// product_title
		$product_title = self::get_product_title( $product, $encoding );
		$terms_string  = self::get_rendered_terms_string( $product );

		// is new
		$is_new = self::is_new( $product );

		$perma_link      = self::get_permalink( $product );
		$rendered_images = self::get_rendered_images( $product );

		// render prices
		$is_on_sale = self::is_on_sale( $product );
		$price_html = self::get_price_rendered( $product );

		// add_to_cart link
		$add_to_cart_link = self::get_add_to_cart_url( $product );

		return new ProductCardRenderer(
			$product_title,
			$is_new,
			$perma_link,
			$rendered_images,
			$terms_string,
			$price_html,
			$is_on_sale,
			$add_to_cart_link,
			[]
		);
	}

	/**
	 * @param WC_Product $product
	 * @param string $encoding
	 *
	 * @return string
	 */
	private static function get_product_title( WC_Product $product, string $encoding = '' ): string {
		return $encoding
			? mb_convert_encoding( $product->get_title(), $encoding )
			: $product->get_title();
	}

	/**
	 * @param WC_Product $product
	 *
	 * @return string
	 */
	private static function get_rendered_terms_string( WC_Product $product ): string {
		// terms string for display
		$metals_terms = wp_get_post_terms( $product->get_id(), 'metals-and-accessories', [ 'fields' => 'names' ] );
		// $color_terms = wp_get_post_terms($product_id, 'color', array('fields' => 'names'));
		$category_terms = wp_get_post_terms( $product->get_id(), 'product_cat', [ 'fields' => 'names' ] );
		// $application_terms = wp_get_post_terms($product_id, 'application', array('fields' => 'names'));

		$terms = array_merge( $metals_terms, $category_terms );

		// Check if there are any terms and not an error
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			// Implode the names array to a single string separated by "|"
			// $terms_string = ;
			$terms_string = htmlspecialchars( implode( ' | ', $terms ), ENT_QUOTES, 'UTF-8' );
		} else {
			$terms_string = '';
		}

		return $terms_string;
	}

	/**
	 * @param WC_Product $product
	 *
	 * @return bool
	 */
	private static function is_new( WC_Product $product ): bool {
		$label_new = get_field( 'label-new', $product->get_id() );

		return ! empty( $label_new );
	}

	/**
	 * @param WC_Product $product
	 *
	 * @return string
	 */
	private static function get_permalink( WC_Product $product ): string {
		return $product->get_permalink();
	}

	/**
	 * @param WC_Product $product
	 *
	 * @return string
	 */
	private static function get_rendered_images( WC_Product $product ): string {
		// render links
		$attachment_ids    = $product->get_gallery_image_ids();
		$post_thumbnail_id = $product->get_image_id();

		array_unshift( $attachment_ids, $post_thumbnail_id );

		return self::render_multiple_attachment_images( $attachment_ids );
	}

	/**
	 * @param WC_Product $product
	 *
	 * @return bool
	 */
	private static function is_on_sale( WC_Product $product ): bool {
		return $product->is_on_sale();
	}

	/**
	 * @param WC_Product $product
	 *
	 * @return string
	 */
	private static function get_price_rendered( WC_Product $product ): string {
		return $product->get_price_html();
	}

	/**
	 * @param WC_Product $product
	 *
	 * @return string
	 */
	private static function get_add_to_cart_url( WC_Product $product ): string {
		return $product->add_to_cart_url();
	}

	private static function render_single_attachment_image( int $attachment_id ): string {
		$attachment_image = wp_get_attachment_image( $attachment_id, 'large', false, [ 'class' => 'h-full w-fill object-cover' ] );

		return "<div class='h-full'>$attachment_image</div>";
	}

	private static function render_multiple_attachment_images( array $attachment_image_ids ): string {
		$rendered_images = array_map( [ self::class, 'render_single_attachment_image' ], $attachment_image_ids );

		return implode( ' ', $rendered_images );
	}
}
