<?php
namespace Vendidero\Germanized\Pro\Blocks\BlockTypes;

/**
 * CheckoutOrderSummaryCouponFormBlock class.
 */
class CheckoutShippingVatId extends AbstractInnerBlock {
	/**
	 * Block name.
	 *
	 * @var string
	 */
	protected $block_name = 'checkout-shipping-vat-id';

	protected function enqueue_data( array $attributes = array() ) {
		parent::enqueue_data( $attributes );

		if ( class_exists( 'Vendidero\EUTaxHelper\Helper' ) ) {
			$this->assets->register_data( 'postcodeVatExempts', \Vendidero\EUTaxHelper\Helper::get_vat_postcode_exemptions_by_country() );
		}
	}
}
