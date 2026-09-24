<?php
/**
 * WooCommerce integration loader + shared cart state.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_Woo
 */
class ZT_Woo {

	/**
	 * Hooks.
	 */
	public static function init() {
		$dir = ZT_PATH . 'includes/woo/';
		require_once $dir . 'class-zt-tiers.php';
		require_once $dir . 'class-zt-samples.php';
		require_once $dir . 'class-zt-checkout.php';
		require_once $dir . 'class-zt-tracking.php';
		require_once $dir . 'class-zt-wishlist.php';
		require_once $dir . 'class-zt-product-meta.php';
		require_once $dir . 'class-zt-account.php';

		ZT_Tiers::init();
		ZT_Samples::init();
		ZT_Checkout::init();
		ZT_Tracking::init();
		ZT_Wishlist::init();
		ZT_Product_Meta::init();
		ZT_Account::init();

		add_action( 'woocommerce_shipping_init', array( __CLASS__, 'shipping_init' ) );
		add_filter( 'woocommerce_shipping_methods', array( __CLASS__, 'shipping_methods' ) );
		add_filter( 'woocommerce_payment_gateways', array( __CLASS__, 'gateways' ) );
		add_action( 'plugins_loaded', array( __CLASS__, 'gateway_class' ), 30 );

		add_filter( 'woocommerce_add_to_cart_fragments', array( __CLASS__, 'fragments' ) );
		add_filter( 'woocommerce_checkout_redirect_empty_cart', array( __CLASS__, 'no_redirect_in_editor' ) );
		add_filter( 'woocommerce_cart_redirect_after_error', '__return_false' );
		add_action( 'pre_get_posts', array( __CLASS__, 'shop_filters' ) );
		add_filter( 'woocommerce_currency_symbol', array( __CLASS__, 'currency_symbol' ), 20, 2 );
		add_filter( 'woocommerce_get_breadcrumb', array( __CLASS__, 'noop' ) );
		// no WooCommerce wrappers on our templates
		add_filter( 'woocommerce_show_page_title', '__return_false' );
	}

	/**
	 * Pass-through.
	 *
	 * @param mixed $v Value.
	 * @return mixed
	 */
	public static function noop( $v ) {
		return $v;
	}

	/**
	 * Shipping class file.
	 */
	public static function shipping_init() {
		require_once ZT_PATH . 'includes/woo/class-zt-shipping-method.php';
	}

	/**
	 * Register the shipping method.
	 *
	 * @param array $m Methods.
	 * @return array
	 */
	public static function shipping_methods( $m ) {
		$m['zt_shipping'] = 'ZT_Shipping_Method';
		return $m;
	}

	/**
	 * Gateway class file.
	 */
	public static function gateway_class() {
		if ( class_exists( 'WC_Payment_Gateway' ) ) {
			require_once ZT_PATH . 'includes/woo/class-zt-gateway-card.php';
		}
	}

	/**
	 * Register the card-to-card gateway.
	 *
	 * @param array $g Gateways.
	 * @return array
	 */
	public static function gateways( $g ) {
		if ( zt_opt( 'checkout.card_enabled', 1 ) && class_exists( 'ZT_Gateway_Card' ) ) {
			$g[] = 'ZT_Gateway_Card';
		}
		return $g;
	}

	/**
	 * Currency symbol shown by WooCommerce itself (emails, admin).
	 *
	 * @param string $symbol   Symbol.
	 * @param string $currency Currency.
	 * @return string
	 */
	public static function currency_symbol( $symbol, $currency ) {
		if ( in_array( $currency, array( 'IRT', 'IRR', 'IRHT', 'IRHR' ), true ) && 'IRT' === $currency ) {
			return zt_currency();
		}
		return $symbol;
	}

	/**
	 * Keep the checkout page editable in Elementor even with an empty cart.
	 *
	 * @param bool $redirect Redirect.
	 * @return bool
	 */
	public static function no_redirect_in_editor( $redirect ) {
		if ( isset( $_GET['elementor-preview'] ) || isset( $_GET['preview'] ) || ZT_Context::demo() ) { // phpcs:ignore
			return false;
		}
		return $redirect;
	}

	/**
	 * Cart count fragment.
	 *
	 * @param array $f Fragments.
	 * @return array
	 */
	public static function fragments( $f ) {
		$f['span.zt-cart-count-data'] = '<span class="zt-cart-count-data" data-count="' . esc_attr( WC()->cart->get_cart_contents_count() ) . '" hidden></span>';
		return $f;
	}

	/**
	 * ?on_sale=1 / product search tweaks on the shop archive.
	 *
	 * @param WP_Query $q Query.
	 */
	public static function shop_filters( $q ) {
		if ( is_admin() || ! $q->is_main_query() ) {
			return;
		}
		if ( ! empty( $_GET['on_sale'] ) && ( $q->is_post_type_archive( 'product' ) || $q->is_tax( get_object_taxonomies( 'product' ) ) || 'product' === $q->get( 'post_type' ) ) ) { // phpcs:ignore
			$ids = wc_get_product_ids_on_sale();
			$q->set( 'post__in', $ids ? $ids : array( 0 ) );
		}
	}

	/**
	 * Amount of the tier discount / coupons shown as "تخفیف" (store units).
	 *
	 * @return float
	 */
	public static function discount_total() {
		$cart = WC()->cart;
		$d    = (float) $cart->get_discount_total();
		foreach ( $cart->get_fees() as $fee ) {
			if ( $fee->amount < 0 ) {
				$d += abs( $fee->amount );
			}
		}
		return $d;
	}

	/**
	 * Shared cart state for the cart page widgets / AJAX (display units).
	 *
	 * @return array
	 */
	public static function cart_state() {
		$cart = WC()->cart;
		if ( ! $cart ) {
			return array();
		}
		$cart->calculate_totals();
		$subtotal = (float) $cart->get_subtotal() + ( $cart->display_prices_including_tax() ? (float) $cart->get_subtotal_tax() : 0 );
		$discount = self::discount_total();
		$lines    = array();
		foreach ( $cart->get_cart() as $key => $item ) {
			if ( ! empty( $item['zt_sample'] ) ) {
				continue;
			}
			$lines[ $key ] = array(
				'qty'   => (int) $item['quantity'],
				'total' => zt_amount( $item['line_subtotal'] + ( $cart->display_prices_including_tax() ? $item['line_subtotal_tax'] : 0 ) ),
			);
		}
		$tier_base = zt_amount( ZT_Tiers::base_amount() );
		return array(
			'count'            => (int) $cart->get_cart_contents_count(),
			'subtotal'         => zt_amount( $subtotal ),
			'discount'         => zt_amount( $discount ),
			'total'            => zt_amount( max( 0, $subtotal - $discount ) ),
			'grand'            => zt_amount( (float) $cart->get_total( 'edit' ) ),
			'lines'            => $lines,
			'subtotal_tier'    => $tier_base,
			'tiers'            => ZT_Tiers::state( $tier_base ),
			'free_shipping'    => ZT_Tiers::free_shipping_reached( $tier_base ),
			'samples_unlocked' => zt_amount( $subtotal ) >= (float) zt_opt( 'cart.samples_min', 0 ),
			'sample'           => ZT_Samples::selected(),
		);
	}
}
