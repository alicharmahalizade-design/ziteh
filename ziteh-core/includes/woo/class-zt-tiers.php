<?php
/**
 * Tiered discounts (settings-driven): percent / fixed / free shipping.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_Tiers
 */
class ZT_Tiers {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'woocommerce_cart_calculate_fees', array( __CLASS__, 'apply_fee' ), 20 );
		add_filter( 'woocommerce_package_rates', array( __CLASS__, 'free_shipping' ), 50, 2 );
	}

	/**
	 * Enabled tiers sorted ascending by amount.
	 *
	 * @return array
	 */
	public static function tiers() {
		if ( ! zt_opt( 'cart.tiers_enabled', 1 ) ) {
			return array();
		}
		$t = array_values(
			array_filter(
				(array) zt_opt( 'cart.tiers', array() ),
				function ( $x ) {
					return isset( $x['amount'] ) && (float) $x['amount'] > 0;
				}
			)
		);
		usort(
			$t,
			function ( $a, $b ) {
				return (float) $a['amount'] <=> (float) $b['amount'];
			}
		);
		return $t;
	}

	/**
	 * Sub-total the tiers are computed on (store units).
	 *
	 * @return float
	 */
	public static function base_amount() {
		$cart = WC()->cart;
		if ( ! $cart ) {
			return 0;
		}
		$sum     = 0;
		$exclude = zt_opt( 'cart.tier_exclude_sale', 0 );
		foreach ( $cart->get_cart() as $item ) {
			if ( $exclude && isset( $item['data'] ) && $item['data']->is_on_sale() ) {
				continue;
			}
			$sum += (float) $item['line_subtotal'] + ( $cart->display_prices_including_tax() ? (float) $item['line_subtotal_tax'] : 0 );
		}
		return $sum;
	}

	/**
	 * Best discount tier reached.
	 *
	 * @param float $base Base (display units).
	 * @return array|null
	 */
	public static function best( $base ) {
		$best = null;
		foreach ( self::tiers() as $t ) {
			if ( 'free_shipping' === $t['type'] || $base < (float) $t['amount'] ) {
				continue;
			}
			$best = $t; // ascending: the last reached one wins.
		}
		return $best;
	}

	/**
	 * Free-shipping tier reached?
	 *
	 * @param float $base Base (display units).
	 * @return bool
	 */
	public static function free_shipping_reached( $base ) {
		foreach ( self::tiers() as $t ) {
			if ( 'free_shipping' === $t['type'] && $base >= (float) $t['amount'] ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Add the negative fee.
	 *
	 * @param WC_Cart $cart Cart.
	 */
	public static function apply_fee( $cart ) {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return;
		}
		$base_store = self::base_amount();
		$best       = self::best( zt_amount( $base_store ) );
		if ( ! $best ) {
			return;
		}
		$amount = 'percent' === $best['type'] ? $base_store * (float) $best['value'] / 100 : (float) $best['value'] * max( 1, (float) zt_opt( 'general.price_divisor', 1 ) );
		$amount = min( $amount, $base_store );
		if ( $amount > 0 ) {
			$cart->add_fee( zt_opt( 'cart.tier_fee_label', 'تخفیف پلکانی' ), -1 * round( $amount ), false );
		}
	}

	/**
	 * Zero the cost of eligible Ziteh shipping rates when the free shipping tier is reached.
	 *
	 * @param array $rates   Rates.
	 * @param array $package Package.
	 * @return array
	 */
	public static function free_shipping( $rates, $package ) {
		if ( ! WC()->cart || ! self::free_shipping_reached( zt_amount( self::base_amount() ) ) ) {
			return $rates;
		}
		foreach ( $rates as $id => $rate ) {
			$meta = $rate->get_meta_data();
			if ( 'zt_shipping' === $rate->get_method_id() && empty( $meta['zt_free'] ) ) {
				continue;
			}
			if ( in_array( $rate->get_method_id(), array( 'zt_shipping', 'flat_rate' ), true ) ) {
				$rate->set_cost( 0 );
				$rate->set_taxes( array() );
				$rates[ $id ] = $rate;
			}
		}
		return $rates;
	}

	/**
	 * State for the tier widget.
	 *
	 * @param float $base Base (display units).
	 * @return array
	 */
	public static function state( $base ) {
		$tiers = self::tiers();
		if ( ! $tiers ) {
			return array(
				'msg'  => '',
				'bar'  => 0,
				'fill' => 0,
				'done' => array(),
			);
		}
		$next = null;
		$done = array();
		foreach ( $tiers as $i => $t ) {
			if ( $base >= (float) $t['amount'] ) {
				$done[] = (float) $t['amount'];
			} elseif ( null === $next ) {
				$next = $t;
			}
		}
		if ( $next ) {
			$msg = str_replace(
				array( '{amount}', '{label}' ),
				array( zt_money( (float) $next['amount'] - $base ), $next['title'] ),
				zt_opt( 'cart.tier_msg_next' )
			);
			$bar = max( 0, min( 100, $base / (float) $next['amount'] * 100 ) );
		} else {
			$msg = zt_opt( 'cart.tier_msg_done' );
			$bar = 100;
		}
		// fill of the line between the dots (left → right = ascending)
		$n    = count( $tiers );
		$fill = 0;
		if ( $n > 1 ) {
			$seg = 100 / ( $n - 1 );
			for ( $i = 0; $i < $n - 1; $i++ ) {
				$a = (float) $tiers[ $i ]['amount'];
				$b = (float) $tiers[ $i + 1 ]['amount'];
				if ( $base >= $b ) {
					$fill += $seg;
				} elseif ( $base > $a ) {
					$fill += $seg * ( $base - $a ) / max( 1, $b - $a );
				}
			}
		}
		return array(
			'msg'  => $msg,
			'bar'  => round( $bar, 2 ),
			'fill' => round( $fill, 2 ),
			'done' => $done,
		);
	}
}
