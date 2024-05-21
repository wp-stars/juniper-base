<?php
namespace Vendidero\Germanized\Blocks;

use Automattic\WooCommerce\StoreApi\Exceptions\RouteException;
use Automattic\WooCommerce\StoreApi\Schemas\V1\CartSchema;
use Automattic\WooCommerce\StoreApi\Schemas\V1\CheckoutSchema;
use Vendidero\Germanized\Blocks\PaymentGateways\DirectDebit;
use Vendidero\Germanized\Blocks\PaymentGateways\Invoice;
use Vendidero\Germanized\Package;

final class Checkout {

	public function __construct() {
		$this->adjust_markup();
		$this->register_filters();
		$this->register_integrations();
		$this->register_endpoint_data();
		$this->register_validation_and_storage();
	}

	private function register_filters() {
		add_filter(
			'woocommerce_get_item_data',
			function( $item_data, $item ) {
				$needs_price_labels = has_block( 'woocommerce/checkout' ) || has_block( 'woocommerce/cart' ) || WC()->is_rest_api_request();

				if ( apply_filters( 'woocommerce_gzd_cart_checkout_needs_block_price_labels', $needs_price_labels ) ) {
					$labels = wc_gzd_get_checkout_shopmarks();

					if ( is_checkout() || has_block( 'woocommerce/checkout' ) ) {
						$labels = wc_gzd_get_checkout_shopmarks();
					} elseif ( is_cart() || has_block( 'woocommerce/cart' ) ) {
						$labels = wc_gzd_get_cart_shopmarks();
					}

					$label_item_data = array();

					foreach ( $labels as $label ) {
						$callback  = $label->get_callback();
						$arg_count = $label->get_number_of_params();

						if ( 'differential_taxation' === $label->get_type() ) {
							add_filter( 'woocommerce_gzd_differential_taxation_notice_text_mark', '__return_false' );
							$callback  = 'woocommerce_gzd_template_differential_taxation_notice_cart';
							$arg_count = 0;
						}

						$args = array( '', $item, $item['key'] );

						if ( 2 === $arg_count ) {
							$args = array( $item, $item['key'] );
						} elseif ( 0 === $arg_count ) {
							$args = array();
						}

						ob_start();
						if ( $label->get_is_action() ) {
							call_user_func_array( $callback, $args );
						} else {
							echo wp_kses_post( call_user_func_array( $callback, $args ) );
						}
						$output = trim( ob_get_clean() );

						if ( ! empty( $output ) ) {
							$label_item_data[] = array(
								'key'     => 'gzd-' . $label->get_type(),
								'value'   => $output,
								'display' => '',
							);
						}
					}

					if ( ! empty( $label_item_data ) ) {
						$item_data = array_merge( $label_item_data, $item_data );
					}
				}

				return $item_data;
			},
			10000,
			2
		);
	}

	private function adjust_markup() {
		add_filter(
			'render_block',
			function( $content, $block ) {
				/**
				 * Whether to disable the (structural) adjustments applied to the WooCommerce checkout block.
				 *
				 * @param boolean Whether to disable the checkout adjustments or not.
				 *
				 * @since 3.14.0
				 */
				if ( 'woocommerce/checkout' === $block['blockName'] && ! apply_filters( 'woocommerce_gzd_disable_checkout_block_adjustments', false ) ) {
					$content = str_replace( 'wp-block-woocommerce-checkout ', 'wp-block-woocommerce-checkout wc-gzd-checkout ', $content );

					// Find the last 2 closing divs of the checkout block and replace them with our custom submit wrap.
					preg_match( '/<\/div>(\s*)<\/div>$/', $content, $matches );

					if ( ! empty( $matches ) ) {
						$replacement = '<div class="wc-gzd-checkout-submit"><div data-block-name="woocommerce/checkout-order-summary-block" class="wp-block-woocommerce-checkout-order-summary-block"></div><div data-block-name="woocommerce/checkout-actions-block" class="wp-block-woocommerce-checkout-actions-block"></div></div></div></div>';
						$content     = preg_replace( '/<\/div>(\s*)<\/div>$/', $replacement, $content );
					}
				}

				return $content;
			},
			1000,
			2
		);
	}

	private function register_integrations() {
		add_action(
			'woocommerce_blocks_checkout_block_registration',
			function( $integration_registry ) {
				$integration_registry->register( new \Vendidero\Germanized\Blocks\Integrations\Checkout() );
			}
		);

		add_action(
			'woocommerce_blocks_payment_method_type_registration',
			function( $payment_method_registry ) {
				$payment_method_registry->register(
					Package::container()->get( Invoice::class )
				);

				$payment_method_registry->register(
					Package::container()->get( DirectDebit::class )
				);
			}
		);
	}

