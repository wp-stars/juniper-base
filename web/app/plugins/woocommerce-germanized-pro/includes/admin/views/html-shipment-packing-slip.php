<?php
/**
 * Shipment label HTML for meta box.
 *
 * @package WooCommerce_Germanized/DHL/Admin
 */
defined( 'ABSPATH' ) || exit;

/**
 * @var \Vendidero\Germanized\Pro\StoreaBill\PackingSlip $packing_slip
 * @var \Vendidero\Germanized\Shipments\Shipment $shipment
 */
$actions = array();

if ( $packing_slip ) {
	$actions['download'] = array(
		'url'     => $packing_slip->get_download_url(),
		'name'    => __( 'Download packing slip', 'woocommerce-germanized-pro' ),
		'action'  => 'download_packing_slip',
		'classes' => 'download',
		'target'  => '_blank',
	);

	$actions['refresh'] = array(
		'name'    => __( 'Refresh packing slip', 'woocommerce-germanized-pro' ),
		'action'  => 'create_packing_slip',
		'classes' => 'create-packing-slip refresh',
	);

	$actions['delete'] = array(
		'classes'           => 'remove-packing-slip delete',
		'name'              => __( 'Delete packing slip', 'woocommerce-germanized-pro' ),
		'action'            => 'delete_packing_slip',
		'target'            => '_blank',
		'custom_attributes' => array(
			'data-packing_slip' => $packing_slip->get_id(),
		),
	);
} else {
	$actions['create'] = array(
		'name'    => __( 'Create packing slip', 'woocommerce-germanized-pro' ),
		'action'  => 'create_packing_slip',
		'classes' => 'create-packing-slip create',
	);
}
?>

<div class="wc-gzd-shipment-packing-slip wc-gzd-shipment-action-wrapper column column-spaced col-auto" data-packing_slip="<?php echo esc_attr( $packing_slip ? $packing_slip->get_id() : '' ); ?>">
	<h4><?php echo ( ( $packing_slip ) ? wp_kses_post( $packing_slip->get_title() ) : esc_html__( 'Packing Slip', 'woocommerce-germanized-pro' ) ); ?></h4>

	<div class="wc-gzd-shipment-packing-slip-content">
		<div class="shipment-packing-slip-actions shipment-inner-actions">
			<div class="shipment-packing-slip-actions-wrapper shipment-inner-actions-wrapper shipment-packing-slip-create">
				<?php echo wc_gzd_render_shipment_action_buttons( $actions ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</div>
	</div>
</div>
