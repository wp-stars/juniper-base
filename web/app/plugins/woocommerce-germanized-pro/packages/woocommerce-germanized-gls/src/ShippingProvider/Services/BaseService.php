<?php

namespace Vendidero\Germanized\GLS\ShippingProvider\Services;

use Vendidero\Germanized\Shipments\ShippingProvider\Service;

defined( 'ABSPATH' ) || exit;

class BaseService extends Service {

	protected $level = '';

	public function __construct( $shipping_provider, $args = array() ) {
		parent::__construct( $shipping_provider, $args );

		$args = wp_parse_args(
			$args,
			array(
				'level' => 'shipment',
			)
		);

		$this->level = $args['level'];
	}

	public function get_level() {
		return $this->level;
	}
}
