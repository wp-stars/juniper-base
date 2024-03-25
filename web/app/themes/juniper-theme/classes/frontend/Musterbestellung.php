<?php
// #1 Register the new product type 'Musterbestellung'
add_filter( 'product_type_selector', 'add_musterbestellung_product_type' );
  
function add_musterbestellung_product_type( $types ){
    $types['musterbestellung'] = 'Musterbestellung';
    return $types;
}

// --------------------------
// #2 Add New Product Type Class

add_action( 'init', 'create_musterbestellung_product_type' );

function create_musterbestellung_product_type(){
    class WC_Product_Musterbestellung extends WC_Product_Simple {
        public function get_type() {
            return 'musterbestellung';
        }
    }
}

// --------------------------
// #3 Load New Product Type Class

add_filter( 'woocommerce_product_class', 'woocommerce_musterbestellung_product_class', 10, 2 );

function woocommerce_musterbestellung_product_class( $classname, $product_type ) {
    if ( 'musterbestellung' === $product_type ) {
        $classname = 'WC_Product_Musterbestellung';
    }
    return $classname;
}

// --------------------------
// #4 Show Product Data General Tab Prices

add_action('woocommerce_product_data_panels', 'musterbestellung_product_type_custom_js');

function musterbestellung_product_type_custom_js() {
    global $product_object;
    if ($product_object && 'musterbestellung' === $product_object->get_type()) {
        wc_enqueue_js("
            jQuery(document).ready(function($) {
                // Show the general tab and pricing options for the 'Musterbestellung' product type.
                $('.options_group.pricing').addClass('show_if_musterbestellung').show();
                $('.show_if_simple').addClass('show_if_musterbestellung').show();
                // Ensure general tab is visible
                $('li.general_options.general_tab').addClass('show_if_musterbestellung').show();
            });
        ");
    }
}

// --------------------------
// #5 Show Add to Cart Button

add_action( 'woocommerce_musterbestellung_add_to_cart', function() {
    do_action( 'woocommerce_simple_add_to_cart' );
});

// --------------------------
// #6 Adding Custom Fields to Frontend

add_action( 'woocommerce_before_add_to_cart_button', 'add_custom_fields_to_musterbestellung_frontend' );

function add_custom_fields_to_musterbestellung_frontend() {
    global $product;

    if ('musterbestellung' === $product->get_type()) {
        // Check if the cookie exists and decode it
        $selectedProducts = isset($_COOKIE['musterbestellungProducts']) ? json_decode(stripslashes($_COOKIE['musterbestellungProducts']), true) : [];
        $options = get_products_options();

        echo '<div class="musterbestellung-custom-fields">';
        for ($i = 1; $i <= 3; $i++) {
            // Determine the selected value for this iteration
            $selectedValue = '';
            // Determine the selected value for this iteration by finding the first instance
            if (!empty($selectedProducts)) {
                foreach ($selectedProducts as $index => $productId) {
                    if (array_key_exists($productId, $options)) {
                        // Set the selected value to the first instance of the product ID
                        $selectedValue = intval($productId);
                        // Remove this product ID from the array to avoid duplicate selections
                        unset($selectedProducts[$index]);
                        break; // Stop the loop once the first match is found
                    }
                }
            }

            // Pass the 'selected' option to pre-select the dropdown
            woocommerce_form_field('_associated_product_' . $i, array(
                'type'     => 'select',
                'id'       => '_associated_product_select_' . $i,
                'class'    => array('musterbestellung-input', 'block', 'w-full', 'bg-white', 'px-4', 'py-2', 'pr-8'),
                'label'    => sprintf(__('Select Associated Product %d', 'woocommerce'), $i),
                'options'  => $options,
                'default' => $selectedValue, // Use the 'selected' parameter to pre-select the option
            ));
        }
        echo '</div>';
    }
}

// Function to populate the options in the custom fields
function get_products_options() {
    $products = wc_get_products(array('status' => 'publish', 'limit' => -1));
    $options = array('' => __('Select a Product', 'woocommerce'));
    foreach ($products as $product) {
        if ('musterbestellung' !== $product->get_type()) {
            $options[$product->get_id()] = $product->get_name();
        }
    }
    return $options;
}

function enqueue_custom_js_for_musterbestellung() {
    if (is_product()) {
        global $product;
        if ($product->get_type() === 'musterbestellung') {
            wp_enqueue_script('custom-musterbestellung-js', get_template_directory_uri() . '/assets/js/custom-musterbestellung.js', array('jquery'), '', true);

            // Localize script to inject PHP values if necessary (demonstrative; not used directly in your JS)
            wp_localize_script('custom-musterbestellung-js', 'musterbestellungParams', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'restUrl' => get_rest_url()
                // Any other data you might want to pass from PHP to your JS; this is just a placeholder
            ));
        }
    }
}

add_action('wp_enqueue_scripts', 'enqueue_custom_js_for_musterbestellung');

add_action('rest_api_init', function () {
    register_rest_route('wps/v1', '/get-musterbestellung/', array(
        'methods' => 'GET',
        'callback' => 'get_musterbestellung_products',
        'permission_callback' => '__return_true', // Adjust permissions based on your needs
    ));
});

function get_musterbestellung_products(WP_REST_Request $request) {
    $product_ids = explode(',', $request->get_param('ids'));
    $products = [];
    
    foreach ($product_ids as $id) {
        // Assuming getProductImageById is a function that returns an image URL by product ID
        $product = new \stdClass();

        $product->image = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'single-post-thumbnail' );
        $product->name = get_the_title( $id );
        
        $products[] = $product; 
    }
    
    return new WP_REST_Response($products, 200);
}

