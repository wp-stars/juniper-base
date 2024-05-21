<?php

namespace Vendidero\Germanized\Shipments\ShippingMethod;

use Vendidero\Germanized\Shipments\Package;
use Vendidero\Germanized\Shipments\Packing\CartItem;
use Vendidero\Germanized\Shipments\Packing\ItemList;
use Vendidero\Germanized\Shipments\ShippingProvider\Helper;
use Vendidero\Germanized\Shipments\Utilities\NumberUtil;

defined( 'ABSPATH' ) || exit;

class MethodHelper {

	protected static $provider_method_settings = null;

	protected static $methods = array();

	/**
	 * Hook in methods.
	 */
	public static function init() {
		// Use a high priority here to make sure we are hooking even after plugins such as flexible shipping
		add_filter( 'woocommerce_shipping_methods', array( __CLASS__, 'set_method_filters' ), 5000, 1 );

		add_filter( 'woocommerce_generate_shipping_provider_method_tabs_html', array( __CLASS__, 'render_method_tabs' ), 10, 4 );
		add_filter( 'woocommerce_generate_shipping_provider_method_zone_override_open_html', array( __CLASS__, 'render_zone_override' ), 10, 4 );
		add_filter( 'woocommerce_generate_shipping_provider_method_zone_override_close_html', array( __CLASS__, 'render_zone_override_close' ), 10, 4 );
		add_filter( 'woocommerce_generate_shipping_provider_method_tabs_open_html', array( __CLASS__, 'render_method_tab_content' ), 10, 4 );
		add_filter( 'woocommerce_generate_shipping_provider_method_tabs_close_html', array( __CLASS__, 'render_method_tab_content_close' ), 10, 4 );
		add_filter( 'woocommerce_generate_shipping_provider_method_configuration_sets_html', array( __CLASS__, 'render_method_configuration_sets' ), 10 );

		add_filter( 'woocommerce_cart_shipping_packages', array( __CLASS__, 'register_cart_items_to_pack' ) );
		add_filter( 'woocommerce_shipping_methods', array( __CLASS__, 'register_shipping_methods' ) );
		add_filter( 'woocommerce_hidden_order_itemmeta', array( __CLASS__, 'set_shipping_order_meta_hidden' ) );
	}

	public static function set_shipping_order_meta_hidden( $meta ) {
		$meta = array_merge(
			$meta,
			array(
				'_packed_items',
				'_packed_item_map',
				'_packaging_ids',
				'_rule_ids',
				'_packages',
			)
		);

		return $meta;
	}

	public static function register_shipping_methods( $methods ) {
		if ( ! Package::is_packing_supported() ) {
			return $methods;
		}

		foreach ( Helper::instance()->get_shipping_providers() as $provider ) {
			$methods[ "shipping_provider_{$provider->get_name()}" ] = new ShippingMethod( 0, $provider );
		}

		return $methods;
	}

