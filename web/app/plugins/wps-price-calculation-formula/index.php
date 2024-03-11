<?php

/**
 * wps-price-calculation-formula
 *
 * @package           PluginPackage
 * @author            wp-stars
 * @copyright         2024 wp-stars gmbh
 *
 * @wordpress-plugin
 * Plugin Name:       WPS Price Calculation Formula
 * Plugin URI:        https://wp-stars.com
 * Description:       Extends WooCommerce with a price calculation formula
 * Version:           1.0.0
 * Requires PHP:      8.2
 * Author:            wp-stars gmbh
 * Author URI:        https://wp-stars.com
 * Text Domain:       wps-price-calculation-formula
 */

use WPS\PriceCalculationFormula\PriceFormulaHandler;

require_once 'classes/Plugin.php';
require_once 'classes/PriceFormulaHandler.php';
require_once 'classes/FormulaProduct.php';
require_once 'classes/TransformationVariablesRepository.php';
require_once 'classes/Logger.php';

new WPS\PriceCalculationFormula\Plugin(__DIR__);

register_activation_hook( __FILE__, function(){

    // create the logging tables
    WPS\PriceCalculationFormula\Logger::createPriceLogTable();
    WPS\PriceCalculationFormula\Logger::createFormulaVariablesLogTable();

});