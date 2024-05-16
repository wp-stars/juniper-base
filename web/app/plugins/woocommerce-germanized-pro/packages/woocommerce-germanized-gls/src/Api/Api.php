<?php

namespace Vendidero\Germanized\GLS\Api;

use Vendidero\Germanized\GLS\Label\Retoure;
use Vendidero\Germanized\GLS\Label\Simple;
use Vendidero\Germanized\GLS\Package;
use Vendidero\Germanized\Shipments\Shipment;

defined( 'ABSPATH' ) || exit;

/**
 * GLS ShipIt API
 *
 * @see https://shipit.gls-group.eu/webservices/3_2_9/doxygen/WS-REST-API/index.html
 */
class Api {
	const DEV_ENVIRONMENT  = 0;
	const PROD_ENVIRONMENT = 1;

	/** @var Api */
	private static $instance;

	/** @var int */
	protected static $environment = self::DEV_ENVIRONMENT;

	/**
	 * @return Api
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	/**
	 * Set API environment to development version
	 */
	public static function dev() {
		self::$environment = self::DEV_ENVIRONMENT;
	}

	/**
	 * Set API environment to production version
	 */
	public static function prod() {
		self::$environment = self::PROD_ENVIRONMENT;
	}

	public static function is_sandbox() {
		return self::DEV_ENVIRONMENT === self::$environment;
	}

	/**
	 * @param string $ref_text
	 * @param Shipment $shipment
	 * @param int $max_length
	 *
	 * @return string
	 */
	protected function get_reference( $ref_text, $shipment, $max_length = 50 ) {
		return mb_strcut( str_replace( array( '{shipment_id}', '{order_id}' ), array( $shipment->get_shipment_number(), $shipment->get_order_number() ), $ref_text ), 0, $max_length );
	}

	/**
	 * @param Simple|Retoure $label
	 *
	 * @return \WP_Error|true
	 */
	public function cancel_label( $label ) {
		if ( $label->get_gls_track_id() ) {
			$response = $this->post( 'cancel/' . $label->get_gls_track_id() );

			if ( is_wp_error( $response ) ) {
				return $response;
			} else {
				return true;
			}
		}

		return new \WP_Error( 'gls_error', _x( 'There was an error while cancelling the label', 'gls', 'woocommerce-germanized-pro' ) );
	}

