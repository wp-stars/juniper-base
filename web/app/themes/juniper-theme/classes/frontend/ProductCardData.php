<?php

namespace frontend;

use WC_Product;

class ProductCardData {
	private string $product_title;
	private bool $is_new;
	private string $product_perma_link;
	private string $rendered_images;
	private string $rendered_terms;
	private string $price_html;
	private string $is_on_sale;
	private string $add_to_cart_link;

	/**
	 * @param string $product_title
	 * @param bool $is_new
	 * @param string $product_perma_link
	 * @param string $rendered_images
	 * @param string $rendered_terms
	 * @param string $price_html
	 * @param string $is_on_sale
	 * @param string $add_to_cart_link
	 */
	private function __construct( string $product_title, bool $is_new, string $product_perma_link, string $rendered_images, string $rendered_terms, string $price_html, string $is_on_sale, string $add_to_cart_link ) {
		$this->product_title      = $product_title;
		$this->is_new             = $is_new;
		$this->product_perma_link = $product_perma_link;
		$this->rendered_images    = $rendered_images;
		$this->rendered_terms     = $rendered_terms;
		$this->price_html         = $price_html;
		$this->is_on_sale         = $is_on_sale;
		$this->add_to_cart_link   = $add_to_cart_link;
	}

	public static function generate_on_product_id( $product_id, $encoding ): ProductCardData {
		$product = wc_get_product( $product_id );
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

		return new ProductCardData(
			$product_title,
			$is_new,
			$perma_link,
			$rendered_images,
			$terms_string,
			$price_html,
			$is_on_sale,
			$add_to_cart_link
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

	public function render(): string {

		$addition_to_cart_icon_id          = apply_filters( 'wps_get_attachment_id_with_name_like', 'Warenkorb Animation White' );
		$addition_to_cart_lottie_json_link = wp_get_attachment_url( $addition_to_cart_icon_id );

		ob_start();
		?>
		<div
			class="overflow-hidden isolate shadow-lg relative product-card h-full pb-5 border border-solid border-[#DCDDDE] col-span-6 sm:col-span-3 md:col-span-2 flex flex-col">
			<?php
			if ( $this->is_new ) { ?>
				<span class="absolute top-0 right-0 px-3 py-2 bg-accent z-10"><?= __( 'New', 'wps-juniper' ); ?></span>
			<?php } ?>

			<div class="stretch-this">
				<a href="<?= $this->product_perma_link ?>">
					<div class="product-gallery slick-slider product-card-slider h-[21rem]">
						<?= $this->rendered_images ?>
					</div>
				</a>
				<hr class="my-[24px] w-[calc(100% - 40px)] mx-[20px]"/>
			</div>

			<div class="flex flex-col px-[20px] justify-between grow">
				<p class="uppercase mb-8 text-xs"><?= $this->rendered_terms; ?></p>
				<a href="<?= $this->product_perma_link ?>">
					<h4 class="mb-8"><?= $this->product_title ?></h4>
				</a>
				<h5 class="mb-8">
					<span class="price"><?php echo $this->price_html; ?></span>
					<?php if ( ! $this->is_on_sale && $this->price_html ) { ?>
						<span class="ml-2 text-sm font-thin"><?= __( 'excl. VAT', 'wps-juniper' ); ?></span>
					<?php } ?>
				</h5>

				<div class="inline-flex justify-between ">
					<?php if ( $this->price_html ) { ?>
						<a href="<?= esc_url( $this->add_to_cart_link ); ?>"
						   class="product-card-btn relative h-full lottieOnClick btn btn-black text-white w-full text-center font-semibold hover:bg-[#4D4D4D]">
							<div class="relative h-full">
								<div class='pr-[20px]'>
									<?= __( 'Add to cart', 'wps-juniper' ); ?>
								</div>

								<lottie-player
									class="absolute h-[35px] w-[80px] bottom-0 right-[-40px]"
									mode='normal'
									src="<?= $addition_to_cart_lottie_json_link ?>"
								></lottie-player>
							</div>
						</a>
					<?php } else { ?>
						<div class="h-[0.5rem] md:h-[4rem]"></div>
					<?php } ?>
				</div>

			</div>
		</div>
		<?php
		$html = ob_get_contents();  // Get the buffer's content.
		ob_end_clean();  // Clean the buffer without sending it and turn off output buffering.

		return $html ?: '';
	}

}
