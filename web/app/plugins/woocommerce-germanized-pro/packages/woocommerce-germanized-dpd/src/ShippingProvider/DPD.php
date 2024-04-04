<?php
/**
 * ShippingProvider impl.
 *
 * @package WooCommerce/Blocks
 */
namespace Vendidero\Germanized\DPD\ShippingProvider;

use Vendidero\Germanized\DPD\Package;
use Vendidero\Germanized\DPD\ShippingProvider\Services\HigherInsurance;
use Vendidero\Germanized\Shipments\Admin\Settings;
use Vendidero\Germanized\Shipments\Labels\ConfigurationSet;
use Vendidero\Germanized\Shipments\Shipment;
use Vendidero\Germanized\Shipments\ShippingMethod\MethodHelper;
use Vendidero\Germanized\Shipments\ShippingProvider\Auto;

defined( 'ABSPATH' ) || exit;

class DPD extends Auto {

	protected function get_default_label_default_print_format() {
		return 'web_connect' === $this->get_api_type() ? 'A6' : 'PDF_A6';
	}

	public function get_title( $context = 'view' ) {
		return _x( 'DPD', 'dpd', 'woocommerce-germanized-pro' );
	}

	public function get_name( $context = 'view' ) {
		return 'dpd';
	}

	public function get_description( $context = 'view' ) {
		return _x( 'Create DPD labels and return labels conveniently.', 'dpd', 'woocommerce-germanized-pro' );
	}

	public function get_default_tracking_url_placeholder() {
		return 'https://tracking.dpd.de/parcelstatus?query={tracking_id}&locale=de_DE';
	}

	public function is_sandbox() {
		return Package::get_api()->is_sandbox();
	}

	public function get_label_classname( $type ) {
		if ( 'return' === $type ) {
			return '\Vendidero\Germanized\DPD\Label\Retoure';
		} else {
			return '\Vendidero\Germanized\DPD\Label\Simple';
		}
	}

	/**
	 * @param string $label_type
	 * @param false|Shipment $shipment
	 *
	 * @return bool
	 */
	public function supports_labels( $label_type, $shipment = false ) {
		$label_types = array( 'simple', 'return' );

		/**
		 * DPD does not support return labels for third countries
		 */
		if ( $shipment && 'return' === $label_type && $shipment->is_shipping_international() ) {
			return false;
		}

		return in_array( $label_type, $label_types, true );
	}

	public function supports_customer_return_requests() {
		return true;
	}

	public function hide_return_address() {
		return false;
	}

	public function get_api_username( $context = 'view' ) {
		return $this->get_meta( 'api_username', true, $context );
	}

	public function get_api_type( $context = 'view' ) {
		$api_type = $this->get_meta( 'api_type', true, $context );

		if ( 'view' === $context && empty( $api_type ) ) {
			$api_type = 'cloud';
		}

		return $api_type;
	}

	public function set_api_username( $username ) {
		$this->update_meta_data( 'api_username', $username );
	}

	public function get_setting_sections() {
		$sections = parent::get_setting_sections();

		return $sections;
	}

	/**
	 * @param \Vendidero\Germanized\Shipments\Shipment $shipment
	 *
	 * @return array
	 */
	protected function get_return_label_fields( $shipment ) {
		$settings     = parent::get_return_label_fields( $shipment );
		$default_args = $this->get_default_label_props( $shipment );

		return $settings;
	}

	protected function register_print_formats() {
		if ( 'cloud' === $this->get_api_type() ) {
			$page_formats = array(
				'PDF_A4' => _x( 'A4', 'dpd', 'woocommerce-germanized-pro' ),
				'PDF_A6' => _x( 'A6', 'dpd', 'woocommerce-germanized-pro' ),
			);
		} else {
			$page_formats = array(
				'A4' => _x( 'A4', 'dpd', 'woocommerce-germanized-pro' ),
				'A6' => _x( 'A6', 'dpd', 'woocommerce-germanized-pro' ),
				'A7' => _x( 'A7', 'dpd', 'woocommerce-germanized-pro' ),
			);
		}

		foreach ( $page_formats as $format_id => $format_label ) {
			$this->register_print_format(
				$format_id,
				array(
					'label' => $format_label,
				)
			);
		}
	}

