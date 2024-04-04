<?php

namespace Vendidero\StoreaBill\Interfaces;

/**
 * Order Interface
 *
 * @package  Germanized/StoreaBill/Interfaces
 * @version  1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Order class.
 */
interface PaymentMethod extends Reference {

	public function get_name();

	public function get_description();

	public function get_title();

	public function get_instructions();
}
