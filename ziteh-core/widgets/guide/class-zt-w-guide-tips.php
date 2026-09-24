<?php
/**
 * Guide: tips list.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Guide_Tips
 */
class ZT_W_Guide_Tips extends ZT_Widget_Base {

	protected $zt_group = 'guide';
	protected $zt_icon  = 'eicon-check-circle';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-guide-tips';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'نکته‌ها (چک‌لیست)';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'نکته‌ها' );
		$this->ctl( 'title', 'text', 'عنوان', 'چند نکته مهم' );
		$this->ctl( 'icon', 'icon', 'آیکون عنوان', 'leaf' );
		$this->ctl( 'item_icon', 'icon', 'آیکون آیتم‌ها', 'check-circle' );
		$this->repeater(
			'items',
			'نکته‌ها',
			array( array( 'text', 'textarea', 'متن', '' ) ),
			array(
				array( 'text' => 'محصولات جدید را یکی‌یکی به روتین خود اضافه کنید.' ),
				array( 'text' => 'در صورت بروز سوزش، قرمزی یا حساسیت مداوم، مصرف محصول را متوقف کرده و با پزشک یا داروساز مشورت کنید.' ),
				array( 'text' => 'از استفاده هم‌زمان چند محصول درمانی قوی بدون راهنمایی متخصص خودداری کنید.' ),
				array( 'text' => 'نتیجه مراقبت از پوست معمولاً به استفاده منظم و مداوم وابسته است و ممکن است در افراد مختلف متفاوت باشد.' ),
			),
			'text'
		);
		$this->end_controls_section();
		$this->style_section(
			'st',
			'نکته‌ها',
			array(
				array( 'h', 'عنوان', '.zt-guide__h', array( 'typo', 'color', 'margin' ) ),
				array( 'box', 'کادر', '.zt-guide__tips', array( 'bg', 'border', 'radius', 'padding' ) ),
				array( 'li', 'متن', '.zt-guide__tips li', array( 'typo', 'color' ) ),
				array( 'ic', 'آیکون', '.zt-guide__tips li svg', array( 'size', 'color' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		if ( '' !== $s['title'] ) {
			echo '<h2 class="zt-guide__h">' . zt_icon( $s['icon'] ) . ' ' . esc_html( $s['title'] ) . '</h2>'; // phpcs:ignore
		}
		echo '<ul class="zt-guide__tips">';
		foreach ( (array) $s['items'] as $it ) {
			echo '<li>' . zt_icon( $s['item_icon'] ) . ' ' . esc_html( $it['text'] ) . '</li>'; // phpcs:ignore
		}
		echo '</ul>';
	}
}
