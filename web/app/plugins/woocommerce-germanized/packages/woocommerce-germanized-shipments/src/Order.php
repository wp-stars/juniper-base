<?php

namespace Vendidero\Germanized\Shipments;

use Exception;
use Vendidero\Germanized\Shipments\Admin\Settings;
use Vendidero\Germanized\Shipments\Packing\Helper;
use Vendidero\Germanized\Shipments\Packing\ItemList;
use Vendidero\Germanized\Shipments\Packing\OrderItem;
use Vendidero\Germanized\Shipments\ShippingMethod\MethodHelper;
use Vendidero\Germanized\Shipments\ShippingMethod\ProviderMethod;
use Vendidero\Germanized\Shipments\ShippingMethod\ShippingMethod;
use WC_DateTime;
use DateTimeZone;
use WC_Order;
use WC_Customer;
use WC_Order_Item;

defined( 'ABSPATH' ) || exit;

/**
 * Shipment Order
 *
 * @class       WC_GZD_Shipment_Order
 * @version     1.0.0
 * @author      Vendidero
 */
class Order {

	/**
	 * The actual order item object
	 *
	 * @var object
	 */
	protected $order;

	protected $shipments = null;

	protected $package_data = null;

	protected $shipments_to_delete = array();

	/**
	 * @param WC_Customer $customer
	 */
	public function __construct( $order ) {
		$this->order = $order;
	}

	/**
	 * Returns the Woo WC_Order original object
	 *
	 * @return object|WC_Order
	 */
	public function get_order() {
		return $this->order;
	}

	/**
	 * @return WC_DateTime|null
	 */
	public function get_date_shipped() {
		$date_shipped = $this->get_order()->get_meta( '_date_shipped', true );

		if ( $date_shipped ) {
			try {
				$date_shipped = new WC_DateTime( "@{$date_shipped}" );

				// Set local timezone or offset.
				if ( get_option( 'timezone_string' ) ) {
					$date_shipped->setTimezone( new DateTimeZone( wc_timezone_string() ) );
				} else {
					$date_shipped->set_utc_offset( wc_timezone_offset() );
				}
			} catch ( Exception $e ) {
				$date_shipped = null;
			}
		} else {
			$date_shipped = null;
		}

		return $date_shipped;
	}

	public function is_shipped() {
		$shipping_status = $this->get_shipping_status();

		return apply_filters( 'woocommerce_gzd_shipment_order_shipping_status', ( in_array( $shipping_status, array( 'shipped', 'delivered' ), true ) || ( 'partially-delivered' === $shipping_status && ! $this->needs_shipping( array( 'sent_only' => true ) ) ) ), $this );
	}

	public function get_last_tracking_id() {
		$tracking_id = '';

		foreach ( array_reverse( $this->get_simple_shipments( true ) ) as $shipment ) {
			if ( ! empty( $shipment->get_tracking_id() ) ) {
				$tracking_id = $shipment->get_tracking_id();
				break;
			}
		}

		return apply_filters( 'woocommerce_gzd_shipment_order_last_tracking_id', $tracking_id, $this );
	}

	public function get_shipping_status() {
		$status                  = 'not-shipped';
		$shipments               = $this->get_simple_shipments();
		$all_shipments_delivered = false;
		$all_shipments_shipped   = false;

		if ( ! empty( $shipments ) ) {
			$all_shipments_delivered = true;
			$all_shipments_shipped   = true;

			foreach ( $shipments as $shipment ) {
				if ( ! $shipment->has_status( 'delivered' ) ) {
					$all_shipments_delivered = false;
				} else {
					$status = 'partially-delivered';
				}

				if ( ! $shipment->is_shipped() ) {
					$all_shipments_shipped = false;
				} elseif ( 'partially-delivered' !== $status ) {
					$status = 'partially-shipped';
				}
			}
		}

		$needs_shipping = $this->needs_shipping( array( 'sent_only' => true ) );

		if ( $all_shipments_delivered && ! $needs_shipping ) {
			$status = 'delivered';
		} elseif ( 'partially-delivered' !== $status && ( $all_shipments_shipped && ! $needs_shipping ) ) {
			$status = 'shipped';
		} elseif ( ! in_array( $status, array( 'partially-shipped', 'partially-delivered' ), true ) && ! $needs_shipping ) {
			$status = 'no-shipping-needed';
		}

		return apply_filters( 'woocommerce_gzd_shipment_order_shipping_status', $status, $this );
	}

	public function supports_third_party_email_transmission() {
		$supports_email_transmission = function_exists( 'wc_gzd_order_supports_parcel_delivery_reminder' ) ? wc_gzd_order_supports_parcel_delivery_reminder( $this->get_order() ) : 'yes' === $this->get_order()->get_meta( '_parcel_delivery_opted_in' );

		/**
		 * Filter to adjust whether the email address may be transmitted to third-parties, e.g.
		 * the shipping provider (via label requests) or not.
		 *
		 * @param boolean $supports_email_transmission Whether the order supports email transmission or not.
		 * @param Order   $order The order instance.
		 *
		 * @since 3.0.0
		 * @package Vendidero/Germanized/Shipments
		 */
		return apply_filters( 'woocommerce_gzd_shipment_order_supports_email_transmission', $supports_email_transmission, $this );
	}