	public static function register_cart_items_to_pack( $cart_contents ) {
		if ( ! Package::is_packing_supported() || apply_filters( 'woocommerce_gzd_shipments_disable_cart_packing', false ) ) {
			return $cart_contents;
		}

		foreach ( $cart_contents as $index => $content ) {
			$package_data = array(
				'total'            => 0.0,
				'subtotal'         => 0.0,
				'weight'           => 0.0,
				'volume'           => 0.0,
				'products'         => array(),
				'shipping_classes' => array(),
				'item_count'       => 0,
			);

			$items          = new ItemList();
			$bundled_by_map = array();

			foreach ( $content['contents'] as $content_key => $item ) {
				$item = apply_filters( 'woocommerce_gzd_shipments_cart_item', $item, $content_key );

				/**
				 * Product Bundles cart item compatibility:
				 * In case the current item belongs to a parent bundle item (which contains the actual price)
				 * copy the pricing data from the parent once, e.g. for the first bundled item.
				 */
				if ( isset( $item['bundled_by'] ) && 0.0 === (float) $item['line_total'] && function_exists( 'wc_pb_get_bundled_cart_item_container' ) ) {
					$bundled_by = $item['bundled_by'];

					if ( ! in_array( $bundled_by, $bundled_by_map, true ) ) {
						if ( $container = wc_pb_get_bundled_cart_item_container( $item ) ) {
							$item['line_total']        = (float) $container['line_total'];
							$item['line_subtotal']     = (float) $container['line_subtotal'];
							$item['line_tax']          = (float) $container['line_tax'];
							$item['line_subtotal_tax'] = (float) $container['line_subtotal_tax'];

							$bundled_by_map[] = $bundled_by;
						}
					}
				}

				$product = $item['data'];

				if ( ! is_a( $product, 'WC_Product' ) ) {
					continue;
				}

				$s_product     = wc_gzd_shipments_get_product( $product );
				$line_total    = (float) $item['line_total'];
				$line_subtotal = (float) $item['line_subtotal'];

				if ( wc()->cart->display_prices_including_tax() ) {
					$line_total    += (float) $item['line_tax'];
					$line_subtotal += (float) $item['line_subtotal_tax'];
				}

				$quantity = (int) ceil( (float) $item['quantity'] );
				$width    = ( empty( $s_product->get_shipping_width() ) ? 0 : wc_format_decimal( $s_product->get_shipping_width() ) ) * $quantity;
				$length   = ( empty( $s_product->get_shipping_length() ) ? 0 : wc_format_decimal( $s_product->get_shipping_length() ) ) * $quantity;
				$height   = ( empty( $s_product->get_shipping_height() ) ? 0 : wc_format_decimal( $s_product->get_shipping_height() ) ) * $quantity;
				$weight   = ( empty( $product->get_weight() ) ? 0 : wc_format_decimal( $product->get_weight() ) ) * $quantity;

				$package_data['total']      += $line_total;
				$package_data['subtotal']   += $line_subtotal;
				$package_data['weight']     += $weight;
				$package_data['volume']     += ( $width * $length * $height );
				$package_data['item_count'] += $quantity;

				if ( $product && ! array_key_exists( $product->get_id(), $package_data['products'] ) ) {
					$package_data['products'][ $product->get_id() ] = $product;

					if ( ! empty( $product->get_shipping_class_id() ) ) {
						$package_data['shipping_classes'][] = $product->get_shipping_class_id();
					}
				}

				$cart_item = new CartItem( $item, wc()->cart->display_prices_including_tax() );
				$items->insert( $cart_item, $quantity );
			}

			/**
			 * In case prices have already been calculated, use the official
			 * Woo API for better compatibility with extensions, e.g. Bundles.
			 */
			if ( 0 !== WC()->cart->get_subtotal() ) {
				$total    = (float) WC()->cart->get_cart_contents_total();
				$subtotal = (float) WC()->cart->get_subtotal();

				if ( wc()->cart->display_prices_including_tax() ) {
					$total    += (float) WC()->cart->get_cart_contents_tax();
					$subtotal += (float) WC()->cart->get_subtotal_tax();
				}

				$package_data['total']    = NumberUtil::round_to_precision( $total );
				$package_data['subtotal'] = NumberUtil::round_to_precision( $subtotal );
			}

			$cart_contents[ $index ]['package_data']  = $package_data;
			$cart_contents[ $index ]['items_to_pack'] = $items;
		}

		return $cart_contents;
	}

	public static function render_method_configuration_sets() {
		return '';
	}

	public static function set_method_filters( $methods ) {
		foreach ( $methods as $method => $class ) {
			if ( self::method_is_excluded( $method ) ) {
				continue;
			}

			/**
			 * Update during save
			 */
			add_filter( 'woocommerce_shipping_' . $method . '_instance_settings_values', array( __CLASS__, 'filter_method_settings' ), 10, 2 );
			/**
			 * Register additional setting fields
			 */
			add_filter( 'woocommerce_shipping_instance_form_fields_' . $method, array( __CLASS__, 'add_method_settings' ), 10, 1 );
			/**
			 * Lazy-load option values
			 */
			add_filter( 'woocommerce_shipping_' . $method . '_instance_option', array( __CLASS__, 'filter_method_option_value' ), 10, 3 );

			/**
			 * Use this filter as a backup to support plugins like Flexible Shipping which may override methods
			 */
			add_filter( 'woocommerce_settings_api_form_fields_' . $method, array( __CLASS__, 'add_method_settings' ), 10, 1 );
		}

		return $methods;
	}

