<?php
/*
Plugin Name: WP-Stars Debugger
Description: Dieses Plugin stellt die PHP funktion debug() zur Verfügung, um Variablen und Objekte zu debuggen.
Version: 1.1.0
Author: WP-Stars | Michael Ritsch (MRX)
Author URI: https://wp-stars.com/
Text Domain: wps-debugger
*/

// disallow direct execution
defined( 'ABSPATH' ) or die();

require __DIR__ . '/vendor/autoload.php';