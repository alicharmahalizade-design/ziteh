<?php
/**
 * Gift sample picker: selection lives in the WooCommerce session and is added
 * to the order as a free line item.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_Samples
 */
class ZT_Samples {

	const KEY = 'zt_sample';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'woocommerce_checkout_create_order', array( __CLASS__, 'add_to_order' ), 20, 2 );
		add_action( 'woocommerce_cart_emptied', array( __CLASS__, 'clear' ) );
	}

	/**
	 * Configured samples.
	 *
	 * @return array
	 */
	public static function all() {
		return zt_opt( 'cart.samples_enabled', 1 ) ? array_values( (array) zt_opt( 'cart.samples', array() ) ) : array();
	}

	/**
	 * Selected sample index (-1 = none).
	 *
	 * @return int
	 */
	public static function selected() {
		if ( ! function_exists( 'WC' ) || ! WC()->session ) {
			return -1;
		}
		$v = WC()->session->get( self::KEY, -1 );
		return is_numeric( $v ) ? (int) $v : -1;
	}

	/**
	 * Select a sample.
	 *
	 * @param int $index Index (-1 = none).
	 * @return bool
	 */
	public static function select( $index ) {
		$all = self::all();
		if ( $index >= 0 && ! isset( $all[ $index ] ) ) {
			return false;
		}
		if ( WC()->session ) {
			if ( ! WC()->session->has_session() ) {
				WC()->session->set_customer_session_cookie( true );
			}
			WC()->session->set( self::KEY, (int) $index );
		}
		return true;
	}

	/**
	 * Clear selection.
	 */
	public static function clear() {
		if ( WC()->session ) {
			WC()->session->set( self::KEY, -1 );
		}
	}

	/**
	 * Is the cart eligible?
	 *
	 * @return bool
	 */
	public static function unlocked() {
		if ( ! WC()->cart ) {
			return false;
		}
		$sub = (float) WC()->cart->get_subtotal() + ( WC()->cart->display_prices_including_tax() ? (float) WC()->cart->get_subtotal_tax() : 0 );
		return zt_amount( $sub ) >= (float) zt_opt( 'cart.samples_min', 0 );
	}

	/**
	 * Add the chosen sample to the order as a free line.
	 *
	 * @param WC_Order $order Order.
	 * @param array    $data  Posted data.
	 */
	public static function add_to_order( $order, $data ) {
		$i   = self::selected();
		$all = self::all();
		if ( $i < 0 || ! isset( $all[ $i ] ) || ! self::unlocked() ) {
			return;
		}
		$s    = $all[ $i ];
		$item = new WC_Order_Item_Product();
		$item->set_name( zt_opt( 'cart.samples_line_name', 'نمونه هدیه (سمپل)' ) . ' — ' . $s['title'] );
		$item->set_quantity( 1 );
		$item->set_subtotal( 0 );
		$item->set_total( 0 );
		if ( ! empty( $s['product_id'] ) && wc_get_product( (int) $s['product_id'] ) ) {
			$item->set_product_id( (int) $s['product_id'] );
		}
		$item->add_meta_data( '_zt_sample', 1, true );
		$item->add_meta_data( 'حجم', $s['size'], true );
		if ( ! empty( $s['image'] ) ) {
			$item->add_meta_data( '_zt_sample_img', $s['image'], true );
		}
		$order->add_item( $item );
		$order->update_meta_data( '_zt_sample', $s['title'] );
	}
}