	/**
	 * @param \WC_Shipping_Method|string|integer $method
	 *
	 * @return ProviderMethod|false
	 */
	public static function get_provider_method( $maybe_method ) {
		$original_id = $maybe_method;
		$method      = false;
		$method_id   = '';
		$instance_id = 0;

		if ( is_a( $original_id, 'WC_Shipping_Rate' ) ) {
			$instance_id = $original_id->get_instance_id();
			$method_id   = $original_id->get_method_id();
		} elseif ( is_a( $original_id, 'WC_Shipping_Method' ) ) {
			$instance_id = $original_id->get_instance_id();
			$method_id   = $original_id->id;
			$method      = $original_id;
		} elseif ( ! is_numeric( $original_id ) && is_string( $original_id ) ) {
			if ( strpos( $original_id, ':' ) !== false ) {
				$expl        = explode( ':', $original_id );
				$instance_id = ( ( ! empty( $expl ) && count( $expl ) > 1 ) ? (int) $expl[1] : 0 );
				$method_id   = ( ! empty( $expl ) ) ? $expl[0] : $original_id;
			} else {
				/**
				 * Plugins like Flexible Shipping use underscores to separate instance ids.
				 * Example: flexible_shipping_4_1. In this case, 4 ist the instance id.
				 * method_id: flexible_shipping
				 * instance_id: 4
				 *
				 * On the other hand legacy shipping methods may be string only, e.g. an instance id might not exist.
				 * Example: local_pickup_plus
				 * method: local_pickup_plus
				 * instance_id: 0
				 */
				$expl      = explode( '_', $original_id );
				$numbers   = array_values( array_filter( $expl, 'is_numeric' ) );
				$method_id = rtrim( preg_replace( '/[0-9]+/', '', $original_id ), '_' );

				if ( ! empty( $numbers ) ) {
					$instance_id = absint( $numbers[0] );
				} else {
					$instance_id = 0;
				}
			}
		} elseif ( is_numeric( $original_id ) ) {
			$instance_id = absint( $original_id );
		}

		$method_key = $method_id . '_' . $instance_id;

		if ( array_key_exists( $method_key, self::$methods ) ) {
			return self::$methods[ $method_key ];
		} else {
			if ( ! is_a( $method, 'WC_Shipping_Method' ) && ! empty( $instance_id ) ) {
				// Make sure shipping zones are loaded
				include_once WC_ABSPATH . 'includes/class-wc-shipping-zones.php';

				$method = \WC_Shipping_Zones::get_shipping_method( $instance_id );
			}

			/**
			 * Fallback for legacy shipping methods that do not support instance ids.
			 */
			if ( ! $method && empty( $instance_id ) && ! empty( $method_id ) ) {
				$shipping_methods = WC()->shipping()->get_shipping_methods();

				if ( array_key_exists( $method_id, $shipping_methods ) ) {
					$method = $shipping_methods[ $method_id ];
				}
			}

			if ( ! is_a( $method, 'WC_Shipping_Method' ) ) {
				self::$methods[ $method_key ] = new ProviderMethodPlaceholder(
					array(
						'id'          => $method_id,
						'instance_id' => $instance_id,
					)
				);
			} else {
				self::$methods[ $method_key ] = new ProviderMethod( $method );
			}
		}

		return self::$methods[ $method_key ];
	}

	public static function method_is_excluded( $method ) {
		$is_excluded = false;
		$excluded    = apply_filters( 'woocommerce_gzd_shipments_get_methods_excluded_from_provider_settings', array( 'pr_dhl_paket', 'flexible_shipping_info' ) );

		if ( in_array( $method, $excluded, true ) ) {
			$is_excluded = true;
		} elseif ( 'shipping_provider_' === substr( $method, 0, 18 ) ) {
			$is_excluded = true;
		}

		return apply_filters( 'woocommerce_gzd_shipments_shipping_method_is_excluded_from_provider_settings', $is_excluded, $method );
	}

