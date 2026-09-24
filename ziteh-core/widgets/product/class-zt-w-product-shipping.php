<?php
/**
 * Product: shipping methods accordion.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Product_Shipping
 */
class ZT_W_Product_Shipping extends ZT_Widget_Base {

	protected $zt_group = 'product';
	protected $zt_icon  = 'eicon-accordion';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-product-shipping';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'روش‌های ارسال (آکاردئون)';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'محتوا' );
		$this->ctl( 'icon', 'icon', 'آیکون', 'truck' );
		$this->ctl( 'title', 'text', 'عنوان', 'روش‌های ارسال' );
		$this->ctl( 'sub', 'text', 'زیرعنوان', 'پیک لاهیجان، پست پیشتاز و پس‌کرایه' );
		$this->ctl( 'open', 'switch', 'به‌صورت باز نمایش داده شود', false );
		$this->ctl( 'src', 'select', 'فهرست', 'settings', array( 'options' => array( 'settings' => 'از روش‌های ارسال (تنظیمات زیته)', 'manual' => 'دستی' ) ) );
		$this->repeater(
			'items',
			'آیتم‌ها (دستی)',
			array(
				array( 'title', 'text', 'عنوان', '' ),
				array( 'text', 'textarea', 'توضیح', '' ),
			),
			array(
				array( 'title' => 'ارسال با پیک در لاهیجان', 'text' => 'تحویل در روزهای کاری، ۹ صبح تا ۸ غروب' ),
				array( 'title' => 'ارسال با پست پیشتاز', 'text' => 'ارسال از زیته ۲ تا ۳ روز کاری' ),
				array( 'title' => 'ارسال با پست پیشتاز به صورت پس‌کرایه', 'text' => 'هزینه پرداختی شما، فقط برای بسته‌بندی می‌باشد. پرداخت هزینه پست، درب منزل شماست. ارسال از زیته ۲ تا ۳ روز کاری' ),
			),
			'{{{ title }}}',
			array( 'condition' => array( 'src' => 'manual' ) )
		);
		$this->end_controls_section();
		$this->style_section(
			'st',
			'آکاردئون',
			array(
				array( 'box', 'کادر', '.zt-ship', array( 'bg', 'border', 'radius', 'margin' ) ),
				array( 'head', 'سرتیتر', '.zt-ship__head', array( 'padding', 'gap' ) ),
				array( 'icon', 'آیکون', '.zt-ship__head > svg:first-child', array( 'size', 'color' ) ),
				array( 'title', 'عنوان', '.zt-ship__head b', array( 'typo', 'color' ) ),
				array( 'sub', 'زیرعنوان', '.zt-ship__head span span', array( 'typo', 'color' ) ),
				array( 'body', 'محتوا', '.zt-ship__body', array( 'typo', 'color', 'padding' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$items = array();
		if ( 'settings' === $s['src'] ) {
			foreach ( (array) zt_opt( 'checkout.shipping', array() ) as $o ) {
				if ( ! empty( $o['enabled'] ) ) {
					$items[] = array( $o['title'], $o['desc'] );
				}
			}
		} else {
			foreach ( (array) $s['items'] as $r ) {
				$items[] = array( $r['title'], $r['text'] );
			}
		}
		echo '<div class="zt-ship' . ( 'yes' === $s['open'] ? ' zt-is-open' : '' ) . '" data-zt-accordion><button class="zt-ship__head" data-zt-accordion-head>' . zt_icon( $s['icon'] ); // phpcs:ignore
		echo '<span><b>' . esc_html( $s['title'] ) . '</b><span>' . esc_html( $s['sub'] ) . '</span></span>' . zt_icon( 'chev-down', array( 'class' => 'zt-chev' ) ) . '</button>'; // phpcs:ignore
		echo '<div class="zt-ship__body"><ul class="zt-shiplist">';
		foreach ( $items as $it ) {
			echo '<li><b>' . esc_html( $it[0] ) . '</b><span>' . esc_html( $it[1] ) . '</span></li>';
		}
		echo '</ul></div></div>';
	}
}
