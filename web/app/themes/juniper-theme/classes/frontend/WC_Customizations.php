<?php

namespace WPS\frontend;

class WC_Customizations {

    public function __construct() {
        // Add the action to print product categories
        add_action('wps_print_product_categories', array($this, 'custom_product_categories'));

        add_action('wps_print_metals_and_accessories', array($this, 'print_metals_and_accessories'));

        add_filter( 'woocommerce_product_tabs', array($this, 'add_product_tabs'), 9999 );

        add_action('woocommerce_product_options_general_product_data', array($this, 'add_custom_text_field'));
        add_action('woocommerce_process_product_meta', array($this, 'save_custom_text_field'));
        add_action('wps_print_custom_text_field', array($this, 'print_custom_text_field'));

    }

    public function add_product_tabs() {
        $tabs['description'] = array(
            'title' => __( 'Description', 'wps-juniper' ), // TAB TITLE
            'priority' => 50,
            'callback' => array($this, 'product_description_tab'), // TAB CONTENT CALLBACK
        );

        $tabs['technical_data'] = array(
            'title' => __( 'Technical Data', 'wps-juniper' ), // TAB TITLE
            'priority' => 50,
            'callback' => array($this, 'product_technical_data_tab'), // TAB CONTENT CALLBACK
        );
        return $tabs;
    }

    public function product_technical_data_tab() {
        echo 'Technical data';
    }

    public function product_description_tab() {
        global $product;

        echo $product->get_description();
    }

    public function custom_product_categories() {
        global $product;
        $product_id = $product->get_id();
        $product_categories = wp_get_post_terms($product_id, 'product_cat');

        if ($product_categories) {
            ?>
            <div class="flex flex-wrap product-categories mb-[13px] gap-3.5">
                <?php
                foreach ($product_categories as $category) {
                    ?>
                    <div class="py-1 px-3 bg-accent uppercase inline-block"><?php echo esc_html($category->name); ?></div>
                    <?php
                }
                ?>
            </div>
            <?php
        }
    }

    public function print_metals_and_accessories() {
        global $product;
        $product_id = $product->get_id();
        $product_tags = wp_get_post_terms($product_id, 'metals-and-accessories');

        if ($product_tags) {
            ?>
            <div class="flex flex-wrap metals-and-accessories mb-[31px]">
                <?php
                $category_names = array();
                foreach ($product_tags as $tag) {
                    $tag_names[] = esc_html($tag->name);
                }
                $tag_text = implode(' | ', $tag_names);
                ?>
                    <div class="uppercase inline-block"><?php echo esc_html($tag->name); ?></div>
                
            </div>
            <?php
        }
    }

    public function add_custom_text_field() {
        woocommerce_wp_text_input(array(
            'id' => '_custom_text_field',
            'label' => __('Custom Text Field', 'woocommerce'),
            'placeholder' => '',
            'desc_tip' => 'true',
            'description' => __('Enter the custom value here.', 'woocommerce'),
        ));
    }

    public function save_custom_text_field($post_id) {
        $custom_field_value = isset($_POST['_custom_text_field']) ? $_POST['_custom_text_field'] : '';
        if (!empty($custom_field_value)) {
            update_post_meta($post_id, '_custom_text_field', sanitize_text_field($custom_field_value));
        }
    }


    public function print_custom_text_field() {
        global $product;

        $custom_value = get_post_meta($product->get_id(), '_custom_text_field', true);

        if (!empty($custom_value)) {
            echo '<h3 class="text-black text-2xl font-bold leading-7 mb-3.5">' . esc_html($custom_value) . '</h3>';
        }
    }
}