	public function has_shipped_shipments() {
		$shipments = $this->get_simple_shipments();

		foreach ( $shipments as $shipment ) {
			if ( $shipment->is_shipped() ) {
				return true;
			}
		}

		return false;
	}

	public function get_return_status() {
		$status    = 'open';
		$shipments = $this->get_return_shipments();

		if ( ! empty( $shipments ) ) {
			foreach ( $shipments as $shipment ) {
				if ( $shipment->has_status( 'delivered' ) ) {
					$status = 'partially-returned';
					break;
				}
			}
		}

		if ( ! $this->needs_return( array( 'delivered_only' => true ) ) && $this->has_shipped_shipments() ) {
			$status = 'returned';
		}

		return $status;
	}

	public function get_default_return_shipping_provider() {
		$default_provider_instance = wc_gzd_get_order_shipping_provider( $this->get_order() );
		$default_provider          = $default_provider_instance ? $default_provider_instance->get_name() : '';
		$shipments                 = $this->get_simple_shipments();

		foreach ( $shipments as $shipment ) {
			if ( $shipment->is_shipped() ) {
				$default_provider = $shipment->get_shipping_provider();
			}
		}

		return apply_filters( 'woocommerce_gzd_shipment_order_return_default_shipping_provider', $default_provider, $this );
	}

	protected function get_package_data() {
		if ( is_null( $this->package_data ) ) {
			$items        = $this->get_available_items_for_shipment();
			$package_data = array(
				'total'            => 0.0,
				'subtotal'         => 0.0,
				'weight'           => 0.0,
				'volume'           => 0.0,
				'products'         => array(),
				'shipping_classes' => array(),
				'item_count'       => 0,
				'items'            => new ItemList(),
			);

			foreach ( $items as $order_item_id => $item ) {
				if ( ! $order_item = $this->get_order()->get_item( $order_item_id ) ) {
					continue;
				}

				$line_total    = (float) $order_item->get_total();
				$line_subtotal = (float) $order_item->get_subtotal();

				if ( $this->get_order()->get_prices_include_tax() ) {
					$line_total    += (float) $order_item->get_total_tax();
					$line_subtotal += (float) $order_item->get_subtotal_tax();
				}

				$quantity = (int) $item['max_quantity'];

				if ( $product = $order_item->get_product() ) {
					$s_product = wc_gzd_shipments_get_product( $product );

					$width  = ( empty( $s_product->get_shipping_width() ) ? 0 : wc_format_decimal( $s_product->get_shipping_width() ) ) * $quantity;
					$length = ( empty( $s_product->get_shipping_length() ) ? 0 : wc_format_decimal( $s_product->get_shipping_length() ) ) * $quantity;
					$height = ( empty( $s_product->get_shipping_height() ) ? 0 : wc_format_decimal( $s_product->get_shipping_height() ) ) * $quantity;
					$weight = ( empty( $s_product->get_weight() ) ? 0 : wc_format_decimal( $product->get_weight() ) ) * $quantity;

					$package_data['weight'] += $weight;
					$package_data['volume'] += ( $width * $length * $height );

					if ( $product && ! array_key_exists( $product->get_id(), $package_data['products'] ) ) {
						$package_data['products'][ $product->get_id() ] = $product;

						if ( ! empty( $product->get_shipping_class_id() ) ) {
							$package_data['shipping_classes'][] = $product->get_shipping_class_id();
						}
					}
				}

				$package_data['total']      += $line_total;
				$package_data['subtotal']   += $line_subtotal;
				$package_data['item_count'] += $quantity;

				$box_item = new Packing\OrderItem( $order_item );
				$package_data['items']->insert( $box_item, $quantity );
			}

			$this->package_data = $package_data;
		}

		return $this->package_data;
	}

