<?php

namespace Vendidero\Germanized\GLS;

use DateTime;
use DateTimeZone;
use Exception;
use Vendidero\Germanized\GLS\Api\Api;
use Vendidero\Germanized\GLS\Label\Simple;
use Vendidero\Germanized\Shipments\Shipment;
use Vendidero\Germanized\Shipments\ShippingProvider\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Main package class.
 */
class Package {

	/**
	 * Version.
	 *
	 * @var string
	 */
	const VERSION = '1.1.1';

	protected static $api = null;

	protected static $iso = null;

	/**
	 * Init the package - load the REST API Server class.
	 */
	public static function init() {
		if ( self::has_dependencies() ) {
			// Add shipping provider
			add_filter( 'woocommerce_gzd_shipping_provider_class_names', array( __CLASS__, 'add_shipping_provider_class_name' ), 20, 1 );
		}

		if ( ! did_action( 'woocommerce_gzd_shipments_init' ) ) {
			add_action( 'woocommerce_gzd_shipments_init', array( __CLASS__, 'on_shipments_init' ), 20 );
		} else {
			self::on_shipments_init();
		}
	}

	public static function on_shipments_init() {
		if ( ! self::has_dependencies() ) {
			return;
		}

		self::includes();

		if ( self::is_enabled() ) {
			self::init_hooks();
		}
	}

	public static function has_dependencies() {
		return ( class_exists( 'WooCommerce' ) && class_exists( '\Vendidero\Germanized\Shipments\Package' ) && self::base_country_is_supported() && apply_filters( 'woocommerce_gzd_gls_enabled', true ) );
	}

	public static function base_country_is_supported() {
		return in_array( self::get_base_country(), self::get_supported_countries(), true );
	}

	public static function get_supported_countries() {
		return apply_filters( 'woocommerce_gzd_gls_supported_countries', array( 'DE', 'AT', 'CH', 'BE', 'LU', 'FR', 'IE', 'ES' ) );
	}

