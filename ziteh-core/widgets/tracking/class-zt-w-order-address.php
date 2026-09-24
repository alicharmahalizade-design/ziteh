<?php
/**
 * Tracking: shipping info box.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/demo.php';

/**
 * Class ZT_W_Order_Address
 */
class ZT_W_Order_Address extends ZT_Widget_Base {

	protected $zt_group = 'tracking';
	protected $zt_icon  = 'eicon-map-pin';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-order-address';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'اطلاعات ارسال سفارش';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'محتوا' );
		$this->ctl( 'icon', 'icon', 'آیکون', 'pin' );
		$this->ctl( 'title', 'text', 'عنوان', 'اطلاعات ارسال' );
		$this->ctl( 'map', 'switch', 'دکمه نقشه', true );
		$this->ctl( 'map_text', 'text', 'متن دکمه نقشه', 'نمایش روی نقشه' );
		$this->end_controls_section();
		$this->style_section(
			'st',
			'کادر',
			array(
				array( 'box', 'کادر', '.zt-sbox', array( 'bg', 'radius', 'padding', 'margin' ) ),
				array( 'title', 'عنوان', '.zt-sbox__title', self::fx( 'text' ) ),
				array( 'name', 'نام', '.zt-sbox b.zt-name', array( 'typo', 'color' ) ),
				array( 'p', 'متن', '.zt-sbox p', array( 'typo', 'color' ) ),
				array( 'btn', 'دکمه', '.zt-sbox .zt-btn', self::fx( 'button' ) ),
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
		echo '<div class="zt-sbox"><h3 class="zt-sbox__title">' . zt_icon( $s['icon'] ) . ' ' . esc_html( $s['title'] ) . '</h3><b class="zt-name">' . esc_html( $v['name'] ) . '</b>'; // phpcs:ignore
		echo '<p>' . esc_html( zt_fa( $v['phone'] ) ) . '</p><p>' . str_replace( "\n", '<br>', esc_html( zt_fa( $v['address'] ) ) ) . '</p>';
		if ( 'yes' === $s['map'] ) {
			$url = str_replace( '{address}', rawurlencode( str_replace( "\n", ' ', $v['address'] ) ), zt_opt( 'tracking.map_url' ) );
			echo '<a href="' . esc_url( $v['sample'] ? '#' : $url ) . '" class="zt-btn zt-btn--ghost zt-btn--sm zt-btn--block" target="_blank" rel="noopener">' . zt_icon( 'map' ) . ' ' . esc_html( $s['map_text'] ) . '</a>'; // phpcs:ignore
		}
		echo '</div>';
	}
}
