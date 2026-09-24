<?php
/**
 * Order tracking: extra statuses, stage timestamps, shipment events (admin
 * metabox), ETA, loyalty points on completion.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_Tracking
 */
class ZT_Tracking {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_statuses' ) );
		add_filter( 'wc_order_statuses', array( __CLASS__, 'statuses' ) );
		add_filter( 'woocommerce_reports_order_statuses', array( __CLASS__, 'paid_statuses' ) );
		add_filter( 'woocommerce_order_is_paid_statuses', array( __CLASS__, 'paid_statuses' ) );
		add_action( 'woocommerce_order_status_changed', array( __CLASS__, 'stamp' ), 10, 4 );
		add_action( 'woocommerce_order_status_completed', array( __CLASS__, 'award_points' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'meta_box' ) );
		add_action( 'woocommerce_process_shop_order_meta', array( __CLASS__, 'save_box' ), 50 );
		add_action( 'admin_footer', array( __CLASS__, 'box_js' ) );
		add_action( 'template_redirect', array( __CLASS__, 'save_receipt' ) );
	}

	/**
	 * Card-to-card payment info posted from the tracking page.
	 */
	public static function save_receipt() {
		if ( empty( $_POST['zt_receipt_order'] ) || empty( $_POST['zt_receipt'] ) ) { // phpcs:ignore
			return;
		}
		$id    = absint( $_POST['zt_receipt_order'] ); // phpcs:ignore
		$order = wc_get_order( $id );
		$nonce = isset( $_POST['zt_receipt_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['zt_receipt_nonce'] ) ) : '';
		$key   = isset( $_POST['zt_receipt_key'] ) ? sanitize_text_field( wp_unslash( $_POST['zt_receipt_key'] ) ) : '';
		if ( ! $order || ! wp_verify_nonce( $nonce, 'zt_receipt_' . $id ) || ! hash_equals( $order->get_order_key(), $key ) || $order->get_meta( '_zt_receipt' ) ) {
			return;
		}
		$val = sanitize_text_field( wp_unslash( $_POST['zt_receipt'] ) );
		$order->update_meta_data( '_zt_receipt', $val );
		$order->add_order_note( 'اطلاعات واریز کارت به کارت: ' . $val, 0, true );
		$order->save();
		wp_safe_redirect( add_query_arg( array( 'zt_order' => $id, 'zt_key' => $key ), zt_page_url( 'tracking' ) ) );
		exit;
	}

	/**
	 * Extra order statuses.
	 */
	public static function register_statuses() {
		register_post_status(
			'wc-zt-shipped',
			array(
				'label'                     => 'در حال ارسال',
				'public'                    => false,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s: count */
				'label_count'               => _n_noop( 'در حال ارسال <span class="count">(%s)</span>', 'در حال ارسال <span class="count">(%s)</span>' ),
			)
		);
		register_post_status(
			'wc-zt-transit',
			array(
				'label'                     => 'در راه',
				'public'                    => false,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s: count */
				'label_count'               => _n_noop( 'در راه <span class="count">(%s)</span>', 'در راه <span class="count">(%s)</span>' ),
			)
		);
	}

	/**
	 * Add to the status list (after processing).
	 *
	 * @param array $s Statuses.
	 * @return array
	 */
	public static function statuses( $s ) {
		$out = array();
		foreach ( $s as $k => $v ) {
			$out[ $k ] = $v;
			if ( 'wc-processing' === $k ) {
				$out['wc-zt-shipped'] = 'در حال ارسال';
				$out['wc-zt-transit'] = 'در راه';
			}
		}
		return $out;
	}

	/**
	 * Treat the new statuses as paid.
	 *
	 * @param array $s Statuses.
	 * @return array
	 */
	public static function paid_statuses( $s ) {
		$s[] = 'zt-shipped';
		$s[] = 'zt-transit';
		return array_unique( $s );
	}

	/**
	 * Record when each status was reached.
	 *
	 * @param int      $id    Order id.
	 * @param string   $from  From.
	 * @param string   $to    To.
	 * @param WC_Order $order Order.
	 */
	public static function stamp( $id, $from, $to, $order ) {
		$times = $order->get_meta( '_zt_status_times' );
		$times = is_array( $times ) ? $times : array();
		if ( empty( $times[ $to ] ) ) {
			$times[ $to ] = time();
		}
		$order->update_meta_data( '_zt_status_times', $times );
		$order->save_meta_data();
	}

	/**
	 * Loyalty points once the order completes.
	 *
	 * @param int $id Order.
	 */
	public static function award_points( $id ) {
		if ( ! zt_opt( 'checkout.points_enabled', 1 ) ) {
			return;
		}
		$order = wc_get_order( $id );
		if ( ! $order || $order->get_meta( '_zt_points_awarded' ) || ! $order->get_customer_id() ) {
			return;
		}
		$pts = (int) $order->get_meta( '_zt_points' );
		if ( ! $pts ) {
			$pts = ZT_Checkout::points_for( (float) $order->get_total() );
		}
		$uid = $order->get_customer_id();
		update_user_meta( $uid, '_zt_points', (int) get_user_meta( $uid, '_zt_points', true ) + $pts );
		$order->update_meta_data( '_zt_points_awarded', $pts );
		$order->save_meta_data();
	}

	/**
	 * Stage list from settings.
	 *
	 * @return array [ [title, icon, statuses[]] ]
	 */
	public static function stages() {
		$out = array();
		foreach ( (array) zt_opt( 'tracking.stages', array() ) as $s ) {
			$out[] = array( $s['title'], $s['icon'], array_filter( array_map( 'trim', explode( ',', (string) $s['statuses'] ) ) ) );
		}
		return $out;
	}

	/**
	 * Timeline state for an order.
	 *
	 * @param WC_Order $order Order.
	 * @return array [ ['title','icon','state' => done|current|idle,'time' => ts|0], ... ]
	 */
	public static function timeline( $order ) {
		$stages  = self::stages();
		$status  = $order->get_status();
		$times   = $order->get_meta( '_zt_status_times' );
		$times   = is_array( $times ) ? $times : array();
		$current = 0;
		foreach ( $stages as $i => $s ) {
			if ( in_array( $status, $s[2], true ) ) {
				$current = $i;
			}
		}
		$out = array();
		foreach ( $stages as $i => $s ) {
			$t = 0;
			if ( 0 === $i ) {
				$t = $order->get_date_created() ? $order->get_date_created()->getTimestamp() : 0;
			}
			foreach ( $s[2] as $st ) {
				if ( ! empty( $times[ $st ] ) ) {
					$t = $times[ $st ];
				}
			}
			$state = $i < $current ? 'done' : ( $i === $current ? 'current' : 'idle' );
			if ( 'completed' === $status && $i === $current ) {
				$state = 'done';
			}
			$out[] = array(
				'title' => $s[0],
				'icon'  => $s[1],
				'state' => $state,
				'time'  => 'idle' === $state ? 0 : $t,
			);
		}
		return $out;
	}

	/**
	 * Tracking data saved from the admin box.
	 *
	 * @param WC_Order $order Order.
	 * @return array
	 */
	public static function data( $order ) {
		$d = $order->get_meta( '_zt_track' );
		return wp_parse_args(
			is_array( $d ) ? $d : array(),
			array(
				'code'   => '',
				'eta'    => '',
				'events' => array(),
			)
		);
	}

	/**
	 * Admin box.
	 */
	public static function meta_box() {
		$screen = function_exists( 'wc_get_page_screen_id' ) ? wc_get_page_screen_id( 'shop-order' ) : 'shop_order';
		add_meta_box( 'zt-track', 'پیگیری مرسوله (زیته)', array( __CLASS__, 'render_box' ), $screen, 'normal', 'default' );
	}

	/**
	 * Render admin box.
	 *
	 * @param WP_Post|WC_Order $obj Order.
	 */
	public static function render_box( $obj ) {
		$order = $obj instanceof WC_Order ? $obj : wc_get_order( $obj->ID );
		if ( ! $order ) {
			return;
		}
		$d = self::data( $order );
		wp_nonce_field( 'zt_track', 'zt_track_nonce' );
		echo '<p><label>کد رهگیری پست: <input type="text" name="zt_track[code]" value="' . esc_attr( $d['code'] ) . '" style="width:260px"></label> ';
		echo '<label style="margin-inline-start:14px">زمان تقریبی تحویل: <input type="text" name="zt_track[eta]" value="' . esc_attr( $d['eta'] ) . '" placeholder="۲۵ اردیبهشت ۱۴۰۳ (۱ الی ۲ روز کاری)" style="width:320px"></label></p>';
		echo '<table class="widefat zt-track-events"><thead><tr><th>مرحله</th><th>زمان (مثلا ۲۴ اردیبهشت ۱۴۰۳ – ۰۸:۳۰)</th><th>مکان</th><th>انجام شده</th><th></th></tr></thead><tbody>';
		$rows = $d['events'] ? $d['events'] : array(
			array( 'title' => 'تحویل به پست', 'time' => '', 'place' => '', 'done' => 0 ),
			array( 'title' => 'خروج از مرکز مبدا', 'time' => '', 'place' => '', 'done' => 0 ),
			array( 'title' => 'در حال انتقال به مقصد', 'time' => '', 'place' => '', 'done' => 0 ),
			array( 'title' => 'تحویل به مقصد', 'time' => '', 'place' => 'در انتظار تحویل', 'done' => 0 ),
		);
		foreach ( array_values( $rows ) as $i => $r ) {
			echo '<tr><td><input type="text" name="zt_track[events][' . (int) $i . '][title]" value="' . esc_attr( $r['title'] ) . '"></td>';
			echo '<td><input type="text" name="zt_track[events][' . (int) $i . '][time]" value="' . esc_attr( $r['time'] ) . '"></td>';
			echo '<td><input type="text" name="zt_track[events][' . (int) $i . '][place]" value="' . esc_attr( $r['place'] ) . '"></td>';
			echo '<td><input type="checkbox" name="zt_track[events][' . (int) $i . '][done]" value="1"' . checked( ! empty( $r['done'] ), true, false ) . '></td>';
			echo '<td><button type="button" class="button zt-ev-del">×</button></td></tr>';
		}
		echo '</tbody></table><p><button type="button" class="button zt-ev-add">+ افزودن مرحله</button></p>';
		$pts = $order->get_meta( '_zt_points' );
		if ( $pts ) {
			echo '<p>امتیاز باشگاه این سفارش: <b>' . esc_html( zt_fa( $pts ) ) . '</b>' . ( $order->get_meta( '_zt_points_awarded' ) ? ' (اعطا شده)' : ' (پس از تکمیل سفارش اعطا می‌شود)' ) . '</p>';
		}
		if ( $order->get_meta( '_zt_receipt' ) ) {
			echo '<p>اطلاعات پرداخت کارت به کارت مشتری: <b>' . esc_html( $order->get_meta( '_zt_receipt' ) ) . '</b></p>';
		}
	}

	/**
	 * Save admin box.
	 *
	 * @param int $order_id Order id.
	 */
	public static function save_box( $order_id ) {
		if ( ! isset( $_POST['zt_track_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['zt_track_nonce'] ), 'zt_track' ) ) {
			return;
		}
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}
		$raw = isset( $_POST['zt_track'] ) ? wp_unslash( $_POST['zt_track'] ) : array(); // phpcs:ignore
		$d   = array(
			'code'   => sanitize_text_field( isset( $raw['code'] ) ? $raw['code'] : '' ),
			'eta'    => sanitize_text_field( isset( $raw['eta'] ) ? $raw['eta'] : '' ),
			'events' => array(),
		);
		foreach ( (array) ( isset( $raw['events'] ) ? $raw['events'] : array() ) as $e ) {
			if ( empty( $e['title'] ) ) {
				continue;
			}
			$d['events'][] = array(
				'title' => sanitize_text_field( $e['title'] ),
				'time'  => sanitize_text_field( isset( $e['time'] ) ? $e['time'] : '' ),
				'place' => sanitize_text_field( isset( $e['place'] ) ? $e['place'] : '' ),
				'done'  => empty( $e['done'] ) ? 0 : 1,
			);
		}
		$order->update_meta_data( '_zt_track', $d );
		$order->save_meta_data();
	}

	/**
	 * Add/remove rows in the admin box.
	 */
	public static function box_js() {
		$screen = get_current_screen();
		if ( ! $screen || false === strpos( (string) $screen->id, 'order' ) ) {
			return;
		}
		?>
		<script>
		jQuery(function($){
			$(document).on('click','.zt-ev-add',function(){var t=$('.zt-track-events tbody');var i=t.find('tr').length;
				t.append('<tr><td><input type="text" name="zt_track[events]['+i+'][title]"></td><td><input type="text" name="zt_track[events]['+i+'][time]"></td><td><input type="text" name="zt_track[events]['+i+'][place]"></td><td><input type="checkbox" name="zt_track[events]['+i+'][done]" value="1"></td><td><button type="button" class="button zt-ev-del">×</button></td></tr>');});
			$(document).on('click','.zt-ev-del',function(){$(this).closest('tr').remove();});
		});
		</script>
		<?php
	}
}
