<?php

namespace WPS\frontend;

class WC_Customizations {

    public function __construct() {
        // Add the action to print product categories
        add_action('wps_print_product_categories', array($this, 'custom_product_categories'));

        add_action('wps_print_metals_and_accessories', array($this, 'print_metals_and_accessories'));

        add_filter( 'woocommerce_product_tabs', array($this, 'add_product_tabs'), 9999 );

        // hide product subheadline field from product data metabox - we replaced it with a ACF Field
        //add_action('woocommerce_product_options_general_product_data', array($this, 'add_subheadline_text_field'));
        //add_action('woocommerce_process_product_meta', array($this, 'save_subheadline_text_field'));
        //add_action('wps_print_subheadline_text_field', array($this, 'print_subheadline_text_field'));

        add_filter( 'woocommerce_product_data_tabs', array($this, 'add_technical_data_guide_product_tab') );
        add_action( 'woocommerce_product_data_panels', array($this, 'display_technical_data_guide_product_data_tab_content') );
        add_action( 'woocommerce_admin_process_product_object', array($this, 'save_technical_data_guide_fields_values') );
    }

    public function add_product_tabs($tabs) {
        $tabs['description'] = array(
            'title' => __( 'Description', 'wps-juniper' ), // TAB TITLE
            'priority' => 50,
            'callback' => array($this, 'product_description_tab'), // TAB CONTENT CALLBACK
        );

        $tabs['technical_data_guide'] = array(
            'title' => __( 'Technical Data', 'wps-juniper' ),
            // 'target' => 'technical_data_product_data',
            // 'class' => array( 'show_if_simple', 'show_if_variable' ),
            'priority' => 50,
            'callback' => array($this, 'product_technical_data_tab'),
        );

        return $tabs;
    }

    public function product_technical_data_tab() {
        global $product;
        echo wp_kses_post($product->get_meta('_technical_data'));
    }

    public function product_description_tab() {
        global $product;

        $html = '';
        $title = get_field('wps_sp_description_title', $product->get_id()) ?? '';
        $description = get_field('wps_sp_description_text', $product->get_id()) ?? '';
        $featureText = get_field('wps_sp_features_text', $product->get_id()) ?? '';
        $applicationText = get_field('wps_sp_areas_of_application_text', $product->get_id()) ?? '';
        $descriptionTitle = get_field('wps_sp_description_title', $product->get_id()) ?? '';

        if(!!$title || !!$description){
            $html .= '<div class="mb-6">';
            if(!!$title) $html .= "<h3>$title</h3>";
            if(!!$description) $html.= $description;
            $html .= '</div>';
        }

        if(!!$featureText){
            $html .= '<div class="mb-6">';
            $html .= '<h3>'.__('Eigenschaften & Vorteile', 'wps-juniper').'</h3>';
            $html .= $featureText;
            $html .= '</div>';
        }

        if(!!$applicationText){
            $html .= '<div class="mb-6">';
            $html .= '<h3>' . __('Anwendung', 'wps-juniper') . '</h3>';
            $html .= $applicationText;
            $html .= '</div>';
        }

        if($descriptionTitle){
            $html .= '<div class="mb-6">';
            $html .= '<h3>'.__('Downloads', 'wps-juniper').'</h3>';
            $html .= $descriptionTitle;
            $html .= '</div>';
        }

        echo $html;
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
                $tag_names = array(); // Initialize $tag_names array
                foreach ($product_tags as $tag) {
                    $tag_names[] = esc_html($tag->name); // Add tag name to $tag_names array
                }
                $tag_text = implode(' | ', $tag_names); // Create a string from the $tag_names array
                ?>
                <div class="uppercase inline-block"><?php echo $tag_text; ?></div> <!-- Display all tag names -->
            </div>
            <?php
        }
    }

    public function add_subheadline_text_field() {
        woocommerce_wp_text_input(array(
            'id' => '_subheadline_text_field',
            'label' => __('Subheadline for the Product', 'woocommerce'),
            'placeholder' => '',
            'desc_tip' => 'true',
            'description' => __('Enter the subheadline for the product, which will be shown below the Header.', 'woocommerce'),
        ));
    }

    public function save_subheadline_text_field($post_id) {
        $custom_field_value = isset($_POST['_subheadline_text_field']) ? $_POST['_subheadline_text_field'] : '';
        if (!empty($custom_field_value)) {
            update_post_meta($post_id, '_subheadline_text_field', sanitize_text_field($custom_field_value));
        }
    }


    public function print_subheadline_text_field() {
        global $product;

        $custom_value = get_post_meta($product->get_id(), '_subheadline_text_field', true);

        if (!empty($custom_value)) {
            echo '<h2 class="text-black text-2xl font-bold mb-3.5">' . esc_html($custom_value) . '</h2>';
        }
    }



    public function add_technical_data_guide_product_tab($tabs) {
        $tabs['technical_data_guide'] = array(
            'label'    => __( 'Technical Data', 'text-domain' ),
            'target'   => 'technical_data_product_data',
            'class'    => array( 'show_if_simple', 'show_if_variable' ),
        );
        return $tabs;
    }

    public function display_technical_data_guide_product_data_tab_content() {
        global $product_object;

        echo '<div id="technical_data_product_data" class="panel woocommerce_options_panel">
        <div class="options_group px-2">';

        echo '<h4>Technical Data for the product "<strong>'.$product_object->get_name().'</strong>"…</h4>';

        wp_editor(
            htmlspecialchars_decode($product_object->get_meta('_technical_data')), 
            '_technical_data', 
            array(
                'wpautop' => false,
                'media_buttons' => true,
                'textarea_name' => '_technical_data',
                'textarea_rows' => 15, 
            )
        );

        echo '</div></div>';
    }

    public function save_technical_data_guide_fields_values( $product ) {
        $technical_data = isset( $_POST['_technical_data'] ) ? wp_kses_post($_POST['_technical_data']) : '';
        $product->update_meta_data( '_technical_data', $technical_data );
    }
    

}