	protected function register_products() {
		if ( 'cloud' === $this->get_api_type() ) {
			$dom_products = array(
				'Classic_Predict'     => _x( 'DPD Classic Predict', 'dpd', 'woocommerce-germanized-pro' ),
				'Express_830'         => _x( 'DPD Express 8:30', 'dpd', 'woocommerce-germanized-pro' ),
				'Express_10'          => _x( 'DPD Express 10:00', 'dpd', 'woocommerce-germanized-pro' ),
				'Express_12'          => _x( 'DPD Express 12:00', 'dpd', 'woocommerce-germanized-pro' ),
				'Express_18'          => _x( 'DPD Express 18:00', 'dpd', 'woocommerce-germanized-pro' ),
				'Express_12_Saturday' => _x( 'DPD Express 12:00 (Saturday)', 'dpd', 'woocommerce-germanized-pro' ),
			);

			$this->register_product(
				'Classic',
				array(
					'label'                    => _x( 'DPD Classic', 'dpd', 'woocommerce-germanized-pro' ),
					'zones'          => array( 'dom', 'eu', 'int' ),
					'shipment_types' => array( 'simple' ),
					'countries'      => array( 'ALL_EU', 'CH', 'GB', 'NO' ),
				)
			);

			foreach ( $dom_products as $product_id => $label ) {
				$this->register_product(
					$product_id,
					array(
						'label'                    => $label,
						'zones'          => array( 'dom' ),
						'shipment_types' => array( 'simple' ),
					)
				);
			}

			$this->register_product(
				'Express_International',
				array(
					'label'                    => _x( 'DPD Express', 'dpd', 'woocommerce-germanized-pro' ),
					'zones'          => array( 'eu', 'int' ),
					'shipment_types' => array( 'simple' ),
				)
			);

			$this->register_product(
				'Classic_Return',
				array(
					'label'                    => _x( 'DPD Classic Return', 'dpd', 'woocommerce-germanized-pro' ),
					'zones'          => array( 'dom', 'eu', 'int' ),
					'shipment_types' => array( 'return' ),
				)
			);

			$this->register_product(
				'Shop_Return',
				array(
					'label'                    => _x( 'DPD Shop Return', 'dpd', 'woocommerce-germanized-pro' ),
					'zones'          => array( 'dom', 'eu', 'int' ),
					'shipment_types' => array( 'return' ),
				)
			);
		} else {
			$this->register_product(
				'CL',
				array(
					'label'                    => _x( 'DPD Classic', 'dpd', 'woocommerce-germanized-pro' ),
					'zones'          => array( 'dom', 'eu', 'int' ),
					'countries'      => array( 'ALL_EU', 'CH', 'GB', 'NO' ),
					'shipment_types' => array( 'simple', 'return' ),
				)
			);

			$this->register_product(
				'IE2',
				array(
					'label'                    => _x( 'DPD Express', 'dpd', 'woocommerce-germanized-pro' ),
					'zones'          => array( 'dom', 'eu', 'int' ),
					'shipment_types' => array( 'simple', 'return' ),
				)
			);

			$this->register_product(
				'E10',
				array(
					'label'                    => _x( 'DPD 10:00', 'dpd', 'woocommerce-germanized-pro' ),
					'zones'          => array( 'dom', 'eu' ),
					'countries'      => array( 'DE', 'BE', 'NL', 'LU' ),
					'shipment_types' => array( 'simple', 'return' ),
				)
			);

			$this->register_product(
				'E12',
				array(
					'label'                    => _x( 'DPD 12:00', 'dpd', 'woocommerce-germanized-pro' ),
					'zones'          => array( 'dom', 'eu' ),
					'countries'      => array( 'DE', 'BE', 'NL', 'LU' ),
					'shipment_types' => array( 'simple', 'return' ),
				)
			);

			$this->register_product(
				'E18',
				array(
					'label'                    => _x( 'DPD 18:00', 'dpd', 'woocommerce-germanized-pro' ),
					'zones'          => array( 'dom', 'eu', 'int' ),
					'countries'      => array( 'DE', 'BE', 'NL', 'LU', 'CH', 'LI' ),
					'shipment_types' => array( 'simple', 'return' ),
				)
			);

			$dom_products = array(
				'E830' => _x( 'DPD 8:30', 'dpd', 'woocommerce-germanized-pro' ),
				'E18'  => _x( 'DPD 18:00', 'dpd', 'woocommerce-germanized-pro' ),
				'MAX'  => _x( 'DPD MAX', 'dpd', 'woocommerce-germanized-pro' ),
				'PL'   => _x( 'DPD PARCELLetter', 'dpd', 'woocommerce-germanized-pro' ),
				'PM4'  => _x( 'DPD Priority', 'dpd', 'woocommerce-germanized-pro' ),
			);

			foreach ( $dom_products as $product_id => $label ) {
				$this->register_product(
					$product_id,
					array(
						'label'                    => $label,
						'zones'          => array( 'dom' ),
						'shipment_types' => array( 'simple', 'return' ),
					)
				);
			}
		}
	}

