<?php

namespace wps\bmd;

class Plugin
{
    public function __construct()
    {
        add_action('init', [$this, 'initAction']);
    }

    public function initAction()
    {
        add_action( 'woocommerce_thankyou', [$this, 'newOrderAction'], 10, 1 );
    }

    public function newOrderAction(int $orderID){

        if(!$orderID) return;

        // Getting an instance of the order object
        $order = wc_get_order( $orderID );

        $exporter = new Exporter();
        $exporter->processOrder($order);

    }


}