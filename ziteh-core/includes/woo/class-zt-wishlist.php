<?php
/**
 * Wishlist (user meta for customers, cookie for guests) + "علاقه‌مندی‌ها"
 * account endpoint.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_Wishlist
 */
class ZT_Wishlist {

	const META   = '_zt_wishlist';
	const COOKIE = 'zt_wishlist';
	const EP     = 'zt-wishlist';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'endpoint' ) );
		add_filter( 'woocommerce_get_query_vars', array( __CLASS__, 'query_vars' ) );
		add_filter( 'woocommerce_account_menu_items', array( __CLASS__, 'menu' ) );
		add_filter( 'woocommerce_endpoint_' . self::EP . '_title', array( __CLASS__, 'title' ) );
		add_action( 'woocommerce_account_' . self::EP . '_endpoint', array( __CLASS__, 'render_endpoint' ) );
		add_action( 'wp_login', array( __CLASS__, 'merge_on_login' ), 10, 2 );
	}

	/**
	 * Endpoint.
	 */
	public static function endpoint() {
		add_rewrite_endpoint( self::EP, EP_ROOT | EP_PAGES );
	}

	/**
	 * WC query vars.
	 *
	 * @param array $v Vars.
	 * @return array
	 */
	public static function query_vars( $v ) {
		$v[ self::EP ] = self::EP;
		return $v;
	}

	/**
	 * Account menu item.
	 *
	 * @param array $items Items.
	 * @return array
	 */
	public static function menu( $items ) {
		$out = array();
		foreach ( $items as $k => $v ) {
			$out[ $k ] = $v;
			if ( 'orders' === $k ) {
				$out[ self::EP ] = 'علاقه‌مندی‌ها';
			}
		}
		if ( ! isset( $out[ self::EP ] ) ) {
			$out[ self::EP ] = 'علاقه‌مندی‌ها';
		}
		return $out;
	}

	/**
	 * Endpoint title.
	 *
	 * @return string
	 */
	public static function title() {
		return 'علاقه‌مندی‌ها';
	}

	/**
	 * Product ids.
	 *
	 * @return int[]
	 */
	public static function get() {
		if ( is_user_logged_in() ) {
			$l = get_user_meta( get_current_user_id(), self::META, true );
			return array_values( array_filter( array_map( 'absint', is_array( $l ) ? $l : array() ) ) );
		}
		$c = isset( $_COOKIE[ self::COOKIE ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ self::COOKIE ] ) ) : '';
		return array_values( array_filter( array_map( 'absint', explode( ',', $c ) ) ) );
	}

	/**
	 * Save.
	 *
	 * @param int[] $ids Ids.
	 */
	private static function save( $ids ) {
		$ids = array_slice( array_values( array_unique( array_map( 'absint', $ids ) ) ), 0, 100 );
		if ( is_user_logged_in() ) {
			update_user_meta( get_current_user_id(), self::META, $ids );
		} elseif ( zt_opt( 'tracking.wishlist_guest', 1 ) ) {
			$v = implode( ',', $ids );
			setcookie( self::COOKIE, $v, time() + YEAR_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true );
			$_COOKIE[ self::COOKIE ] = $v;
		}
	}

	/**
	 * In list?
	 *
	 * @param int $id Id.
	 * @return bool
	 */
	public static function has( $id ) {
		return in_array( (int) $id, self::get(), true );
	}

	/**
	 * Toggle.
	 *
	 * @param int $id Product id.
	 * @return bool New state.
	 */
	public static function toggle( $id ) {
		$ids = self::get();
		if ( in_array( $id, $ids, true ) ) {
			$ids = array_diff( $ids, array( $id ) );
			self::save( $ids );
			return false;
		}
		array_unshift( $ids, $id );
		self::save( $ids );
		return true;
	}

	/**
	 * Merge guest list into the account on login.
	 *
	 * @param string  $login Login.
	 * @param WP_User $user  User.
	 */
	public static function merge_on_login( $login, $user ) {
		$c = isset( $_COOKIE[ self::COOKIE ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ self::COOKIE ] ) ) : '';
		if ( ! $c ) {
			return;
		}
		$l = get_user_meta( $user->ID, self::META, true );
		$l = is_array( $l ) ? $l : array();
		update_user_meta( $user->ID, self::META, array_values( array_unique( array_merge( array_map( 'absint', explode( ',', $c ) ), $l ) ) ) );
		setcookie( self::COOKIE, '', time() - 3600, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN );
	}

	/**
	 * Endpoint content (favourites grid).
	 */
	public static function render_endpoint() {
		$ids = self::get();
		echo '<section class="zt-dcard"><div class="zt-dcard__head"><h3>' . zt_icon( 'heart' ) . ' علاقه‌مندی‌ها</h3></div>'; // phpcs:ignore
		if ( ! $ids ) {
			echo '<div class="zt-cart-empty"><span class="zt-cart-empty__ic">' . zt_icon( 'heart' ) . '</span><b>لیست علاقه‌مندی شما خالی است</b><p>با زدن آیکون قلب روی هر محصول، آن را اینجا ذخیره کنید.</p><a class="zt-btn zt-btn--primary" href="' . esc_url( zt_page_url( 'shop' ) ) . '">مشاهده محصولات</a></div></section>'; // phpcs:ignore
			return;
		}
		echo '<div class="zt-favs zt-favs--page">';
		foreach ( $ids as $id ) {
			$it = ZT_Parts::product_item( $id, 'medium' );
			if ( ! $it ) {
				continue;
			}
			echo '<div class="zt-fav" data-zt-product="' . esc_attr( ZT_Parts::qa_json( $it ) ) . '"><a href="' . esc_url( $it['url'] ) . '"><img src="' . esc_url( $it['img'] ) . '" alt=""><b>' . esc_html( $it['title'] ) . '</b></a><span>' . wp_kses_post( zt_price( $it['price'], '' ) ) . '</span>';
			echo '<div class="zt-fav__acts"><button class="zt-btn zt-btn--soft zt-btn--sm" data-zt-quick>' . zt_icon( 'cart' ) . ' افزودن</button><button class="zt-fav__rm zt-is-on" data-zt-wish="' . esc_attr( $id ) . '" aria-label="حذف">' . zt_icon( 'heart-fill' ) . '</button></div></div>'; // phpcs:ignore
		}
		echo '</div></section>';
	}
}
