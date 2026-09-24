<?php
/**
 * Tracking: shipment events + ETA.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/demo.php';

/**
 * Class ZT_W_Order_Shipment
 */
class ZT_W_Order_Shipment extends ZT_Widget_Base {

	protected $zt_group = 'tracking';
	protected $zt_icon  = 'eicon-shipping';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-order-shipment';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'جزئیات ارسال مرسوله';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'محتوا' );
		$this->ctl( 'notice', 'notice', '', 'مراحل ارسال، کد رهگیری و زمان تحویل در صفحه ویرایش سفارش (کادر «پیگیری مرسوله زیته») ثبت می‌شوند.' );
		$this->ctl( 'icon', 'icon', 'آیکون', 'truck-fast' );
		$this->ctl( 'title', 'text', 'عنوان', 'جزئیات ارسال' );
		$this->ctl( 'eta_tpl', 'text', 'متن زمان تحویل', 'زمان تقریبی تحویل: {eta}' );
		$this->ctl( 'code_tpl', 'text', 'متن کد رهگیری', 'کد رهگیری پستی: {code}' );
		$this->ctl( 'orn', 'media', 'تصویر تزئینی', 'branch-soft.png' );
		$this->end_controls_section();
		$this->style_section(
			'st',
			'جزئیات ارسال',
			array(
				array( 'box', 'کادر', '.zt-ship-detail', array( 'bg', 'border', 'radius', 'padding', 'margin' ) ),
				array( 'title', 'عنوان', '.zt-dbox__title', self::fx( 'text' ) ),
				array( 'dot', 'دایره مرحله', '.zt-sd i', array( 'size', 'bg', 'border_color' ) ),
				array( 'b', 'عنوان مرحله', '.zt-sd b', array( 'typo', 'color' ) ),
				array( 'span', 'زمان و مکان', '.zt-sd span', array( 'typo', 'color' ) ),
				array( 'eta', 'کادر زمان تحویل', '.zt-eta', array( 'bg', 'radius', 'padding' ) ),
				array( 'etab', 'متن زمان تحویل', '.zt-eta b', array( 'typo', 'color' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$v = ZT_Order_View::get();
		if ( ! $v || ( ! $v['events'] && ! $v['eta'] && ! $v['code'] ) ) {
			echo ZT_Parts::empty_marker(); // phpcs:ignore
			return;
		}
		$ev   = array_values( $v['events'] );
		$n    = count( $ev );
		$done = count( array_filter( $ev, function ( $e ) {
			return ! empty( $e['done'] );
		} ) );
		$style = ( $v['sample'] || ! $n ) ? '' : ' style="--zt-sdw:' . esc_attr( round( min( $done, $n - 0.5 ) / $n * 100, 3 ) ) . '%"';
		echo '<section class="zt-ship-detail"><h2 class="zt-dbox__title">' . zt_icon( $s['icon'] ) . ' ' . esc_html( $s['title'] ) . '</h2>'; // phpcs:ignore
		if ( $v['code'] ) {
			echo '<p class="zt-sd-code">' . esc_html( zt_fa( str_replace( '{code}', $v['code'], $s['code_tpl'] ) ) ) . '</p>';
		}
		if ( $ev ) {
			echo '<div class="zt-sd-line"' . $style . '>'; // phpcs:ignore
			foreach ( $ev as $e ) {
				$is = ! empty( $e['done'] );
				echo '<div class="zt-sd ' . ( $is ? 'zt-is-done' : 'zt-is-idle' ) . '"><i>' . ( $is ? zt_icon( 'check' ) : '' ) . '</i><b>' . esc_html( $e['title'] ) . '</b><span>' . esc_html( '' !== $e['time'] ? zt_fa( $e['time'] ) : '—' ) . '</span><span>' . esc_html( $e['place'] ) . '</span></div>'; // phpcs:ignore
			}
			echo '</div>';
		}
		if ( $v['eta'] ) {
			echo '<div class="zt-eta">';
			if ( ! empty( $s['orn']['url'] ) ) {
				echo '<img class="zt-orn" src="' . esc_url( zt_img_url( $s['orn'] ) ) . '" alt="">';
			}
			echo zt_icon( 'calendar', array( 'class' => 'zt-cal' ) ) . '<div><b>' . esc_html( zt_fa( str_replace( '{eta}', $v['eta'], $s['eta_tpl'] ) ) ) . '</b><span>' . esc_html( zt_opt( 'tracking.eta_note' ) ) . '</span></div></div>'; // phpcs:ignore
		}
		echo '</section>';
	}
}