	protected function register_services() {
		if ( 'web_connect' === $this->get_api_type() ) {
			$this->register_service(
				'saturday_delivery',
				array(
					'label'    => _x( 'Saturday Delivery', 'dpd', 'woocommerce-germanized-pro' ),
					'products' => array( 'E12' ),
				)
			);

			$this->register_service( new HigherInsurance( $this ) );

			$this->register_service(
				'international_guarantee',
				array(
					'label'           => _x( 'International Guarantee', 'dpd', 'woocommerce-germanized-pro' ),
					'products'        => array( 'CL', 'E18' ),
					'zones' => array( 'eu', 'int' ),
				)
			);
		}
	}

	/**
	 * @param \Vendidero\Germanized\Shipments\Shipment $shipment
	 *
	 * @return array
	 */
	protected function get_simple_label_fields( $shipment ) {
		$settings     = parent::get_simple_label_fields( $shipment );
		$default_args = $this->get_default_label_props( $shipment );

		if ( 'cloud' === $this->get_api_type() ) {
			$settings = array_merge(
				$settings,
				array(
					array(
						'id'          => 'pickup_date',
						'label'       => _x( 'Pickup date', 'dpd', 'woocommerce-germanized-pro' ),
						'description' => '',
						'type'        => 'date',
						'value'       => isset( $default_args['pickup_date'] ) ? $default_args['pickup_date'] : '',
					),
				)
			);
		}

		if ( 'web_connect' === $this->get_api_type() ) {
			if ( $shipment->is_shipping_international() ) {
				$terms = Package::get_api()->get_international_customs_terms();

				if ( 'GB' === $shipment->get_country() && $shipment->get_total() <= 135 ) {
					$terms = array_intersect_key( $terms, array( '07' => '' ) );
				}

				$settings = array_merge(
					$settings,
					array(
						array(
							'id'          => 'customs_terms',
							'label'       => _x( 'Customs terms', 'dpd', 'woocommerce-germanized-pro' ),
							'description' => '',
							'type'        => 'select',
							'options'     => $terms,
							'value'       => isset( $default_args['customs_terms'] ) ? $default_args['customs_terms'] : '',
						),
						array(
							'id'          => 'customs_paper',
							'label'       => _x( 'Customs paper', 'dpd', 'woocommerce-germanized-pro' ),
							'description' => '',
							'type'        => 'multiselect',
							'options'     => Package::get_api()->get_international_customs_paper(),
							'value'       => isset( $default_args['customs_paper'] ) ? $default_args['customs_paper'] : '',
						),
					)
				);
			}
		}

		return $settings;
	}

	protected function get_default_customs_terms() {
		return '06';
	}

	protected function get_default_customs_paper() {
		return array( 'B', 'G' );
	}

