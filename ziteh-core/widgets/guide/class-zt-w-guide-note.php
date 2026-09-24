<?php
/**
 * Guide: notice box.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Guide_Note
 */
class ZT_W_Guide_Note extends ZT_Widget_Base {

	protected $zt_group = 'guide';
	protected $zt_icon  = 'eicon-alert';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-guide-note';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'کادر هشدار / نکته';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'متن' );
		$this->ctl( 'icon', 'icon', 'آیکون', 'info' );
		$this->ctl( 'text', 'textarea', 'متن', "این راهنما صرفاً جنبه آموزشی دارد و جایگزین توصیه پزشک یا متخصص پوست نیست. اگر دچار\n        بیماری‌های پوستی، حساسیت یا مشکلات خاص هستید، پیش از استفاده از محصولات جدید با پزشک یا\n        داروساز مشورت کنید." );
		$this->end_controls_section();
		$this->style_section(
			'st',
			'کادر',
			array(
				array( 'box', 'کادر', '.zt-guide__note', array( 'bg', 'border', 'radius', 'padding', 'margin' ) ),
				array( 'ic', 'آیکون', '.zt-guide__note svg', array( 'size', 'color' ) ),
				array( 'p', 'متن', '.zt-guide__note p', self::fx( 'text' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		echo '<div class="zt-guide__note">' . zt_icon( $s['icon'] ) . '<p>' . esc_html( $s['text'] ) . '</p></div>'; // phpcs:ignore
	}
}
