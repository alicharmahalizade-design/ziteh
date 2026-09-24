<?php
/**
 * Checkout: order summary (items, coupon, totals, points).
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Checkout_Summary
 */
class ZT_W_Checkout_Summary extends ZT_Widget_Base {

	protected $zt_group = 'checkout';
	protected $zt_icon  = 'eicon-price-list';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-checkout-summary';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'خلاصه سفارش (صورت‌حساب)';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'محتوا' );
		$this->ctl( 'icon', 'icon', 'آیکون عنوان', 'clipboard' );
		$this->ctl( 'title', 'text', 'عنوان', 'خلاصه سفارش' );
		$this->ctl( 'coupon', 'switch', 'کادر کد تخفیف', true );
		$this->ctl( 'coupon_title', 'text', 'عنوان کد تخفیف', 'کد تخفیف دارید؟' );
		$this->ctl( 'coupon_ph', 'text', 'متن راهنمای کد', 'کد تخفیف را وارد کنید' );
		$this->ctl( 'coupon_btn', 'text', 'دکمه کد', 'اعمال' );
		$this->ctl( 'l_subtotal', 'text', 'برچسب جمع جزئی', 'جمع جزئی' );
		$this->ctl( 'l_discount', 'text', 'برچسب تخفیف', 'تخفیف محصولات' );
		$this->ctl( 'l_ship', 'text', 'برچسب ارسال', 'هزینه ارسال' );
		$this->ctl( 'l_total', 'text', 'برچسب مبلغ نهایی', 'مبلغ قابل پرداخت' );
		$this->ctl( 'points', 'switch', 'نمایش امتیاز سفارش', true );
		$this->end_controls_section();
		$this->style_section(
			'st',
			'خلاصه سفارش',
			array(
				array( 'box', 'کادر', '.zt-osum', array( 'bg', 'radius', 'padding', 'shadow', 'border' ) ),
				array( 'title', 'عنوان', '.zt-osum__title', self::fx( 'text' ) ),
				array( 'img', 'تصویر کالا', '.zt-oitem img', array( 'size', 'radius' ) ),
				array( 'name', 'نام کالا', '.zt-oitem b', array( 'typo', 'color' ) ),
				array( 'price', 'قیمت کالا', '.zt-oitem .zt-price', array( 'typo', 'color' ) ),
				array( 'coupon', 'کادر کد تخفیف', '.zt-coupon div', array( 'bg', 'border', 'radius' ) ),
				array( 'cbtn', 'دکمه کد', '.zt-coupon button', array( 'bg', 'bg_hover', 'color', 'typo' ) ),
				array( 'row', 'ردیف‌ها', '.zt-osum__row', array( 'typo', 'color' ) ),
				array( 'total', 'مبلغ نهایی', '.zt-osum__total b', array( 'typo', 'color' ) ),
				array( 'pts', 'امتیاز', '.zt-osum__pts', array( 'typo', 'color' ) ),
			)
		);
	}

	/**
	 * Design sample.
	 *
	 * @param array $o Options.
	 * @return string
	 */
	private function demo( $o ) {
		$items = array(
			array( 'co-item-1.jpg', 'شامپو تقویت‌کننده و ضد ریزش موی زیته', '۵۰۰ میلی‌لیتر', '۱ عدد', '۳۸۵,۰۰۰ تومان' ),
			array( 'co-item-2.jpg', 'سرم ضد ریزش مو زیته', '۶۰ میلی‌لیتر', '۲ عدد', '۷۹۰,۰۰۰ تومان' ),
			array( 'co-item-3.jpg', 'ماسک مو تغذیه‌کننده زیته', '۲۰۰ میلی‌لیتر', '۱ عدد', '۴۷۵,۰۰۰ تومان' ),
		);
		$out = '<div class="zt-osum-body"><div class="zt-osum__items"><div>';
		foreach ( $items as $it ) {
			$out .= '<div class="zt-oitem"><img src="' . esc_url( zt_asset_img( $it[0] ) ) . '" alt=""><div><b>' . esc_html( $it[1] ) . '</b><span>' . esc_html( $it[2] ) . '</span><span>' . esc_html( $it[3] ) . '</span><span class="zt-price">' . esc_html( $it[4] ) . '</span></div><button type="button" class="zt-rm" aria-label="حذف">' . zt_icon( 'x' ) . '</button></div>';
		}
		$out .= '</div></div>';
		if ( $o['coupon'] ) {
			$out .= '<div class="zt-coupon" data-zt-coupon><p>' . esc_html( $o['coupon_title'] ) . '</p><div><input type="text" autocomplete="off" autocapitalize="characters" enterkeyhint="go" placeholder="' . esc_attr( $o['coupon_ph'] ) . '"><button type="button">' . esc_html( $o['coupon_btn'] ) . '</button></div></div>';
		}
		$out .= '<div class="zt-osum__row"><span>' . esc_html( $o['l_subtotal'] ) . '</span><b>۱,۶۵۰,۰۰۰ تومان</b></div>';
		$out .= '<div class="zt-osum__row"><span>' . esc_html( $o['l_discount'] ) . '</span><b>− ۱۶۵,۰۰۰ تومان</b></div>';
		$out .= '<div class="zt-osum__row"><span>' . esc_html( $o['l_ship'] ) . '</span><b>رایگان</b></div>';
		$out .= '<div class="zt-osum__total"><span>' . esc_html( $o['l_total'] ) . '</span><b>۱,۴۸۵,۰۰۰ <small>تومان</small></b></div>';
		if ( $o['points'] ) {
			$out .= '<p class="zt-osum__pts">' . esc_html( str_replace( '{points}', zt_fa( 148 ), zt_opt( 'checkout.points_text' ) ) ) . '</p>';
		}
		return $out . '</div>';
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$o = array(
			'coupon'       => 'yes' === $s['coupon'],
			'coupon_title' => $s['coupon_title'],
			'coupon_ph'    => $s['coupon_ph'],
			'coupon_btn'   => $s['coupon_btn'],
			'l_subtotal'   => $s['l_subtotal'],
			'l_discount'   => $s['l_discount'],
			'l_ship'       => $s['l_ship'],
			'l_total'      => $s['l_total'],
			'points'       => 'yes' === $s['points'],
		);
		if ( ZT_Checkout::summary_opts() !== $o && ! $this->is_editor() ) {
			update_option( 'zt_osum_opts', $o, false );
		}
		$woo  = zt_is_woo() && WC()->cart;
		$demo = ZT_Context::demo() || ! $woo || ( $this->is_editor() && WC()->cart->is_empty() );
		echo '<div class="zt-osum"><h2 class="zt-osum__title">' . zt_icon( $s['icon'] ) . ' ' . esc_html( $s['title'] ) . '</h2>'; // phpcs:ignore
		echo $demo ? $this->demo( $o ) : ZT_Checkout::render_summary( $o ); // phpcs:ignore
		echo '</div>';
	}
}