	public static function validate_method_zone_override( $value ) {
		return ! is_null( $value ) ? 'yes' : 'no';
	}

	/**
	 * @param mixed $value
	 * @param mixed $setting_id
	 * @param \WC_Shipping_Method $method
	 *
	 * @return mixed
	 */
	public static function filter_method_option_value( $value, $setting_id, $method ) {
		$shipping_method = self::get_provider_method( $method );

		if ( $shipping_method->is_configuration_set_setting( $setting_id ) ) {
			if ( $configuration_set = $shipping_method->get_configuration_set( $setting_id ) ) {
				$suffix = $shipping_method->get_configuration_setting_suffix( $setting_id );

				if ( 'override' === $suffix ) {
					return 'yes';
				} else {
					return $configuration_set->has_setting( $setting_id ) ? $configuration_set->get_setting( $setting_id ) : $value;
				}
			}
		}

		return $value;
	}

	/**
	 * @param array $p_settings
	 * @param \WC_Shipping_Method $shipping_method
	 *
	 * @return array
	 */
	public static function filter_method_settings( $p_settings, $shipping_method ) {
		$shipping_provider = isset( $p_settings['shipping_provider'] ) ? $p_settings['shipping_provider'] : '';
		$method            = self::get_provider_method( $shipping_method );

		$method->set_shipping_provider( $shipping_provider );

		foreach ( $p_settings as $setting_id => $setting_val ) {
			if ( 'configuration_sets' === $setting_id ) {
				unset( $p_settings[ $setting_id ] );
			} elseif ( $method->is_configuration_set_setting( $setting_id ) ) {
				$args = $method->get_configuration_set_args_by_id( $setting_id );

				if ( ! empty( $args['shipping_provider_name'] ) && $args['shipping_provider_name'] === $method->get_shipping_provider() ) {
					if ( 'override' === $args['setting_name'] ) {
						if ( wc_string_to_bool( $setting_val ) ) {
							if ( $config_set = $method->get_or_create_configuration_set( $args ) ) {
								$config_set->update_setting( $setting_id, $setting_val );
							}
						} else {
							$method->reset_configuration_sets( $args );
						}
					} elseif ( $config_set = $method->get_configuration_set( $args ) ) {
						$config_set->update_setting( $setting_id, $setting_val );
					}
				}

				unset( $p_settings[ $setting_id ] );
			}
		}

		$p_settings['configuration_sets'] = $method->get_configuration_sets();

		/**
		 * Force reloading instance default settings to prevent cached values
		 */
		$shipping_method->instance_settings = array();

		return $p_settings;
	}

	public static function add_method_settings( $p_settings ) {
		$wc = WC();

		/**
		 * Prevent undefined index notices during REST API calls.
		 *
		 * @see WC_REST_Shipping_Zone_Methods_V2_Controller::get_settings()
		 */
		if ( is_callable( array( $wc, 'is_rest_api_request' ) ) && $wc->is_rest_api_request() ) {
			return $p_settings;
		}

		$shipping_provider_settings = self::get_method_settings();

		return array_merge( $p_settings, $shipping_provider_settings );
	}

	protected static function load_all_method_settings() {
		$screen                  = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
		$load_all_setting_fields = false;

		if ( $screen && isset( $screen->id ) && 'woocommerce_page_wc-settings' === $screen->id ) {
			$load_all_setting_fields = true;
		}

		if (
			doing_action( 'wp_ajax_woocommerce_shipping_zone_methods_save_settings' ) ||
			doing_action( 'wp_ajax_woocommerce_shipping_zone_add_method' ) ||
			doing_action( 'wp_ajax_woocommerce_shipping_zone_remove_method' )
		) {
			$load_all_setting_fields = true;
		}

		return $load_all_setting_fields;
	}

