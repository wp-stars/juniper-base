<?php

function timber_set_product($post) {
    global $product;

    if (is_woocommerce()) {
        $product = wc_get_product($post->ID);
    }
}


if (!class_exists('Timber')) {
    echo 'Timber not activated. Make sure you activate the plugin in <a href="/wp-admin/plugins.php#timber">/wp-admin/plugins.php</a>';

    return;
}

$context = Timber::context();
$context['sidebar'] = Timber::get_widgets('shop-sidebar');

if (is_shop()) {
    $page_id = get_option( 'woocommerce_shop_page_id' ); // Replace with your custom page ID
    $post = get_post($page_id);
    $context['page_content'] = apply_filters( 'the_content', $post->post_content );

    if (has_block('acf/filter')) {
        $time = time();
        $theme_path = get_template_directory_uri();

        wp_enqueue_style('filter-css', $theme_path . '/blocks/filter/style.css', array(), $time, 'all');
        wp_enqueue_script('filter-js', $theme_path . '/blocks/filter/script.js', array(), $time, true);

        $attributes = [];
        wp_enqueue_script( 'filterBlock', $theme_path . '/blocks/filter/build/frontend.js', array(
            'wp-blocks',
            'wp-element',
            'wp-editor',
            'wp-api',
            'wp-element',
            'wp-i18n',
            'wp-polyfill',
            'wp-api-fetch'
        ), $time, true );
        wp_localize_script( 'filterBlock', 'filterData', $attributes );
    }
}

if (is_singular('product')) {
    $context['post'] = Timber::get_post();
    $product = wc_get_product($context['post']->ID);
    $context['product'] = $product;

    // Get related products
    $related_limit = wc_get_loop_prop('columns');
    $related_ids = wc_get_related_products($context['post']->id, $related_limit);
    $context['related_products'] = Timber::get_posts($related_ids);

    // Restore the context and loop back to the main query loop.
    wp_reset_postdata();

    Timber::render('views/woo/single-product.twig', $context);
} else {
    $posts = Timber::get_posts();
    $context['products'] = $posts;

    if (is_product_category()) {
        $queried_object = get_queried_object();
        $term_id = $queried_object->term_id;
        $context['category'] = get_term($term_id, 'product_cat');
        $context['title'] = single_term_title('', false);
    }

    wp_reset_postdata();
    Timber::render('views/woo/archive.twig', $context);
}