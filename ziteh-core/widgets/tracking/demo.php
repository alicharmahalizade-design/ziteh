<?php
/**
 * Sample order (design) for the tracking widgets.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'ZT_Order_View' ) ) {
	/**
	 * Normalised order view used by all tracking widgets.
	 */
	class ZT_Order_View {

		/**
		 * Current order view (real or sample) or null.
		 *
		 * @return array|null
		 */
		public static function get() {
			static $v = false;
			if ( false !== $v ) {
				return $v;
			}
			$o = ZT_Context::order();
			if ( $o ) {
				$v = self::from_order( $o );
			} elseif ( ZT_Context::demo() || zt_is_editor() ) {
				$v = self::sample();
			} else {
				$v = null;
			}
			return $v;
		}

		/**
		 * Build from a WooCommerce order.
		 *
		 * @param WC_Order $o Order.
		 * @return array
		 */
		public static function from_order( $o ) {
			$created = $o->get_date_created() ? $o->get_date_created()->getTimestamp() : time();
			$tl      = ZT_Tracking::timeline( $o );
			$cur     = 0;
			foreach ( $tl as $i => $t ) {
				if ( 'idle' !== $t['state'] ) {
					$cur = $i;
				}
			}
			$items = array();
			foreach ( $o->get_items() as $item ) {
				$p      = $item->get_product();
				$img    = $item->get_meta( '_zt_sample_img' );
				if ( ! $img ) {
					$img = $p && $p->get_image_id() ? wp_get_attachment_image_url( $p->get_image_id(), 'woocommerce_thumbnail' ) : wc_placeholder_img_src();
				}
				$pid     = $p ? ( $p->get_parent_id() ? $p->get_parent_id() : $p->get_id() ) : 0;
				$sub     = $item->get_meta( 'حجم' );
				$sub     = $sub ? $sub : ( $pid ? get_post_meta( $pid, '_zt_volume', true ) : '' );
				$qty     = (int) $item->get_quantity();
				$total   = (float) $o->get_line_subtotal( $item, true );
				$items[] = array( $item->get_name(), $sub, $img, $qty, $qty ? $total / $qty : 0, $total );
			}
			$ship_method = $o->get_shipping_method();
			$discount    = (float) $o->get_discount_total();
			foreach ( $o->get_fees() as $fee ) {
				if ( (float) $fee->get_total() < 0 ) {
					$discount += abs( (float) $fee->get_total() );
				}
			}
			$addr = trim( implode( '، ', array_filter( array( WC()->countries->get_states( 'IR' )[ $o->get_billing_state() ] ?? '', $o->get_billing_city(), $o->get_billing_address_1(), $o->get_billing_address_2() ) ) ) );
			$iran = ZT_Checkout::iran();
			if ( isset( $iran[ $o->get_billing_state() ] ) ) {
				$addr = trim( implode( '، ', array_filter( array( $iran[ $o->get_billing_state() ][0], $o->get_billing_city(), $o->get_billing_address_1(), $o->get_billing_address_2() ) ) ) );
			}
			$track = ZT_Tracking::data( $o );
			return array(
				'id'         => $o->get_order_number(),
				'created'    => $created,
				'status'     => wc_get_order_status_name( $o->get_status() ),
				'status_ic'  => isset( $tl[ $cur ] ) ? $tl[ $cur ]['icon'] : 'clipboard',
				'ship'       => $ship_method,
				'pay'        => $o->get_payment_method_title(),
				'total'      => (float) $o->get_total(),
				'timeline'   => $tl,
				'current'    => $cur,
				'items'      => $items,
				'subtotal'   => (float) $o->get_subtotal(),
				'discount'   => $discount,
				'shipping'   => (float) $o->get_shipping_total(),
				'name'       => trim( $o->get_formatted_billing_full_name() ),
				'phone'      => $o->get_billing_phone(),
				'address'    => $addr,
				'events'     => $track['events'],
				'eta'        => $track['eta'],
				'code'       => $track['code'],
				'sample'     => false,
				'order'      => $o,
			);
		}

		/**
		 * The design's sample order.
		 *
		 * @return array
		 */
		public static function sample() {
			$m = max( 1, (float) zt_opt( 'general.price_divisor', 1 ) );
			return array(
				'id'        => '15587',
				'created'   => 0,
				'created_s' => '۲۳ اردیبهشت ۱۴۰۳ — ۱۰:۴۲',
				'status'    => 'در حال ارسال',
				'status_ic' => 'truck-fast',
				'ship'      => 'پست پیشتاز',
				'pay'       => 'پرداخت آنلاین',
				'total'     => 1485000 * $m,
				'timeline'  => array(
					array( 'title' => 'ثبت سفارش', 'icon' => 'clipboard', 'state' => 'done', 'time_s' => array( '۲۳ اردیبهشت ۱۴۰۳', '۱۰:۴۲' ) ),
					array( 'title' => 'در حال آماده‌سازی', 'icon' => 'box', 'state' => 'done', 'time_s' => array( '۲۳ اردیبهشت ۱۴۰۳', '۱۳:۱۵' ) ),
					array( 'title' => 'در حال ارسال', 'icon' => 'truck-fast', 'state' => 'current', 'time_s' => array( '۲۴ اردیبهشت ۱۴۰۳', '۰۸:۳۰' ) ),
					array( 'title' => 'در راه', 'icon' => 'pin', 'state' => 'idle', 'time_s' => null ),
					array( 'title' => 'تحویل شده', 'icon' => 'check', 'state' => 'idle', 'time_s' => null ),
				),
				'current'   => 2,
				'items'     => array(
					array( 'شامپو تقویت‌کننده و ضد ریزش مو زیته', '۵۰۰ میلی‌لیتر', zt_asset_img( 't-item-1.jpg' ), 1, 385000 * $m, 385000 * $m ),
					array( 'سرم ضد ریزش مو زیته', '۳۰ میلی‌لیتر', zt_asset_img( 't-item-2.jpg' ), 2, 395000 * $m, 790000 * $m ),
					array( 'ماسک مو تغذیه‌کننده زیته', '۲۰۰ میلی‌لیتر', zt_asset_img( 't-item-3.jpg' ), 1, 475000 * $m, 475000 * $m ),
					array( 'نمونه هدیه (سمپل)', 'شامپو و سرم', zt_asset_img( 't-item-4.jpg' ), 1, 0, 0 ),
				),
				'subtotal'  => 1655000 * $m,
				'discount'  => 165000 * $m,
				'shipping'  => 0,
				'name'      => 'نادر محمدی',
				'phone'     => '۰۹۱۲ ۱۳۳ ۴۵۶۷',
				'address'   => "تهران، خیابان پاسداران، خیابان گل نبی\nکوچه دوم، پلاک ۱۲، واحد ۳",
				'events'    => array(
					array( 'title' => 'تحویل به پست', 'time' => '۲۴ اردیبهشت ۱۴۰۳ – ۰۸:۳۰', 'place' => 'تهران، مرکز پردازش پست', 'done' => 1 ),
					array( 'title' => 'خروج از مرکز مبدا', 'time' => '۲۴ اردیبهشت ۱۴۰۳ – ۱۱:۰۰', 'place' => 'تهران، مرکز پردازش پست', 'done' => 1 ),
					array( 'title' => 'در حال انتقال به مقصد', 'time' => '۲۴ اردیبهشت ۱۴۰۳ – ۱۶:۴۵', 'place' => "تهران \u{a0}←\u{a0} اصفهان", 'done' => 1 ),
					array( 'title' => 'تحویل به مقصد', 'time' => '', 'place' => 'در انتظار تحویل', 'done' => 0 ),
				),
				'eta'       => '۲۵ اردیبهشت ۱۴۰۳ (۱ الی ۲ روز کاری)',
				'code'      => '',
				'sample'    => true,
				'order'     => null,
			);
		}
	}
}
