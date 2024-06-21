<?php
/**
 * Simple product add to cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/simple.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woo.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;

global $product;

wp_enqueue_script('custom-musterbestellung-js', get_template_directory_uri() . '/assets/js/custom-musterbestellung.js', array('jquery'), '', true);

echo wc_get_stock_html( $product ); // WPCS: XSS ok.

$sample_icon_id = apply_filters('wps_get_attachment_id_with_name_like', 'Sample Icon Animation');
$sample_icon_lottie_json_link = wp_get_attachment_url($sample_icon_id);

$addition_to_cart_icon_id = apply_filters('wps_get_attachment_id_with_name_like', 'Warenkorb Animation White');
$addition_to_cart_lottie_json_link = wp_get_attachment_url($addition_to_cart_icon_id);

if ( !!$product->is_purchasable() && $product->is_in_stock() ) : ?>

    <?php do_action( 'woocommerce_before_add_to_cart_form' ); ?>

    <form class="cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data'>
        <?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

        <div class="inline-flex gap-4 flex-wrap">
            <div class="test flex flex-row gap-4 w-full sm:w-auto">
                <?php
                do_action( 'woocommerce_before_add_to_cart_quantity' );

                if ( $product->get_price() > 0 && $product->is_in_stock() ){

                    woocommerce_quantity_input(
                        array(
                            'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
                            'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
                            'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(), // WPCS: CSRF ok, input var ok.
                        )
                    );

                }

                do_action( 'woocommerce_after_add_to_cart_quantity' );
                ?>

                <?php if( $product->get_price() > 0 && $product->is_in_stock() ) : ?>

                    <button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="relative btn btn-primary single_add_to_cart_button disabled:bg-darkgray w-full sm:w-auto lottieOnClick <?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>">
						<lottie-player
								class="absolute left-0 pr-4 h-[50px] w-[80px] pb-3"
								mode='normal'
								src="<?= $addition_to_cart_lottie_json_link ?>"
						></lottie-player>

                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" class="ml-8">
                            <path d="M17.8125 10C17.8125 10.2486 17.7137 10.4871 17.5379 10.6629C17.3621 10.8387 17.1236 10.9375 16.875 10.9375H10.9375V16.875C10.9375 17.1236 10.8387 17.3621 10.6629 17.5379C10.4871 17.7137 10.2486 17.8125 10 17.8125C9.75136 17.8125 9.5129 17.7137 9.33709 17.5379C9.16127 17.3621 9.0625 17.1236 9.0625 16.875V10.9375H3.125C2.87636 10.9375 2.6379 10.8387 2.46209 10.6629C2.28627 10.4871 2.1875 10.2486 2.1875 10C2.1875 9.75136 2.28627 9.5129 2.46209 9.33709C2.6379 9.16127 2.87636 9.0625 3.125 9.0625H9.0625V3.125C9.0625 2.87636 9.16127 2.6379 9.33709 2.46209C9.5129 2.28627 9.75136 2.1875 10 2.1875C10.2486 2.1875 10.4871 2.28627 10.6629 2.46209C10.8387 2.6379 10.9375 2.87636 10.9375 3.125V9.0625H16.875C17.1236 9.0625 17.3621 9.16127 17.5379 9.33709C17.7137 9.5129 17.8125 9.75136 17.8125 10Z" fill="white"/>
                        </svg>

                    </button>

                <?php endif; ?>

            </div>
            <?php
            $purchasability_terms = get_the_terms( $product->get_id(), 'purchasability' );
            // Check if 'sample available' is one of the terms
            $sample_available = false;
            if ( ! empty( $purchasability_terms ) && ! is_wp_error( $purchasability_terms ) ) {
                foreach ( $purchasability_terms as $term ) {
                    if ( $term->slug === 'muster-verfuegbar' || $term->slug === 'sample-available-en' ) {
                        $sample_available = true;
                        break;
                    }
                }
            }

            // Show the "Muster bestellen" button only if 'sample available' is set
            if ( $sample_available ) : ?>
                <button class="btn btn-accent add-to-musterbestellung font-semibold w-full sm:w-auto lottieOnClick" type="button" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">
					<lottie-player
							mode="normal"
							src="<?= $sample_icon_lottie_json_link ?>"
							style="width: 24px; height: 24px; color: white"
					></lottie-player>

                    <!-- Button content here -->
                    <?= __('Order a sample', 'wps-juniper'); ?>
                </button>
            <?php endif; ?>

            <button class="btn btn-bordered product-question-button font-semibold w-full sm:w-auto" onclick="openModal('product-request-modal');" data-id="<?php echo $product->get_id();?>" type="button">
                <?= __('Questions about the product?', 'wps-juniper'); ?>
            </button>
        </div>

        <?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
    </form>

    <?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>

<?php else: ?>

    <div class="flex flex-row gap-4">
        <?php
        $purchasability_terms = get_the_terms( $product->get_id(), 'purchasability' );

        // Check if 'sample available' is one of the terms
        $sample_available = false;
        if ( ! empty( $purchasability_terms ) && ! is_wp_error( $purchasability_terms ) ) {
            foreach ( $purchasability_terms as $term ) {
                if ( $term->slug === 'muster-verfuegbar' || $term->slug === 'sample-available-en' ) {
                    $sample_available = true;
                    break;
                }
            }
        }

        // Show the "Muster bestellen" button only if 'sample available' is set
        if ( $sample_available ) : ?>
            <button class="btn btn-accent add-to-musterbestellung font-semibold w-full sm:w-auto lottieOnClick" type="button" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">
					<lottie-player
							mode="normal"
							src="<?= $sample_icon_lottie_json_link ?>"
							style="width: 24px; height: 24px; color: white"
					></lottie-player>

                    <!-- Button content here -->
                    <?= __('Order a sample', 'wps-juniper'); ?>
                </button>
        <?php endif; ?>

        <button class="btn btn-bordered product-question-button font-semibold w-full sm:w-auto" onclick="openModal('product-request-modal');" data-id="<?php echo $product->get_id();?>" type="button">
            <?= __('Questions about the product?', 'wps-juniper'); ?>
        </button>
    </div>
<?php endif; ?>