<?php

namespace wps\bmd;

class Plugin
{
    private string $optionsPageName = 'BMD API Export';
    private string $optionsPageSlug = 'wps-bmd-api-settings';
    private string $dir = '';

    public function __construct(string $dir)
    {
        $this->dir = $dir;
        add_action('init', [$this, 'initAction']);
        add_action('admin_menu', [$this, 'adminMenuAction']);
    }

    public function initAction()
    {
        add_action( 'woocommerce_thankyou', [$this, 'handleSale'], 10, 1 );
    }

    public function adminMenuAction(){
        add_submenu_page(
            'tools.php',
            $this->optionsPageName,
            $this->optionsPageName,
            'manage_options',
            $this->optionsPageSlug,
            [$this, 'optionsPage']
        );
    }

    public function optionsPage(){

        echo '<div class="wrap">';
        include $this->dir . '/views/order-selector.html';

        if(isset($_GET['order-selector'])){
            $orderID = (int) sanitize_text_field($_GET['order-selector']);
            $this->handleSale($orderID, false, false);
        }

        echo '</div>';
    }

    public function handleSale(int $orderID, bool $silent = true, bool $storeFile = true){

        if(!$orderID) return;

        // Getting an instance of the order object
        $order = wc_get_order( $orderID );

        if(false === $order) {
            if(false === $silent){

                if(function_exists('dump')){
                    dump('order not found');
                }else{
                    echo '<pre>';
                    var_dump('order not found');
                    echo '</pre>';
                }
            }
            return;
        }

        // setup exporter instance
        $exporter = new Exporter();
        $exporter->readOrder($order)->createXML();

        // output for testing and debugging (tools/BMD API Export)
        if(false === $silent){
            echo '<p><strong>XML Upload Pfad:</strong> ' . $exporter->getUploadDir() . '</p>';
            echo '<h2>Daten aus der Order #' . $orderID . '</h2>';

            if(function_exists('dump')){
                dump($exporter->data);
            }else{
                echo '<pre>';
                var_dump($exporter->data);
                echo '</pre>';
            }

            echo '<h2>Erzeugtes XML aus der Order #' . $orderID . '</h2>';
            echo '<pre style="overflow-x: auto; border-right: solid 15px #ccc; background-color: #ccc; padding: 15px;">' . htmlentities($exporter->formatXML()) . '</pre>';
        }

        // store xml to upload dir
        if(true === $storeFile){
            $exporter->storeXML();
        }

    }


}