	private function register_endpoint_data() {
		woocommerce_store_api_register_endpoint_data(
			array(
				'endpoint'        => CartSchema::IDENTIFIER,
				'namespace'       => 'woocommerce-germanized',
				'data_callback'   => function() {
					return $this->get_cart_data();
				},
				'schema_callback' => function () {
					return $this->get_cart_schema();
				},
			)
		);

		woocommerce_store_api_register_endpoint_data(
			array(
				'endpoint'        => CheckoutSchema::IDENTIFIER,
				'namespace'       => 'woocommerce-germanized',
				'schema_callback' => function () {
					return $this->get_checkout_schema();
				},
			)
		);

		woocommerce_store_api_register_update_callback(
			array(
				'namespace' => 'woocommerce-germanized-checkboxes',
				'callback'  => function( $data ) {
					$checkboxes = isset( $data['checkboxes'] ) ? (array) wc_clean( wp_unslash( $data['checkboxes'] ) ) : array();

					$this->parse_checkboxes( $checkboxes );
				},
			)
		);
	}

	private function register_validation_and_storage() {
		/**
		 * This hook does not contain any request data, therefor has only limited value.
		 */
		add_action(
			'woocommerce_store_api_checkout_update_order_meta',
			function( $order ) {
				\WC_GZD_Checkout::instance()->order_meta( $order );
			},
			5
		);

		add_action(
			'woocommerce_store_api_checkout_update_order_from_request',
			function( $order, $request ) {
				$this->validate( $order, $request );

				\WC_GZD_Checkout::instance()->order_store_checkbox_data( $order );
				\WC_GZD_Checkout::instance()->add_order_notes( $order );
			},
			10,
			2
		);
	}

