<?php
/**
 * Related Products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/related.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://woo.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     3.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $related_products ) : ?>

	<section class="related products mb-28 sm:mb-72">

		<?php
		$heading = apply_filters( 'woocommerce_product_related_products_heading', __( 'Related products', 'woocommerce' ) );

		if ( $heading ) :
			?>
			<h2 class="mb-[30px] text-3xl"><?php echo esc_html( $heading ); ?></h2>
		<?php endif; ?>
		
		<?php woocommerce_product_loop_start(); ?>

		<?php 
			$product_count = 0;
			foreach ( $related_products as $related_product ) : 
				if ($product_count >= 3) {
					break;
				}
				?>
				<?php
					echo do_shortcode("[wps_get_product_card product_id='{$related_product->get_id()}']");
					$product_count++;
				?>

			<?php endforeach; ?>

		<?php woocommerce_product_loop_end(); ?>

	</section>
	<?php
endif;

wp_reset_postdata();
