<?php

namespace WPS\frontend;

class WC_Customizations {

    public function __construct() {
        // Add the action to print product categories
        add_action('wps_print_product_categories', array($this, 'custom_product_categories'));

        add_action('wps_print_metals_and_accessories', array($this, 'print_metals_and_accessories'));
    }

    public function custom_product_categories() {
        if (function_exists('wc_get_product_category_list')) {
            echo '<div class="inline-flex gap-3 product-taxonomies">
                    <span class="py-1 px-3 bg-accent uppercase">Neu</span>
                    <span class="py-1 px-3 bg-accent uppercase">Edelmetall Elektroyte</span>
                    <span class="py-1 px-3 bg-lightgray uppercase">Edelmetallverbingung</span>
                </div>';
        }
    }

    public function print_metals_and_accessories() {
        echo 'Metals and Accessories';
    }
}