	/**
	 * Create shipments (if needed) based on current packing configuration.
	 *
	 * @param string $default_status
	 *
	 * @return array|\WP_Error
	 */
	public function create_shipments( $default_status = 'processing' ) {
		$shipments_created = array();
		$errors            = new \WP_Error();

		if ( $this->needs_shipping() ) {
			if ( $this->has_auto_packing() ) {
				if ( $method = $this->get_builtin_shipping_method() ) {
					$packaging_boxes = $method->get_method()->get_available_packaging_boxes( $this->get_package_data() );
				} else {
					$available_packaging = wc_gzd_get_packaging_list();

					if ( $provider = wc_gzd_get_order_shipping_provider( $this ) ) {
						$available_packaging = wc_gzd_get_packaging_list( array( 'shipping_provider' => $provider->get_name() ) );
					}

					$packaging_boxes = Helper::get_packaging_boxes( $available_packaging );
				}

				$items        = $this->get_items_to_pack_left_for_shipping();
				$packed_boxes = Helper::pack( $items, $packaging_boxes, 'order' );

				if ( empty( $packaging_boxes ) && 0 === count( $packed_boxes ) ) {
					$shipment = wc_gzd_create_shipment( $this, array( 'props' => array( 'status' => $default_status ) ) );

					if ( ! is_wp_error( $shipment ) ) {
						$this->add_shipment( $shipment );
						$shipments_created[ $shipment->get_id() ] = $shipment;
					} else {
						foreach ( $shipment->get_error_messages() as $code => $message ) {
							$errors->add( $code, $message );
						}
					}
				} else {
					if ( 0 === count( $packed_boxes ) ) {
						$errors->add( 404, sprintf( _x( 'Seems like none of your <a href="%1$s">packaging options</a> is available for this order.', 'shipments', 'woocommerce-germanized' ), Settings::get_settings_url( 'packaging' ) ) );
					} else {
						foreach ( $packed_boxes as $box ) {
							$packaging      = $box->getBox();
							$items          = $box->getItems();
							$shipment_items = array();

							foreach ( $items as $item ) {
								$order_item = $item->getItem();

								if ( ! isset( $shipment_items[ $order_item->get_id() ] ) ) {
									$shipment_items[ $order_item->get_id() ] = 1;
								} else {
									$shipment_items[ $order_item->get_id() ]++;
								}
							}

							$shipment = wc_gzd_create_shipment(
								$this,
								array(
									'items' => $shipment_items,
									'props' => array(
										'packaging_id' => $packaging->get_id(),
										'status'       => $default_status,
									),
								)
							);

							if ( ! is_wp_error( $shipment ) ) {
								$this->add_shipment( $shipment );

								$shipments_created[ $shipment->get_id() ] = $shipment;
							} else {
								foreach ( $shipments_created as $id => $shipment_created ) {
									$shipment_created->delete( true );
									$this->remove_shipment( $id );
								}

								foreach ( $shipment->get_error_messages() as $code => $message ) {
									$errors->add( $code, $message );
								}
							}
						}
					}
				}
			} else {
				$shipment = wc_gzd_create_shipment( $this, array( 'props' => array( 'status' => $default_status ) ) );

				if ( ! is_wp_error( $shipment ) ) {
					$this->add_shipment( $shipment );
					$shipments_created[ $shipment->get_id() ] = $shipment;
				} else {
					foreach ( $shipment->get_error_messages() as $code => $message ) {
						$errors->add( $code, $message );
					}
				}
			}
		}

		if ( wc_gzd_shipment_wp_error_has_errors( $errors ) ) {
			return $errors;
		} else {
			$this->save();
		}

		return $shipments_created;
	}