	public static function get_method_settings( $force_load_all = false ) {
		$load_all_settings = $force_load_all ? true : self::load_all_method_settings();
		$method_settings   = array(
			'label_configuration_set_shipping_provider_title' => array(
				'title'       => _x( 'Shipping Provider Settings', 'shipments', 'woocommerce-germanized' ),
				'type'        => 'title',
				'id'          => 'label_configuration_set_shipping_provider_title',
				'default'     => '',
				'description' => _x( 'Adjust shipping provider settings used for managing shipments.', 'shipments', 'woocommerce-germanized' ),
			),
			'shipping_provider'  => array(
				'title'       => _x( 'Shipping Provider', 'shipments', 'woocommerce-germanized' ),
				'type'        => 'select',
				/**
				 * Filter to adjust default shipping provider pre-selected within shipping provider method settings.
				 *
				 * @param string $provider_name The shipping provider name e.g. dhl.
				 *
				 * @since 3.0.6
				 * @package Vendidero/Germanized/Shipments
				 */
				'default'     => apply_filters( 'woocommerce_gzd_shipping_provider_method_default_provider', '' ),
				'options'     => wc_gzd_get_shipping_provider_select(),
				'description' => _x( 'Choose a shipping provider which will be selected by default for an eligible shipment.', 'shipments', 'woocommerce-germanized' ),
			),
			'configuration_sets' => array(
				'title'   => '',
				'type'    => 'shipping_provider_method_configuration_sets',
				'default' => array(),
			),
		);

		if ( $load_all_settings ) {
			if ( is_null( self::$provider_method_settings ) ) {
				self::$provider_method_settings = array();

				foreach ( wc_gzd_get_shipping_providers() as $provider ) {
					if ( ! $provider->is_activated() ) {
						continue;
					}

					self::$provider_method_settings[ $provider->get_name() ] = $provider->get_shipping_method_settings();
				}
			}

			$supported_zones = array_keys( wc_gzd_get_shipping_label_zones() );

			foreach ( self::$provider_method_settings as $provider => $zone_settings ) {
				$provider_tabs           = array();
				$provider_inner_settings = array();

				foreach ( $zone_settings as $zone => $shipment_type_settings ) {
					if ( ! in_array( $zone, $supported_zones, true ) ) {
						continue;
					}

					foreach ( $shipment_type_settings as $shipment_type => $settings ) {
						if ( ! isset( $provider_inner_settings[ $shipment_type ] ) ) {
							$provider_inner_settings[ $shipment_type ] = array();
						}

						$provider_inner_settings[ $shipment_type ]         = array_merge( $provider_inner_settings[ $shipment_type ], $settings );
						$provider_tabs[ $provider . '_' . $shipment_type ] = wc_gzd_get_shipment_label_title( $shipment_type );
					}
				}

				if ( ! empty( $provider_inner_settings ) ) {
					$tabs_open_id = "label_config_set_tabs_{$provider}";

					$method_settings = array_merge(
						$method_settings,
						array(
							$tabs_open_id => array(
								'id'           => $tabs_open_id,
								'tabs'         => $provider_tabs,
								'type'         => 'shipping_provider_method_tabs',
								'default'      => '',
								'display_only' => true,
								'provider'     => $provider,
							),
						)
					);

					$count = 0;

					foreach ( $provider_inner_settings as $shipment_type => $settings ) {
						$count ++;

						$tabs_open_id  = "label_config_set_tabs_{$provider}_{$shipment_type}_open";
						$tabs_close_id = "label_config_set_tabs_{$provider}_{$shipment_type}_close";

						$method_settings = array_merge(
							$method_settings,
							array(
								$tabs_open_id => array(
									'id'       => $tabs_open_id,
									'type'     => 'shipping_provider_method_tabs_open',
									'tab'      => $provider . '_' . $shipment_type,
									'default'  => '',
									'provider' => $provider,
									'active'   => 1 === $count ? true : false,
								),
							)
						);

						$method_settings = array_merge( $method_settings, $settings );

						$method_settings = array_merge(
							$method_settings,
							array(
								$tabs_close_id => array(
									'id'       => $tabs_close_id,
									'type'     => 'shipping_provider_method_tabs_close',
									'tab'      => $provider . '_' . $shipment_type,
									'default'  => '',
									'provider' => $provider,
								),
							)
						);
					}
				}
			}
		}

		/**
		 * Append a stop title to make sure the table is closed within settings.
		 */
		$method_settings = array_merge(
			apply_filters( 'woocommerce_gzd_shipping_provider_method_admin_settings', $method_settings, $load_all_settings ),
			array(
				'label_configuration_set_shipping_provider_stop_title' => array(
					'title'   => '',
					'id'      => 'label_configuration_set_shipping_provider_stop_title',
					'type'    => 'title',
					'default' => '',
				),
			)
		);

		return $method_settings;
	}