	public static function get_date_de_timezone( $format = 'Y-m-d' ) {
		try {
			$tz_obj         = new DateTimeZone( 'Europe/Berlin' );
			$current_date   = new DateTime( 'now', $tz_obj );
			$date_formatted = $current_date->format( $format );

			return $date_formatted;
		} catch ( Exception $e ) {
			return date( $format ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
		}
	}

	public static function is_enabled() {
		return ( self::is_gls_enabled() );
	}

	public static function is_gls_enabled() {
		$is_enabled = false;

		if ( method_exists( '\Vendidero\Germanized\Shipments\ShippingProvider\Helper', 'is_shipping_provider_activated' ) ) {
			$is_enabled = Helper::instance()->is_shipping_provider_activated( 'gls' );
		} else {
			if ( $provider = self::get_gls_shipping_provider() ) {
				$is_enabled = $provider->is_activated();
			}
		}

		return $is_enabled;
	}

	public static function get_api_username() {
		if ( self::is_debug_mode() && defined( 'WC_GZD_GLS_API_USERNAME' ) ) {
			return WC_GZD_GLS_API_USERNAME;
		} else {
			return self::get_gls_shipping_provider()->get_api_username();
		}
	}

	public static function get_api_password() {
		if ( self::is_debug_mode() && defined( 'WC_GZD_GLS_API_PASSWORD' ) ) {
			return WC_GZD_GLS_API_PASSWORD;
		} else {
			return self::get_gls_shipping_provider()->get_setting( 'api_password' );
		}
	}

	public static function get_api_contact_id() {
		if ( self::is_debug_mode() && defined( 'WC_GZD_GLS_API_CONTACT_ID' ) ) {
			return WC_GZD_GLS_API_CONTACT_ID;
		} else {
			return self::get_gls_shipping_provider()->get_setting( 'api_contact_id' );
		}
	}

	public static function get_api_url() {
		$api_url = '';

		if ( self::is_debug_mode() && defined( 'WC_GZD_GLS_API_URL' ) ) {
			$api_url = WC_GZD_GLS_API_URL;
		} else {
			$api_url_id = self::get_gls_shipping_provider()->get_setting( 'api_url' );
			$api_urls   = self::get_available_api_urls();

			if ( array_key_exists( $api_url_id, $api_urls ) ) {
				$api_url = $api_urls[ $api_url_id ];
			}
		}

		return apply_filters( 'woocommerce_gzd_gls_api_url', $api_url );
	}

	public static function get_available_api_urls() {
		if ( 'AT' === self::get_base_country() ) {
			$urls = array(
				'at01' => 'https://shipit-wbm-at01.gls-group.eu',
			);
		} else {
			$urls = array(
				'de01'  => 'https://shipit-wbm-de01.gls-group.eu',
				'de02'  => 'https://shipit-wbm-de02.gls-group.eu',
				'de03'  => 'https://shipit-wbm-de03.gls-group.eu',
				'de04'  => 'https://shipit-wbm-de04.gls-group.eu',
				'de05'  => 'https://shipit-wbm-de05.gls-group.eu',
				'de07'  => 'https://shipit-wbm-de07.gls-group.eu',
				'int01' => 'https://shipit-wbm-int01.gls-group.eu',
				'de08'  => 'https://wbm-de08.shipit.gls-group.com',
			);
		}

		return apply_filters( 'woocommerce_gzd_gls_available_api_urls', $urls );
	}

	public static function get_domestic_products( $is_return = false ) {
		if ( $is_return ) {
			return array(
				'PARCEL' => _x( 'Parcel', 'gls', 'woocommerce-germanized-pro' ),
			);
		} else {
			return array(
				'PARCEL'  => _x( 'Parcel', 'gls', 'woocommerce-germanized-pro' ),
				'EXPRESS' => _x( 'Express', 'gls', 'woocommerce-germanized-pro' ),
			);
		}
	}

	public static function get_eu_products( $is_return = false ) {
		if ( $is_return ) {
			return array(
				'PARCEL' => _x( 'Parcel', 'gls', 'woocommerce-germanized-pro' ),
			);
		} else {
			return array(
				'PARCEL'  => _x( 'Parcel', 'gls', 'woocommerce-germanized-pro' ),
				'EXPRESS' => _x( 'Express', 'gls', 'woocommerce-germanized-pro' ),
			);
		}
	}

	public static function get_international_products( $is_return = false ) {
		if ( $is_return ) {
			return array(
				'PARCEL' => _x( 'Parcel', 'gls', 'woocommerce-germanized-pro' ),
			);
		} else {
			return array(
				'PARCEL'  => _x( 'Parcel', 'gls', 'woocommerce-germanized-pro' ),
				'EXPRESS' => _x( 'Express', 'gls', 'woocommerce-germanized-pro' ),
			);
		}
	}

	public static function get_return_types() {
		return array(
			'shop_return'     => _x( 'Shop Return', 'gls', 'woocommerce-germanized-pro' ),
			'pick_and_return' => _x( 'Pick & Return', 'gls', 'woocommerce-germanized-pro' ),
		);
	}

	/**
	 * @return Api
	 */
	public static function get_api() {
		$api = \Vendidero\Germanized\GLS\Api\Api::instance();

		if ( self::is_debug_mode() ) {
			$api::dev();
		} else {
			$api::prod();
		}

		return $api;
	}

	private static function includes() {

	}

	public static function init_hooks() {
		// Filter templates
		add_filter( 'woocommerce_gzd_default_plugin_template', array( __CLASS__, 'filter_templates' ), 10, 3 );
	}

	public static function filter_templates( $path, $template_name ) {
		if ( file_exists( self::get_path() . '/templates/' . $template_name ) ) {
			$path = self::get_path() . '/templates/' . $template_name;
		}

		return $path;
	}

	/**
	 * @return false
	 */
	public static function get_gls_shipping_provider() {
		$provider = wc_gzd_get_shipping_provider( 'gls' );

		if ( ! is_a( $provider, '\Vendidero\Germanized\GLS\ShippingProvider\GLS' ) ) {
			return false;
		}

		return $provider;
	}

	public static function add_shipping_provider_class_name( $class_names ) {
		$class_names['gls'] = '\Vendidero\Germanized\GLS\ShippingProvider\GLS';

		return $class_names;
	}

	public static function install() {
		self::on_shipments_init();
		Install::install();
	}

	public static function install_integration() {
		self::install();
	}

	public static function is_integration() {
		return class_exists( 'WooCommerce_Germanized' ) ? true : false;
	}

	/**
	 * Return the version of the package.
	 *
	 * @return string
	 */
	public static function get_version() {
		return self::VERSION;
	}

	/**
	 * Return the path to the package.
	 *
	 * @return string
	 */
	public static function get_path() {
		return dirname( __DIR__ );
	}

	public static function get_template_path() {
		return 'woocommerce-germanized/';
	}

	/**
	 * Return the path to the package.
	 *
	 * @return string
	 */
	public static function get_url() {
		return plugins_url( '', __DIR__ );
	}

	public static function get_assets_url() {
		return self::get_url() . '/assets';
	}

	public static function is_debug_mode() {
		$is_debug_mode = ( defined( 'WC_GZD_GLS_DEBUG' ) && WC_GZD_GLS_DEBUG );

		return $is_debug_mode;
	}

	public static function enable_logging() {
		return ( defined( 'WC_GZD_GLS_LOG_ENABLE' ) && WC_GZD_GLS_LOG_ENABLE ) || self::is_debug_mode();
	}

	private static function define_constant( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	public static function log( $message, $type = 'info' ) {
		$logger         = wc_get_logger();
		$enable_logging = self::enable_logging() ? true : false;

		if ( ! $logger ) {
			return false;
		}

		/**
		 * Filter that allows adjusting whether to enable or disable
		 * logging for the DPD package (e.g. API requests).
		 *
		 * @param boolean $enable_logging True if logging should be enabled. False otherwise.
		 *
		 * @package Vendidero/Germanized/DPD
		 */
		if ( ! apply_filters( 'woocommerce_gzd_gls_enable_logging', $enable_logging ) ) {
			return false;
		}

		if ( ! is_callable( array( $logger, $type ) ) ) {
			$type = 'info';
		}

		$logger->{$type}( $message, array( 'source' => 'woocommerce-germanized-gls' ) );

		return true;
	}

	public static function get_available_incoterms() {
		return array(
			'10' => _x( 'DDP', 'gls', 'woocommerce-germanized-pro' ),
			'20' => _x( 'DAP', 'gls', 'woocommerce-germanized-pro' ),
			'30' => _x( 'DDP, VAT unpaid', 'gls', 'woocommerce-germanized-pro' ),
			'40' => _x( 'DAP, cleared', 'gls', 'woocommerce-germanized-pro' ),
			'50' => _x( 'DDP, small packages', 'gls', 'woocommerce-germanized-pro' ),
		);
	}

	public static function get_base_country() {
		$base_location = wc_get_base_location();
		$base_country  = $base_location['country'];

		/**
		 * Filter to adjust the DPD base country.
		 *
		 * @param string $country The country as ISO code.
		 *
		 * @since 3.0.0
		 * @package Vendidero/Germanized/DPD
		 */
		return apply_filters( 'woocommerce_gzd_gls_base_country', $base_country );
	}
}
