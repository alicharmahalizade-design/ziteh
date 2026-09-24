<?php
/**
 * AJAX endpoints (search, cart, samples, wishlist, newsletter, contact, coupon).
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_Ajax
 */
class ZT_Ajax {

	/**
	 * Hooks.
	 */
	public static function init() {
		foreach ( array( 'search', 'add_to_cart', 'cart_qty', 'cart_remove', 'cart_clear', 'cart_state', 'sample', 'wishlist', 'newsletter', 'contact', 'coupon' ) as $a ) {
			add_action( 'wp_ajax_zt_' . $a, array( __CLASS__, $a ) );
			add_action( 'wp_ajax_nopriv_zt_' . $a, array( __CLASS__, $a ) );
		}
	}

	/**
	 * Verify nonce.
	 */
	private static function check() {
		if ( ! check_ajax_referer( 'zt-ajax', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => 'نشست شما منقضی شده است؛ صفحه را دوباره بارگذاری کنید.' ), 403 );
		}
	}

	/**
	 * Require WooCommerce.
	 */
	private static function woo() {
		if ( ! zt_is_woo() || ! WC()->cart ) {
			wp_send_json_error( array( 'message' => 'فروشگاه فعال نیست.' ) );
		}
	}

	/**
	 * Request value.
	 *
	 * @param string $k Key.
	 * @param mixed  $d Default.
	 * @return mixed
	 */
	private static function req( $k, $d = '' ) {
		return isset( $_POST[ $k ] ) ? wp_unslash( $_POST[ $k ] ) : $d; // phpcs:ignore
	}

	/**
	 * Live product search.
	 */
	public static function search() {
		self::check();
		$q     = sanitize_text_field( self::req( 'q' ) );
		$limit = max( 1, (int) zt_opt( 'shell.search_limit', 12 ) );
		$html  = '';
		$count = 0;
		if ( '' !== $q && zt_is_woo() ) {
			$norm  = zt_normalize_fa( $q );
			$alt   = str_replace( array( 'ی', 'ک' ), array( 'ي', 'ك' ), $norm );
			$ids   = array();
			foreach ( array_unique( array( $q, $norm, $alt ) ) as $term ) {
				$found = get_posts(
					array(
						'post_type'      => 'product',
						'post_status'    => 'publish',
						's'              => $term,
						'posts_per_page' => 40,
						'fields'         => 'ids',
					)
				);
				$ids   = array_merge( $ids, $found );
			}
			// match categories too
			$terms = get_terms(
				array(
					'taxonomy'   => 'product_cat',
					'name__like' => $norm,
					'hide_empty' => true,
					'fields'     => 'ids',
				)
			);
			if ( $terms && ! is_wp_error( $terms ) ) {
				$ids = array_merge(
					$ids,
					get_posts(
						array(
							'post_type'      => 'product',
							'post_status'    => 'publish',
							'posts_per_page' => 20,
							'fields'         => 'ids',
							'tax_query'      => array( array( 'taxonomy' => 'product_cat', 'terms' => $terms ) ), // phpcs:ignore
						)
					)
				);
			}
			// SKU
			$sku = wc_get_product_id_by_sku( zt_en( $q ) );
			if ( $sku ) {
				array_unshift( $ids, $sku );
			}
			$ids   = array_values( array_unique( array_map( 'intval', $ids ) ) );
			$count = count( $ids );
			foreach ( array_slice( $ids, 0, $limit ) as $id ) {
				$it = ZT_Parts::product_item( $id, 'thumbnail' );
				if ( $it ) {
					$html .= ZT_Parts::search_row( $it );
				}
			}
		}
		wp_send_json_success(
			array(
				'html'  => $html,
				'count' => $count,
			)
		);
	}

	/**
	 * Add to cart (simple + variations).
	 */
	public static function add_to_cart() {
		self::check();
		self::woo();
		$pid   = absint( self::req( 'product_id' ) );
		$qty   = max( 1, absint( zt_en( self::req( 'quantity', 1 ) ) ) );
		$vid   = absint( self::req( 'variation_id', 0 ) );
		$attrs = array();
		foreach ( $_POST as $k => $v ) { // phpcs:ignore
			if ( 0 === strpos( $k, 'attribute_' ) ) {
				$attrs[ sanitize_title( $k ) ] = sanitize_text_field( wp_unslash( $v ) );
			}
		}
		$product = wc_get_product( $pid );
		if ( ! $product ) {
			wp_send_json_error( array( 'message' => 'محصول پیدا نشد.' ) );
		}
		if ( $product->is_type( 'variable' ) && ! $vid ) {
			wp_send_json_error(
				array(
					'message'  => 'لطفاً گزینه‌های محصول را انتخاب کنید.',
					'redirect' => $product->get_permalink(),
				)
			);
		}
		if ( ! $product->is_type( array( 'simple', 'variable', 'variation' ) ) ) {
			wp_send_json_error(
				array(
					'message'  => 'برای خرید این محصول به صفحه آن بروید.',
					'redirect' => $product->get_permalink(),
				)
			);
		}
		wc_clear_notices();
		$passed = apply_filters( 'woocommerce_add_to_cart_validation', true, $pid, $qty, $vid, $attrs );
		$key    = $passed ? WC()->cart->add_to_cart( $pid, $qty, $vid, $attrs ) : false;
		if ( ! $key ) {
			$notices = wc_get_notices( 'error' );
			wc_clear_notices();
			$msg = $notices ? wp_strip_all_tags( is_array( $notices[0] ) ? $notices[0]['notice'] : $notices[0] ) : 'افزودن به سبد ممکن نشد.';
			wp_send_json_error( array( 'message' => $msg ) );
		}
		wc_clear_notices();
		do_action( 'woocommerce_ajax_added_to_cart', $pid );
		WC()->cart->calculate_totals();
		ob_start();
		woocommerce_mini_cart();
		ob_end_clean();
		wp_send_json_success(
			array(
				'count'     => WC()->cart->get_cart_contents_count(),
				'key'       => $key,
				'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array() ),
				'cart_hash' => WC()->cart->get_cart_hash(),
			)
		);
	}

	/**
	 * Change a cart line quantity.
	 */
	public static function cart_qty() {
		self::check();
		self::woo();
		$key = sanitize_text_field( self::req( 'key' ) );
		$qty = absint( zt_en( self::req( 'qty', 1 ) ) );
		if ( ! WC()->cart->get_cart_item( $key ) ) {
			wp_send_json_error( array( 'message' => 'این کالا در سبد شما نیست.' ) );
		}
		$item    = WC()->cart->get_cart_item( $key );
		$product = $item['data'];
		if ( $product && $product->managing_stock() && ! $product->backorders_allowed() && $qty > $product->get_stock_quantity() ) {
			$qty = max( 1, (int) $product->get_stock_quantity() );
		}
		WC()->cart->set_quantity( $key, max( 1, $qty ), true );
		wp_send_json_success( ZT_Woo::cart_state() );
	}

	/**
	 * Remove a line.
	 */
	public static function cart_remove() {
		self::check();
		self::woo();
		WC()->cart->remove_cart_item( sanitize_text_field( self::req( 'key' ) ) );
		wp_send_json_success( ZT_Woo::cart_state() );
	}

	/**
	 * Empty the cart.
	 */
	public static function cart_clear() {
		self::check();
		self::woo();
		WC()->cart->empty_cart();
		wp_send_json_success( ZT_Woo::cart_state() );
	}

	/**
	 * Current state.
	 */
	public static function cart_state() {
		self::check();
		self::woo();
		wp_send_json_success( ZT_Woo::cart_state() );
	}

	/**
	 * Choose a gift sample.
	 */
	public static function sample() {
		self::check();
		self::woo();
		if ( ! ZT_Samples::unlocked() ) {
			wp_send_json_error( array( 'message' => str_replace( '{amount}', zt_money( zt_opt( 'cart.samples_min' ) ), zt_opt( 'cart.samples_locked' ) ) ) );
		}
		$i = (int) self::req( 'index', -1 );
		ZT_Samples::select( $i );
		wp_send_json_success( array( 'state' => ZT_Woo::cart_state() ) );
	}

	/**
	 * Toggle wishlist.
	 */
	public static function wishlist() {
		self::check();
		$id = absint( self::req( 'product_id' ) );
		if ( ! $id || ! class_exists( 'ZT_Wishlist' ) ) {
			wp_send_json_error( array( 'message' => 'محصول نامعتبر است.' ) );
		}
		if ( ! is_user_logged_in() && ! zt_opt( 'tracking.wishlist_guest', 1 ) ) {
			wp_send_json_error(
				array(
					'message'  => 'برای افزودن به علاقه‌مندی ابتدا وارد شوید.',
					'redirect' => zt_page_url( 'account' ),
				)
			);
		}
		$on = ZT_Wishlist::toggle( $id );
		wp_send_json_success(
			array(
				'on'    => $on,
				'count' => count( ZT_Wishlist::get() ),
			)
		);
	}

	/**
	 * Newsletter signup.
	 */
	public static function newsletter() {
		self::check();
		$email = sanitize_email( self::req( 'email' ) );
		if ( ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => zt_opt( 'newsletter.invalid' ) ) );
		}
		$r = ZT_Newsletter::subscribe( $email );
		if ( 'exists' === $r ) {
			wp_send_json_error( array( 'message' => zt_opt( 'newsletter.exists' ) ) );
		}
		wp_send_json_success( array( 'message' => zt_opt( 'newsletter.success' ) ) );
	}

	/**
	 * Contact form.
	 */
	public static function contact() {
		self::check();
		$name  = sanitize_text_field( self::req( 'name' ) );
		$phone = sanitize_text_field( zt_en( self::req( 'phone' ) ) );
		$email = sanitize_email( self::req( 'email' ) );
		$msg   = sanitize_textarea_field( self::req( 'message' ) );
		$subj  = sanitize_text_field( self::req( 'subject' ) );
		if ( '' === trim( $name ) || '' === trim( $msg ) || ( '' === $phone && ! is_email( $email ) ) ) {
			wp_send_json_error( array( 'message' => 'لطفاً نام، راه ارتباطی و متن پیام را کامل وارد کنید.' ) );
		}
		if ( '' !== (string) self::req( 'website' ) ) { // honeypot
			wp_send_json_success( array( 'message' => zt_opt( 'newsletter.contact_success' ) ) );
		}
		if ( '' !== $subj ) {
			$msg = 'موضوع: ' . $subj . "\n\n" . $msg;
		}
		ZT_Contact::store( compact( 'name', 'phone', 'email', 'msg' ) );
		wp_send_json_success( array( 'message' => zt_opt( 'newsletter.contact_success' ) ) );
	}

	/**
	 * Apply a coupon on the checkout.
	 */
	public static function coupon() {
		self::check();
		self::woo();
		$code = wc_format_coupon_code( sanitize_text_field( self::req( 'code' ) ) );
		if ( '' === $code ) {
			wp_send_json_error( array( 'message' => 'کد تخفیف را وارد کنید.' ) );
		}
		wc_clear_notices();
		$ok = WC()->cart->apply_coupon( $code );
		$n  = wc_get_notices( $ok ? 'success' : 'error' );
		wc_clear_notices();
		$msg = $n ? wp_strip_all_tags( is_array( $n[0] ) ? $n[0]['notice'] : $n[0] ) : ( $ok ? 'کد تخفیف اعمال شد.' : 'کد تخفیف معتبر نیست.' );
		if ( ! $ok ) {
			wp_send_json_error( array( 'message' => $msg ) );
		}
		wp_send_json_success( array( 'message' => $msg ) );
	}
}
