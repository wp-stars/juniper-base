<?php

namespace WPS\PriceCalculationFormula;

class Logger
{

    public static function getLatestProductUpdates(int $productID, int $limit = 5){
        global $wpdb;

        $table_name = $wpdb->prefix . 'wps_price_calculation_product_price_log';
        $sql = "SELECT * FROM $table_name WHERE product_id = $productID ORDER BY date DESC LIMIT $limit";

        return $wpdb->get_results($sql);
    }

    public static function optionsPageUpdate(){

        global $wpdb;

        $table_name = $wpdb->prefix . 'wps_price_calculation_transformation_value_log';
        $user_id = get_current_user_id();

        $transformationVariablesRepository = new TransformationVariablesRepository();
        $transformationVariablesRepository->init();

        $new_values = json_encode($transformationVariablesRepository->get(), JSON_THROW_ON_ERROR);

        $date = new \DateTime('now', new \DateTimeZone('Europe/Berlin'));
        $date = $date->format('Y-m-d H:i:s');

        $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'new_values' => $new_values,
                'date' => $date
            )
        );
    }

    public static function productUpdate(int $post_id, bool $system = false){
        global $wpdb;

        $table_name = $wpdb->prefix . 'wps_price_calculation_product_price_log';

        $user_id = get_current_user_id();

        if($system === true){
            $user_id = 0;
        }

        $product = wc_get_product($post_id);
        $new_price = $product->get_price();

        $date = new \DateTime('now', new \DateTimeZone('Europe/Berlin'));
        $date = $date->format('Y-m-d H:i:s');

        $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'product_id' => $post_id,
                'new_price' => $new_price,
                'date' => $date
            )
        );
    }

    public static function createPriceLogTable()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wps_price_calculation_product_price_log';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id mediumint(9) NOT NULL,
            product_id mediumint(9) NOT NULL,
            new_price decimal(10,2) NOT NULL,
            date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public static function createFormulaVariablesLogTable()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wps_price_calculation_transformation_value_log';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id mediumint(9) NOT NULL,
            new_values longtext NOT NULL, 
            date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}