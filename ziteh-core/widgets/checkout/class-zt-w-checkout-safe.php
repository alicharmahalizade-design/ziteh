<?php
/**
 * Checkout: "secure purchase" box.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Checkout_Safe
 */
class ZT_W_Checkout_Safe extends ZT_Widget_Base {

	protected $zt_group = 'checkout';
	protected $zt_icon  = 'eicon-lock-user';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-checkout-safe';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'کادر خرید امن';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'محتوا' );
		$this->ctl( 'icon', 'icon', 'آیکون عنوان', 'lock' );
		$this->ctl( 'title', 'text', 'عنوان', 'خریدی امن و مطمئن' );
		$this->ctl( 'orn', 'media', 'تصویر تزئینی', 'branch-soft.png' );
		$this->repeater(
			'items',
			'موارد',
			array(
				array( 'icon', 'icon', 'آیکون', 'shield' ),
				array( 'text', 'text', 'متن', '' ),
			),
			array(
				array( 'icon' => 'shield', 'text' => 'پرداخت امن و رمزنگاری شده' ),
				array( 'icon' => 'check-circle', 'text' => 'ضمانت اصالت کالا' ),
				array( 'icon' => 'headset', 'text' => 'پشتیبانی ۲۴ ساعته' ),
			),
			'{{{ text }}}'
		);
		$this->end_controls_section();
		$this->style_section(
			'st',
			'کادر',
			array(
				array( 'box', 'کادر', '.zt-safe', array( 'bg', 'radius', 'padding', 'margin' ) ),
				array( 'title', 'عنوان', '.zt-safe__title', self::fx( 'text' ) ),
				array( 'li', 'موارد', '.zt-safe li', array( 'typo', 'color' ) ),
				array( 'ic', 'آیکون موارد', '.zt-safe li svg', array( 'size', 'color' ) ),
				array( 'orn', 'تصویر تزئینی', '.zt-safe .zt-orn', array( 'width', 'opacity', 'display' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		echo '<div class="zt-safe">';
		if ( ! empty( $s['orn']['url'] ) ) {
			echo '<img class="zt-orn" src="' . esc_url( zt_img_url( $s['orn'] ) ) . '" alt="">';
		}
		echo '<h3 class="zt-safe__title">' . zt_icon( $s['icon'] ) . ' ' . esc_html( $s['title'] ) . '</h3><ul>'; // phpcs:ignore
		foreach ( (array) $s['items'] as $it ) {
			echo '<li>' . zt_icon( $it['icon'] ) . ' ' . esc_html( $it['text'] ) . '</li>'; // phpcs:ignore
		}
		echo '</ul></div>';
	}
}