	/**
	 * @param Shipment $shipment
	 * @param $props
	 *
	 * @return \WP_Error|mixed
	 */
	protected function validate_label_request( $shipment, $args = array() ) {
		$args  = wp_parse_args( $args, 'return' === $shipment->get_type() ? $this->get_default_return_label_props( $shipment ) : $this->get_default_simple_label_props( $shipment ) );
		$error = new \WP_Error();

		if ( 'web_connect' === $this->get_api_type() && $shipment->is_shipping_international() ) {
			if ( ! in_array( $args['customs_terms'], array_keys( Package::get_api()->get_international_customs_terms() ), true ) ) {
				$error->add( 'customs_terms', _x( 'Please choose a customs term.', 'dpd', 'woocommerce-germanized-pro' ) );
			}
		}

		if ( 'cloud' === $this->get_api_type() ) {
			if ( empty( $args['pickup_date'] ) || ! \Vendidero\Germanized\Shipments\Package::is_valid_datetime( $args['pickup_date'], 'Y-m-d' ) ) {
				$error->add( 500, _x( 'Error while parsing pickup date.', 'dpd', 'woocommerce-germanized-pro' ) );
			}
		}

		if ( wc_gzd_shipment_wp_error_has_errors( $error ) ) {
			return $error;
		}

		return $args;
	}

	/**
	 * @param Shipment $shipment
	 *
	 * @return array
	 */
	protected function get_default_label_props( $shipment ) {
		if ( 'return' === $shipment->get_type() ) {
			$dpd_defaults = $this->get_default_return_label_props( $shipment );
		} else {
			$dpd_defaults = $this->get_default_simple_label_props( $shipment );
		}

		$defaults = parent::get_default_label_props( $shipment );

		return array_replace_recursive( $defaults, $dpd_defaults );
	}

	/**
	 * @param Shipment $shipment
	 *
	 * @return array
	 */
	protected function get_default_return_label_props( $shipment ) {
		$product_id = $this->get_default_label_product( $shipment );
		$defaults   = array();

		if ( 'cloud' === $this->get_api_type() ) {
			if ( $pickup_date = Package::get_api()->get_next_available_pickup_date( $product_id ) ) {
				$defaults = array_merge(
					$defaults,
					array(
						'pickup_date' => $pickup_date->format( 'Y-m-d' ),
					)
				);
			}
		}

		return $defaults;
	}

	/**
	 * @param Shipment $shipment
	 *
	 * @return array
	 */
	protected function get_default_simple_label_props( $shipment ) {
		$product_id = $this->get_default_label_product( $shipment );
		$defaults   = array();

		if ( 'web_connect' === $this->get_api_type() ) {
			$defaults = array_merge(
				$defaults,
				array(
					'customs_terms' => $this->get_setting( 'label_default_customs_terms', $this->get_default_customs_terms() ),
					'customs_paper' => $this->get_setting( 'label_default_customs_paper', $this->get_default_customs_paper() ),
				)
			);
		} elseif ( 'cloud' === $this->get_api_type() ) {
			if ( $pickup_date = Package::get_api()->get_next_available_pickup_date( $product_id ) ) {
				$defaults = array_merge(
					$defaults,
					array(
						'pickup_date' => $pickup_date->format( 'Y-m-d' ),
					)
				);
			}
		}

		return $defaults;
	}

	protected function get_available_base_countries() {
		return Package::get_supported_countries();
	}

