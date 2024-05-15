<?php


namespace WPS\frontend;

class ProductCard {
    
    public function __construct() {
        add_shortcode('wps_get_product_card', [$this, 'product_card_html']);

        header('Content-Type: text/html; charset=utf-8');
    }

    public function product_card_html( $atts ) {
        header('Content-Type: text/html; charset=utf-8');
        $product_id = $atts['product_id'];
        $product = wc_get_product($product_id);

        $html = "";

      
        $metals_terms = wp_get_post_terms($product_id, 'metals-and-accessories', array('fields' => 'names'));
        // $color_terms = wp_get_post_terms($product_id, 'color', array('fields' => 'names'));
        $category_terms = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'names'));
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

        ob_start();
        ?>
            <div class="overflow-hidden shadow-lg relative product-card h-[100%] pb-[20px] border border-solid border-[#DCDDDE] col-span-6 sm:col-span-3 md:col-span-2 flex flex-col">
            <?php
                $label_new = get_field('label-new', $product->get_id());
                if ($label_new) { ?>
                    <span class="absolute top-0 right-0 p-1 bg-accent"><?= __('New', 'wps-juniper'); ?></span>
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
    <?php if ($product->get_price() > 0) : ?>
        <a href="<?= $product->add_to_cart_url(); ?>" class="product-card-btn btn btn-black text-white w-[100%] text-center font-semibold hover:bg-[#4D4D4D]">
            <?= __('In den Warenkorb', 'wps-theme'); ?>
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <g xmlns="http://www.w3.org/2000/svg" id="ShoppingCart">
                    <path id="Vector" d="M20.8256 5.51906C20.7552 5.43481 20.6672 5.36705 20.5677 5.32056C20.4683 5.27407 20.3598 5.24998 20.25 5.25H5.12625L4.66781 2.73187C4.60502 2.38625 4.42292 2.07363 4.15325 1.84851C3.88359 1.62339 3.54347 1.50005 3.19219 1.5H1.5C1.30109 1.5 1.11032 1.57902 0.96967 1.71967C0.829018 1.86032 0.75 2.05109 0.75 2.25C0.75 2.44891 0.829018 2.63968 0.96967 2.78033C1.11032 2.92098 1.30109 3 1.5 3H3.1875L5.58375 16.1522C5.65434 16.5422 5.82671 16.9067 6.08344 17.2087C5.72911 17.5397 5.47336 17.9623 5.34455 18.4298C5.21575 18.8972 5.21892 19.3912 5.35371 19.8569C5.48851 20.3226 5.74966 20.7419 6.10821 21.0683C6.46676 21.3947 6.9087 21.6154 7.38502 21.7059C7.86134 21.7965 8.35344 21.7533 8.80673 21.5813C9.26003 21.4092 9.65682 21.115 9.9531 20.7312C10.2494 20.3474 10.4336 19.889 10.4853 19.407C10.537 18.9249 10.4541 18.4379 10.2459 18H14.5041C14.3363 18.3513 14.2495 18.7357 14.25 19.125C14.25 19.6442 14.404 20.1517 14.6924 20.5834C14.9808 21.0151 15.3908 21.3515 15.8705 21.5502C16.3501 21.7489 16.8779 21.8008 17.3871 21.6996C17.8963 21.5983 18.364 21.3483 18.7312 20.9812C19.0983 20.614 19.3483 20.1463 19.4496 19.6371C19.5508 19.1279 19.4989 18.6001 19.3002 18.1205C19.1015 17.6408 18.7651 17.2308 18.3334 16.9424C17.9017 16.654 17.3942 16.5 16.875 16.5H7.79719C7.62155 16.5 7.45149 16.4383 7.31665 16.3257C7.18182 16.2132 7.09077 16.0569 7.05938 15.8841L6.76219 14.25H17.6372C18.1641 14.2499 18.6743 14.0649 19.0788 13.7272C19.4833 13.3896 19.7564 12.9206 19.8506 12.4022L20.9906 6.13406C21.0099 6.02572 21.0051 5.91447 20.9766 5.80818C20.9481 5.7019 20.8966 5.60319 20.8256 5.51906ZM9 19.125C9 19.3475 8.93402 19.565 8.8104 19.75C8.68679 19.935 8.51109 20.0792 8.30552 20.1644C8.09995 20.2495 7.87375 20.2718 7.65552 20.2284C7.43729 20.185 7.23684 20.0778 7.0795 19.9205C6.92217 19.7632 6.81502 19.5627 6.77162 19.3445C6.72821 19.1262 6.75049 18.9 6.83564 18.6945C6.92078 18.4889 7.06498 18.3132 7.24998 18.1896C7.43499 18.066 7.6525 18 7.875 18C8.17337 18 8.45952 18.1185 8.6705 18.3295C8.88147 18.5405 9 18.8266 9 19.125ZM18 19.125C18 19.3475 17.934 19.565 17.8104 19.75C17.6868 19.935 17.5111 20.0792 17.3055 20.1644C17.1 20.2495 16.8738 20.2718 16.6555 20.2284C16.4373 20.185 16.2368 20.0778 16.0795 19.9205C15.9222 19.7632 15.815 19.5627 15.7716 19.3445C15.7282 19.1262 15.7505 18.9 15.8356 18.6945C15.9208 18.4889 16.065 18.3132 16.25 18.1896C16.435 18.066 16.6525 18 16.875 18C17.1734 18 17.4595 18.1185 17.6705 18.3295C17.8815 18.5405 18 18.8266 18 19.125ZM18.375 12.1341C18.3435 12.3074 18.2521 12.464 18.1166 12.5766C17.9812 12.6893 17.8105 12.7506 17.6344 12.75H6.48938L5.39906 6.75H19.3509L18.375 12.1341Z" fill="white"/>
                </g>
            </svg>
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
