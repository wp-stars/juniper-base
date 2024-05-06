<?php
/**
 * Single Product tabs using Tailwind CSS
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/tabs/tabs.php.
 *
 * @see     https://woo.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product_tabs = apply_filters( 'woocommerce_product_tabs', array() );

if ( ! empty( $product_tabs ) ) : ?>

	<div class="woocommerce-tabs wc-tabs-wrapper mb-[8.125rem]">
	<ul class="tabs wc-tabs flex flex-wrap border-t mb-11" role="tablist">
    <?php foreach ($product_tabs as $key => $product_tab) : ?>
        <li class="<?= esc_attr($key); ?>_tab" id="tab-title-<?php echo esc_attr($key); ?>" role="tab"
            aria-controls="tab-<?php echo esc_attr($key); ?>">
            <a href="#tab-<?php echo esc_attr($key); ?>"
                class="<?= $key === 0 ? 'active' : ''; ?> tab-link text-black py-1 px-6 inline-block border-t-2 border-transparent hover:border-black focus:border-black text-base">
                <?php echo wp_kses_post(apply_filters('woocommerce_product_' . $key . '_tab_title', $product_tab['title'], $key)); ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>
		<?php foreach ( $product_tabs as $key => $product_tab ) : ?>
			<div class="woocommerce-Tabs-panel woocommerce-Tabs-panel--<?php echo esc_attr( $key ); ?> panel entry-content wc-tab" id="tab-<?php echo esc_attr( $key ); ?>" role="tabpanel" aria-labelledby="tab-title-<?php echo esc_attr( $key ); ?>">
				<?php
				if ( isset( $product_tab['callback'] ) ) {
					call_user_func( $product_tab['callback'], $key, $product_tab );
				}
				?>
			</div>
		<?php endforeach; ?>

		<?php do_action( 'woocommerce_product_after_tabs' ); ?>
	</div>

<?php endif; ?>
