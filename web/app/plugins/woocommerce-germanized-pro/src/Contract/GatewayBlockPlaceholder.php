<?php
namespace Vendidero\Germanized\Pro\Contract;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use Vendidero\Germanized\Pro\Blocks\Assets;

final class GatewayBlockPlaceholder extends AbstractPaymentMethodType {

	/**
	 * @var string
	 */
	protected $name = 'placeholder';

	/**
	 * @var Assets
	 */
	protected $assets = null;

	protected $original_gateway = null;

	/**
	 * @param AbstractPaymentMethodType|\WC_Payment_Gateway $original_gateway
	 * @param Assets $assets
	 */
	public function __construct( $original_gateway, $assets ) {
		$this->original_gateway = $original_gateway;
		$this->assets           = $assets;
		$this->name             = 'placeholder_' . $this->get_gateway_name();
	}

	public function get_gateway_name() {
		if ( is_a( $this->original_gateway, 'WC_Payment_Gateway' ) ) {
			$name = $this->original_gateway->id;
		} else {
			$name = $this->original_gateway->get_name();
		}

		return $name;
	}

	public function initialize() {
		if ( ! is_a( $this->original_gateway, 'WC_Payment_Gateway' ) ) {
			$this->original_gateway->initialize();
		}
	}

	public function get_setting( $name, $default = '' ) {
		if ( is_a( $this->original_gateway, 'WC_Payment_Gateway' ) ) {
			return $this->original_gateway->get_option( $name, $default );
		} else {
			return $this->original_gateway->get_setting( $name, $default );
		}
	}

	/**
	 * Returns if this payment method should be active. If false, the scripts will not be enqueued.
	 *
	 * @return boolean
	 */
	public function is_active() {
		if ( is_a( $this->original_gateway, 'WC_Payment_Gateway' ) ) {
			return $this->original_gateway->is_available();
		} else {
			return $this->original_gateway->is_active();
		}
	}

	/**
	 * Returns an array of scripts/handles to be registered for this payment method.
	 *
	 * @return array
	 */
	public function get_payment_method_script_handles() {
		$this->assets->register_script(
			'wc-gzdp-payment-method-placeholder',
			'build/wc-gzdp-payment-method-placeholder.js'
		);

		return array( 'wc-gzdp-payment-method-placeholder' );
	}

	/**
	 * Returns an array of key=>value pairs of data made available to the payment methods script.
	 *
	 * @return array
	 */
	public function get_payment_method_data() {
		/**
		 * Some gateways, e.g. Stripe call WC()->payment_gateways->get_available_payment_gateways()
		 * to get the supported options of the original gateway. Remove our custom filter to make sure
		 * that the lookup does not fail.
		 */
		\WC_GZDP_Contract_Helper::instance()->remove_gateway_filter();

		if ( is_a( $this->original_gateway, 'WC_Payment_Gateway' ) ) {
			$method_data = array(
				'title'    => $this->original_gateway->title,
				'supports' => $this->original_gateway->supports,
			);
		} else {
			$method_data = $this->original_gateway->get_payment_method_data();
		}

		\WC_GZDP_Contract_Helper::instance()->remove_gateway_filter();

		$data = array_replace_recursive(
			array(
				'name'        => $this->get_gateway_name(),
				'description' => $this->get_setting( 'description' ),
			),
			$method_data
		);

		return $data;
	}

	/**
	 * Call child methods if the method does not exist.
	 *
	 * @param $method
	 * @param $args
	 *
	 * @return bool|mixed
	 */
	public function __call( $method, $args ) {
		return call_user_func_array( array( $this->original_gateway, $method ), $args );
	}

	/**
	 * __isset legacy.
	 * @param mixed $key
	 * @return bool
	 */
	public function __isset( $key ) {
		return isset( $this->original_gateway->{$key} );
	}

	/**
	 * __get function.
	 * @param string $key
	 * @return string
	 */
	public function __get( $key ) {
		return $this->original_gateway->{$key};
	}

	/**
	 * __set function.
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function __set( $key, $value ) {
		$this->original_gateway->{$key} = $value;
	}
}
