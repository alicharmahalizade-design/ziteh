<?php
/**
 * Tracking: order items + totals.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/demo.php';

/**
 * Class ZT_W_Order_Items
 */
class ZT_W_Order_Items extends ZT_Widget_Base {

	protected $zt_group = 'tracking';
	protected $zt_icon  = 'eicon-table';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-order-items';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'جزئیات سفارش (اقلام)';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'محتوا' );
		$this->ctl( 'icon', 'icon', 'آیکون عنوان', 'package' );
		$this->ctl( 'title', 'text', 'عنوان', 'جزئیات سفارش' );
		$this->ctl( 'cols', 'text', 'عنوان ستون‌ها', 'محصول,تعداد,قیمت واحد,جمع کل' );
		$this->ctl( 'l_sub', 'text', 'جمع جزئی', 'جمع جزئی' );
		$this->ctl( 'l_disc', 'text', 'تخفیف', 'تخفیف' );
		$this->ctl( 'l_ship', 'text', 'هزینه ارسال', 'هزینه ارسال' );
		$this->ctl( 'l_total', 'text', 'مبلغ قابل پرداخت', 'مبلغ قابل پرداخت' );
		$this->end_controls_section();
		$this->style_section(
			'st',
			'جدول',
			array(
				array( 'box', 'کادر', '.zt-dbox', array( 'bg', 'border', 'radius', 'padding' ) ),
				array( 'title', 'عنوان', '.zt-dbox__title', self::fx( 'text' ) ),
				array( 'th', 'سرستون', '.zt-itable th', array( 'typo', 'color' ) ),
				array( 'td', 'خانه‌ها', '.zt-itable td', array( 'typo', 'color' ) ),
				array( 'img', 'تصویر', '.zt-iprod img', array( 'size', 'radius' ) ),
				array( 'tot', 'جمع‌ها', '.zt-itotals div', array( 'typo', 'color' ) ),
				array( 'grand', 'جمع نهایی', '.zt-itotals div.zt-grand', array( 'typo', 'color' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$v = ZT_Order_View::get();
		if ( ! $v ) {
			echo ZT_Parts::empty_marker(); // phpcs:ignore
			return;
		}
		$c   = array_pad( array_map( 'trim', explode( ',', $s['cols'] ) ), 4, '' );
		$cur = ' &nbsp;' . esc_html( zt_currency() );
		echo '<div class="zt-dbox"><h2 class="zt-dbox__title">' . zt_icon( $s['icon'] ) . ' ' . esc_html( $s['title'] ) . '</h2>'; // phpcs:ignore
		echo '<table class="zt-itable"><thead><tr><th>' . esc_html( $c[0] ) . '</th><th>' . esc_html( $c[1] ) . '</th><th>' . esc_html( $c[2] ) . '</th><th>' . esc_html( $c[3] ) . '</th></tr></thead><tbody>';
		foreach ( $v['items'] as $it ) {
			echo '<tr><td><div class="zt-iprod"><img src="' . esc_url( $it[2] ) . '" alt=""><div><b>' . esc_html( $it[0] ) . '</b>' . ( $it[1] ? '<span>' . esc_html( zt_fa( $it[1] ) ) . '</span>' : '' ) . '</div></div></td>';
			echo '<td>' . esc_html( zt_fa( $it[3] ) ) . '</td><td>' . esc_html( zt_money( $it[4], true ) ) . '</td><td>' . esc_html( zt_money( $it[5], true ) ) . '</td></tr>';
		}
		echo '</tbody></table><div class="zt-itotals">';
		echo '<div><span>' . esc_html( $s['l_sub'] ) . '</span><span>' . esc_html( zt_money( $v['subtotal'], true ) ) . $cur . '</span></div>'; // phpcs:ignore
		if ( $v['discount'] > 0 || $v['sample'] ) {
			echo '<div><span>' . esc_html( $s['l_disc'] ) . '</span><span>− ' . esc_html( zt_money( $v['discount'], true ) ) . $cur . '</span></div>'; // phpcs:ignore
		}
		echo '<div><span>' . esc_html( $s['l_ship'] ) . '</span><span>' . esc_html( zt_money( $v['shipping'], true ) ) . $cur . '</span></div>'; // phpcs:ignore
		echo '<div class="zt-grand"><span>' . esc_html( $s['l_total'] ) . '</span><span>' . esc_html( zt_money( $v['total'], true ) ) . $cur . '</span></div>'; // phpcs:ignore
		echo '</div></div>';
	}
}
