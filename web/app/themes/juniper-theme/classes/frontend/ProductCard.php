<?php


namespace frontend;

class ProductCard {
    
    public function __construct() {
        add_shortcode('wps_get_product_card', [$this, 'product_card_html']);

//        header('Content-Type: text/html; charset=utf-8');
    }

    public function product_card_html( $atts ) {
//        header('Content-Type: text/html; charset=utf-8');
        $product_id = $atts['product_id'];
        $product = wc_get_product($product_id);

	    $product_title = !empty($atts['encoding']) 
			? mb_convert_encoding( $product->get_title(), $atts['encoding'])
			: $product->get_title();

	    $metals_terms = wp_get_post_terms( $product_id, 'metals-and-accessories', [ 'fields' => 'names' ] );
        // $color_terms = wp_get_post_terms($product_id, 'color', array('fields' => 'names'));
        $category_terms = wp_get_post_terms( $product_id, 'product_cat', [ 'fields' => 'names' ] );
        // $application_terms = wp_get_post_terms($product_id, 'application', array('fields' => 'names'));

        $terms = array_merge($metals_terms, $category_terms);

        // Check if there are any terms and not an error
        if (!is_wp_error($terms) && !empty($terms)) {
            // Implode the names array to a single string separated by "|"
            // $terms_string = ;
            $terms_string = htmlspecialchars(implode(' | ', $terms), ENT_QUOTES, 'UTF-8');
        } else {
            $terms_string = '';
        }

	    $addition_to_cart_icon_id = apply_filters('wps_get_attachment_id_with_name_like', 'Warenkorb Animation White');
	    $addition_to_cart_lottie_json_link = wp_get_attachment_url($addition_to_cart_icon_id);

        ob_start();
        ?>
            <div class="overflow-hidden isolate shadow-lg relative product-card h-[100%] pb-[20px] border border-solid border-[#DCDDDE] col-span-6 sm:col-span-3 md:col-span-2 flex flex-col">
            <?php
                $label_new = get_field('label-new', $product->get_id());
                if ($label_new) { ?>
                    <span class="absolute top-0 right-0 px-3 py-2 bg-accent z-10"><?= __('New', 'wps-juniper'); ?></span>
                <?php } ?>

                <div class="stretch-this">
                <a href="<?= $product->get_permalink(); ?>">
                    <div class="product-gallery slick-slider product-card-slider h-[21rem]">
                        <?php 
                        // Display the main product image as the first slide
                        $post_thumbnail_id = $product->get_image_id();
                        if ($post_thumbnail_id) {
                            echo '<div class="h-[100%]">' . wp_get_attachment_image($post_thumbnail_id, 'large', false, array( 'class' => '!h-[100%] w-[100%] object-cover' )) . '</div>';
                        }

                        // Display the gallery images
                        $attachment_ids = $product->get_gallery_image_ids();
                        foreach ($attachment_ids as $attachment_id) {
                            if ($attachment_id !== $post_thumbnail_id) { // Ensure the main image is not repeated
                                $image_html = wp_get_attachment_image($attachment_id, 'large', false, array( 'class' => '!h-[100%] w-[100%] object-cover' ));
                                echo '<div class="h-[100%]">' . $image_html . '</div>';
                            }
                        }
                        ?>
                    </div>
                </a>
                <hr class="my-[24px] w-[calc(100% - 40px)] mx-[20px]" />
                </div>

                <div class="flex flex-col px-[20px] justify-between grow">
                    <p class="uppercase mb-8 text-xs"><?= $terms_string; ?></p>
                    <a href="<?= $product->get_permalink(); ?>">
                        <h4 class="mb-8"><?= $product_title ?></h4>
                    </a>
                    <h5 class="mb-8">
                        <?php 
                            if ( $product->is_on_sale() ) : 
                                $regular_price = wc_price( $product->get_regular_price() );
                                $sale_price = wc_price( $product->get_sale_price() );
                            ?>
                            <div class="flex flex-row">
                                <span class="regular-price line-through pr-2 text-[#737373]"><?php echo $regular_price; ?></span>
                                <span class="sale-price"><?php echo $sale_price; ?></span>
                            </div>
                            <?php else: 
                                $price_html = $product->get_price_html();
                                if ( $price_html ) : ?>
                                    <span class="price"><?php echo $price_html; ?></span><span class="ml-2 text-sm font-thin"><?php _e('excl. VAT', 'wps-juniper'); ?></span>
                                <?php endif;
                            endif;
                        ?>
                    </h5>
                    <div class="inline-flex justify-between ">
    <?php if ($product->get_price() > 0) : ?>
        <a href="<?= esc_url( $product->add_to_cart_url() ); ?>" class="product-card-btn relative h-full lottieOnClick btn btn-black text-white w-full text-center font-semibold hover:bg-[#4D4D4D]">
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
    <?php else : ?>
        <div class="h-[0.5rem] md:h-[4rem]">
        </div>
    <?php endif; ?>
</div>

                </div>
            </div>
        <?php
        $html = ob_get_contents();  // Get the buffer's content.
        ob_end_clean();  // Clean the buffer without sending it and turn off output buffering.

        return $html;
    }
}
?>
