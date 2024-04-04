<?php

namespace Vendidero\Germanized\DPD\Api\WebConnect;

use Vendidero\Germanized\DPD\Api\Authentication;
use Vendidero\Germanized\DPD\Label\Retoure;
use Vendidero\Germanized\DPD\Label\Simple;
use Vendidero\Germanized\DPD\Package;
use Vendidero\Germanized\Shipments\Shipment;
use League\ISO3166\ISO3166;

defined( 'ABSPATH' ) || exit;

class Api extends \Vendidero\Germanized\DPD\Api\Api {

	private $client = null;

	/** @var string[] */
	private $endpoint = array(
		self::DEV_ENVIRONMENT  => 'https://public-ws-stage.dpd.com/',
		self::PROD_ENVIRONMENT => 'https://public-ws.dpd.com/',
	);

	public function reset_auth() {
		$username       = sanitize_key( Package::get_api_username() );
		$transient_name = "wc_gzd_dpd_api_auth_{$username}";

		delete_transient( $transient_name );
	}

	/**
	 * @return bool|\WP_Error|Authentication
	 * @throws \SoapFault
	 */
	public function auth( $force = false ) {
		$username       = sanitize_key( Package::get_api_username() );
		$transient_name = "wc_gzd_dpd_api_auth_{$username}";

		if ( $force ) {
			$this->reset_auth();
			$auth_data = null;
		} else {
			$auth_data = get_transient( $transient_name );
		}

		if ( ! $auth_data || ! isset( $auth_data->authToken ) ) {
			try {
				$client = new \SoapClient(
					$this->getEndpoint() . 'services/LoginService/V2_0/?wsdl',
					array(
						'trace' => true,
					)
				);
				$client->__setLocation( $this->getEndpoint() . 'services/LoginService/V2_0/' );

				$response = $client->getAuth(
					array(
						'delisId'         => Package::get_api_username(),
						'password'        => Package::get_api_password(),
						'messageLanguage' => Package::get_api_language(),
					)
				);

				if ( isset( $response->return ) ) {
					$valid = strtotime( 'tomorrow 3:00' ) - time();

					/**
					 * Persist the encrypted key
					 */
					$store            = clone $response->return;
					$store->authToken = \WC_GZD_Secret_Box_Helper::encrypt( $store->authToken );

					set_transient( $transient_name, $store, $valid );

					$authentication = new Authentication();
					$authentication->setAuthToken( $response->return->authToken );
					$authentication->setDelisId( $response->return->delisId );
					$authentication->setDepot( $response->return->depot );
					$authentication->setCustomerUid( $response->return->customerUid );
					$authentication->setMessageLanguage( Package::get_api_language() );

					return $authentication;
				} else {
					return false;
				}
			} catch ( \SoapFault $exception ) {
				return $this->processException( $exception );
			}
		} else {
			$authentication = new Authentication();

			$authentication->setAuthToken( \WC_GZD_Secret_Box_Helper::decrypt( $auth_data->authToken ) );
			$authentication->setDelisId( $auth_data->delisId );
			$authentication->setDepot( $auth_data->depot );
			$authentication->setCustomerUid( $auth_data->customerUid );
			$authentication->setMessageLanguage( Package::get_api_language() );

			return $authentication;
		}
	}

	/**
	 * @param \WP_Error $error
	 * @param \WP_Error $new_error
	 *
	 * @return \WP_Error $error
	 */
	private function merge_error_messages( $error, $new_error ) {
		foreach ( $new_error->get_error_codes() as $code ) {
			foreach ( $new_error->get_error_messages( $code ) as $message ) {
				$error->add( $code, $message );
			}
		}

		return $error;
	}

	public function get_last_request() {
		if ( ! is_null( $this->client ) ) {
			return $this->client->__getLastRequest();
		}

		return false;
	}

