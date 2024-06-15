<?php

namespace frontend\ProductCard;

abstract class CardRenderer {
	private string $product_title;
	private bool $is_new;
	private string $product_perma_link;
	private string $rendered_images;
	private string $rendered_terms;
	private string $price_html;
	private string $is_on_sale;
	private string $add_to_cart_link;
	private array $container_classes;

	/**
	 * @param string $product_title
	 * @param bool $is_new
	 * @param string $product_perma_link
	 * @param string $rendered_images
	 * @param string $rendered_terms
	 * @param string $price_html
	 * @param string $is_on_sale
	 * @param string $add_to_cart_link
	 *
	 * @noinspection PhpMissingVisibilityInspection
	 */
	function __construct( string $product_title, bool $is_new, string $product_perma_link, string $rendered_images, string $rendered_terms, string $price_html, string $is_on_sale, string $add_to_cart_link, array $container_classes ) {
		$this->product_title      = $product_title;
		$this->is_new             = $is_new;
		$this->product_perma_link = $product_perma_link;
		$this->rendered_images    = $rendered_images;
		$this->rendered_terms     = $rendered_terms;
		$this->price_html         = $price_html;
		$this->is_on_sale         = $is_on_sale;
		$this->add_to_cart_link   = $add_to_cart_link;
		$this->container_classes  = $container_classes;
	}

	abstract public static function generate( $reference, $encoding ): CardRenderer;

	public function render(): string {

		$addition_to_cart_icon_id          = apply_filters( 'wps_get_attachment_id_with_name_like', 'Warenkorb Animation White' );
		$addition_to_cart_lottie_json_link = wp_get_attachment_url( $addition_to_cart_icon_id );

		$extra_classes_rendered = implode( ' ', $this->container_classes);

		ob_start();
		?>
		<div class="overflow-hidden isolate shadow-lg relative product-card h-full pb-5 border border-solid border-[#DCDDDE] col-span-6 sm:col-span-3 md:col-span-2 flex flex-col <?= $extra_classes_rendered ?>">
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