	public function validate_shipments( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'save' => true,
			)
		);

		foreach ( $this->get_simple_shipments() as $shipment ) {
			if ( $shipment->is_editable() ) {

				// Make sure we are working based on the current instance.
				$shipment->set_order_shipment( $this );
				$shipment->sync();

				$this->validate_shipment_item_quantities( $shipment->get_id() );
			}
		}

		if ( $args['save'] ) {
			$this->save();
		}
	}

	/**
	 * @param Shipment $shipment
	 *
	 * @return float
	 */
	public function calculate_shipment_additional_total( $shipment ) {
		$fees_total = 0.0;

		foreach ( $this->get_order()->get_fees() as $item ) {
			$fees_total += ( (float) $item->get_total() + (float) $item->get_total_tax() );
		}

		$additional_total = $fees_total + (float) $this->get_order()->get_shipping_total() + (float) $this->get_order()->get_shipping_tax();

		foreach ( $this->get_simple_shipments() as $simple_shipment ) {
			if ( $shipment->get_id() === $simple_shipment->get_id() ) {
				continue;
			}

			$additional_total -= (float) $simple_shipment->get_additional_total();
		}

		$additional_total = wc_format_decimal( $additional_total, '' );

		if ( (float) $additional_total < 0.0 ) {
			$additional_total = 0.0;
		}

		return $additional_total;
	}

	public function validate_shipment_item_quantities( $shipment_id = false ) {
		$shipment    = $shipment_id ? $this->get_shipment( $shipment_id ) : false;
		$shipments   = ( $shipment_id && $shipment ) ? array( $shipment ) : $this->get_simple_shipments();
		$order_items = $this->get_shippable_items();

		foreach ( $shipments as $shipment ) {

			if ( ! is_a( $shipment, 'Vendidero\Germanized\Shipments\Shipment' ) ) {
				continue;
			}

			// Do only check draft shipments
			if ( $shipment->is_editable() ) {
				foreach ( $shipment->get_items() as $item ) {

					// Order item does not exist
					if ( ! isset( $order_items[ $item->get_order_item_id() ] ) ) {

						/**
						 * Filter to decide whether to keep non-existing OrderItems within
						 * the Shipment while validating or not.
						 *
						 * @param boolean                                      $keep Whether to keep non-existing OrderItems or not.
						 * @param ShipmentItem $item The shipment item object.
						 * @param Shipment $shipment The shipment object.
						 *
						 * @since 3.0.0
						 * @package Vendidero/Germanized/Shipments
						 */
						if ( ! apply_filters( 'woocommerce_gzd_shipment_order_keep_non_order_item', false, $item, $shipment ) ) {
							$shipment->remove_item( $item->get_id() );
						}

						continue;
					}

					$order_item = $order_items[ $item->get_order_item_id() ];
					$quantity   = $this->get_item_quantity_left_for_shipping(
						$order_item,
						array(
							'shipment_id'              => $shipment->get_id(),
							'exclude_current_shipment' => true,
						)
					);

					if ( $quantity <= 0 ) {
						$shipment->remove_item( $item->get_id() );
					} else {
						$new_quantity = absint( $item->get_quantity() );

						if ( $item->get_quantity() > $quantity ) {
							$new_quantity = $quantity;
						}

						$item->sync( array( 'quantity' => $new_quantity ) );
					}
				}

				if ( empty( $shipment->get_items() ) ) {
					$this->remove_shipment( $shipment->get_id() );
				}
			}
		}
	}

	/**
	 * @return Shipment[] Shipments
	 */
	public function get_shipments() {
		if ( is_null( $this->shipments ) ) {
			$this->shipments = wc_gzd_get_shipments(
				array(
					'order_id' => $this->get_order()->get_id(),
					'limit'    => -1,
					'orderby'  => 'date_created',
					'type'     => array( 'simple', 'return' ),
					'order'    => 'ASC',
				)
			);

			/**
			 * As by default WordPress cache engine only stores object clones
			 * we need to update the cache after, e.g. loading shipments to make sure
			 * those shipments are not reloaded on the next cache hit.
			 */
			if ( $cache = \Vendidero\Germanized\Shipments\Caches\Helper::get_cache_object( 'shipment-orders' ) ) {
				$cache->set( $this, $this->get_order()->get_id() );
			}
		}

		$shipments = (array) $this->shipments;

		return $shipments;
	}

	/**
	 * @return SimpleShipment[]
	 */
	public function get_simple_shipments( $shipped_only = false ) {
		$simple = array();

		foreach ( $this->get_shipments() as $shipment ) {
			if ( 'simple' === $shipment->get_type() ) {
				if ( $shipped_only && ! $shipment->is_shipped() ) {
					continue;
				}

				$simple[] = $shipment;
			}
		}

		return $simple;
	}

	/**
	 * @return ReturnShipment[]
	 */
	public function get_return_shipments() {
		$returns = array();

		foreach ( $this->get_shipments() as $shipment ) {
			if ( 'return' === $shipment->get_type() ) {
				$returns[] = $shipment;
			}
		}

		return $returns;
	}

	public function add_shipment( &$shipment ) {
		$this->package_data = null;

		$shipments = $this->get_shipments();

		$this->shipments[] = $shipment;
	}

	public function remove_shipment( $shipment_id ) {
		$this->package_data = null;
		$shipments          = $this->get_shipments();

		foreach ( $this->shipments as $key => $shipment ) {
			if ( $shipment->get_id() === (int) $shipment_id ) {
				$this->shipments_to_delete[] = $shipment;

				unset( $this->shipments[ $key ] );
				break;
			}
		}
	}

	/**
	 * @param $shipment_id
	 *
	 * @return bool|SimpleShipment|ReturnShipment
	 */
	public function get_shipment( $shipment_id ) {
		$shipments = $this->get_shipments();

		foreach ( $shipments as $shipment ) {

			if ( $shipment->get_id() === (int) $shipment_id ) {
				return $shipment;
			}
		}

		return false;
	}

	/**
	 * @param WC_Order_Item $order_item
	 */
	public function get_item_quantity_left_for_shipping( $order_item, $args = array() ) {
		$quantity_left = 0;
		$args          = wp_parse_args(
			$args,
			array(
				'sent_only'                => false,
				'shipment_id'              => 0,
				'exclude_current_shipment' => false,
			)
		);

		if ( is_numeric( $order_item ) ) {
			$order_item = $this->get_order()->get_item( $order_item );
		}

		if ( $order_item ) {
			$quantity_left = $this->get_shippable_item_quantity( $order_item );

			foreach ( $this->get_shipments() as $shipment ) {
				if ( $args['sent_only'] && ! $shipment->is_shipped() ) {
					continue;
				}

				if ( $args['exclude_current_shipment'] && $args['shipment_id'] > 0 && ( $shipment->get_id() === $args['shipment_id'] ) ) {
					continue;
				}

				if ( $item = $shipment->get_item_by_order_item_id( $order_item->get_id() ) ) {
					if ( 'return' === $shipment->get_type() ) {
						if ( ! $args['sent_only'] && $shipment->is_shipped() ) {
							$quantity_left += absint( $item->get_quantity() );
						}
					} else {
						$quantity_left -= absint( $item->get_quantity() );
					}
				}
			}
		}

		if ( $quantity_left < 0 ) {
			$quantity_left = 0;
		}

		/**
		 * Filter to adjust the quantity left for shipment of a specific order item.
		 *
		 * @param integer                                      $quantity_left The quantity left for shipment.
		 * @param WC_Order_Item                                $order_item The order item object.
		 * @param Order $this The shipment order object.
		 *
		 * @since 3.0.0
		 * @package Vendidero/Germanized/Shipments
		 */
		return apply_filters( 'woocommerce_gzd_shipment_order_item_quantity_left_for_shipping', $quantity_left, $order_item, $this );
	}

	public function get_item_quantity_sent_by_order_item_id( $order_item_id ) {
		$shipments = $this->get_simple_shipments();
		$quantity  = 0;

		foreach ( $shipments as $shipment ) {

			if ( ! $shipment->is_shipped() ) {
				continue;
			}

			if ( $item = $shipment->get_item_by_order_item_id( $order_item_id ) ) {
				$quantity += absint( $item->get_quantity() );
			}
		}

		return $quantity;
	}

	public function order_item_is_non_returnable( $order_item_id ) {
		$is_non_returnable = false;
		$order_item        = is_a( $order_item_id, 'WC_Order_Item' ) ? $order_item_id : $this->get_order()->get_item( $order_item_id );

		if ( $order_item ) {
			if ( is_callable( array( $order_item, 'get_product' ) ) ) {
				if ( $product = $order_item->get_product() ) {
					$is_non_returnable = wc_gzd_shipments_get_product( $product )->is_non_returnable();
				}
			}
		}

		return apply_filters( 'woocommerce_gzd_shipment_order_item_is_non_returnable', $is_non_returnable, $order_item_id, $this );
	}

	/**
	 * @param ShipmentItem $item
	 */
	public function get_item_quantity_left_for_returning( $order_item_id, $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'delivered_only'           => false,
				'shipment_id'              => 0,
				'exclude_current_shipment' => false,
			)
		);

		$quantity_left = $this->get_item_quantity_sent_by_order_item_id( $order_item_id );

		if ( $this->order_item_is_non_returnable( $order_item_id ) ) {
			$quantity_left = 0;
		}

		foreach ( $this->get_return_shipments() as $shipment ) {
			if ( $args['delivered_only'] && ! $shipment->has_status( 'delivered' ) ) {
				continue;
			}

			if ( $args['exclude_current_shipment'] && $args['shipment_id'] > 0 && ( $shipment->get_id() === $args['shipment_id'] ) ) {
				continue;
			}

			if ( $shipment_item = $shipment->get_item_by_order_item_id( $order_item_id ) ) {
				$quantity_left -= absint( $shipment_item->get_quantity() );
			}
		}

		if ( $quantity_left < 0 ) {
			$quantity_left = 0;
		}

		/**
		 * Filter to adjust the quantity left for returning of a specific order item.
		 *
		 * @param integer       $quantity_left The quantity left for shipment.
		 * @param integer       $order_item_id The order item id.
		 * @param Order         $this The shipment order object.
		 *
		 * @since 3.0.0
		 * @package Vendidero/Germanized/Shipments
		 */
		return apply_filters( 'woocommerce_gzd_shipment_order_item_quantity_left_for_returning', $quantity_left, $order_item_id, $this );
	}

	/**
	 * @param false $legacy_group_by_product_group
	 *
	 * @return ItemList|OrderItem[]
	 */
	public function get_items_to_pack_left_for_shipping( $legacy_group_by_product_group = null ) {
		$items_to_be_packed = ! is_null( $legacy_group_by_product_group ) ? array() : $this->get_package_data()['items'];

		if ( ! is_null( $legacy_group_by_product_group ) ) {
			foreach ( $this->get_available_items_for_shipment() as $order_item_id => $item ) {
				if ( ! $order_item = $this->get_order()->get_item( $order_item_id ) ) {
					continue;
				}

				$box_item = new Packing\OrderItem( $order_item );

				$product_group = '';

				if ( $product = $order_item->get_product() ) {
					$product_group = '';

					if ( 'yes' === get_option( 'woocommerce_gzd_shipments_packing_group_by_shipping_class' ) ) {
						$product_group = $product->get_shipping_class();
					}
				}

				if ( ! array_key_exists( $product_group, $items_to_be_packed ) ) {
					$items_to_be_packed[ $product_group ] = new ItemList();
				}

				$items_to_be_packed[ $product_group ]->insert( $box_item, $item['max_quantity'] );
			}
		}

		return apply_filters( 'woocommerce_gzd_shipment_order_items_to_pack_left_for_shipping', $items_to_be_packed );
	}

	/**
	 * @param bool|Shipment $shipment
	 * @return array
	 */
	public function get_available_items_for_shipment( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'disable_duplicates'       => false,
				'shipment_id'              => 0,
				'sent_only'                => false,
				'exclude_current_shipment' => false,
			)
		);

		$items    = array();
		$shipment = $args['shipment_id'] ? $this->get_shipment( $args['shipment_id'] ) : false;

		foreach ( $this->get_shippable_items() as $item ) {
			$quantity_left = $this->get_item_quantity_left_for_shipping( $item, $args );

			if ( $shipment ) {
				if ( $args['disable_duplicates'] && $shipment->get_item_by_order_item_id( $item->get_id() ) ) {
					continue;
				}
			}

			if ( $quantity_left > 0 ) {
				$sku = '';

				if ( is_callable( array( $item, 'get_product' ) ) ) {
					if ( $product = $item->get_product() ) {
						$sku = $product->get_sku();
					}
				}

				$items[ $item->get_id() ] = array(
					'name'         => $item->get_name() . ( ! empty( $sku ) ? ' (' . esc_html( $sku ) . ')' : '' ),
					'max_quantity' => $quantity_left,
				);
			}
		}

		return $items;
	}

	/**
	 * Returns the first found matching shipment item for a certain order item id.
	 *
	 * @param $order_item_id
	 *
	 * @return bool|ShipmentItem
	 */
	public function get_simple_shipment_item( $order_item_id ) {
		foreach ( $this->get_simple_shipments() as $shipment ) {

			if ( $item = $shipment->get_item_by_order_item_id( $order_item_id ) ) {
				return $item;
			}
		}

		return false;
	}

	public function get_non_returnable_items() {
		$items = array();

		foreach ( $this->get_returnable_items() as $item ) {
			if ( $this->order_item_is_non_returnable( $item->get_order_item_id() ) ) {
				$sku = $item->get_sku();

				$items[ $item->get_order_item_id() ] = array(
					'name'         => $item->get_name() . ( ! empty( $sku ) ? ' (' . esc_html( $sku ) . ')' : '' ),
					'max_quantity' => 0,
				);
			}
		}

		return $items;
	}

	/**
	 * @return array
	 */
	public function get_available_items_for_return( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'disable_duplicates'       => false,
				'shipment_id'              => 0,
				'delivered_only'           => false,
				'exclude_current_shipment' => false,
			)
		);

		$items    = array();
		$shipment = $args['shipment_id'] ? $this->get_shipment( $args['shipment_id'] ) : false;

		foreach ( $this->get_returnable_items() as $item ) {
			$quantity_left = $this->get_item_quantity_left_for_returning( $item->get_order_item_id(), $args );

			if ( $shipment ) {
				if ( $args['disable_duplicates'] && $shipment->get_item_by_order_item_id( $item->get_order_item_id() ) ) {
					continue;
				}
			}

			if ( $quantity_left > 0 ) {
				$sku = $item->get_sku();

				$items[ $item->get_order_item_id() ] = array(
					'name'         => $item->get_name() . ( ! empty( $sku ) ? ' (' . esc_html( $sku ) . ')' : '' ),
					'max_quantity' => $quantity_left,
				);
			}
		}

		return $items;
	}

	public function item_needs_shipping( $order_item, $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'sent_only' => false,
			)
		);

		$needs_shipping = false;

		if ( $this->get_item_quantity_left_for_shipping( $order_item, $args ) > 0 ) {
			$needs_shipping = true;
		}

		/**
		 * Filter to decide whether an order item needs shipping or not.
		 *
		 * @param boolean                               $needs_shipping Whether the item needs shipping or not.
		 * @param WC_Order_Item                        $item The order item object.
		 * @param array                                 $args Additional arguments to be considered.
		 * @param Order $order The shipment order object.
		 *
		 * @since 3.0.0
		 * @package Vendidero/Germanized/Shipments
		 */
		return apply_filters( 'woocommerce_gzd_shipment_order_item_needs_shipping', $needs_shipping, $order_item, $args, $this );
	}

	/**
	 * Checks whether an item needs return or not by checking the quantity left for return.
	 *
	 * @param ShipmentItem $item
	 * @param array $args
	 *
	 * @return mixed|void
	 */
	public function item_needs_return( $item, $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'delivered_only' => false,
			)
		);

		$needs_return = false;

		if ( $this->get_item_quantity_left_for_returning( $item->get_order_item_id(), $args ) > 0 ) {
			$needs_return = true;
		}

		/**
		 * Filter to decide whether a shipment item needs return or not.
		 *
		 * @param boolean      $needs_return Whether the item needs return or not.
		 * @param ShipmentItem $item The order item object.
		 * @param array        $args Additional arguments to be considered.
		 * @param Order $order The shipment order object.
		 *
		 * @since 3.0.0
		 * @package Vendidero/Germanized/Shipments
		 */
		return apply_filters( 'woocommerce_gzd_shipment_item_needs_return', $needs_return, $item, $args, $this );
	}

	/**
	 * Returns the return request key added to allow a guest customer to add
	 * a new return request to a certain order.
	 *
	 * @return mixed
	 */
	public function get_order_return_request_key() {
		return $this->get_order()->get_meta( '_return_request_key' );
	}

	/**
	 * Removes the return request key from the order. Saves the order.
	 */
	public function delete_order_return_request_key() {
		$this->get_order()->delete_meta_data( '_return_request_key' );
		$this->get_order()->save();
	}

	/**
	 * Returns items that are ready for shipping (defaults to non-virtual line items).
	 *
	 * @return WC_Order_Item[] Shippable items.
	 */
	public function get_shippable_items() {
		$items = $this->get_order()->get_items( 'line_item' );

		foreach ( $items as $key => $item ) {
			$product = is_callable( array( $item, 'get_product' ) ) ? $item->get_product() : false;

			if ( $product ) {
				if ( $product->is_virtual() || $this->get_shippable_item_quantity( $item ) <= 0 ) {
					unset( $items[ $key ] );
				}
			}
		}

		$items = array_filter( $items );

		/**
		 * Filter to adjust shippable order items for a specific order.
		 * By default excludes virtual items.
		 *
		 * @param WC_Order_Item[]                       $items Array containing shippable order items.
		 * @param WC_Order                              $order The order object.
		 * @param Order $order The shipment order object.
		 *
		 * @since 3.0.0
		 * @package Vendidero/Germanized/Shipments
		 */
		return apply_filters( 'woocommerce_gzd_shipment_order_shippable_items', $items, $this->get_order(), $this );
	}

	/**
	 * Returns items that are ready for return. By default only shipped (or delivered) items are returnable.
	 *
	 * @return ShipmentItem[] Shippable items.
	 */
	public function get_returnable_items() {
		$items = array();

		foreach ( $this->get_simple_shipments() as $shipment ) {
			if ( ! $shipment->is_shipped() ) {
				continue;
			}

			foreach ( $shipment->get_items() as $item ) {
				if ( $this->order_item_is_non_returnable( $item->get_order_item_id() ) ) {
					continue;
				}

				if ( ! isset( $items[ $item->get_order_item_id() ] ) ) {
					$new_item                            = clone $item;
					$items[ $item->get_order_item_id() ] = $new_item;
				} else {
					$new_quantity = absint( $items[ $item->get_order_item_id() ]->get_quantity() ) + absint( $item->get_quantity() );
					$items[ $item->get_order_item_id() ]->set_quantity( $new_quantity );
				}
			}
		}

		/**
		 * Filter to adjust returnable items for a specific order.
		 *
		 * @param ShipmentItem[] $items Array containing shippable order items.
		 * @param WC_Order       $order The order object.
		 * @param Order          $order The shipment order object.
		 *
		 * @since 3.0.0
		 * @package Vendidero/Germanized/Shipments
		 */
		return apply_filters( 'woocommerce_gzd_shipment_order_returnable_items', $items, $this->get_order(), $this );
	}

	public function get_shippable_item_quantity( $order_item ) {
		$refunded_qty = absint( $this->get_order()->get_qty_refunded_for_item( $order_item->get_id() ) );

		// Make sure we are safe to substract quantity for logical purposes
		if ( $refunded_qty < 0 ) {
			$refunded_qty *= -1;
		}

		$quantity_left = absint( $order_item->get_quantity() ) - $refunded_qty;

		/**
		 * Filter that allows adjusting the quantity left for shipping or a specific order item.
		 *
		 * @param integer                               $quantity_left The quantity left for shipping.
		 * @param WC_Order_Item                        $item The order item object.
		 * @param Order $order The shipment order object.
		 *
		 * @since 3.0.0
		 * @package Vendidero/Germanized/Shipments
		 */
		return apply_filters( 'woocommerce_gzd_shipment_order_item_shippable_quantity', $quantity_left, $order_item, $this );
	}

	/**
	 * Returns the total number of shippable items.
	 *
	 * @return mixed|void
	 */
	public function get_shippable_item_count() {
		$count = 0;

		foreach ( $this->get_shippable_items() as $item ) {
			$count += $this->get_shippable_item_quantity( $item );
		}

		/**
		 * Filters the total number of shippable items available in an order.
		 *
		 * @param integer                               $count The total number of items.
		 * @param Order $order The shipment order object.
		 *
		 * @since 3.0.0
		 * @package Vendidero/Germanized/Shipments
		 */
		return apply_filters( 'woocommerce_gzd_shipment_order_shippable_item_count', $count, $this );
	}

	/**
	 * Returns the number of total returnable items.
	 *
	 * @return mixed|void
	 */
	public function get_returnable_item_count() {
		$count = 0;

		foreach ( $this->get_returnable_items() as $item ) {
			$count += absint( $item->get_quantity() );
		}

		/**
		 * Filters the total number of returnable items available in an order.
		 *
		 * @param integer $count The total number of items.
		 * @param Order   $order The shipment order object.
		 *
		 * @since 3.0.0
		 * @package Vendidero/Germanized/Shipments
		 */
		return apply_filters( 'woocommerce_gzd_shipment_order_returnable_item_count', $count, $this );
	}

	protected function has_local_pickup() {
		$shipping_methods = $this->get_order()->get_shipping_methods();
		$has_pickup       = false;

		/**
		 * Filters which shipping methods are considered local pickup method
		 * which by default do not require shipment.
		 *
		 * @param string[] $pickup_methods Array of local pickup shipping method ids.
		 *
		 * @since 3.1.6
		 * @package Vendidero/Germanized/Shipments
		 */
		$pickup_methods = apply_filters( 'woocommerce_gzd_shipment_local_pickup_shipping_methods', array( 'local_pickup' ) );

		foreach ( $shipping_methods as $shipping_method ) {
			if ( in_array( $shipping_method->get_method_id(), $pickup_methods, true ) ) {
				$has_pickup = true;
				break;
			}
		}

		return $has_pickup;
	}

	/**
	 * @return ProviderMethod|false
	 */
	public function get_builtin_shipping_method() {
		$method = false;

		if ( Package::is_packing_supported() ) {
			$shipping_method_id = wc_gzd_get_shipment_order_shipping_method_id( $this->get_order() );

			if ( 'shipping_provider_' === substr( $shipping_method_id, 0, 18 ) ) {
				if ( $method = MethodHelper::get_provider_method( $shipping_method_id ) ) {
					return $method;
				}
			}
		}

		return $method;
	}

	public function has_auto_packing() {
		$has_auto_packing = false;

		if ( Package::is_packing_supported() ) {
			$has_auto_packing = Helper::enable_auto_packing();

			if ( ! $has_auto_packing ) {
				if ( self::get_builtin_shipping_method() ) {
					$has_auto_packing = true;
				}
			}
		}

		return apply_filters( 'woocommerce_gzd_shipment_order_has_auto_packing', $has_auto_packing, $this->get_order(), $this );
	}

	/**
	 * Checks whether the order needs shipping or not by checking quantity
	 * for every line item.
	 *
	 * @param bool $sent_only Whether to only include shipments treated as sent or not.
	 *
	 * @return bool Whether the order needs shipping or not.
	 */
	public function needs_shipping( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'sent_only' => false,
			)
		);

		$order_items    = $this->get_shippable_items();
		$needs_shipping = false;
		$has_pickup     = $this->has_local_pickup();

		if ( ! $has_pickup ) {
			foreach ( $order_items as $order_item ) {
				if ( $this->item_needs_shipping( $order_item, $args ) ) {
					$needs_shipping = true;
					break;
				}
			}
		}

		/**
		 * Filter to decide whether an order needs shipping or not.
		 *
		 * @param boolean  $needs_shipping Whether the order needs shipping or not.
		 * @param WC_Order $order The order object.
		 * @param Order    $order The shipment order object.
		 *
		 * @since 3.0.0
		 * @package Vendidero/Germanized/Shipments
		 */
		return apply_filters( 'woocommerce_gzd_shipment_order_needs_shipping', $needs_shipping, $this->get_order(), $this );
	}

	/**
	 * Checks whether the order needs return or not by checking quantity
	 * for every line item.
	 *
	 * @return bool Whether the order needs shipping or not.
	 */
	public function needs_return( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'delivered_only' => false,
			)
		);

		$items        = $this->get_returnable_items();
		$needs_return = false;

		foreach ( $items as $item ) {
			if ( $this->item_needs_return( $item, $args ) ) {
				$needs_return = true;
				break;
			}
		}

		/**
		 * Filter to decide whether an order needs return or not.
		 *
		 * @param boolean  $needs_return Whether the order needs return or not.
		 * @param WC_Order $order The order object.
		 * @param Order    $order The shipment order object.
		 *
		 * @since 3.0.0
		 * @package Vendidero/Germanized/Shipments
		 */
		return apply_filters( 'woocommerce_gzd_shipment_order_needs_return', $needs_return, $this->get_order(), $this );
	}

	public function save() {
		if ( ! empty( $this->shipments_to_delete ) ) {
			foreach ( $this->shipments_to_delete as $shipment ) {
				$shipment->delete( true );
			}
		}

		foreach ( $this->shipments as $shipment ) {
			$shipment->save();
		}

		$this->package_data        = null;
		$this->shipments           = null;
		$this->shipments_to_delete = null;

		if ( $cache = \Vendidero\Germanized\Shipments\Caches\Helper::get_cache_object( 'shipment-orders' ) ) {
			$cache->remove( $this->get_order()->get_id() );
		}
	}

	/**
	 * Call child methods if the method does not exist.
	 *
	 * @param $method
	 * @param $args
	 *
	 * @return bool|mixed
	 */
	public function __call( $method, $args ) {
		if ( method_exists( $this->order, $method ) ) {
			return call_user_func_array( array( $this->order, $method ), $args );
		}

		return false;
	}
}
