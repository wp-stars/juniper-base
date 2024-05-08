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
            'title' => __( 'Beschreibung', 'wps-juniper' ), // TAB TITLE
            'priority' => 50,
            'callback' => array($this, 'product_description_tab'), // TAB CONTENT CALLBACK
        );

        $tabs['technical_data_guide'] = array(
            'title' => __( 'Technische Daten', 'wps-juniper' ),
            // 'target' => 'technical_data_product_data',
            // 'class' => array( 'show_if_simple', 'show_if_variable' ),
            'priority' => 50,
            'callback' => array($this, 'product_technical_data_tab'),
        );

        return $tabs;
    }

    public function product_technical_data_tab() {
        global $product;
        if(!!$product && !!($productMeta = $product->get_meta('_technical_data'))){
            echo '<div class="technical-data-tab-content">';
            echo wp_kses_post($productMeta);
            echo '</div>';
        }
    }

    public function buildDescriptionTagList(&$tagList, $productID, $taxonomy){
        $terms = wp_get_post_terms( $productID, $taxonomy);
        if(!!$terms && !$terms instanceof WP_Error){
            array_filter($terms, function($term) use (&$tagList){
                $tagList[] = $term->name;
            });
        }
    }

    public function product_description_tab() {
        global $product;

        $html = '';
        $productID = $product->get_id();

        $title = get_field('wps_sp_description_title', $productID) ?? '';
        $description = get_field('wps_sp_description_text', $productID) ?? '';
        $featureText = get_field('wps_sp_features_text', $productID) ?? '';
        $applicationText = get_field('wps_sp_areas_of_application_text', $productID) ?? '';
        $descriptionTitle = get_field('wps_sp_description_title', $productID) ?? '';
        $downloads = get_field('wps_sp_downloads', $productID) ?? '';

        $icon = '<svg class="w-4 h-4 inline" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none">
                    <path d="M16.6922 6.43281L12.3172 2.05781C12.2591 1.99979 12.1902 1.95378 12.1143 1.92241C12.0384 1.89105 11.9571 1.87494 11.875 1.875H4.375C4.04348 1.875 3.72554 2.0067 3.49112 2.24112C3.2567 2.47554 3.125 2.79348 3.125 3.125V16.875C3.125 17.2065 3.2567 17.5245 3.49112 17.7589C3.72554 17.9933 4.04348 18.125 4.375 18.125H15.625C15.9565 18.125 16.2745 17.9933 16.5089 17.7589C16.7433 17.5245 16.875 17.2065 16.875 16.875V6.875C16.8751 6.7929 16.859 6.71159 16.8276 6.63572C16.7962 6.55985 16.7502 6.4909 16.6922 6.43281ZM12.5 4.00859L14.7414 6.25H12.5V4.00859ZM15.625 16.875H4.375V3.125H11.25V6.875C11.25 7.04076 11.3158 7.19973 11.4331 7.31694C11.5503 7.43415 11.7092 7.5 11.875 7.5H15.625V16.875ZM12.3172 12.0578C12.3753 12.1159 12.4214 12.1848 12.4529 12.2607C12.4843 12.3365 12.5005 12.4179 12.5005 12.5C12.5005 12.5821 12.4843 12.6635 12.4529 12.7393C12.4214 12.8152 12.3753 12.8841 12.3172 12.9422L10.4422 14.8172C10.3841 14.8753 10.3152 14.9214 10.2393 14.9529C10.1635 14.9843 10.0821 15.0005 10 15.0005C9.91787 15.0005 9.83654 14.9843 9.76066 14.9529C9.68479 14.9214 9.61586 14.8753 9.55781 14.8172L7.68281 12.9422C7.56554 12.8249 7.49965 12.6659 7.49965 12.5C7.49965 12.3341 7.56554 12.1751 7.68281 12.0578C7.80009 11.9405 7.95915 11.8747 8.125 11.8747C8.29085 11.8747 8.44991 11.9405 8.56719 12.0578L9.375 12.8664V9.375C9.375 9.20924 9.44085 9.05027 9.55806 8.93306C9.67527 8.81585 9.83424 8.75 10 8.75C10.1658 8.75 10.3247 8.81585 10.4419 8.93306C10.5592 9.05027 10.625 9.20924 10.625 9.375V12.8664L11.4328 12.0578C11.4909 11.9997 11.5598 11.9536 11.6357 11.9221C11.7115 11.8907 11.7929 11.8745 11.875 11.8745C11.9571 11.8745 12.0385 11.8907 12.1143 11.9221C12.1902 11.9536 12.2591 11.9997 12.3172 12.0578Z" fill="black"/>
                </svg>';


        // render taglist

        $tagList = array();
        $this->buildDescriptionTagList($tagList, $productID, 'product_cat');
        $this->buildDescriptionTagList($tagList, $productID, 'metals-and-accessories');
        $this->buildDescriptionTagList($tagList, $productID, 'application');
        $this->buildDescriptionTagList($tagList, $productID, 'color');
        $this->buildDescriptionTagList($tagList, $productID, 'anwendung');
        //$this->buildDescriptionTagList($tagList, $productID, 'product_tag');
        //$this->buildDescriptionTagList($tagList, $productID, 'purchasability');

        $html .= '<div class="show-list-of-tags-and-categories mb-8 italic">';
        $html .= implode(' | ', array_unique($tagList));
        $html .= '</div>';

        // render content

        if(!!$title || !!$description){
            $html .= '<div class="mb-10">';
            if(!!$title) $html .= "<h3>$title</h3>";
            if(!!$description) $html.= $description;
            $html .= '</div>';
        }

        if(!!$featureText){
            $html .= '<div class="mb-10">';
            $html .= '<h3 class="mb-4">'.__('Eigenschaften & Vorteile', 'wps-juniper').'</h3>';
            $html .= $featureText;
            $html .= '</div>';
        }

        if(!!$applicationText){
            $html .= '<div class="mb-10">';
            $html .= '<h3 class="mb-4">' . __('Anwendung', 'wps-juniper') . '</h3>';
            $html .= $applicationText;
            $html .= '</div>';
        }

        if(is_array($downloads) && count($downloads) > 0){
            $html .= '<div class="mb-10">';
            $html .= '<h3 class="mb-4">'.__('Downloads', 'wps-juniper').'</h3>';
            $html .= '<ul class="list-none no-before-element">';
            foreach ($downloads as $download){
                $download = $download['pdf'];
                $file = $download['url'] ?? '';
                $title = $download['filename'] ?? '';
                $html .= "<li>$icon <a class='no-underline hover:underline focus:underline' href='$file' target='_blank'>$title</a></li>";
            }
            $html .= '</ul>';
            $html .= '</div>';
        }

        echo $html;
    }

    public function custom_product_categories() {
        global $product;

        $tags = [];

        $product_id = $product->get_id();
        $product_categories = wp_get_post_terms($product_id, 'product_cat');
        $isNew = has_term( 'neu', 'product_tag', $product_id );

        // add the new tag to the tags array
        if($isNew){
            $tags[] = [
                'label' => __('Neu', 'wps'),
                'color' => '#FFEB00',
                'class' => 'font-bold'
            ];
        }

        // add the product categories to the tags array
        if(!!$product_categories && is_array($product_categories) && count($product_categories) > 0){
            foreach ($product_categories as $category) {

                $color = get_field('wps_product_cat_color', 'product_cat_'.$category->term_id) ?? '#ffffff';

                $tags[] = [
                    'label' => esc_html($category->name),
                    'color' => $color,
                    'class' => ''
                ];
            }
        }

        // print the tags to the template
        if(count($tags) > 0){
            echo '<div class="flex flex-wrap product-categories mb-[13px] gap-3.5">';
            foreach ($tags as $tag) {

                extract($tag);
                $borderColor = $color;
                $textColor = '#000';

                if($color === '#ffffff') $borderColor = '#000';

                $style = "style='background-color: $color; border: solid 1px $borderColor !important; color: $textColor;'";

                echo "<div class='py-2 px-2 uppercase inline-block $class' $style>$label</div>";

            }
            echo '</div>';
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