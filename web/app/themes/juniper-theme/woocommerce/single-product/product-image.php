<?php
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wc_get_gallery_image_html' ) ) {
    return;
}

global $product;

add_image_size('custom-size', 800, 800, true);

$columns           = apply_filters( 'woocommerce_product_thumbnails_columns', 4 );
$post_thumbnail_id = $product->get_image_id();
$wrapper_classes   = apply_filters(
    'woocommerce_single_product_image_gallery_classes',
    array(
        'woocommerce-product-gallery',
        'woocommerce-product-gallery--' . ($post_thumbnail_id ? 'with-images' : 'without-images'),
        'woocommerce-product-gallery--columns-' . absint($columns),
        'images',
        'max-h-[27.5rem]'
    )
);
?>
<div class="<?php echo esc_attr( implode(' ', array_map('sanitize_html_class', $wrapper_classes)) ); ?>" data-columns="<?php echo esc_attr($columns); ?>" style="opacity: 0; transition: opacity .25s ease-in-out;">
    <div class="woocommerce-product-gallery__wrapper slick-slider flex justify-center items-center">
        <?php
        // Display the main product image as the first slide
        if ($post_thumbnail_id) {
            echo '<div>' . wp_get_attachment_image($post_thumbnail_id, 'product-single-page-picture-size', false, array('class' => 'max-h-[27.5rem] object-cover')) . '</div>';
        }

        // Display gallery images
        $attachment_ids = $product->get_gallery_image_ids();
        foreach ($attachment_ids as $attachment_id) {
            if ($attachment_id !== $post_thumbnail_id) { // Ensure the main image is not repeated
                echo '<div>' . wp_get_attachment_image($attachment_id, 'product-single-page-picture-size', false, array('class' => 'max-h-[27.5rem] object-cover')) . '</div>';
            }
        }

        // Fallback for no images
        if (!$post_thumbnail_id && empty($attachment_ids)) {
            echo '<div class="woocommerce-product-gallery__image--placeholder">';
            echo sprintf('<img src="%s" alt="%s" class="wp-post-image" />', esc_url(wc_placeholder_img_src('woocommerce_single')), esc_html__('Awaiting product image', 'woocommerce'));
            echo '</div>';
        }
        ?>
    </div>
</div>
