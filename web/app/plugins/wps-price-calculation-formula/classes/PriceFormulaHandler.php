<?php

namespace WPS\PriceCalculationFormula;

class PriceFormulaHandler
{

    public  function __construct(){

        // wordpress actions
        add_action('acf/save_post', [$this, 'updateOptionsPageTrigger'], 10, 2);
        add_action('save_post', [$this, 'updateProductTrigger'], 10, 3);

        // custom actions
        add_action(('wps_price_calculation_formula_update_options_page'), [$this, 'updateOptionsPageEvent']);
        add_action('wps_price_calculation_formula_update_single_product', [$this, 'updateSingleProductEvent']);
    }

    public function updateOptionsPageEvent(){

        $this->permissionCheck();
        //todo: get all products that need to be updated
    }

    public function updateSingleProductEvent(int $post_id){

        $this->permissionCheck();
        //todo: update single product price
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

    //todo: log every change into a log table
}