	private function get_cart_schema() {
		return array(
			'applies_for_photovoltaic_system_vat_exempt' => array(
				'description' => __( 'Whether the cart applies for a photovoltaic system vat exempt or not.', 'woocommerce-germanized' ),
				'type'        => 'boolean',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'photovoltaic_system_law_details'            => array(
				'description' => __( 'The current cart\'s photovoltaic system law details.', 'woocommerce-germanized' ),
				'type'        => 'object',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
				'properties'  => array(
					'text' => array(
						'description' => __( 'The actual law, e.g. paragraph.', 'woocommerce-germanized' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'url'  => array(
						'description' => __( 'The URL to the law.', 'woocommerce-germanized' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
				),
			),
			'shipping_costs_notice'                      => array(
				'description' => __( 'Cart shipping costs notice.', 'woocommerce-germanized' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'checkboxes'                                 => array(
				'description' => __( 'List of cart checkboxes.', 'woocommerce-germanized' ),
				'type'        => 'array',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'              => array(
							'description' => __( 'Unique identifier for the checkbox within the cart.', 'woocommerce-germanized' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'name'            => array(
							'description' => __( 'Checkbox name.', 'woocommerce-germanized' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'label'           => array(
							'description' => __( 'Checkbox label.', 'woocommerce-germanized' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'checked'         => array(
							'description' => __( 'Checkbox checked status.', 'woocommerce-germanized' ),
							'type'        => 'boolean',
							'context'     => array( 'view', 'edit' ),
							'default'     => false,
						),
						'hidden'          => array(
							'description' => __( 'Checkbox hidden.', 'woocommerce-germanized' ),
							'type'        => 'boolean',
							'context'     => array( 'view', 'edit' ),
							'default'     => false,
						),
						'error_message'   => array(
							'description' => __( 'Checkbox error message.', 'woocommerce-germanized' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'wrapper_classes' => array(
							'description' => __( 'Wrapper classes.', 'woocommerce-germanized' ),
							'type'        => 'array',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'custom_styles'   => array(
							'description' => __( 'Custom styles.', 'woocommerce-germanized' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'html_id'         => array(
							'description' => __( 'HTML field id.', 'woocommerce-germanized' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'has_checkbox'    => array(
							'description' => __( 'Whether to show a checkbox field or not.', 'woocommerce-germanized' ),
							'type'        => 'boolean',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'is_required'     => array(
							'description' => __( 'Whether the checkbox is required or not.', 'woocommerce-germanized' ),
							'type'        => 'boolean',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
					),
				),
			),
		);
	}

	private function get_cart_data() {
		$checkboxes_for_api     = array();
		$checkboxes_force_print = array( 'privacy' );
		$customer               = wc()->customer;

		foreach ( $this->get_checkboxes() as $id => $checkbox ) {
			if ( ! $checkbox->is_printable() && ! in_array( $checkbox->get_id(), $checkboxes_force_print, true ) ) {
				continue;
			}

			$checkboxes_for_api[] = array(
				'id'              => $id,
				'name'            => $checkbox->get_html_name(),
				'label'           => $checkbox->get_label(),
				'wrapper_classes' => array_diff( $checkbox->get_html_wrapper_classes(), array( 'validate-required', 'form-row' ) ),
				'custom_styles'   => $checkbox->get_html_style(),
				'error_message'   => $checkbox->get_error_message( true ),
				'html_id'         => $checkbox->get_html_id(),
				'has_checkbox'    => ! $checkbox->hide_input(),
				'is_required'     => $checkbox->is_mandatory(),
				'checked'         => $checkbox->hide_input() ? true : false,
				'hidden'          => $checkbox->is_hidden(),
			);
		}

		return array(
			'applies_for_photovoltaic_system_vat_exempt' => wc_gzd_cart_applies_for_photovoltaic_system_vat_exemption(),
			'photovoltaic_system_law_details'            => wc_gzd_cart_get_photovoltaic_systems_law_details(),
			'checkboxes'                                 => $checkboxes_for_api,
			'shipping_costs_notice'                      => wc_gzd_get_shipping_costs_text(),
		);
	}

	private function get_checkout_schema() {
		return array(
			'checkboxes' => array(
				'description' => __( 'List of cart checkboxes.', 'woocommerce-germanized' ),
				'type'        => array( 'array', 'null' ),
				'context'     => array( 'view', 'edit' ),
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'      => array(
							'description' => __( 'Unique identifier for the checkbox within the cart.', 'woocommerce-germanized' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'checked' => array(
							'description' => __( 'Checkbox checked status.', 'woocommerce-germanized' ),
							'type'        => 'boolean',
							'context'     => array( 'view', 'edit' ),
						),
					),
				),
			),
		);
	}

	private function get_checkboxes() {
		add_filter(
			'woocommerce_gzd_get_checkout_value',
			function( $value, $key ) {
				$getter   = "get_{$key}";
				$customer = wc()->customer;

				if ( is_callable( array( $customer, $getter ) ) ) {
					$value = $customer->{ $getter }();
				}

				return $value;
			},
			10,
			2
		);

		$checkbox_manager = \WC_GZD_Legal_Checkbox_Manager::instance();

		return $checkbox_manager->get_checkboxes(
			array(
				'locations' => 'checkout',
				'sort'      => true,
			),
			'render'
		);
	}

	private function parse_checkboxes( $checkboxes ) {
		$checkbox_manager = \WC_GZD_Legal_Checkbox_Manager::instance();

		foreach ( $checkboxes as $checkbox_data ) {
			$checkbox_data = wp_parse_args(
				$checkbox_data,
				array(
					'id'      => '',
					'checked' => false,
				)
			);

			$checkbox = $checkbox_manager->get_checkbox( $checkbox_data['id'] );

			if ( ! $checkbox ) {
				continue;
			}

			if ( true === filter_var( $checkbox_data['checked'], FILTER_VALIDATE_BOOLEAN ) ) {
				$checkboxes_checked[] = $checkbox_data['id'];
			}
		}

		if ( ! empty( $checkboxes_checked ) ) {
			add_filter(
				'woocommerce_gzd_checkout_checkbox_is_checked',
				function( $is_checked, $checkbox_id ) use ( $checkboxes_checked ) {
					if ( in_array( $checkbox_id, $checkboxes_checked, true ) ) {
						$is_checked = true;
					}

					return $is_checked;
				},
				999,
				2
			);
		}

		return $checkboxes_checked;
	}

	/**
	 * @param \WC_Order $order
	 * @param \WP_REST_Request $request
	 *
	 * @return void
	 */
	private function validate( $order, $request ) {
		$data = $this->get_checkout_data_from_request( $request );

		if ( $this->has_checkout_data( 'checkboxes', $request ) ) {
			$checkboxes_checked = $this->parse_checkboxes( $data['checkboxes'] );

			foreach ( $this->get_checkboxes() as $id => $checkbox ) {
				if ( ! $checkbox->validate( in_array( $id, $checkboxes_checked, true ) ? 'yes' : '' ) ) {
					throw new RouteException( "checkbox_{$id}", $checkbox->get_error_message(), 400 );
				}
			}
		}
	}

	private function has_checkout_data( $param, $request ) {
		$request_data = isset( $request['extensions']['woocommerce-germanized'] ) ? (array) $request['extensions']['woocommerce-germanized'] : array();

		return isset( $request_data[ $param ] ) && null !== $request_data[ $param ];
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return array
	 */
	private function get_checkout_data_from_request( $request ) {
		$data = array_filter( (array) wc_clean( $request['extensions']['woocommerce-germanized'] ) );

		$data = wp_parse_args(
			$data,
			array(
				'checkboxes' => array(),
			)
		);

		return $data;
	}
}
