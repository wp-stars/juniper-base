<?php

namespace Vendidero\Germanized\DHL\Api;

use Vendidero\Germanized\DHL\Package;
use Vendidero\Germanized\DHL\Label;
use Vendidero\Germanized\DHL\ParcelLocator;
use Vendidero\Germanized\Shipments\Labels\Factory;
use Vendidero\Germanized\Shipments\PDFMerger;
use Vendidero\Germanized\Shipments\PDFSplitter;
use Vendidero\Germanized\Shipments\ShipmentError;

defined( 'ABSPATH' ) || exit;

class LabelRest extends Rest {

	public function get_base_url() {
		return Package::get_label_rest_api_url();
	}

	protected function get_auth() {
		if ( Package::is_debug_mode() ) {
			return $this->get_basic_auth_encode( 'sandy_sandbox', 'pass' );
		} else {
			return $this->get_basic_auth_encode( Package::get_gk_api_user(), Package::get_gk_api_signature() );
		}
	}

	protected function handle_get_response( $response_code, $response_body ) {
		$this->handle_post_response( $response_code, $response_body );
	}

	protected function handle_post_response( $response_code, $response_body ) {
		$response_code = absint( $response_code );

		switch ( $response_code ) {
			case 200:
			case 201:
				break;
			default:
				$error_messages = array();

				if ( isset( $response_body->items ) && isset( $response_body->items[0]->validationMessages ) ) {
					foreach ( $response_body->items[0]->validationMessages as $message ) {
						if ( ! in_array( $message->validationMessage, $error_messages, true ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
							$error_messages[] = $message->validationMessage; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						}
					}
				} elseif ( isset( $response_body->items ) && isset( $response_body->items[0]->message ) ) {
					foreach ( $response_body->items as $message ) {
						$property_path = isset( $message->propertyPath ) ? $message->propertyPath : ''; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						$property_path = str_replace( 'shipments[0].', '', $property_path );

						$error_message = ( ! empty( $error_message ) ? "\n" : '' ) . ( ! empty( $property_path ) ? $property_path . ': ' : '' ) . $message->message;

						if ( ! in_array( $error_message, $error_messages, true ) ) {
							$error_messages[] = $error_message;
						}
					}
				} elseif ( empty( $response_body->status->detail ) && empty( $response_body->detail ) ) {
					$error_message = _x( 'POST error or timeout occurred. Please try again later.', 'dhl', 'woocommerce-germanized' );

					if ( ! in_array( $error_message, $error_messages, true ) ) {
						$error_messages[] = $error_message;
					}
				} else {
					$error_message = ! empty( $response_body->status->detail ) ? $response_body->status->detail : $response_body->detail;

					if ( ! in_array( $error_message, $error_messages, true ) ) {
						$error_messages[] = $error_message;
					}
				}

				Package::log( 'POST Error: ' . $response_code . ' - ' . wc_print_r( $error_messages, true ) );

				throw new \Exception( implode( "\n", $error_messages ), $response_code );
		}
	}

	protected function set_header( $authorization = '', $request_type = 'GET', $endpoint = '' ) {
		parent::set_header( $authorization, $request_type, $endpoint );

		$this->remote_header['Authorization'] = $authorization;
		$this->remote_header['dhl-api-key']   = Package::get_dhl_com_api_key();
		$this->remote_header['Accept']        = '*/*';
	}

	/**
	 * @param \Vendidero\Germanized\DHL\Label\DHL $label
	 *
	 * @throws \Exception
	 */
	public function get_label( $label ) {
		return $this->create_label( $label );
	}

	/**
	 * @param \Vendidero\Germanized\DHL\Label\DHL $label
	 *
	 * @return boolean|ShipmentError
	 * @throws \Exception
	 */
	public function create_label( $label ) {
		$result       = true;
		$shipment     = $label->get_shipment();
		$dhl_provider = Package::get_dhl_shipping_provider();

		if ( ! $shipment ) {
			throw new \Exception( sprintf( _x( 'Could not fetch shipment %d.', 'dhl', 'woocommerce-germanized' ), $label->get_shipment_id() ) );
		}

		$currency            = $shipment->get_order() ? $shipment->get_order()->get_currency() : 'EUR';
		$billing_number_args = array(
			'api_type' => 'dhl.com',
			'services' => $label->get_services(),
		);

		$account_number = wc_gzd_dhl_get_billing_number( $label->get_product_id(), $billing_number_args );
		$services       = array();
		$bank_data      = array();

		foreach ( $label->get_services() as $service ) {
			$service_name = lcfirst( $service );

			if ( in_array( $service, array( 'GoGreen', 'dhlRetoure' ), true ) ) {
				continue;
			}

			switch ( $service ) {
				case 'AdditionalInsurance':
					$services[ $service_name ] = array(
						'currency' => $currency,
						'value'    => apply_filters( 'woocommerce_gzd_dhl_label_api_insurance_amount', $shipment->get_total(), $shipment, $label ),
					);
					break;
				case 'IdentCheck':
					$services[ $service_name ] = array(
						'firstName'   => $shipment->get_first_name(),
						'lastName'    => $shipment->get_last_name(),
						'dateOfBirth' => $label->get_ident_date_of_birth(),
						'minimumAge'  => $label->get_ident_min_age(),
					);
					break;
				case 'CashOnDelivery':
					$bank_data_map = array(
						'bank_holder' => 'accountHolder',
						'bank_name'   => 'bankName',
						'bank_iban'   => 'iban',
						'bank_bic'    => 'bic',
						'bank_ref'    => 'transferNote1',
						'bank_ref_2'  => 'transferNote2',
					);

					$ref_replacements = wc_gzd_dhl_get_label_payment_ref_placeholder( $shipment );

					foreach ( $bank_data_map as $key => $value ) {
						if ( $setting_value = Package::get_setting( $key ) ) {
							$bank_data[ $value ] = $setting_value;

							if ( in_array( $key, array( 'bank_ref', 'bank_ref_2' ), true ) ) {
								$bank_data[ $value ] = str_replace( array_keys( $ref_replacements ), array_values( $ref_replacements ), $bank_data[ $value ] );
							}
						}
					}

					$services[ $service_name ] = array(
						'amount'        => array(
							'currency' => $currency,
							'value'    => $label->get_cod_total(),
						),
						'bankAccount'   => array_diff_key(
							$bank_data,
							array(
								'transferNote1' => '',
								'transferNote2' => '',
							)
						),
						'transferNote1' => $bank_data['transferNote1'],
						'transferNote2' => $bank_data['transferNote2'],
					);
					break;
				case 'PreferredDay':
					$services[ $service_name ] = $label->get_preferred_day();
					break;
				case 'VisualCheckOfAge':
					$services[ $service_name ] = $label->get_visual_min_age();
					break;
				case 'PreferredLocation':
					$services[ $service_name ] = $label->get_preferred_location();
					break;
				case 'PreferredNeighbour':
					$services[ $service_name ] = $label->get_preferred_neighbor();
					break;
				case 'ParcelOutletRouting':
					if ( ! empty( $shipment->get_email() ) ) {
						$services[ $service_name ] = $shipment->get_email();
					}
					break;
				case 'CDP':
					$services['closestDropPoint'] = true;
					break;
				case 'PDDP':
					$services['postalDeliveryDutyPaid'] = true;
					break;
				case 'Endorsement':
					$services[ $service_name ] = wc_gzd_dhl_get_label_endorsement_type( $label, $shipment, 'dhl.com' );
					break;
				default:
					$services[ $service_name ] = true;
			}
		}

		if ( $label->has_inlay_return() ) {
			$services['dhlRetoure'] = array(
				'billingNumber' => wc_gzd_dhl_get_billing_number( 'return', $billing_number_args ),
				'refNo'         => wc_gzd_dhl_get_inlay_return_label_reference( $label, $shipment ),
				'returnAddress' => array(
					'name1'         => $label->get_return_company() ? $label->get_return_company() : $label->get_return_formatted_full_name(),
					'name2'         => $label->get_return_company() ? $label->get_return_formatted_full_name() : '',
					'addressStreet' => $label->get_return_street() . ' ' . $label->get_return_street_number(),
					'postalCode'    => $label->get_return_postcode(),
					'city'          => $label->get_return_city(),
					'state'         => wc_gzd_dhl_format_label_state( $label->get_return_state(), $label->get_return_country() ),
					'contactName'   => $label->get_return_formatted_full_name(),
					'phone'         => $label->get_return_phone(),
					'email'         => $label->get_return_email(),
					'country'       => wc_gzd_country_to_alpha3( $label->get_return_country() ),
				),
			);
		}

		$shipment_request = array(
			'product'       => $label->get_product_id(),
			'billingNumber' => $account_number,
			'refNo'         => mb_substr( wc_gzd_dhl_get_label_customer_reference( $label, $shipment ), 0, 35 ),
			'shipDate'      => Package::get_date_de_timezone( 'Y-m-d' ),
			'shipper'       => array(),
			'consignee'     => array(),
			'details'       => array(
				'weight' => array(
					'uom'   => 'kg',
					'value' => $label->get_weight(),
				),
			),
		);

		if ( ! empty( $services ) ) {
			$shipment_request['services'] = $services;
		}

		if ( $label->has_dimensions() ) {
			$shipment_request['details']['dim'] = array(
				'uom'    => 'mm',
				'height' => wc_format_decimal( wc_get_dimension( $label->get_height(), 'mm', 'cm' ), 0 ),
				'width'  => wc_format_decimal( wc_get_dimension( $label->get_width(), 'mm', 'cm' ), 0 ),
				'length' => wc_format_decimal( wc_get_dimension( $label->get_length(), 'mm', 'cm' ), 0 ),
			);
		}

		/**
		 * This filter allows using a ShipperReference configured in the GKP instead of transmitting
		 * the shipper data from the DHL settings. Use this filter carefully and make sure that the
		 * reference exists.
		 *
		 * @param string $shipper_reference The shipper reference from the GKP.
		 * @param Label\DHL  $label The label instance.
		 *
		 * @since 3.0.5
		 * @package Vendidero/Germanized/DHL
		 */
		$shipper_reference = apply_filters( 'woocommerce_gzd_dhl_label_api_shipper_reference', $dhl_provider->has_custom_shipper_reference() ? $dhl_provider->get_label_custom_shipper_reference() : '', $label );

		if ( ! empty( $shipper_reference ) ) {
			$shipment_request['shipper']['shipperRef'] = $shipper_reference;
		} else {
			$name1   = apply_filters( 'woocommerce_gzd_dhl_label_api_shipper_name1', trim( $shipment->get_sender_company() ? $shipment->get_sender_company() : $shipment->get_formatted_sender_full_name() ), $label );
			$name2   = apply_filters( 'woocommerce_gzd_dhl_label_api_shipper_name2', trim( $shipment->get_sender_company() ? $shipment->get_formatted_sender_full_name() : '' ), $label );
			$name3   = apply_filters( 'woocommerce_gzd_dhl_label_api_shipper_name3', trim( $shipment->get_sender_address_2() ), $label );
			$street  = apply_filters( 'woocommerce_gzd_dhl_label_api_shipper_street', $shipment->get_sender_address_1(), $label );
			$zip     = apply_filters( 'woocommerce_gzd_dhl_label_api_shipper_zip', $shipment->get_sender_postcode(), $label );
			$city    = apply_filters( 'woocommerce_gzd_dhl_label_api_shipper_city', $shipment->get_sender_city(), $label );
			$email   = apply_filters( 'woocommerce_gzd_dhl_label_api_shipper_email', $shipment->get_sender_email(), $label );
			$country = apply_filters( 'woocommerce_gzd_dhl_label_api_shipper_country', $shipment->get_sender_country(), $label );

			$fields_necessary = array(
				'street'    => $street,
				'full_name' => $name1,
				'postcode'  => $zip,
				'city'      => $city,
			);

			$address_fields         = wc_gzd_get_shipment_setting_default_address_fields();
			$missing_address_fields = array();

			foreach ( $fields_necessary as $field => $value ) {
				if ( empty( $value ) && array_key_exists( $field, $address_fields ) ) {
					$missing_address_fields[] = $address_fields[ $field ];
				}
			}

			if ( ! empty( $missing_address_fields ) ) {
				throw new \Exception( sprintf( _x( 'Your shipper address is incomplete (%1$s). Please validate your <a href="%2$s">settings</a> and try again.', 'dhl', 'woocommerce-germanized' ), implode( ', ', $missing_address_fields ), esc_url( admin_url( 'admin.php?page=wc-settings&tab=germanized-shipments&section=address' ) ) ) );
			}

			$shipment_request['shipper'] = array(
				'name1'         => $name1,
				'name2'         => $name2,
				'name3'         => $name3,
				'addressStreet' => $street,
				'postalCode'    => $zip,
				'city'          => $city,
				'country'       => wc_gzd_country_to_alpha3( $country ),
				'email'         => $email,
				'contactName'   => trim( $shipment->get_formatted_sender_full_name() ),
			);
		}

		if ( 'DE' === $shipment->get_country() && $shipment->send_to_external_pickup() ) {
			if ( $shipment->send_to_external_pickup( 'packstation' ) ) {
				$locker_id = filter_var( $shipment->get_address_1(), FILTER_SANITIZE_NUMBER_INT );

				$shipment_request['consignee'] = array(
					'name'       => $shipment->get_formatted_full_name(),
					'lockerID'   => $locker_id,
					'postNumber' => ParcelLocator::get_postnumber_by_shipment( $shipment ),
					'city'       => $shipment->get_city(),
					'postalCode' => $shipment->get_postcode(),
					'country'    => wc_gzd_country_to_alpha3( $shipment->get_country() ),
				);
			} else {
				$retail_id = filter_var( $shipment->get_address_1(), FILTER_SANITIZE_NUMBER_INT );

				$shipment_request['consignee'] = array(
					'name'       => $shipment->get_formatted_full_name(),
					'retailID'   => $retail_id,
					'city'       => $shipment->get_city(),
					'postalCode' => $shipment->get_postcode(),
					'country'    => wc_gzd_country_to_alpha3( $shipment->get_country() ),
				);

				if ( $post_number = ParcelLocator::get_postnumber_by_shipment( $shipment ) ) {
					$shipment_request['consignee']['postNumber'] = $post_number;
				} else {
					$shipment_request['consignee']['email'] = $shipment->get_email();
				}
			}
		} else {
			$formatted_recipient_state = wc_gzd_dhl_format_label_state( $shipment->get_state(), $shipment->get_country() );
			$street_number             = $shipment->get_address_street_number();
			$street_addition           = $shipment->get_address_street_addition();
			$address_1                 = $shipment->get_address_1();
			$address_2                 = $shipment->get_address_2();

			if ( empty( $street_number ) && ! empty( $address_2 ) ) {
				$address_1_tmp   = wc_gzd_split_shipment_street( $address_1 . ' ' . $address_2 );
				$address_1       = $address_1_tmp['street'] . ' ' . $address_1_tmp['number'];
				$address_2       = '';
				$street_addition = $address_1_tmp['addition'];
			}

			$shipment_request['consignee'] = array(
				'name1'                         => $shipment->get_company() ? $shipment->get_company() : $shipment->get_formatted_full_name(),
				'name2'                         => $shipment->get_company() ? $shipment->get_formatted_full_name() : '',
				/**
				 * By default the name3 parameter is used to transmit the additional
				 * address field to the DHL API. You may adjust the field value by using this filter.
				 *
				 * @param string $value The field value.
				 * @param Label\DHL  $label The label instance.
				 *
				 * @since 3.0.3
				 * @package Vendidero/Germanized/DHL
				 */
				'name3'                         => apply_filters( 'woocommerce_gzd_dhl_label_api_receiver_name3', $address_2, $label ),
				'addressStreet'                 => $address_1,
				'additionalAddressInformation1' => $street_addition,
				'postalCode'                    => $shipment->get_postcode(),
				'city'                          => $shipment->get_city(),
				'state'                         => $formatted_recipient_state,
				'country'                       => wc_gzd_country_to_alpha3( $shipment->get_country() ),
				/**
				 * Choose whether to transmit the full name of the shipment receiver as contactPerson
				 * while creating a label.
				 *
				 * @param string $name The name of the shipmen receiver.
				 * @param Label\DHL  $label The label instance.
				 *
				 * @since 3.0.5
				 * @package Vendidero/Germanized/DHL
				 */
				'contactName'                   => apply_filters( 'woocommerce_gzd_dhl_label_api_communication_contact_person', $shipment->get_formatted_full_name(), $label ),
				/**
				 * Choose whether to transfer the phone number to DHL on creating a label.
				 * By default the phone number is not transmitted.
				 *
				 * @param string $phone The phone number.
				 * @param Label\DHL  $label The label instance.
				 *
				 * @since 3.0.3
				 * @package Vendidero/Germanized/DHL
				 */
				'phone'                         => apply_filters( 'woocommerce_gzd_dhl_label_api_communication_phone', '', $label ),
				/**
				 * Choose whether to transfer the email to DHL on creating a label.
				 * By default the email is only transmitted if the customer opted in.
				 *
				 * This email address is not used to notify the customer via DHL. It is only
				 * meant for communicaton purposes.
				 *
				 * @param string $email The email.
				 * @param Label\DHL  $label The label instance.
				 *
				 * @since 3.0.3
				 * @package Vendidero/Germanized/DHL
				 */
				'email'                         => apply_filters( 'woocommerce_gzd_dhl_label_api_communication_email', $label->has_email_notification() || isset( $services['closestDropPoint'] ) ? $shipment->get_email() : '', $label ),
			);
		}

		if ( Package::is_crossborder_shipment( $shipment->get_country(), $shipment->get_postcode() ) ) {
			if ( count( $shipment->get_items() ) > 30 ) {
				throw new \Exception( sprintf( _x( 'Only %1$s shipment items can be processed, your shipment has %2$s items.', 'dhl', 'woocommerce-germanized' ), 30, count( $shipment->get_items() ) ) );
			}

			$customs_label_data = wc_gzd_dhl_get_shipment_customs_data( $label );
			$customs_items      = array();

			foreach ( $customs_label_data['items'] as $item_id => $item_data ) {
				$customs_items[] = array(
					'itemDescription'  => $item_data['description'],
					'countryOfOrigin'  => wc_gzd_country_to_alpha3( $item_data['origin_code'] ),
					'hsCode'           => $item_data['tariff_number'],
					'packagedQuantity' => $item_data['quantity'],
					'itemValue'        => array(
						'currency' => $customs_label_data['currency'],
						'value'    => $item_data['single_value'],
					),
					'itemWeight'       => array(
						'uom'   => 'kg',
						'value' => $item_data['single_weight_in_kg'],
					),
				);
			}

			/**
			 * In case the customs item total weight is greater than label weight (e.g. due to rounding issues) replace it
			 */
			if ( $customs_label_data['item_total_weight_in_kg'] > $label->get_weight() ) {
				$shipment_request['details']['weight']['value'] = $customs_label_data['item_total_weight_in_kg'] + $shipment->get_packaging_weight();
			}

			$export_type = $this->get_export_type( $customs_label_data, $label );

			$customs_data = array(
				'shippingConditions' => $label->get_duties(),
				'postalCharges'      => array(
					'value'    => $customs_label_data['additional_fee'],
					'currency' => $customs_label_data['currency'],
				),
				'exportDescription'  => mb_substr( $customs_label_data['export_reason_description'], 0, 80 ),
				'officeOfOrigin'     => $customs_label_data['place_of_commital'],
				'items'              => $customs_items,
				'exportType'         => strtoupper( $export_type ),
				/**
				 * Filter to allow adjusting the export invoice number.
				 *
				 * @param string $invoice_number The invoice number.
				 * @param Label\Label $label The label instance.
				 *
				 * @since 3.3.4
				 * @package Vendidero/Germanized/DHL
				 */
				'invoiceNo'          => apply_filters( 'woocommerce_gzd_dhl_label_api_export_invoice_number', $customs_label_data['invoice_number'], $label ),
			);

			$shipment_request['customs'] = apply_filters( 'woocommerce_gzd_dhl_label_rest_api_customs_data', $customs_data, $label );
		}

		$shipment_request = apply_filters( 'woocommerce_gzd_dhl_label_rest_api_create_label_request', $shipment_request, $label, $shipment, $this );
		$shipment_request = $this->walk_recursive_remove( $shipment_request );

		$request = array(
			'profile'   => $this->get_profile(),
			'shipments' => array(
				$shipment_request,
			),
		);

		$label_custom_format        = wc_gzd_dhl_get_custom_label_format( $label );
		$label_custom_return_format = wc_gzd_dhl_get_custom_label_format( $label, 'inlay_return' );

		$args = array(
			'combine'    => 'true',
			'mustEncode' => $label->codeable_address_only() ? 'true' : 'false',
		);

		if ( ! empty( $label_custom_format ) ) {
			$args['printFormat'] = $label_custom_format;
		}

		if ( ! empty( $label_custom_return_format ) ) {
			$args['retourePrintFormat'] = $label_custom_return_format;
		}

		$endpoint = add_query_arg( $args, 'orders' );
		$response = $this->post_request( $endpoint, $request );

		try {
			if ( isset( $response->items ) ) {
				$shipment_data = $response->items[0];

				if ( ! isset( $shipment_data->shipmentNo ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					throw new \Exception( _x( 'There was an error generating the label. Please try again or consider switching to sandbox mode.', 'dhl', 'woocommerce-germanized' ) );
				}

				$label->set_number( $shipment_data->shipmentNo ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				$label->save();

				$default_file = base64_decode( $shipment_data->label->b64 ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode,WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

				if ( isset( $shipment_data->returnShipmentNo ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					$return_label = $label->get_inlay_return_label();

					if ( ! $return_label ) {
						if ( $return_label = Factory::get_label( 0, $label->get_shipping_provider(), 'inlay_return' ) ) {
							$return_label->set_parent_id( $label->get_id() );
							$return_label->set_shipment_id( $label->get_shipment_id() );
							$return_label->set_shipping_provider( $label->get_shipping_provider() );

							if ( $shipment = $label->get_shipment() ) {
								$return_label->set_sender_address( $shipment->get_address() );
							}
						}
					}

					if ( $return_label ) {
						$return_label->set_number( $shipment_data->returnShipmentNo ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

						$splitter = new PDFSplitter( $default_file, true );
						$pdfs     = $splitter->split();

						if ( $pdfs && ! empty( $pdfs ) && count( $pdfs ) > 1 ) {
							$return_file = $pdfs[1];
						}

						if ( $return_file ) {
							$return_label->upload_label_file( $return_file );
						}

						$return_label->save();
					}
				}

				$default_path = $label->upload_label_file( $default_file, 'default' );
				$label->save();

				if ( isset( $shipment_data->customsDoc ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					$customs_file = base64_decode( $shipment_data->customsDoc->b64 ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode,WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

					if ( $label->upload_label_file( $customs_file, 'export' ) ) {
						// Merge files
						$merger = new PDFMerger();
						$merger->add( $label->get_default_file() );
						$merger->add( $label->get_export_file() );

						$filename_label = $label->get_filename();
						$file           = $merger->output( $filename_label, 'S' );

						$label->upload_label_file( $file );
					}

					$label->save();
				} else {
					$label->set_path( $default_path );
				}
			}

			if ( isset( $shipment_data->validationMessages ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				$result = new ShipmentError();

				foreach ( $shipment_data->validationMessages as $message ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					$result->add_soft_error( 'label-soft-error', $message->validationMessage ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				}
			}

			if ( in_array( 'AdditionalInsurance', $label->get_services(), true ) && $shipment->get_total() <= 500 ) {
				if ( ! is_a( $result, 'Vendidero\Germanized\Shipments\ShipmentError' ) ) {
					$result = new ShipmentError();
				}

				$result->add_soft_error( 'label-soft-error', _x( 'You\'ve explicitly booked the additional insurance service resulting in additional fees although the shipment total does not exceed EUR 500. The label has been created anyway.', 'dhl', 'woocommerce-germanized' ) );
			}
		} catch ( \Exception $e ) {
			try {
				$this->delete_label( $label );
				$label->delete( true );
			} catch ( \Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			}

			throw new \Exception( _x( 'Error while creating and uploading the label', 'dhl', 'woocommerce-germanized' ) );
		}

		return $result;
	}

	/**
	 * @param Label\DHL $label
	 *
	 * @throws \Exception
	 */
	public function delete_label( $label ) {
		try {
			if ( ! empty( $label->get_number() ) ) {
				$response = $this->delete_request(
					'orders',
					array(
						'profile'  => $this->get_profile(),
						'shipment' => $label->get_number(),
					)
				);

				return true;
			}
		} catch ( \Exception $e ) {
			Package::log( 'Error while cancelling label: ' . $e->getMessage() );

			throw $e;
		}

		return false;
	}

	protected function get_profile() {
		return 'STANDARD_GRUPPENPROFIL';
	}

	protected function walk_recursive_remove( array $array ) {
		foreach ( $array as $k => $v ) {
			if ( is_array( $v ) ) {
				$array[ $k ] = $this->walk_recursive_remove( $v );
			}

			if ( '' === $v ) {
				unset( $array[ $k ] );
			}
		}

		return $array;
	}

	protected function get_export_type( $customs_data, $label ) {
		$export_type = 'commercial_goods';

		if ( isset( $customs_data['export_reason'] ) && ! empty( $customs_data['export_reason'] ) ) {
			if ( 'gift' === $customs_data['export_reason'] ) {
				$export_type = 'PRESENT';
			} elseif ( 'sample' === $customs_data['export_reason'] ) {
				$export_type = 'COMMERCIAL_SAMPLE';
			} elseif ( 'repair' === $customs_data['export_reason'] ) {
				$export_type = 'RETURN_OF_GOODS';
			} elseif ( 'sale' === $customs_data['export_reason'] ) {
				$export_type = 'COMMERCIAL_GOODS';
			} else {
				$export_type = 'OTHER';
			}
		}

		/**
		 * Filter to allow adjusting the export type of a DHL label (for customs). Could be:
		 * <ul>
		 * <li>OTHER</li>
		 * <li>PRESENT</li>
		 * <li>COMMERCIAL_SAMPLE</li>
		 * <li>DOCUMENT</li>
		 * <li>RETURN_OF_GOODS</li>
		 * <li>COMMERCIAL_GOODS</li>
		 * </ul>
		 *
		 * @param string $export_type The export type.
		 * @param Label\Label  $label The label instance.
		 *
		 * @since 3.3.0
		 * @package Vendidero/Germanized/DHL
		 */
		return apply_filters( 'woocommerce_gzd_dhl_label_api_export_type', strtoupper( $export_type ), $label );
	}

	public function test_connection() {
		$error = new \WP_Error();

		try {
			$this->post_request(
				'orders?validate=true',
				array(
					'profile'   => $this->get_profile(),
					'shipments' => array(
						array(
							'product'       => 'V01PAK',
							'billingNumber' => '12345678901234',
							'refNo'         => 'Order No. 1234',
							'shipDate'      => Package::get_date_de_timezone( 'Y-m-d' ),
							'shipper'       => array(
								'name1'         => 'Test',
								'addressStreet' => 'Sträßchensweg 10',
								'postalCode'    => '53113',
								'city'          => 'Bonn',
								'country'       => 'DEU',
							),
							'consignee'     => array(
								'name1'         => 'Test',
								'addressStreet' => 'Kurt-Schumacher-Str. 20',
								'postalCode'    => '53113',
								'city'          => 'Bonn',
								'country'       => 'DEU',
							),
							'details'       => array(
								'weight' => array(
									'uom'   => 'kg',
									'value' => 5,
								),
							),
						),
					),
				)
			);
		} catch ( \Exception $e ) {
			if ( 401 === $e->getCode() ) {
				$error->add( 'unauthorized', _x( 'Your DHL API credentials seem to be invalid.', 'dhl', 'woocommerce-germanized' ) );
			} elseif ( 400 !== $e->getCode() ) {
				$error->add( $e->getCode(), $e->getMessage() );
			}
		}

		return wc_gzd_shipment_wp_error_has_errors( $error ) ? $error : true;
	}
}
