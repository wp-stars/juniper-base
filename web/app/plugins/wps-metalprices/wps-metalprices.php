<?php 
/**
* Plugin.
*
* @package reactplug
* @wordpress-plugin
*
* Plugin Name:     WPS Metalprices Shortcode
* Description:     Embedded react app for IWG
* Author:          WPS Will Nahmens
* Author URL:      https://wp-stars.com
* Version:         1.0
*/

function wpsMetalPrices() {
    wp_enqueue_script( 'metalprices-script' );

    ob_start(); ?>
    <div id="reactNewMetalprices"></div>
    <?php return ob_get_clean();
}
add_shortcode('wpsMetalPrices', 'wpsMetalPrices'); 

add_action('wp_enqueue_scripts', 'enq_react');
function enq_react(){
    wp_enqueue_style(
        'metalprices-style',
        plugin_dir_url( __FILE__ ) . '/build/index.css',
        [],
        filemtime(plugin_dir_path( __FILE__ ) . '/build/index.css')
    );


    wp_register_script('metalprices-script',
        plugin_dir_url( __FILE__ ) . '/build/index.js',
        ['wp-element'],
        time(), // Change this to null for production
        true
    );
    $current_user = wp_get_current_user();
    $wp_vars = [
        'restUrl' => get_rest_url(),
        'metals' => [
            ['key' => 'gold', 'label' => __('Gold', 'iwgplating'), 'number' => 79, 'short' => 'Au', 'unit' => '€/g'],
            ['key' => 'silver', 'label' => __('Silver', 'iwgplating'), 'number' => 47, 'short' => 'Ag', 'unit' => '€/kg'],
            ['key' => 'platin', 'label' => __('Platin', 'iwgplating'), 'number' => 78, 'short' => 'Pt', 'unit' => '€/g'],
            ['key' => 'palladium', 'label' => __('Palladium', 'iwgplating'), 'number' => 46, 'short' => 'Pd', 'unit' => '€/g'],
            ['key' => 'rhodium', 'label' => __('Rhodium', 'iwgplating'), 'number' => 45, 'short' => 'Rh', 'unit' => '€/g'],
            ['key' => 'ruthenium', 'label' => __('Ruthenium', 'iwgplating'), 'number' => 44, 'short' => 'Ru', 'unit' => '€/g'],
        ],
        'ranges' => [
            ['key' => 'day', 'label' => __('Daily value', 'iwgplating'), 'days' => 1],
            ['key' => 'week', 'label' => __('Weekly value', 'iwgplating'), 'days' => 7],
            ['key' => 'month', 'label' => __('Monthly value', 'iwgplating'), 'days' => 30],
            ['key' => 'year', 'label' => __('Annual value', 'iwgplating'), 'days' => 365],
        ],
    ];
    wp_localize_script( 'metalprices-script', 'wpVars', $wp_vars ); //localize script to pass PHP data to JS
}