	protected function get_numeric_iso_code( $iso_alpha_2 ) {
		$iso         = new ISO3166();
		$iso_numeric = '';

		try {
			$data        = $iso->alpha2( $iso_alpha_2 );
			$iso_numeric = $data['numeric'];
		} catch ( \Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
		}

		return $iso_numeric;
	}

	/**
	 * @param Simple|Retoure $label
	 *
	 * @return \WP_Error|true
	 */
	public function get_label( $label ) {
		$authentication = $this->auth();

		if ( is_wp_error( $authentication ) ) {
			return $authentication;
		}

		try {
			$this->client = new \SoapClient(
				$this->getEndpoint() . 'services/ShipmentService/V4_4/?wsdl',
				array(
					'trace' => true,
				)
			);

			$this->client->__setSoapHeaders(
				new \SoapHeader(
					'http://dpd.com/common/service/types/Authentication/2.0',
					'authentication',
					$authentication
				)
			);

			$this->client->__setLocation( $this->getEndpoint() . 'services/ShipmentService/V4_4/' );

			$shipment       = $label->get_shipment();
			$shipment_ref   = $this->get_reference( apply_filters( 'woocommerce_gzd_dpd_label_api_reference', _x( 'Shipment {shipment_id}', 'dpd', 'woocommerce-germanized-pro' ), $label ), $shipment );
			$shipment_ref_2 = $this->get_reference( apply_filters( 'woocommerce_gzd_dpd_label_api_reference_2', _x( 'Order {order_id}', 'dpd', 'woocommerce-germanized-pro' ), $label ), $shipment, 35 );
			$weight         = Package::convert_weight( $label->get_weight() );
			$length         = Package::convert_dimension( $label->get_length() );
			$width          = Package::convert_dimension( $label->get_width() );
			$height         = Package::convert_dimension( $label->get_height() );
			$volume         = $length * $width * $height;
			$parcel_volume  = str_pad( $length, 3, '0', STR_PAD_LEFT ) . str_pad( $width, 3, '0', STR_PAD_LEFT ) . str_pad( $height, 3, '0', STR_PAD_LEFT );

			$provider = $shipment->get_shipping_provider_instance();
			$error    = new \WP_Error();

			$product_and_service_data = array(
				'orderType'                => 'consignment',
				'customerReferenceNumber1' => $shipment_ref,
				'customerReferenceNumber2' => $shipment_ref_2,
			);

			$label_supports_email_transmit = ( $label->supports_third_party_email_notification() || apply_filters( 'woocommerce_gzd_dpd_label_force_email_notification', wc_string_to_bool( $provider->get_setting( 'label_force_email_transfer', 'no' ) ), $label ) );

			if ( 'return' !== $label->get_type() && $shipment->get_email() && $label_supports_email_transmit && in_array( $label->get_product_id(), array( 'CL' ), true ) ) {
				$product_and_service_data['predict'] = array(
					'channel'  => 1,
					'value'    => $shipment->get_email(),
					'language' => $shipment->get_country(),
				);
			}

			if ( 'E12' === $label->get_product_id() && $label->has_service( 'saturday_delivery' ) ) {
				$product_and_service_data['saturdayDelivery'] = true;
			}

			$sender = array(
				'name1'          => mb_substr( $shipment->get_sender_company() ? $shipment->get_sender_company() : $shipment->get_formatted_sender_full_name(), 0, 50 ),
				'name2'          => mb_substr( trim( $shipment->get_sender_company() ? $shipment->get_formatted_sender_full_name() . ' ' . $shipment->get_sender_address_2() : $shipment->get_sender_address_2() ), 0, 35 ),
				'street'         => mb_substr( $shipment->get_sender_address_street(), 0, 50 ),
				'houseNo'        => mb_substr( $shipment->get_sender_address_street_number(), 0, 8 ),
				'country'        => mb_substr( $shipment->get_sender_country(), 0, 2 ),
				'zipCode'        => mb_substr( $shipment->get_sender_postcode(), 0, 9 ),
				'city'           => mb_substr( $shipment->get_sender_city(), 0, 50 ),
				'contact'        => mb_substr( $shipment->get_formatted_sender_full_name(), 0, 35 ),
				'phone'          => mb_substr( $shipment->get_sender_phone(), 0, 30 ),
				'email'          => mb_substr( $shipment->get_sender_email(), 0, 100 ),
				'customerNumber' => mb_substr( '', 0, 17 ),
			);

			$recipient = array(
				'name1'   => mb_substr( $shipment->get_company() ? $shipment->get_company() : $shipment->get_formatted_full_name(), 0, 50 ),
				'name2'   => mb_substr( $shipment->get_address_2(), 0, 35 ),
				'street'  => mb_substr( $shipment->get_address_street(), 0, 50 ),
				'houseNo' => mb_substr( $shipment->get_address_street_number(), 0, 8 ),
				'country' => mb_substr( $shipment->get_country(), 0, 2 ),
				'zipCode' => mb_substr( $shipment->get_postcode(), 0, 9 ),
				'city'    => mb_substr( $shipment->get_city(), 0, 50 ),
				'contact' => mb_substr( apply_filters( 'woocommerce_gzd_dpd_label_recipient_contact', $shipment->get_company() ? $shipment->get_formatted_full_name() : '', $shipment, $label ), 0, 35 ),
				'phone'   => mb_substr( $shipment->get_phone(), 0, 30 ),
				'email'   => mb_substr( $shipment->get_email(), 0, 100 ),
			);

			if ( ! $label_supports_email_transmit ) {
				if ( 'return' === $label->get_type() ) {
					unset( $sender['phone'] );
					unset( $sender['email'] );
				} else {
					unset( $recipient['phone'] );
					unset( $recipient['email'] );
				}
			}

			$customs_data = $label->get_customs_data();

			/**
			 * Additional international guarantee
			 */
			if ( $label->has_service( 'international_guarantee' ) ) {
				$product_and_service_data['guarantee'] = true;
			}

			if ( $shipment->is_shipping_international() || ( $shipment->is_shipping_inner_eu() && 'IE2' === $label->get_product_id() ) ) {
				$invoice_lines = array();
				$item_count    = 0;

				foreach ( $customs_data['items'] as $key => $item ) {
					$item_count++;

					$invoice_lines[] = array(
						'customsInvoicePosition' => $item_count,
						'quantityItems'          => $item['quantity'],
						'customsContent'         => $item['description'],
						'customsTarif'           => $item['tariff_number'],
						'customsAmountLine'      => round( $item['value'] * 100 ),
						'customsOrigin'          => $this->get_numeric_iso_code( $item['origin_code'] ),
						'customsNetWeight'       => Package::convert_weight( $item['weight_in_kg'] ),
						'customsGrossWeight'     => Package::convert_weight( $item['gross_weight_in_kg'] ),
					);
				}

				/**
				 * Make sure phone, email is available for international shipments (e.g. customs)
				 */
				$recipient['email']   = $shipment->get_email();
				$recipient['phone']   = $shipment->get_phone();
				$recipient['contact'] = empty( $recipient['contact'] ) ? $shipment->get_formatted_full_name() : $recipient['contact'];

				$product_and_service_data['international'] = array(
					// True in case is document (letter) and not cardboard
					'parcelType'                          => apply_filters( 'woocommerce_gzd_dpd_label_api_shipment_is_document', false, $label ),
					'customsAmount'                       => round( $customs_data['item_total_value'] * 100 ),
					'customsCurrency'                     => $customs_data['currency'],
					'customsTerms'                        => $label->get_customs_terms(),
					'customsPaper'                        => apply_filters( 'woocommerce_gzd_dpd_customs_paper', implode( '', $label->get_customs_paper() ), $label ),
					'customsOrigin'                       => Package::get_dpd_shipping_provider()->get_shipper_country(),
					'numberOfArticle'                     => count( $customs_data['items'] ),
					'additionalInvoiceLines'              => $invoice_lines,
					'commercialInvoiceConsignorVatNumber' => apply_filters( 'woocommerce_gzd_dpd_label_api_consignor_vat_id', $customs_data['sender_customs_ref_number'], $label ),
					'commercialInvoiceConsignor'          => $sender,
					'commercialInvoiceConsigneeVatNumber' => apply_filters( 'woocommerce_gzd_dpd_label_api_consignee_vat_id', $customs_data['receiver_customs_ref_number'], $label ),
					'commercialInvoiceConsignee'          => $recipient,
				);

				if ( 'GB' === $shipment->get_country() && $shipment->get_total() <= 135 ) {
					/**
					 * Special rules apply for total amounts <= 135 GBP. Senders need to register for a UK VAT ID
					 * Customs terms need to be set to eDAP.
					 *
					 * @see https://www.dpd.com/de/de/support/international/brexit/
					*/
					$product_and_service_data['international']['countryRegistrationNumber'] = isset( $customs_data['sender_customs_uk_vat_id'] ) ? $customs_data['sender_customs_uk_vat_id'] : '';
					$product_and_service_data['international']['customsTerms']              = '07';

					if ( empty( $product_and_service_data['international']['countryRegistrationNumber'] ) ) {
						$error->add( 'missing_field', _x( 'Please provide your UK VAT ID in the shipment address settings.', 'dpd', 'woocommerce-germanized-pro' ) );
					}
				}

				$recipient_error = $this->is_valid_address( $recipient, 'recipient', array( 'email' ) );
				$sender_error    = $this->is_valid_address( $sender, 'sender', array( 'email', 'phone' ) );
			} else {
				$recipient_error = $this->is_valid_address( $recipient );
				$sender_error    = $this->is_valid_address( $sender, 'sender' );
			}

			if ( is_wp_error( $recipient_error ) ) {
				$error = $this->merge_error_messages( $error, $recipient_error );
			}

			if ( is_wp_error( $sender_error ) ) {
				$error = $this->merge_error_messages( $error, $sender_error );
			}

			if ( wc_gzd_shipment_wp_error_has_errors( $error ) ) {
				return $error;
			}

			$content_description = $customs_data['export_type_description'];

			if ( $label->has_service( 'higher_insurance' ) && $label->get_service_prop( 'higher_insurance', 'description' ) ) {
				$content_description = $label->get_service_prop( 'higher_insurance', 'description' );
			}

			$parcel_data = array(
				'weight'                  => $weight,
				'content'                 => mb_strcut( apply_filters( 'woocommerce_gzd_dpd_label_api_parcel_content', $content_description, $label ), 0, 35 ),
				'printInfo1OnParcelLabel' => '' === apply_filters( 'woocommerce_gzd_dpd_label_api_parcel_info', '', $label ) ? false : true,
				'info1'                   => apply_filters( 'woocommerce_gzd_dpd_label_api_parcel_info', '', $label ),
			);

			if ( $volume > 0 ) {
				$parcel_data['volume'] = $parcel_volume;
			}

			if ( $label->has_service( 'higher_insurance' ) ) {
				$amount = $label->get_service_prop( 'higher_insurance', 'amount' ) ? wc_format_decimal( $label->get_service_prop( 'higher_insurance', 'amount' ) ) : '';

				if ( empty( $amount ) ) {
					$amount = $shipment->get_total();
				}

				$parcel_data['higherInsurance'] = array(
					'amount'   => round( (float) $amount * 100 ),
					'currency' => $shipment->get_order() ? $shipment->get_order()->get_currency() : get_woocommerce_currency(),
				);
			}

			$request = array(
				'printOptions' => array(
					'printOption' => array(
						'outputFormat' => 'PDF',
						'paperFormat'  => $label->get_page_format(),
					),
				),
				'order'        => array(
					'generalShipmentData'   => array(
						'sendingDepot'                => $authentication->getDepot(),
						'product'                     => $label->get_product_id(),
						'mpsWeight'                   => $weight,
						'mpsCustomerReferenceNumber1' => $shipment_ref,
						'mpsCustomerReferenceNumber2' => $shipment_ref_2,
						'identificationNumber'        => $shipment->get_shipment_number(),
						'sender'                      => $sender,
						'recipient'                   => $recipient,
					),
					'parcels'               => $parcel_data,
					'productAndServiceData' => $product_and_service_data,
				),
			);

			if ( $volume > 0 ) {
				$request['order']['generalShipmentData']['mpsVolume'] = $volume;
			}

			if ( 'return' === $label->get_type() ) {
				$request['returns'] = true;
			}

			$clean_request = $this->clean_request( $request );
			$response      = $this->client->storeOrders( apply_filters( 'woocommerce_gzd_dpd_label_api_request', $clean_request, $label ) );

			$error   = new \WP_Error();
			$success = false;

			if ( isset( $response->orderResult, $response->orderResult->shipmentResponses ) ) {
				$order_result      = $response->orderResult;
				$shipment_response = $order_result->shipmentResponses;

				/**
				 * Error handling
				 */
				if ( isset( $shipment_response->faults ) ) {
					if ( is_array( $shipment_response->faults ) ) {
						foreach ( $shipment_response->faults as $fault ) {
							$error->add( $fault->faultCode, $fault->message );
						}
					} else {
						$error->add( $shipment_response->faults->faultCode, $shipment_response->faults->message );
					}
				}

				if ( ! wc_gzd_shipment_wp_error_has_errors( $error ) && isset( $shipment_response->mpsId, $shipment_response->parcelInformation ) ) {
					$parcel_information = $shipment_response->parcelInformation;
					$success            = true;

					$label_number = $parcel_information->parcelLabelNumber;
					$mps_id       = $shipment_response->mpsId;
					$pdf          = $order_result->output->content;

					$label->set_number( $label_number );
					$label->set_mps_id( $mps_id );

					if ( $path = $label->upload_label_file( $pdf ) ) {
						$label->set_path( $path );
					} else {
						$error->add( 'upload', _x( 'Error while uploading DPD label.', 'dpd', 'woocommerce-germanized-pro' ) );
					}

					$label->save();
				}
			}

			if ( ! $success && ! wc_gzd_shipment_wp_error_has_errors( $error ) ) {
				$error->add( 'label_error', _x( 'There was an error requesting the label for DPD.', 'dpd', 'woocommerce-germanized-pro' ) );
			}

			return wc_gzd_shipment_wp_error_has_errors( $error ) ? $error : true;
		} catch ( \SoapFault $exception ) {
			return $this->processException( $exception );
		}
	}

	protected function clean_request( $array ) {
		foreach ( $array as $k => $v ) {

			if ( is_array( $v ) ) {
				$array[ $k ] = $this->clean_request( $v );
			}

			if ( '' === $v ) {
				unset( $array[ $k ] );
			}
		}

		return $array;
	}

	/**
	 * @param \SoapFault $exception
	 *
	 * @return \WP_Error
	 */
	protected function processException( $exception ) {
		if ( is_object( isset( $exception->detail ) ? $exception->detail : null ) && is_object( isset( $exception->detail->authenticationFault ) ? $exception->detail->authenticationFault : null ) ) {
			return new \WP_Error(
				$exception->detail->authenticationFault->errorCode,
				$exception->detail->authenticationFault->errorMessage
			);
		} else {
			return new \WP_Error(
				'api_error',
				$exception->getMessage()
			);
		}
	}

	/**
	 * @return string
	 */
	protected function getEndpoint() {
		return $this->endpoint[ static::$environment ];
	}
}
