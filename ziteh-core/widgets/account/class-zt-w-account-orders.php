<?php
/**
 * Account: latest orders.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/common.php';

/**
 * Class ZT_W_Account_Orders
 */
class ZT_W_Account_Orders extends ZT_Widget_Base {

	protected $zt_group = 'account';
	protected $zt_icon  = 'eicon-table';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-account-orders';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'آخرین سفارش‌ها';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'محتوا' );
		$this->ctl( 'icon', 'icon', 'آیکون', 'clipboard' );
		$this->ctl( 'title', 'text', 'عنوان', 'آخرین سفارش‌ها' );
		$this->ctl( 'head_link_text', 'text', 'لینک سربرگ', 'مشاهده همه' );
		$this->ctl( 'wide_text', 'text', 'دکمه پایین', 'مشاهده همه سفارش‌ها' );
		$this->ctl( 'link', 'url', 'لینک مشاهده همه', '{{orders}}' );
		$this->ctl( 'limit', 'number', 'تعداد', 4 );
		$this->ctl( 'row_link', 'select', 'کلیک روی سفارش', 'tracking', array( 'options' => array( 'tracking' => 'صفحه پیگیری', 'view' => 'جزئیات ووکامرس', 'none' => 'بدون لینک' ) ) );
		$this->ctl( 'empty', 'text', 'متن نبود سفارش', 'هنوز سفارشی ثبت نکرده‌اید.' );
		$this->end_controls_section();
		$this->style_section(
			'st',
			'کارت',
			array(
				array( 'box', 'کادر', '.zt-dcard', array( 'bg', 'border', 'radius', 'padding', 'margin' ) ),
				array( 'title', 'عنوان', '.zt-dcard__head h3', array( 'typo', 'color' ) ),
				array( 'hl', 'لینک سربرگ', '.zt-dcard__head a', array( 'typo', 'color', 'hover_color' ) ),
				array( 'td', 'ردیف‌ها', '.zt-orders td', array( 'typo', 'color', 'border_color' ) ),
				array( 'num', 'شماره سفارش', '.zt-orders td:first-child', array( 'typo', 'color' ) ),
				array( 'pill', 'وضعیت', '.zt-orders .zt-ostatus', array( 'typo', 'color', 'bg' ) ),
				array( 'wide', 'دکمه پایین', '.zt-wide-btn', self::fx( 'button' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		if ( ! ZT_Acc::dashboard() ) {
			echo ZT_Parts::empty_marker(); // phpcs:ignore
			return;
		}
		$cur  = ' &nbsp;' . esc_html( zt_currency() );
		$link = zt_url( $s['link'] );
		$rows = array();
		if ( 'sample' === ZT_Acc::mode() ) {
			$m    = max( 1, (float) zt_opt( 'general.price_divisor', 1 ) );
			$rows = array(
				array( '15587', '۱۴۰۳/۰۲/۲۳', 1485000 * $m, 'zt-shipped', 'در حال ارسال', '' ),
				array( '15243', '۱۴۰۳/۰۲/۱۵', 795000 * $m, 'completed', 'تکمیل شده', '' ),
				array( '14981', '۱۴۰۳/۰۲/۰۵', 475000 * $m, 'completed', 'تکمیل شده', '' ),
				array( '14632', '۱۴۰۳/۰۱/۲۵', 1290000 * $m, 'completed', 'تکمیل شده', '' ),
			);
		} else {
			$orders = wc_get_orders(
				array(
					'customer_id' => get_current_user_id(),
					'limit'       => max( 1, (int) $s['limit'] ),
					'orderby'     => 'date',
					'order'       => 'DESC',
				)
			);
			foreach ( $orders as $o ) {
				$href   = 'tracking' === $s['row_link'] ? ZT_Account::track_url( $o ) : ( 'view' === $s['row_link'] ? $o->get_view_order_url() : '' );
				$rows[] = array( $o->get_order_number(), zt_jdate( 'Y/m/d', $o->get_date_created() ? $o->get_date_created()->getTimestamp() : time() ), (float) $o->get_total(), $o->get_status(), wc_get_order_status_name( $o->get_status() ), $href );
			}
		}
		echo '<section class="zt-dcard">' . ZT_Acc::head( $s, $link ); // phpcs:ignore
		if ( ! $rows ) {
			echo '<p class="zt-dcard__empty">' . esc_html( $s['empty'] ) . '</p>';
		} else {
			echo '<table class="zt-orders">';
			foreach ( $rows as $r ) {
				list( $cls, $ic ) = ZT_Account::status_pill( $r[3] );
				echo '<tr' . ( $r[5] ? ' data-zt-href="' . esc_url( $r[5] ) . '"' : '' ) . '><td>' . esc_html( zt_fa( $r[0] ) ) . '#</td><td>' . esc_html( zt_fa( $r[1] ) ) . '</td><td>' . esc_html( zt_money( $r[2], true ) ) . $cur . '</td>'; // phpcs:ignore
				echo '<td><span class="' . esc_attr( $cls ) . '">' . zt_icon( $ic ) . ' ' . esc_html( $r[4] ) . '</span></td></tr>'; // phpcs:ignore
			}
			echo '</table>';
		}
		echo ZT_Acc::wide( $s, $link ) . '</section>'; // phpcs:ignore
	}
}
