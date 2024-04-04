<?php

class WC_GZDP_VAT_Validation {

	private $api_url = 'https://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';

	private $client = null;

	private $options = array(
		'debug'            => false,
		'requester_vat_id' => '',
	);

	private $valid = false;

	private $data = array();

	private $errors = false;

	public function __construct( $options = array() ) {
		foreach ( $options as $option => $value ) {
			$this->options[ $option ] = $value;
		}

		if ( ! class_exists( 'SoapClient' ) ) {
			wp_die( esc_html__( 'SoapClient is required to enable VAT validation', 'woocommerce-germanized-pro' ) );
		}

		try {
			$this->client = new SoapClient( $this->api_url, array( 'trace' => true ) );
		} catch ( Exception $e ) {
			WC_germanized_pro()->log( sprintf( 'Error %s while setting up the SOAP Client for VAT validation: %s', $e->getCode(), $e->getMessage() ), 'error', 'vat-validation' );

			$this->valid = false;
		}
	}

	public function check( $country, $nr ) {
		$rs           = null;
		$this->errors = new WP_Error();
		$instance     = WC_germanized_pro();

		if ( $this->client ) {
			try {
				$args = array(
					'countryCode' => $country,
					'vatNumber'   => $nr,
				);

				if ( ! empty( $this->options['requester_vat_id'] ) ) {
					$request_number               = WC_GZDP_VAT_Helper::instance()->get_vat_id_from_string( $this->options['requester_vat_id'] );
					$args['requesterCountryCode'] = $request_number['country'];
					$args['requesterVatNumber']   = $request_number['number'];
				} else {
					$instance->log( sprintf( 'The requester VAT ID is missing.' ), 'info', 'vat-validation' );
				}

				$rs = $this->client->checkVatApprox( $args );

				if ( $rs->valid ) {
					$instance->log( sprintf( 'Successfully validated: %s', $args['countryCode'] . $args['vatNumber'] ), 'info', 'vat-validation' );

					$this->valid = true;
					$this->data  = array(
						'name'       => $this->parse_string( isset( $rs->name ) ? $rs->name : '' ),
						'identifier' => $this->parse_string( isset( $rs->requestIdentifier ) ? $rs->requestIdentifier : '' ), // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						'company'    => $this->parse_string( isset( $rs->traderName ) ? $rs->traderName : '' ), // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						'address'    => $this->parse_string( isset( $rs->traderAddress ) ? $rs->traderAddress : '' ), // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
						'date'       => date_i18n( 'Y-m-d H:i:s' ),
						'raw'        => (array) $rs,
						'vat_id'     => $country . $nr,
					);
				} else {
					$instance->log( sprintf( 'VAT is invalid: %s', $args['countryCode'] . $args['vatNumber'] ), 'info', 'vat-validation' );

					$this->valid = false;
					$this->data  = array();

					$this->errors->add( 'vat-id-invalid', __( 'The VAT ID you\'ve provided is invalid.', 'woocommerce-germanized-pro' ) );
				}
			} catch ( SoapFault $e ) {
				$this->valid = false;
				$this->data  = array();

				$instance->log( sprintf( 'SOAP Error (%s) while performing VAT ID validation: %s', $e->getCode(), $e->getMessage() ), 'error', 'vat-validation' );

				$this->errors->add( 'vat-request-error', __( 'There was an error while validating your VAT ID. Please try again in a few minutes.', 'woocommerce-germanized-pro' ) );
			}
		}

		return apply_filters( 'woocommerce_gzdp_vat_validation_result', $this->valid, $country, $nr, $rs, $this->options, $this->errors );
	}

	/**
	 * @return bool|WP_Error
	 */
	public function get_error_messages() {
		return ( is_wp_error( $this->errors ) && wc_gzd_wp_error_has_errors( $this->errors ) ) ? $this->errors : false;
	}

	public function is_valid() {
		return $this->valid;
	}

	public function get_name() {
		return isset( $this->data['name'] ) ? $this->data['name'] : '';
	}

	public function get_company() {
		return isset( $this->data['company'] ) ? $this->data['company'] : '';
	}

	public function get_identifier() {
		return isset( $this->data['identifier'] ) ? $this->data['identifier'] : '';
	}

	public function get_date() {
		return isset( $this->data['date'] ) ? $this->data['date'] : '';
	}

	public function get_formatted_address() {
		$address = array_filter( array( trim( $this->get_name() ), trim( $this->get_company() ), trim( $this->get_address() ) ) );

		return implode( ' ', $address );
	}

	public function get_raw() {
		return isset( $this->data['raw'] ) ? $this->data['raw'] : '';
	}

	public function get_data() {
		return $this->data;
	}

	public function get_address() {
		return isset( $this->data['address'] ) ? $this->data['address'] : '';
	}

	public function is_debug() {
		return ( true === $this->options['debug'] );
	}

	private function parse_string( $string ) {
		return ( '---' !== $string ? preg_replace( '/\s+/u', ' ', $string ) : false );
	}
}