	protected function get_general_settings( $for_shipping_method = false ) {
		$settings = array(
			array(
				'title' => '',
				'type'  => 'title',
				'id'    => 'dpd_api_options',
			),

			array(
				'title'   => _x( 'API', 'dpd', 'woocommerce-germanized-pro' ),
				'type'    => 'select',
				'id'      => 'api_type',
				'default' => 'cloud',
				'value'   => $this->get_setting( 'api_type', 'cloud' ),
				'desc'    => '<div class="wc-gzd-additional-desc">' . sprintf( _x( 'DPD offers two different API\'s. Many DPD customers may only have access to the Cloud Webservice. <a href="%1$s">Learn more</a>', 'dpd', 'woocommerce-germanized-pro' ), 'https://vendidero.de/dokument/dpd-integration-einrichten#api-typen' ) . '</div>',
				'options' => array(
					'cloud'       => _x( 'Cloud Webservice', 'dpd', 'woocommerce-germanized-pro' ),
					'web_connect' => _x( 'WebConnect', 'dpd', 'woocommerce-germanized-pro' ),
				),
			),

			array(
				'title'             => _x( 'Username (Delis ID)', 'dpd', 'woocommerce-germanized-pro' ),
				'type'              => 'text',
				'desc'              => '<div class="wc-gzd-additional-desc">' . sprintf( _x( 'Please use your WebConnect username (Delis ID) and password to connect your shop to the <a href="%1$s">DPD WebConnect API</a>.', 'dpd', 'woocommerce-germanized-pro' ), 'https://vendidero.de/dokument/dpd-integration-einrichten#dpd-webconnect' ) . '</div>',
				'id'                => 'api_username',
				'default'           => '',
				'value'             => $this->get_setting( 'api_username', '' ),
				'custom_attributes' => array(
					'data-show_if_api_type' => 'web_connect',
					'autocomplete'          => 'new-password',
				),
			),

			array(
				'title'             => _x( 'Password', 'dpd', 'woocommerce-germanized-pro' ),
				'type'              => 'password',
				'desc'              => '',
				'id'                => 'api_password',
				'value'             => $this->get_setting( 'api_password', '' ),
				'custom_attributes' => array(
					'data-show_if_api_type' => 'web_connect',
					'autocomplete'          => 'new-password',
				),
			),

			array(
				'title'             => _x( 'Username (Cloud User ID)', 'dpd', 'woocommerce-germanized-pro' ),
				'type'              => 'text',
				'desc'              => '<div class="wc-gzd-additional-desc">' . sprintf( _x( 'Please use your Cloud User ID and password to connect your shop to the <a href="%1$s">DPD Cloud Webservice</a>.', 'dpd', 'woocommerce-germanized-pro' ), 'https://vendidero.de/dokument/dpd-integration-einrichten#dpd-cloud-webservice' ) . '</div>',
				'id'                => 'cloud_api_username',
				'default'           => '',
				'value'             => $this->get_setting( 'cloud_api_username', '' ),
				'custom_attributes' => array(
					'data-show_if_api_type' => 'cloud',
					'autocomplete'          => 'new-password',
				),
			),

			array(
				'title'             => _x( 'Password (Token)', 'dpd', 'woocommerce-germanized-pro' ),
				'type'              => 'password',
				'desc'              => '',
				'id'                => 'cloud_api_password',
				'value'             => $this->get_setting( 'cloud_api_password', '' ),
				'custom_attributes' => array(
					'data-show_if_api_type' => 'cloud',
					'autocomplete'          => 'new-password',
				),
			),

			array(
				'type' => 'sectionend',
				'id'   => 'dpd_api_options',
			),
		);

		$settings = array_merge(
			$settings,
			array(
				array(
					'title' => _x( 'Tracking', 'dpd', 'woocommerce-germanized-pro' ),
					'type'  => 'title',
					'id'    => 'tracking_options',
				),
			)
		);

		$general_settings = parent::get_general_settings();

		return array_merge( $settings, $general_settings );
	}

