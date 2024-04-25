<?php

namespace WPS\frontend;

class ProductCard {

    public function __construct() {
        add_shortcode('wps_get_product_card', [$this, 'product_card_html']);

        header('Content-Type: text/html; charset=utf-8');
    }

    public function product_card_html( $atts ) {
        $product_id = $atts['product_id'];
        $product = wc_get_product($product_id);

        $html = "";

        $terms = wp_get_post_terms($product_id, 'metals-and-accessories', array('fields' => 'names'));

        // Check if there are any terms and not an error
        if (!is_wp_error($terms) && !empty($terms)) {
            // Implode the names array to a single string separated by "|"
            $terms_string = implode(' | ', $terms);
        } else {
            $terms_string = '';
        }

        ob_start();
        ?>
            <div class="overflow-hidden shadow-lg relative product-card h-[100%] pb-[20px] border border-solid border-[#DCDDDE] col-span-6 sm:col-span-3 md:col-span-2 ">
                <a href="<?= $product->get_permalink(); ?>">
                    <div class="product-gallery slick-slider product-card-slider h-[21rem]">
                        <?php 
                        // Display the main product image as the first slide
                        $post_thumbnail_id = $product->get_image_id();
                        if ($post_thumbnail_id) {
                            echo '<div class="h-[100%]">' . wp_get_attachment_image($post_thumbnail_id, 'medium', false, array( 'class' => '!h-[100%] w-[100%] object-cover' )) . '</div>';
                        }

                        // Display the gallery images
                        $attachment_ids = $product->get_gallery_image_ids();
                        foreach ($attachment_ids as $attachment_id) {
                            if ($attachment_id !== $post_thumbnail_id) { // Ensure the main image is not repeated
                                $image_html = wp_get_attachment_image($attachment_id, 'medium', false, array( 'class' => '!h-[100%] w-[100%] object-cover' ));
                                echo '<div class="h-[100%]">' . $image_html . '</div>';
                            }
                        }
                        ?>
                    </div>
                </a>
                <?php if(has_term("new", 'product_tag', $product->get_id())) { ?>
                    <span class="absolute top-0 right-0 p-1 bg-accent"><?= __('New', 'wps-juniper'); ?></span>
                <?php } ?>
                <hr class="my-[24px] w-[calc(100% - 40px)] mx-[20px]" />
                <div class="flex flex-col px-[20px] justify-between h-[16rem]">
                    <p class="uppercase mb-8 text-xs"><?= $terms_string; ?></p>
                    <a href="<?= $product->get_permalink(); ?>">
                        <h4 class="mb-8"><?= $product->get_title(); ?></h4>
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
                                    <span class="price"><?php echo $price_html; ?></span>
                                <?php endif; 
                            endif;
                        ?>
                    </h5>
                    <div class="inline-flex justify-between ">
                        <a href="<?= $product->add_to_cart_url(); ?>" class="btn btn-black text-white w-[100%] text-center font-semibold">
                            <?= __('In den Warenkorb', 'wps-theme'); ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <!-- SVG content -->
                            </svg>
                        </a>
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
