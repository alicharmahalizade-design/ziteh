<?php
/**
 * Cart: order summary sidebar.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/demo.php';

/**
 * Class ZT_W_Cart_Summary
 */
class ZT_W_Cart_Summary extends ZT_Widget_Base {

	protected $zt_group = 'cart';
	protected $zt_icon  = 'eicon-price-table';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-cart-summary';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'خلاصه سفارش (سبد)';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'محتوا' );
		$this->ctl( 'icon', 'icon', 'آیکون عنوان', 'leaf' );
		$this->ctl( 'title', 'text', 'عنوان', 'خلاصه سفارش' );
		$this->ctl( 'l_sub', 'text', 'برچسب جمع', 'جمع کل محصولات' );
		$this->ctl( 'l_disc', 'text', 'برچسب تخفیف', 'تخفیف' );
		$this->ctl( 'l_ship', 'text', 'برچسب ارسال', 'هزینه ارسال' );
		$this->ctl( 'l_total', 'text', 'برچسب مبلغ نهایی', 'مبلغ قابل پرداخت' );
		$this->ctl( 'terms', 'textarea', 'متن قوانین ({link}…{/link} برای لینک)', 'با ثبت این سفارش، با {link}قوانین و شرایط{/link} استفاده از سایت زیته موافقم.' );
		$this->ctl( 'terms_link', 'url', 'لینک قوانین', '{{terms}}' );
		$this->ctl( 'btn', 'text', 'دکمه اصلی', 'ثبت و تکمیل سفارش' );
		$this->ctl( 'btn2', 'text', 'دکمه دوم', 'ادامه خرید' );
		$this->ctl( 'btn2_link', 'url', 'لینک دکمه دوم', '{{shop}}' );
		$this->repeater(
			'perks',
			'مزیت‌ها',
			array(
				array( 'icon', 'icon', 'آیکون', 'truck-fast' ),
				array( 'title', 'text', 'عنوان', '' ),
				array( 'sub', 'text', 'توضیح', '' ),
			),
			array(
				array( 'icon' => 'truck-fast', 'title' => 'ارسال سریع', 'sub' => 'ارسال ۲ تا ۳ روز کاری' ),
				array( 'icon' => 'whatsapp', 'title' => 'پشتیبانی', 'sub' => 'پاسخگوی آنلاین' ),
				array( 'icon' => 'shield', 'title' => 'ضمانت اصالت', 'sub' => 'ضمانت اصالت و کیفیت کالا' ),
			),
			'{{{ title }}}'
		);
		$this->end_controls_section();
		$this->style_section(
			'st',
			'خلاصه سفارش',
			array(
				array( 'box', 'کادر', '.zt-sum', array( 'bg', 'border', 'radius', 'padding', 'shadow' ) ),
				array( 'title', 'عنوان', '.zt-sum__title', self::fx( 'text' ) ),
				array( 'row', 'ردیف‌ها', '.zt-sum__row', array( 'typo', 'color', 'padding' ) ),
				array( 'total', 'مبلغ نهایی', '.zt-sum__total b', array( 'typo', 'color' ) ),
				array( 'terms', 'کادر قوانین', '.zt-sum__terms', array( 'bg', 'typo', 'color', 'radius', 'padding' ) ),
				array( 'btn', 'دکمه اصلی', '.zt-sum .zt-btn--primary', self::fx( 'button' ) ),
				array( 'btn2', 'دکمه دوم', '.zt-sum .zt-btn--ghost', self::fx( 'button' ) ),
				array( 'perks', 'مزیت‌ها', '.zt-sum__perks', array( 'bg', 'radius', 'padding' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$st   = ZT_Cart_Demo::on() ? ZT_Cart_Demo::state() : ( zt_is_woo() ? ZT_Woo::cart_state() : ZT_Cart_Demo::state() );
		$cur  = esc_html( zt_currency() );
		$next = zt_opt( 'cart.ship_next_step', 'در مرحله بعد' );
		$free = zt_opt( 'checkout.shipping_free_text', 'رایگان' );
		echo '<div class="zt-sum"><h2 class="zt-sum__title">' . zt_icon( $s['icon'] ) . ' ' . esc_html( $s['title'] ) . '</h2>'; // phpcs:ignore
		echo '<div class="zt-sum__row"><span>' . esc_html( $s['l_sub'] ) . '</span><span><span data-zt-sum-subtotal>' . esc_html( zt_money( $st['subtotal'] ) ) . '</span> ' . $cur . '</span></div>'; // phpcs:ignore
		$disc = $st['discount'] > 0 ? '− ' . zt_money( $st['discount'] ) . ' ' . zt_currency() : '- - - -';
		echo '<div class="zt-sum__row"><span>' . esc_html( $s['l_disc'] ) . '</span><span data-zt-sum-discount data-empty="- - - -" class="' . ( $st['discount'] > 0 ? '' : 'zt-dash' ) . '">' . esc_html( $disc ) . '</span></div>';
		echo '<div class="zt-sum__row"><span>' . esc_html( $s['l_ship'] ) . '</span><span data-zt-sum-ship data-next="' . esc_attr( $next ) . '" data-free="' . esc_attr( $free ) . '" class="' . ( $st['free_shipping'] ? '' : 'zt-dash' ) . '">' . esc_html( $st['free_shipping'] ? $free : $next ) . '</span></div><hr>';
		echo '<div class="zt-sum__total"><span>' . esc_html( $s['l_total'] ) . '</span><b><span data-zt-sum-total>' . esc_html( zt_money( $st['total'] ) ) . '</span> <small>' . $cur . '</small></b></div>'; // phpcs:ignore
		$terms = esc_html( $s['terms'] );
		$terms = str_replace( array( '{link}', '{/link}' ), array( '<a' . zt_link_attrs( $s['terms_link'] ) . '>', '</a>' ), $terms );
		echo '<div class="zt-sum__terms">' . zt_icon( 'shield' ) . '<p>' . $terms . '</p></div>'; // phpcs:ignore
		$empty = empty( $st['count'] ) ? ' style="opacity:.5;pointer-events:none"' : '';
		echo '<a href="' . esc_url( zt_page_url( 'checkout' ) ) . '" class="zt-btn zt-btn--primary zt-btn--block" data-zt-checkout-btn' . $empty . '>' . esc_html( $s['btn'] ) . ' ' . zt_icon( 'arrow-left' ) . '</a>'; // phpcs:ignore
		echo '<a' . zt_link_attrs( $s['btn2_link'] ) . ' class="zt-btn zt-btn--ghost zt-btn--block">' . zt_icon( 'bag' ) . ' ' . esc_html( $s['btn2'] ) . '</a>'; // phpcs:ignore
		echo '<div class="zt-sum__perks">';
		foreach ( (array) $s['perks'] as $p ) {
			echo '<div>' . zt_icon( $p['icon'] ) . '<div><b>' . esc_html( $p['title'] ) . '</b><span>' . esc_html( $p['sub'] ) . '</span></div></div>'; // phpcs:ignore
		}
		echo '</div></div>';
	}
}
