<?php

namespace WPS\PriceCalculationFormula;

class PriceFormulaHandler
{

    private TransformationVariablesRepository|null $transformationVariablesRepository = null;

    public  function __construct(){

        // wordpress actions
        add_action('acf/save_post', [$this, 'updateOptionsPageTrigger'], 10, 2);
        add_action('save_post', [$this, 'updateProductTrigger'], 10, 3);

        // custom actions
        add_action(('wps_price_calculation_formula_update_options_page'), [$this, 'updateOptionsPageEvent']);
        add_action('wps_price_calculation_formula_update_single_product', [$this, 'updateSingleProductEvent']);

        $this->transformationVariablesRepository = new TransformationVariablesRepository();
    }

    public function updateOptionsPageEvent(){

        // log the event
        Logger::optionsPageUpdate();

        $this->permissionCheck();

        // get all products via $wpdb
        global $wpdb;

        $query = "SELECT ID 
                  FROM {$wpdb->posts} 
                  WHERE post_type = 'product' 
                  AND post_status = 'publish'";

        $products = $wpdb->get_results($query);

        if(is_array($products) && count($products) > 0){

            // update values inside the transformationVariablesRepository
            $this->transformationVariablesRepository->init();

            foreach($products as $product){
                $product = new FormulaProduct($product->ID, $this->transformationVariablesRepository);
                $product->updatePrice(true);
            }
        }
    }

    public function updateSingleProductEvent(int $post_id){

        $this->permissionCheck();
        $product = new FormulaProduct($post_id, $this->transformationVariablesRepository);
        $product->updatePrice(false);
    }

    public function updateProductTrigger($post_id, $post, $update){

        if($post->post_type !== 'product'){
            return;
        }

        // trigger the action that updates the product price
        if(function_exists('get_field') && get_field('iwg_price_formular_active', $post->ID) == true){
            do_action('wps_price_calculation_formula_update_single_product', $post->ID);
        }
    }

    public function updateOptionsPageTrigger(int|string $post_id){

        $screen = get_current_screen();

        if(!$screen instanceof \WP_Screen){
            return;
        }

        if($screen->id !== 'toplevel_page_wps-price-calculation-formula-settings'){
            return;
        }

        // trigger the action that updates all the product prices
        do_action('wps_price_calculation_formula_update_options_page');
    }

    public function permissionCheck(){
        if(!current_user_can('edit_posts')){
            $this->abort404();
        }
    }

    public function abort404(){
        global $wp_query;

        $wp_query->set_404();
        status_header( 404 );
        get_template_part( 404 );

        exit();
    }
}