	/**
	 * @param Simple|Retoure $label
	 *
	 * @return \WP_Error|true
	 */
	public function get_label( $label ) {
		$shipment                      = $label->get_shipment();
		$provider                      = $shipment->get_shipping_provider_instance();
		$label_supports_email_transmit = ( $label->supports_third_party_email_notification() || apply_filters( 'woocommerce_gzd_gls_label_force_email_notification', false, $label ) );
		$is_return                     = 'return' === $label->get_type();
		$services                      = $label->get_services();

		if ( in_array( 'FlexDeliveryService', $services, true ) ) {
			$label_supports_email_transmit = true;
		}

		$name_1 = $shipment->get_company() ? $shipment->get_company() : $shipment->get_formatted_full_name();
		$name_2 = $shipment->get_company() ? $shipment->get_formatted_full_name() : $shipment->get_company();

		if ( $is_return ) {
			$name_1 = $shipment->get_sender_company() ? $shipment->get_sender_company() : $shipment->get_formatted_sender_full_name();
			$name_2 = $shipment->get_sender_company() ? $shipment->get_formatted_sender_full_name() : $shipment->get_sender_company();
		}

		/**
		 * GLS takes care of switching consignee address in case of returns.
		 */
		$recipient_address = array(
			'Name1'                => $name_1,
			'Name2'                => $name_2,
			'Name3'                => $is_return ? $shipment->get_sender_address_2() : $shipment->get_address_2(),
			'CountryCode'          => $is_return ? $shipment->get_sender_country() : $shipment->get_country(),
			'ZIPCode'              => $is_return ? $shipment->get_sender_postcode() : $shipment->get_postcode(),
			'City'                 => $is_return ? $shipment->get_sender_city() : $shipment->get_city(),
			'Street'               => $is_return ? $shipment->get_sender_address_1() : $shipment->get_address_1(),
			'eMail'                => $label_supports_email_transmit || $is_return ? ( $is_return ? $shipment->get_sender_email() : $shipment->get_email() ) : '',
			'ContactPerson'        => $is_return ? $shipment->get_formatted_sender_full_name() : $shipment->get_formatted_full_name(),
			'FixedLinePhonenumber' => $is_return ? $shipment->get_sender_phone() : $shipment->get_phone(),
		);

		$shipment_unit_services = array();
		$shipment_services      = array();

		foreach ( $label->get_services() as $service ) {
			$service_obj        = $provider->get_service( $service );
			$clean_service_name = str_replace( 'service', '', strtolower( $service ) );
			$inner_service      = array(
				'ServiceName' => 'service_' . $clean_service_name,
			);

			if ( 'AddonLiability' === $service ) {
				$inner_service['Amount']   = wc_format_decimal( $label->get_service_prop( 'AddonLiability', 'Amount' ) );
				$inner_service['Currency'] = $label->get_service_prop( 'AddonLiability', 'Currency' );
			}

			if ( 'unit' === $service_obj->get_level() ) {
				$the_service = array(
					$service => $inner_service,
				);
			} else {
				$the_service = array(
					'Service' => $inner_service,
				);
			}

			if ( 'shipment' === $service_obj->get_level() ) {
				$shipment_services[] = $the_service;
			} else {
				$shipment_unit_services[] = $the_service;
			}
		}

		if ( 'return' === $label->get_type() ) {
			if ( 'shop_return' === $label->get_return_type() ) {
				$shipment_services[] = array(
					'ShopReturn' => array(
						'ServiceName'    => 'service_shopreturn',
						'NumberOfLabels' => 1,
					),
				);
			} elseif ( 'pick_and_return' === $label->get_return_type() ) {
				$shipment_services[] = array(
					'PickAndReturn' => array(
						'ServiceName' => 'service_pickandreturn',
						'PickupDate'  => $label->get_pickup_date(),
					),
				);
			}
		}

		$request = array(
			'Shipment'        => array(
				'ShipmentReference' => array( $shipment->get_id() ),
				'Product'           => $label->get_product_id(),
				'ShippingDate'      => $label->get_shipping_date(),
				'IncotermCode'      => $label->get_incoterms(),
				'Middleware'        => 'vendideroGermanizedviaGLS',
				'Consignee'         => array(
					'Address'  => $recipient_address,
					'Category' => ! empty( $recipient_address['Name2'] ) ? 'BUSINESS' : 'PRIVATE',
				),
				'Shipper'           => array(
					'ContactID' => Package::get_api_contact_id(),
				),
				'ShipmentUnit'      => array(
					array(
						'ShipmentUnitReference' => array( $this->get_reference( apply_filters( 'woocommerce_gzd_gls_label_api_reference', _x( 'Shipment {shipment_id}', 'gls', 'woocommerce-germanized-pro' ), $label ), $shipment, 40 ) ),
						'Weight'                => $label->get_weight(),
						'Note1'                 => $this->get_reference( apply_filters( 'woocommerce_gzd_gls_label_api_note', '', $label ), $shipment, 50 ),
						'Note2'                 => $this->get_reference( apply_filters( 'woocommerce_gzd_gls_label_api_note_2', '', $label ), $shipment, 50 ),
						'Service'               => $shipment_unit_services,
					),
				),
				'Service'           => $shipment_services,
			),
			'PrintingOptions' => array(
				'ReturnLabels' => array(
					'TemplateSet' => 'NONE',
					'LabelFormat' => 'PDF',
				),
			),
		);

		$request  = $this->clean_request( $request );
		$response = $this->post( '', apply_filters( 'woocommerce_gzd_gls_label_api_request', $request, $label ) );

		if ( ! is_wp_error( $response ) && isset( $response['body']['CreatedShipment']['ParcelData'] ) ) {
			$error         = new \WP_Error();
			$parcel_data   = $response['body']['CreatedShipment']['ParcelData'][0];
			$track_id      = wc_clean( $parcel_data['TrackID'] );
			$parcel_number = wc_clean( $parcel_data['ParcelNumber'] );

			$label->set_number( $parcel_number );
			$label->set_gls_track_id( $track_id );

			if ( isset( $response['body']['CreatedShipment']['PrintData'] ) ) {
				$pdf = base64_decode( $response['body']['CreatedShipment']['PrintData'][0]['Data'] ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode

				if ( $path = $label->upload_label_file( $pdf ) ) {
					$label->set_path( $path );
				} else {
					$error->add( 'upload', _x( 'Error while uploading GLS label.', 'gls', 'woocommerce-germanized-pro' ) );
				}
			}

			$label->save();

			if ( wc_gzd_shipment_wp_error_has_errors( $error ) ) {
				return $error;
			}
		}

		return is_wp_error( $response ) ? $response : true;
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

	protected function get_api_base_url() {
		return trailingslashit( Package::get_api_url() ) . 'backend/rs/shipments';
	}

	protected function get_timeout( $request_type = 'GET' ) {
		return 'GET' === $request_type ? 30 : 100;
	}

	protected function get_header() {
		$headers = array();

		$headers['Content-Type']  = 'application/glsVersion1+json';
		$headers['Accept']        = 'application/glsVersion1+json, application/json';
		$headers['Authorization'] = 'Basic ' . base64_encode( Package::get_api_username() . ':' . Package::get_api_password() ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$headers['User-Agent']    = 'Germanized/' . Package::get_version();

		return $headers;
	}

	/**
	 * @param $endpoint
	 * @param $type
	 * @param $body_args
	 *
	 * @return array|\WP_Error
	 */
	protected function get_response( $endpoint, $type = 'GET', $body_args = array() ) {
		$url  = untrailingslashit( trailingslashit( self::get_api_base_url() ) . $endpoint );
		$code = 400;

		if ( 'GET' === $type ) {
			$response = wp_remote_get(
				esc_url_raw( $url ),
				array(
					'headers' => $this->get_header(),
					'timeout' => $this->get_timeout( $type ),
				)
			);
		} elseif ( 'POST' === $type ) {
			$response = wp_remote_post(
				esc_url_raw( $url ),
				array(
					'headers' => $this->get_header(),
					'timeout' => $this->get_timeout( $type ),
					'body'    => wp_json_encode( $body_args, JSON_PRETTY_PRINT ),
				)
			);
		}

		if ( false !== $response ) {
			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$code    = wp_remote_retrieve_response_code( $response );
			$body    = wp_remote_retrieve_body( $response );
			$headers = wp_remote_retrieve_headers( $response );

			if ( (int) $code >= 300 ) {
				return $this->parse_error( $body, $headers, $code );
			}

			return array(
				'code'    => (int) $code,
				'raw'     => $body,
				'headers' => $headers,
				'body'    => json_decode( $body, true ),
			);
		}

		return new \WP_Error( absint( $code ), sprintf( esc_html_x( 'Error while querying GLS endpoint %s', 'gls', 'woocommerce-germanized-pro' ), esc_url_raw( $endpoint ) ) );
	}

	protected function post( $endpoint, $data = array() ) {
		return $this->get_response( $endpoint, 'POST', $data );
	}

	/**
	 * @param $body
	 * @param $headers
	 *
	 * @return \WP_Error
	 */
	protected function parse_error( $body, $headers, $code ) {
		$error = new \WP_Error();

		if ( isset( $headers['message'] ) ) {
			$error->add( $code, wp_kses_post( htmlentities( utf8_encode( $headers['message'] ) ) ) );
		} elseif ( is_string( $body ) ) {
			$body = wp_strip_all_tags( $body );

			if ( 'error' === substr( strtolower( $body ), 0, 5 ) ) {
				$body = substr( $body, 5 );
			}

			$error->add( $code, wp_kses_post( $body ) );
		} else {
			$error->add( $code, _x( 'There was an unknown error calling the GLS API.', 'gls', 'woocommerce-germanized-pro' ) );
		}

		return $error;
	}

	/** Disabled Api constructor. Use Api::instance() as singleton */
	protected function __construct() {}
}