	public static function render_method_tab_content_close( $html, $key, $value, $method ) {
		return '</table></div>';
	}

	public static function render_method_tab_content( $html, $key, $setting, $method ) {
		$setting = wp_parse_args(
			$setting,
			array(
				'active' => false,
				'id'     => '',
				'tab'    => '',
			)
		);

		return '</table><div class="wc-gzd-shipping-provider-method-tab-content ' . ( $setting['active'] ? 'tab-content-active' : '' ) . '" id="' . esc_attr( $setting['id'] ) . '" data-tab="' . esc_attr( $setting['tab'] ) . '">';
	}

	public static function render_zone_override_close( $html, $key, $setting, $method ) {
		return '</table></div></div>';
	}

	public static function render_zone_override( $html, $key, $setting, $method ) {
		$setting     = wp_parse_args(
			$setting,
			array(
				'active'   => false,
				'id'       => '',
				'tab'      => '',
				'class'    => '',
				'disabled' => false,
				'desc_tip' => '',
				'css'      => '',
			)
		);
		$field_key   = $method->get_field_key( $key );
		$field_value = $method->get_option( $key );
		ob_start();
		?>
		</table>
		<div class="wc-gzd-shipping-provider-override-wrapper">
		<div class="wc-gzd-shipping-provider-override-title-wrapper">
			<h3 class="wc-settings-sub-title <?php echo esc_attr( $setting['class'] ); ?>"><?php echo wp_kses_post( $setting['title'] ); ?></h3>

			<p class="override-checkbox">
				<label for="<?php echo esc_attr( $field_key ); ?>">
					<input <?php disabled( $setting['disabled'], true ); ?> class="<?php echo esc_attr( $setting['class'] ); ?>" type="checkbox" name="<?php echo esc_attr( $field_key ); ?>" id="<?php echo esc_attr( $field_key ); ?>" style="<?php echo esc_attr( $setting['css'] ); ?>" value="1" <?php checked( $field_value, 'yes' ); ?> <?php echo $method->get_custom_attribute_html( $setting ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> />
					<?php echo wp_kses_post( _x( 'Override?', 'shipments', 'woocommerce-germanized' ) ); ?>
					<?php echo $method->get_tooltip_html( $setting ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</label>
			</p>
		</div>
		<div class="wc-gzd-shipping-provider-override-inner-wrapper <?php echo esc_attr( 'yes' === $field_value ? 'has-override' : '' ); ?>">
		<table class="form-table">
		<?php
		$html = ob_get_clean();

		return $html;
	}

	public static function render_method_tabs( $html, $key, $setting, $method ) {
		$setting = wp_parse_args(
			$setting,
			array(
				'id'       => '',
				'tabs'     => array(),
				'provider' => '',
			)
		);
		$count   = 0;
		ob_start();
		?>
		</table>
		<div class="wc-gzd-shipping-provider-method-tabs" id="<?php echo esc_attr( $setting['id'] ); ?>" data-provider="<?php echo esc_attr( $setting['provider'] ); ?>">
			<nav class="nav-tab-wrapper woo-nav-tab-wrapper shipments-nav-tab-wrapper">
				<?php
				foreach ( $setting['tabs'] as $tab => $tab_title ) :
					$count++;
					?>
					<a class="nav-tab <?php echo 1 === $count ? esc_attr( 'nav-tab-active' ) : ''; ?>" href="#<?php echo esc_attr( $tab ); ?>" data-tab="<?php echo esc_attr( $tab ); ?>"><?php echo esc_html( $tab_title ); ?></a>
				<?php endforeach; ?>
			</nav>
		</div>
		<table>
		<?php
		$html = ob_get_clean();

		return $html;
	}
}
