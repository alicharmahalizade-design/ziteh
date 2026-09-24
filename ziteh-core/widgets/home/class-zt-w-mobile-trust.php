<?php
/**
 * Home: mobile trust strip (3 tiles).
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Mobile_Trust
 */
class ZT_W_Mobile_Trust extends ZT_Widget_Base {

	protected $zt_group = 'home';
	protected $zt_icon  = 'eicon-check-circle';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-mobile-trust';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'نوار اعتماد موبایل (فقط موبایل)';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'آیتم‌ها' );
		$this->repeater(
			'items',
			'آیتم‌ها',
			array(
				array( 'icon', 'icon', 'آیکون', 'truck-fast' ),
				array( 'title', 'text', 'عنوان', '' ),
				array( 'sub', 'text', 'توضیح', '' ),
			),
			array(
				array( 'icon' => 'truck-fast', 'title' => 'ارسال سریع', 'sub' => '۲ تا ۳ روز کاری' ),
				array( 'icon' => 'shield', 'title' => 'ضمانت اصالت', 'sub' => '۱۰۰٪ اورجینال' ),
				array( 'icon' => 'lock', 'title' => 'پرداخت امن', 'sub' => 'درگاه معتبر' ),
			),
			'{{{ title }}}'
		);
		$this->end_controls_section();
		$this->style_section(
			'st',
			'آیتم‌ها',
			array(
				array( 'grid', 'شبکه', '.zt-mtrust', array( 'columns', 'gap', 'padding' ) ),
				array( 'tile', 'کاشی', '.zt-mtrust div', array( 'bg', 'radius', 'padding' ) ),
				array( 'icon', 'آیکون', '.zt-mtrust svg', array( 'size', 'color' ) ),
				array( 'title', 'عنوان', '.zt-mtrust b', array( 'typo', 'color' ) ),
				array( 'sub', 'توضیح', '.zt-mtrust span', array( 'typo', 'color' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		echo '<div class="zt-mhome zt-mhome--trust"><div class="zt-mtrust">';
		foreach ( (array) $s['items'] as $it ) {
			echo '<div>' . zt_icon( $it['icon'] ) . '<b>' . esc_html( $it['title'] ) . '</b><span>' . esc_html( $it['sub'] ) . '</span></div>'; // phpcs:ignore
		}
		echo '</div></div>';
	}
}
