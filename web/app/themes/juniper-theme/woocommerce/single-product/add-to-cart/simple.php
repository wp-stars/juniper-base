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

if ( ! $product->is_purchasable() ) {
    // return;
}

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
            <button class="btn btn-accent add-to-musterbestellung font-semibold w-full sm:w-auto" type="button" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M7.20472 16.5967C6.51432 16.5967 5.95789 17.1531 5.95789 17.8435C5.95789 18.5339 6.52463 19.0903 7.20472 19.0903C7.89511 19.0903 8.45155 18.5236 8.45155 17.8435C8.45155 17.1531 7.89511 16.5967 7.20472 16.5967ZM7.20472 18.3793C6.90589 18.3793 6.66889 18.1423 6.66889 17.8435C6.66889 17.5447 6.90589 17.3077 7.20472 17.3077C7.50354 17.3077 7.74055 17.5447 7.74055 17.8435C7.74055 18.1423 7.50354 18.3793 7.20472 18.3793Z" fill="black"/>
                    <path d="M20.858 11.1251L20.178 10.1771C20.0646 10.0122 19.8379 9.98132 19.6833 10.0947C19.5185 10.208 19.4876 10.4347 19.6009 10.5893L20.281 11.5373C20.3016 11.5682 20.3016 11.6197 20.2604 11.6403L18.0759 13.2169L15.2731 9.29093L15.5307 8.78602L17.303 7.51858C17.3339 7.48766 17.3855 7.49797 17.4061 7.53918L17.9316 8.2811C18.0449 8.44597 18.2716 8.47688 18.4262 8.36353C18.5911 8.25019 18.622 8.0338 18.5086 7.86893L17.9831 7.12701C17.7358 6.77666 17.2412 6.69423 16.8805 6.94153L16.2314 7.39492L16.592 6.69422C16.6848 6.50874 16.7054 6.29235 16.6435 6.09657C16.5817 5.90079 16.4478 5.73592 16.252 5.63287L11.8726 3.37621C11.6871 3.28347 11.4707 3.26286 11.275 3.32469C11.0792 3.38652 10.9143 3.53078 10.8113 3.71626L10.4506 4.41695V3.62352C10.4506 3.19073 10.09 2.83008 9.65717 2.83008H4.73167C4.29889 2.83008 3.93823 3.18043 3.93823 3.62352V17.8436C3.93823 19.6365 5.40146 21.0998 7.19442 21.0998C7.89512 21.0998 8.5546 20.8731 9.09043 20.4918C9.10073 20.4815 9.11104 20.4815 9.12134 20.4712L12.5424 18.0291L15.3658 16.0094L20.6623 12.2277C21.0229 11.9804 21.1053 11.4858 20.858 11.1251ZM17.4885 13.6291L15.2422 15.2263L13.4801 12.7532L14.9021 9.99163L17.4885 13.6291ZM12.4187 17.2459L11.6974 16.2361L13.1194 13.4745L14.6754 15.6487L12.4187 17.2459ZM11.5635 14.9171L10.4609 14.3504V11.2488L12.8309 12.4647L12.7485 12.6192L12.7382 12.6296L11.5635 14.9171ZM13.1607 11.8361L10.4609 10.445V7.3434L14.4178 9.38367L13.1607 11.8361ZM9.74991 9.77524H4.66984V7.01366H9.74991V9.77524ZM4.65954 10.4965H9.7396V13.2581H4.65954V10.4965ZM10.4609 15.1541L11.244 15.556L10.4609 17.0707V15.1541ZM11.3265 16.9471L11.8314 17.6581L10.4506 18.6473L11.3265 16.9471ZM11.4501 4.03569C11.4604 4.01508 11.481 4.00478 11.5017 3.99447C11.512 3.98417 11.5326 3.98417 11.5635 4.00478L15.9428 6.26144C15.9635 6.27174 15.9738 6.29235 15.9841 6.30266C15.9944 6.32327 15.9944 6.34388 15.9738 6.36448L14.7475 8.7448L10.4609 6.53966V5.97292L11.4501 4.03569ZM4.74198 3.54108H9.66747C9.70869 3.54108 9.74991 3.57199 9.74991 3.61321V6.29235H4.66984V3.61321C4.65954 3.5823 4.69045 3.54108 4.74198 3.54108ZM7.20473 20.3888C5.80333 20.3888 4.65954 19.245 4.65954 17.8436V13.9691H9.7396V17.8436C9.74991 19.245 8.60612 20.3888 7.20473 20.3888Z" fill="black"/>
                    <path d="M19.0651 9.58967C19.2609 9.58967 19.4155 9.42479 19.4155 9.23932C19.4155 9.04353 19.2506 8.87866 19.0651 8.87866C18.8693 8.87866 18.7045 9.04353 18.7045 9.23932C18.7045 9.42479 18.859 9.58967 19.0651 9.58967Z" fill="black"/>
                </svg>
                <!-- Button content here -->
                <?= __('Order a sample', 'wps-juniper'); ?>
            </button>
        <?php endif; ?>

        <button class="btn btn-bordered product-question-button font-semibold w-full sm:w-auto" onclick="openModal('product-request-modal');" data-id="<?php echo $product->get_id();?>" type="button">
            <?= __('Questions about the product?', 'wps-juniper'); ?>
        </button>
    </div>
<?php endif; ?>