	/**
	 * @param ConfigurationSet $configuration_set
	 *
	 * @return mixed
	 */
	protected function get_label_settings_by_zone( $configuration_set ) {
		$settings = parent::get_label_settings_by_zone( $configuration_set );

		if ( 'web_connect' === $this->get_api_type() && 'shipping_provider' === $configuration_set->get_setting_type() ) {
			if ( 'int' === $configuration_set->get_zone() && 'simple' === $configuration_set->get_shipment_type() ) {
				$settings = array_merge(
					$settings,
					array(
						array(
							'title'    => _x( 'Default Customs Terms', 'dpd', 'woocommerce-germanized-pro' ),
							'type'     => 'select',
							'default'  => self::get_default_customs_terms(),
							'id'       => 'label_default_customs_terms',
							'value'    => $this->get_setting( 'label_default_customs_terms', $this->get_default_customs_terms() ),
							'desc'     => _x( 'Please select your default customs terms.', 'dpd', 'woocommerce-germanized-pro' ),
							'desc_tip' => true,
							'options'  => Package::get_api()->get_international_customs_terms(),
							'class'    => 'wc-enhanced-select',
						),
						array(
							'title'    => _x( 'Default Customs Paper', 'dpd', 'woocommerce-germanized-pro' ),
							'type'     => 'multiselect',
							'default'  => self::get_default_customs_paper(),
							'id'       => 'label_default_customs_paper',
							'value'    => $this->get_setting( 'label_default_customs_paper', $this->get_default_customs_paper() ),
							'desc'     => _x( 'Please select which documents you are attaching to international shipments.', 'dpd', 'woocommerce-germanized-pro' ),
							'desc_tip' => true,
							'options'  => Package::get_api()->get_international_customs_paper(),
							'class'    => 'wc-enhanced-select',
						),
					)
				);
			}
		}

		return $settings;
	}

	public function update_settings( $section = '', $data = null, $save = true ) {
		$settings_to_save       = Settings::get_sanitized_settings( $this->get_settings( $section ), $data );
		$restore_label_defaults = false;

		if ( isset( $settings_to_save['api_type'] ) && $settings_to_save['api_type'] !== $this->get_api_type( 'edit' ) ) {
			$restore_label_defaults = true;
		}

		/**
		 * Reset pickup details transient when username changes
		 */
		if ( isset( $settings_to_save['cloud_api_username'] ) && $settings_to_save['cloud_api_username'] !== $this->get_setting( 'cloud_api_username' ) ) {
			delete_transient( 'dpd_pickup_details' );
		}

		parent::update_settings( $section, $data, $save );

		/**
		 * In case the API type has changed, make sure to restore defaults to prevent setting mismatches.
		 */
		if ( $restore_label_defaults ) {
			$this->reset_configuration_sets();

			foreach ( $this->get_printing_settings() as $setting ) {
				$type    = isset( $setting['type'] ) ? $setting['type'] : 'title';
				$default = isset( $setting['default'] ) ? $setting['default'] : null;

				if ( in_array( $type, array( 'title', 'sectionend', 'html' ), true ) || ! isset( $setting['id'] ) || empty( $setting['id'] ) ) {
					continue;
				}

				$this->update_setting( $setting['id'], $default );
			}

			foreach ( \WC_Shipping_Zones::get_zones() as $zone_data ) {
				if ( $zone = \WC_Shipping_Zones::get_zone( $zone_data['id'] ) ) {
					foreach ( $zone->get_shipping_methods() as $method ) {
						if ( $shipment_method = MethodHelper::get_provider_method( $method ) ) {
							if ( 'dpd' === $shipment_method->get_shipping_provider() ) {
								$config_sets = $shipment_method->get_configuration_sets();

								if ( ! empty( $config_sets ) ) {
									$shipment_method->reset_configuration_sets();
									$current_settings = $shipment_method->get_method()->instance_settings;

									if ( ! empty( $current_settings ) ) {
										update_option( $shipment_method->get_method()->get_instance_option_key(), apply_filters( 'woocommerce_shipping_' . $shipment_method->get_method()->id . '_instance_settings_values', $current_settings, $shipment_method->get_method() ), 'yes' );
									}
								}
							}
						}
					}
				}
			}
		}
	}

	public function get_help_link() {
		return 'https://vendidero.de/dokument/dpd-integration-einrichten';
	}

	public function get_signup_link() {
		return 'https://www.dpd.com/de/de/versenden/angebot-fuer-geschaeftskunden/';
	}

	public function get_available_label_products( $shipment ) {
		if ( is_callable('parent::get_available_label_products' ) ) {
			return parent::get_available_label_products( $shipment );
		} else {
			return array();
		}
	}

	public function get_default_label_product( $shipment ) {
		if ( is_callable('parent::get_default_label_product' ) ) {
			return parent::get_default_label_product( $shipment );
		} else {
			return '';
		}
	}
}
