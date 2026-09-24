<?php
/**
 * Resolves the "current" product / order / post for dynamic widgets — on the
 * front end, inside Ziteh templates and in the Elementor editor preview.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_Context
 */
class ZT_Context {

	/**
	 * Forced product (used while rendering a template for a given product).
	 *
	 * @var WC_Product|null
	 */
	public static $product = null;

	/**
	 * Cached order.
	 *
	 * @var WC_Order|false|null
	 */
	private static $order = null;

	/**
	 * Design-preview mode: every dynamic widget renders the sample content of
	 * the original design (?zt_demo=1, for editors only).
	 *
	 * @return bool
	 */
	public static function demo() {
		static $d = null;
		if ( null === $d ) {
			$d = isset( $_GET['zt_demo'] ) && ( current_user_can( 'edit_pages' ) || ( defined( 'ZT_ALLOW_DEMO' ) && ZT_ALLOW_DEMO ) ); // phpcs:ignore
		}
		return $d;
	}

	/**
	 * Id of the template currently edited (if any).
	 *
	 * @return int
	 */
	public static function editing_template() {
		$id = 0;
		if ( isset( $_GET['elementor-preview'] ) ) { // phpcs:ignore
			$id = absint( $_GET['elementor-preview'] ); // phpcs:ignore
		} elseif ( isset( $_POST['editor_post_id'] ) ) { // phpcs:ignore
			$id = absint( $_POST['editor_post_id'] ); // phpcs:ignore
		} elseif ( is_singular( 'zt_template' ) ) {
			$id = get_the_ID();
		}
		return ( $id && 'zt_template' === get_post_type( $id ) ) ? $id : 0;
	}

	/**
	 * Current product.
	 *
	 * @return WC_Product|null
	 */
	public static function product() {
		if ( ! zt_is_woo() || self::demo() ) {
			return null;
		}
		if ( self::$product ) {
			return self::$product;
		}
		if ( is_singular( 'product' ) ) {
			$p = wc_get_product( get_queried_object_id() );
			if ( $p ) {
				return $p;
			}
		}
		global $product;
		if ( $product instanceof WC_Product ) {
			return $product;
		}
		$tpl = self::editing_template();
		if ( $tpl ) {
			$pid = (int) get_post_meta( $tpl, '_zt_preview_id', true );
			if ( ! $pid ) {
				$ids = wc_get_products(
					array(
						'limit'   => 1,
						'status'  => 'publish',
						'orderby' => 'date',
						'return'  => 'ids',
					)
				);
				$pid = $ids ? (int) $ids[0] : 0;
			}
			if ( $pid ) {
				return wc_get_product( $pid );
			}
		}
		return null;
	}

	/**
	 * Current blog post (single post, or a preview post while editing a template).
	 *
	 * @return WP_Post|null
	 */
	public static function post() {
		if ( is_singular( 'post' ) ) {
			return get_post( get_queried_object_id() );
		}
		$tpl = self::editing_template();
		if ( $tpl ) {
			$pid = (int) get_post_meta( $tpl, '_zt_preview_id', true );
			if ( ! $pid || 'post' !== get_post_type( $pid ) ) {
				$ids = get_posts( array( 'numberposts' => 1, 'fields' => 'ids', 'post_type' => 'post' ) );
				$pid = $ids ? (int) $ids[0] : 0;
			}
			return $pid ? get_post( $pid ) : null;
		}
		global $post;
		return ( $post instanceof WP_Post && 'post' === $post->post_type ) ? $post : null;
	}

	/**
	 * Current order for the tracking page.
	 *
	 * Resolution: ?zt_order=ID&zt_key=wc_order_xxx (guest link) or ?zt_order=ID for the
	 * owner, a posted lookup (order number + phone/email), the "view-order"
	 * endpoint, or the latest order of the logged-in customer.
	 *
	 * @return WC_Order|null
	 */
	public static function order() {
		if ( ! zt_is_woo() || self::demo() ) {
			return null;
		}
		if ( null !== self::$order ) {
			return self::$order ? self::$order : null;
		}
		self::$order = false;
		$req = wp_unslash( $_REQUEST ); // phpcs:ignore
		$id  = isset( $req['zt_order'] ) ? absint( zt_en( $req['zt_order'] ) ) : 0;
		$key = isset( $req['zt_key'] ) ? sanitize_text_field( $req['zt_key'] ) : '';

		if ( ! $id && function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'view-order' ) ) {
			global $wp;
			$id = absint( $wp->query_vars['view-order'] );
		}

		// Posted lookup.
		if ( ! empty( $req['zt_track'] ) && isset( $req['zt_track_nonce'] ) && wp_verify_nonce( $req['zt_track_nonce'], 'zt_track' ) ) {
			$num   = absint( zt_en( preg_replace( '/\D/u', '', zt_en( $req['zt_track'] ) ) ) );
			$ident = strtolower( trim( zt_en( isset( $req['zt_track_id'] ) ? $req['zt_track_id'] : '' ) ) );
			$o     = $num ? wc_get_order( $num ) : false;
			if ( $o && $ident ) {
				$phone = preg_replace( '/\D/', '', zt_en( $o->get_billing_phone() ) );
				$in    = preg_replace( '/\D/', '', $ident );
				if ( strtolower( $o->get_billing_email() ) === $ident || ( $in && $phone && substr( $phone, -10 ) === substr( $in, -10 ) ) ) {
					self::$order = $o;
					return $o;
				}
			}
			self::$order = false;
			return null;
		}

		if ( $id ) {
			$o = wc_get_order( $id );
			if ( $o && ! is_a( $o, 'WC_Order_Refund' ) ) {
				$uid = get_current_user_id();
				if ( ( $key && hash_equals( $o->get_order_key(), $key ) ) || ( $uid && (int) $o->get_customer_id() === $uid ) || current_user_can( 'manage_woocommerce' ) ) {
					self::$order = $o;
					return $o;
				}
			}
		}

		// Latest order of the customer.
		if ( is_user_logged_in() && empty( $req['zt_track'] ) ) {
			$orders = wc_get_orders(
				array(
					'customer_id' => get_current_user_id(),
					'limit'       => 1,
					'orderby'     => 'date',
					'order'       => 'DESC',
				)
			);
			if ( $orders ) {
				self::$order = $orders[0];
				return self::$order;
			}
		}

		// Editor preview: show the latest order of the store (admins only).
		if ( zt_is_editor() && current_user_can( 'manage_woocommerce' ) ) {
			$orders = wc_get_orders(
				array(
					'limit'   => 1,
					'orderby' => 'date',
					'order'   => 'DESC',
				)
			);
			if ( $orders ) {
				self::$order = $orders[0];
				return self::$order;
			}
		}
		return null;
	}

	/**
	 * Was a tracking lookup posted but not matched?
	 *
	 * @return bool
	 */
	public static function lookup_failed() {
		return ! empty( $_REQUEST['zt_track'] ) && ! self::order(); // phpcs:ignore
	}
}
