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
            wp_enqueue_script('single-musterbestellung-js', get_template_directory_uri() . '/assets/js/single-musterbestellung.js', array('jquery'), '', true);

            // Localize script to inject PHP values if necessary (demonstrative; not used directly in your JS)
            wp_localize_script('single-musterbestellung-js', 'singleMusterbestellungParams', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'restUrl' => get_rest_url()
                // Any other data you might want to pass from PHP to your JS; this is just a placeholder
            ));
        }
    }

    wp_enqueue_script('custom-musterbestellung-js', get_template_directory_uri() . '/assets/js/custom-musterbestellung.js', array('jquery'), '', true);

    // Localize script to inject PHP values if necessary (demonstrative; not used directly in your JS)
    wp_localize_script('custom-musterbestellung-js', 'customMusterbestellungParams', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'restUrl' => get_rest_url()
        // Any other data you might want to pass from PHP to your JS; this is just a placeholder
    ));
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

add_filter('woocommerce_add_cart_item_data', 'add_musterbestellung_data_to_cart_item', 10, 3);

function add_musterbestellung_data_to_cart_item($cart_item_data, $product_id, $variation_id) {
    if (isset($_COOKIE['musterbestellungProducts'])) {
        // Decode the JSON from the cookie
        $musterbestellungProducts = json_decode(stripslashes($_COOKIE['musterbestellungProducts']), true);

        if(count($musterbestellungProducts) > 0) {
            $musterbestellungData = array_map('setupMusterbestellungData', $musterbestellungProducts);
            $cart_item_data['musterbestellung_custom_data'] = $musterbestellungData;
            
        }
    }

    return $cart_item_data;
}

function setupMusterbestellungData($product_id) {
    return array(
        'product_id' => $product_id,
        'product_name' => get_the_title($product_id)
    );
}

add_filter('woocommerce_get_item_data', 'display_musterbestellung_data_in_cart', 10, 2);

function display_musterbestellung_data_in_cart($item_data, $cart_item) {
    if (isset($cart_item['musterbestellung_custom_data'])) {
        foreach ($cart_item['musterbestellung_custom_data'] as $key => $value) {
            $index = $key + 1;
            $item_data[] = array(
                'name' => "Product $index",
                'value' => $value['product_name']
            );
        }
    }

    return $item_data;
}

add_action('woocommerce_checkout_create_order_line_item', 'save_musterbestellung_data_with_order', 10, 4);

function save_musterbestellung_data_with_order($item, $cart_item_key, $values, $order) {
    if (isset($values['musterbestellung_custom_data'])) {
        foreach ($values['musterbestellung_custom_data'] as $key => $value) {
            $index = $key + 1;
            $item->add_meta_data("Product $index ID", $value['product_id']);
            $item->add_meta_data("Product $index Name", $values['product_name']);
        }
    }
}

add_filter('woocommerce_add_to_cart_validation', 'limit_musterbestellung_product_in_cart', 10, 3);

function limit_musterbestellung_product_in_cart($passed, $product_id, $quantity) {
    $product = wc_get_product($product_id);
    if ($product->get_type() === 'musterbestellung') {
        // Check if cart contains a product of type 'musterbestellung'
        foreach(WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $cart_product = wc_get_product($cart_item['product_id']);
            if($cart_product->get_type() === 'musterbestellung') {
                
                // Prevent the new product from being added if you decide not to remove the existing one
                wc_add_notice(__('You can only have one "Musterbestellung" product in your cart.', 'woocommerce'), 'error');
                return false;
            }
        }
    }

    return $passed;
}

add_filter('woocommerce_quantity_input_args', 'musterbestellung_quantity_input_args', 10, 2);

function musterbestellung_quantity_input_args($args, $product) {
    if ('musterbestellung' === $product->get_type()) {
        $args['max_value'] = 1;  // Set maximum quantity to 1
        $args['min_value'] = 0;  // Set minimum quantity to 1 to enforce single item only
    }

    return $args;
}

add_action('woocommerce_before_cart_item_quantity_zero', 'check_musterbestellung_product_quantity', 10, 2);
add_action('woocommerce_after_cart_item_quantity_update', 'check_musterbestellung_product_quantity', 10, 2);

function check_musterbestellung_product_quantity($cart_item_key, $quantity) {
    $cart_item = WC()->cart->get_cart_item($cart_item_key);
    $product = wc_get_product($cart_item['product_id']);
    
    if ($product->get_type() === 'musterbestellung' && $quantity > 1) {
        WC()->cart->set_quantity($cart_item_key, 1); // Set the quantity back to 1 if higher
    }
}

function enqueue_disable_add_to_cart_script() {
    $musterbestellung_in_cart = false;

    foreach (WC()->cart->get_cart() as $cart_item) {
        $product = wc_get_product($cart_item['product_id']);
        if ($product && 'musterbestellung' === $product->get_type()) {
            $musterbestellung_in_cart = true;
            break;
        }
    }

    if ($musterbestellung_in_cart) {
        wp_enqueue_script('disable-add-to-cart', get_template_directory_uri() . '/assets/js/disable-add-to-cart.js', array(), false, true);
    }
}
add_action('wp_enqueue_scripts', 'enqueue_disable_add_to_cart_script');




