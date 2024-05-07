<?php
/**
 * Single Product title
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/title.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://woo.com/document/template-structure/
 * @package    WooCommerce\Templates
 * @version    1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}



the_title( '<h1 class="product_title entry-title mb-2 text-headline break-words">', '</h1>' );

if (function_exists('get_field')) {
    $subheadline = get_field('wps_sp_description_title');
    if ($subheadline) {
        echo '<h2 class="text-black text-2xl font-bold mb-3.5">' . $subheadline . '</h2>';
    }
};