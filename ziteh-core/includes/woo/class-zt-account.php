<?php
/**
 * My-account extras: "my reviews" and "discount codes" endpoints, dashboard
 * statistics, current endpoint detection.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_Account
 */
class ZT_Account {

	const EP_REVIEWS = 'zt-reviews';
	const EP_COUPONS = 'zt-coupons';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_filter( 'woocommerce_get_query_vars', array( __CLASS__, 'query_vars' ) );
		add_filter( 'woocommerce_account_menu_items', array( __CLASS__, 'menu' ), 20 );
		add_filter( 'woocommerce_endpoint_' . self::EP_REVIEWS . '_title', array( __CLASS__, 'title_reviews' ) );
		add_filter( 'woocommerce_endpoint_' . self::EP_COUPONS . '_title', array( __CLASS__, 'title_coupons' ) );
		add_action( 'woocommerce_account_' . self::EP_REVIEWS . '_endpoint', array( __CLASS__, 'render_reviews' ) );
		add_action( 'woocommerce_account_' . self::EP_COUPONS . '_endpoint', array( __CLASS__, 'render_coupons' ) );
		add_action( 'woocommerce_coupon_options', array( __CLASS__, 'coupon_option' ) );
		add_action( 'woocommerce_coupon_options_save', array( __CLASS__, 'coupon_save' ) );
		add_filter( 'woocommerce_my_account_my_orders_actions', array( __CLASS__, 'order_actions' ), 10, 2 );
		add_filter( 'authenticate', array( __CLASS__, 'phone_login' ), 15, 3 );
	}

	/**
	 * Allow logging in with the billing mobile number (Persian or Latin digits).
	 *
	 * @param WP_User|WP_Error|null $user     User.
	 * @param string                $username Login.
	 * @param string                $password Password.
	 * @return WP_User|WP_Error|null
	 */
	public static function phone_login( $user, $username, $password ) {
		if ( $user instanceof WP_User || '' === (string) $username || '' === (string) $password ) {
			return $user;
		}
		$digits = preg_replace( '/\D/', '', zt_en( $username ) );
		if ( strlen( $digits ) < 10 || $digits !== preg_replace( '/[\s\-+]/', '', zt_en( $username ) ) ) {
			return $user;
		}
		$tail  = substr( $digits, -10 );
		$found = get_users(
			array(
				'meta_key'     => 'billing_phone', // phpcs:ignore
				'meta_value'   => $tail, // phpcs:ignore
				'meta_compare' => 'LIKE',
				'number'       => 5,
			)
		);
		foreach ( $found as $u ) {
			if ( substr( preg_replace( '/\D/', '', zt_en( get_user_meta( $u->ID, 'billing_phone', true ) ) ), -10 ) === $tail ) {
				return wp_authenticate_username_password( null, $u->user_login, $password );
			}
		}
		return $user;
	}

	/**
	 * Query vars.
	 *
	 * @param array $v Vars.
	 * @return array
	 */
	public static function query_vars( $v ) {
		$v[ self::EP_REVIEWS ] = self::EP_REVIEWS;
		$v[ self::EP_COUPONS ] = self::EP_COUPONS;
		return $v;
	}

	/**
	 * Default WC account menu (used by themes/other places).
	 *
	 * @param array $items Items.
	 * @return array
	 */
	public static function menu( $items ) {
		$logout = isset( $items['customer-logout'] ) ? $items['customer-logout'] : null;
		unset( $items['customer-logout'] );
		$items[ self::EP_REVIEWS ] = 'نظرات من';
		$items[ self::EP_COUPONS ] = 'کدهای تخفیف';
		if ( $logout ) {
			$items['customer-logout'] = $logout;
		}
		return $items;
	}

	/** @return string */
	public static function title_reviews() {
		return 'نظرات من';
	}

	/** @return string */
	public static function title_coupons() {
		return 'کدهای تخفیف';
	}

	/**
	 * "Track" action in the orders list → Ziteh tracking page.
	 *
	 * @param array    $actions Actions.
	 * @param WC_Order $order   Order.
	 * @return array
	 */
	public static function order_actions( $actions, $order ) {
		$actions['zt-track'] = array(
			'url'  => self::track_url( $order ),
			'name' => 'پیگیری',
		);
		return $actions;
	}

	/**
	 * Tracking url for an order.
	 *
	 * @param WC_Order $order Order.
	 * @return string
	 */
	public static function track_url( $order ) {
		return add_query_arg(
			array(
				'zt_order' => $order->get_id(),
				'zt_key'   => $order->get_order_key(),
			),
			zt_page_url( 'tracking' )
		);
	}

	/**
	 * Current account endpoint ('' = dashboard).
	 *
	 * @return string
	 */
	public static function endpoint() {
		if ( ! function_exists( 'WC' ) || ! WC()->query ) {
			return '';
		}
		$ep = WC()->query->get_current_endpoint();
		return $ep ? $ep : '';
	}

	/**
	 * Status pill data (class + icon) for an order status.
	 *
	 * @param string $status Status without wc-.
	 * @return array [ class, icon ]
	 */
	public static function status_pill( $status ) {
		switch ( $status ) {
			case 'zt-shipped':
			case 'zt-transit':
				return array( 'zt-ostatus zt-ostatus--ship', 'truck-fast' );
			case 'processing':
				return array( 'zt-ostatus zt-ostatus--ship', 'box' );
			case 'completed':
				return array( 'zt-ostatus', 'check-circle' );
			case 'cancelled':
			case 'failed':
			case 'refunded':
				return array( 'zt-ostatus zt-ostatus--bad', 'x' );
			default:
				return array( 'zt-ostatus zt-ostatus--wait', 'clock' );
		}
	}

	/**
	 * Dashboard statistics for a user.
	 *
	 * @param int $uid User id.
	 * @return array
	 */
	public static function stats( $uid ) {
		static $cache = array();
		if ( isset( $cache[ $uid ] ) ) {
			return $cache[ $uid ];
		}
		$count = function ( $statuses ) use ( $uid ) {
			return count(
				wc_get_orders(
					array(
						'customer_id' => $uid,
						'status'      => $statuses,
						'limit'       => -1,
						'return'      => 'ids',
					)
				)
			);
		};
		$customer      = new WC_Customer( $uid );
		$cache[ $uid ] = array(
			'points'    => (int) get_user_meta( $uid, '_zt_points', true ),
			'spent'     => (float) $customer->get_total_spent(),
			'completed' => $count( array( 'wc-completed' ) ),
			'shipping'  => $count( array( 'wc-processing', 'wc-zt-shipped', 'wc-zt-transit' ) ),
			'orders'    => (int) $customer->get_order_count(),
			'wishlist'  => count( ZT_Wishlist::get() ),
			'reviews'   => (int) get_comments(
				array(
					'user_id' => $uid,
					'type'    => 'review',
					'count'   => true,
				)
			),
		);
		return $cache[ $uid ];
	}

	/**
	 * Reviews endpoint.
	 */
	public static function render_reviews() {
		$list = get_comments(
			array(
				'user_id' => get_current_user_id(),
				'type'    => 'review',
				'status'  => 'all',
				'number'  => 50,
			)
		);
		echo '<section class="zt-dcard"><div class="zt-dcard__head"><h3>' . zt_icon( 'star-o' ) . ' نظرات من</h3></div>'; // phpcs:ignore
		if ( ! $list ) {
			echo '<div class="zt-cart-empty"><span class="zt-cart-empty__ic">' . zt_icon( 'star-o' ) . '</span><b>هنوز نظری ثبت نکرده‌اید</b><p>تجربه خود از محصولات را با دیگران به اشتراک بگذارید.</p><a class="zt-btn zt-btn--primary" href="' . esc_url( zt_page_url( 'shop' ) ) . '">مشاهده محصولات</a></div></section>'; // phpcs:ignore
			return;
		}
		echo '<div class="zt-myrevs">';
		foreach ( $list as $c ) {
			$p      = wc_get_product( $c->comment_post_ID );
			$rating = (int) get_comment_meta( $c->comment_ID, 'rating', true );
			$img    = $p && $p->get_image_id() ? wp_get_attachment_image_url( $p->get_image_id(), 'thumbnail' ) : wc_placeholder_img_src();
			echo '<article class="zt-myrev"><a href="' . esc_url( get_permalink( $c->comment_post_ID ) ) . '"><img src="' . esc_url( $img ) . '" alt=""></a><div><div class="zt-myrev__head"><b>' . esc_html( get_the_title( $c->comment_post_ID ) ) . '</b><time>' . esc_html( zt_jdate( 'j F Y', strtotime( $c->comment_date_gmt . ' UTC' ) ) ) . '</time></div>';
			echo ZT_Parts::stars( $rating ? $rating : 5 ); // phpcs:ignore
			echo '<p>' . esc_html( $c->comment_content ) . '</p>';
			if ( '1' !== (string) $c->comment_approved ) {
				echo '<span class="zt-ostatus zt-ostatus--wait">' . zt_icon( 'clock' ) . ' در انتظار تایید</span>'; // phpcs:ignore
			}
			echo '</div></article>';
		}
		echo '</div></section>';
	}

	/**
	 * Coupons visible to the current customer.
	 *
	 * @return WC_Coupon[]
	 */
	public static function coupons() {
		$user  = wp_get_current_user();
		$email = strtolower( $user->user_email );
		$ids   = get_posts(
			array(
				'post_type'   => 'shop_coupon',
				'post_status' => 'publish',
				'numberposts' => 100,
				'fields'      => 'ids',
			)
		);
		$out   = array();
		foreach ( $ids as $id ) {
			$c = new WC_Coupon( $id );
			if ( $c->get_date_expires() && $c->get_date_expires()->getTimestamp() < time() ) {
				continue;
			}
			if ( $c->get_usage_limit() && $c->get_usage_count() >= $c->get_usage_limit() ) {
				continue;
			}
			$emails = array_map( 'strtolower', $c->get_email_restrictions() );
			if ( ( $email && in_array( $email, $emails, true ) ) || 'yes' === $c->get_meta( '_zt_public' ) ) {
				$out[] = $c;
			}
		}
		return $out;
	}

	/**
	 * Coupons endpoint.
	 */
	public static function render_coupons() {
		$list = self::coupons();
		echo '<section class="zt-dcard"><div class="zt-dcard__head"><h3>' . zt_icon( 'tag' ) . ' کدهای تخفیف</h3></div>'; // phpcs:ignore
		if ( ! $list ) {
			echo '<div class="zt-cart-empty"><span class="zt-cart-empty__ic">' . zt_icon( 'tag' ) . '</span><b>در حال حاضر کد تخفیفی برای شما وجود ندارد</b><p>کدهای تخفیف اختصاصی شما در این بخش نمایش داده می‌شوند.</p></div></section>'; // phpcs:ignore
			return;
		}
		echo '<div class="zt-coupons">';
		foreach ( $list as $c ) {
			$amount = 'percent' === $c->get_discount_type() ? zt_fa( (float) $c->get_amount() ) . '٪' : zt_price( $c->get_amount(), '' );
			echo '<div class="zt-coupon-card"><div class="zt-coupon-card__amount">' . wp_kses_post( $amount ) . '<small>تخفیف</small></div><div class="zt-coupon-card__body">';
			echo '<b>' . esc_html( $c->get_description() ? $c->get_description() : 'کد تخفیف' ) . '</b>';
			if ( $c->get_minimum_amount() ) {
				echo '<span>حداقل خرید: ' . wp_kses_post( zt_price( $c->get_minimum_amount(), '' ) ) . '</span>';
			}
			if ( $c->get_date_expires() ) {
				echo '<span>اعتبار تا: ' . esc_html( zt_jdate( 'j F Y', $c->get_date_expires()->getTimestamp() ) ) . '</span>';
			}
			echo '<button type="button" class="zt-coupon-card__code" data-zt-copy="' . esc_attr( strtoupper( $c->get_code() ) ) . '">' . esc_html( strtoupper( $c->get_code() ) ) . ' ' . zt_icon( 'copy' ) . '</button></div></div>'; // phpcs:ignore
		}
		echo '</div></section>';
	}

	/**
	 * Coupon admin: public flag.
	 */
	public static function coupon_option() {
		woocommerce_wp_checkbox(
			array(
				'id'          => '_zt_public',
				'label'       => 'نمایش در پنل کاربران',
				'description' => 'این کد در بخش «کدهای تخفیف» پنل همه کاربران نمایش داده شود (کدهای محدود به ایمیل، فقط برای همان کاربر نمایش داده می‌شوند).',
			)
		);
	}

	/**
	 * Save coupon flag.
	 *
	 * @param int $id Coupon id.
	 */
	public static function coupon_save( $id ) {
		update_post_meta( $id, '_zt_public', isset( $_POST['_zt_public'] ) ? 'yes' : 'no' ); // phpcs:ignore
	}
}
