<?php
/**
 * Tracking: order support box.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/demo.php';

/**
 * Class ZT_W_Order_Support
 */
class ZT_W_Order_Support extends ZT_Widget_Base {

	protected $zt_group = 'tracking';
	protected $zt_icon  = 'eicon-headphones';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-order-support';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'پشتیبانی سفارش';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'محتوا' );
		$this->ctl( 'icon', 'icon', 'آیکون', 'headset' );
		$this->ctl( 'title', 'text', 'عنوان (خالی = تنظیمات)', '' );
		$this->ctl( 'text', 'textarea', 'متن (خالی = تنظیمات)', '' );
		$this->ctl( 'btn', 'text', 'دکمه (خالی = تنظیمات)', '' );
		$this->ctl( 'action', 'select', 'عملکرد دکمه', 'link', array( 'options' => array( 'link' => 'لینک (تنظیمات)', 'advice' => 'باز کردن شیت واتساپ/تلگرام/بله' ) ) );
		$this->ctl( 'always', 'switch', 'نمایش حتی بدون سفارش', false );
		$this->end_controls_section();
		$this->style_section(
			'st',
			'کادر',
			array(
				array( 'box', 'کادر', '.zt-sbox', array( 'bg', 'radius', 'padding', 'margin' ) ),
				array( 'title', 'عنوان', '.zt-sbox__title', self::fx( 'text' ) ),
				array( 'p', 'متن', '.zt-sbox p', array( 'typo', 'color' ) ),
				array( 'btn', 'دکمه', '.zt-sbox .zt-btn', self::fx( 'button' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		if ( ! ZT_Order_View::get() && 'yes' !== $s['always'] ) {
			echo ZT_Parts::empty_marker(); // phpcs:ignore
			return;
		}
		$title = '' !== $s['title'] ? $s['title'] : zt_opt( 'tracking.support_title' );
		$text  = '' !== $s['text'] ? $s['text'] : zt_opt( 'tracking.support_text' );
		$btn   = '' !== $s['btn'] ? $s['btn'] : zt_opt( 'tracking.support_button' );
		echo '<div class="zt-sbox"><h3 class="zt-sbox__title">' . zt_icon( $s['icon'] ) . ' ' . esc_html( $title ) . '</h3><p>' . str_replace( "\n", '<br>', esc_html( $text ) ) . '</p>'; // phpcs:ignore
		if ( 'advice' === $s['action'] ) {
			echo '<button class="zt-btn zt-btn--primary zt-btn--block" data-zt-sheet-open="#zt-sheet-advice">' . esc_html( $btn ) . '</button>';
		} else {
			echo '<a href="' . esc_url( zt_url( zt_opt( 'tracking.support_url' ) ) ) . '" class="zt-btn zt-btn--primary zt-btn--block">' . esc_html( $btn ) . '</a>';
		}
		echo '</div>';
	}
}
