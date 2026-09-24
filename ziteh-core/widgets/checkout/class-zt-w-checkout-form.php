<?php
/**
 * Checkout: the WooCommerce checkout form in the Ziteh design
 * (shipping info, shipping method, payment method, note, submit).
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Checkout_Form
 */
class ZT_W_Checkout_Form extends ZT_Widget_Base {

	protected $zt_group = 'checkout';
	protected $zt_icon  = 'eicon-checkout';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-checkout-form';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'فرم صورت‌حساب';
	}

	/** @inheritDoc */
	public function get_script_depends() {
		$sample = ZT_Context::demo() || ! zt_is_woo() || ( zt_is_editor() && ( ! WC()->cart || WC()->cart->is_empty() ) );
		return $sample ? array( 'zt-app' ) : array( 'zt-app', 'wc-checkout' );
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'بخش‌ها' );
		$this->ctl( 'notice', 'notice', '', 'فیلدها، روش‌های ارسال، درگاه‌ها و متن‌ها در «زیته ← صورت‌حساب» تنظیم می‌شوند. این فرم همان فرم واقعی ووکامرس است.' );
		$this->ctl( 'h_info', 'text', 'عنوان اطلاعات ارسال', 'اطلاعات ارسال' );
		$this->ctl( 'i_info', 'icon', 'آیکون', 'user' );
		$this->ctl( 'h_ship', 'text', 'عنوان روش ارسال', 'روش ارسال' );
		$this->ctl( 'i_ship', 'icon', 'آیکون', 'truck' );
		$this->ctl( 'h_pay', 'text', 'عنوان روش پرداخت', 'روش پرداخت' );
		$this->ctl( 'i_pay', 'icon', 'آیکون', 'card' );
		$this->ctl( 'h_note', 'text', 'عنوان یادداشت', 'یادداشت سفارش (اختیاری)' );
		$this->ctl( 'i_note', 'icon', 'آیکون', 'pen' );
		$this->ctl( 'note_ph', 'text', 'متن راهنمای یادداشت', 'اگر نکته‌ای در مورد سفارش خود دارید اینجا بنویسید...' );
		$this->ctl( 'submit', 'text', 'متن دکمه', 'ثبت سفارش و پرداخت' );
		$this->end_controls_section();
		$this->style_section(
			'st',
			'فرم',
			array(
				array( 'head', 'عناوین بخش‌ها', '.zt-co-head', array( 'typo', 'color', 'margin' ) ),
				array( 'headi', 'آیکون عناوین', '.zt-co-head svg', array( 'size', 'color' ) ),
				array( 'box', 'کادر فرم', '.zt-co-box', array( 'bg', 'border', 'radius', 'padding' ) ),
				array( 'grid', 'شبکه فیلدها', '.zt-form-grid', array( 'gap' ) ),
				array( 'label', 'برچسب فیلد', '.zt-label', array( 'typo', 'color' ) ),
				array( 'input', 'فیلدها', '.zt-input, .zt-select, .zt-textarea', array( 'typo', 'color', 'bg', 'border', 'radius', 'height' ) ),
				array( 'opts', 'شبکه گزینه‌ها', '.zt-opts', array( 'columns', 'gap' ) ),
				array( 'opt', 'کارت گزینه', '.zt-opt', array( 'bg', 'border', 'radius', 'padding' ) ),
				array( 'opts2', 'کارت انتخاب‌شده', '.zt-opt.zt-is-selected', array( 'bg', 'border_color', 'shadow' ) ),
				array( 'optb', 'عنوان گزینه', '.zt-opt b', array( 'typo', 'color' ) ),
				array( 'optp', 'قیمت گزینه', '.zt-opt__foot span', array( 'typo', 'color' ) ),
				array( 'submit', 'دکمه ثبت', '.zt-co-submit .zt-btn', self::fx( 'button' ) ),
				array( 'subp', 'متن کنار دکمه', '.zt-co-submit p', array( 'typo', 'color' ) ),
			)
		);
	}

	/**
	 * Demo (design) shipping + payment markup.
	 *
	 * @return array [ship, pay]
	 */
	private function demo_options() {
		$ship = '<div class="zt-opts zt-co-ship" data-zt-radio-group data-zt-ship-group>';
		foreach ( (array) zt_opt( 'checkout.shipping', array() ) as $o ) {
			if ( empty( $o['enabled'] ) ) {
				continue;
			}
			$is    = zt_opt( 'checkout.shipping_default' ) === $o['id'];
			$ship .= '<label class="zt-opt' . ( $is ? ' zt-is-selected' : '' ) . '" data-zt-radio><div class="zt-opt__top">' . zt_icon( $o['icon'] ) . '<div><b>' . esc_html( $o['title'] ) . '</b><small>' . esc_html( $o['desc'] ) . '</small></div></div>';
			$ship .= '<div class="zt-opt__foot"><span>' . esc_html( zt_money( $o['cost'] ) . ' ' . zt_currency() ) . '</span><input type="radio" name="zt_demo_ship" hidden' . checked( $is, true, false ) . '><span class="zt-radio"></span></div></label>';
		}
		$ship .= '</div>';
		$pay   = '<div class="zt-co-pay"><div class="zt-opts" data-zt-radio-group>';
		$list  = array(
			array( 'card2', 'کارت به کارت', 'پرداخت کارت به کارت و ارسال فیش', false ),
			array( 'card', 'پرداخت آنلاین', 'پرداخت امن از طریق درگاه بانکی', true ),
		);
		foreach ( $list as $g ) {
			$pay .= '<label class="zt-opt' . ( $g[3] ? ' zt-is-selected' : '' ) . '" data-zt-radio><div class="zt-opt__top">' . zt_icon( $g[0] ) . '<div><b>' . esc_html( $g[1] ) . '</b><small>' . esc_html( $g[2] ) . '</small></div></div><div class="zt-opt__foot"><span></span><input type="radio" name="zt_demo_pay" hidden' . checked( $g[3], true, false ) . '><span class="zt-radio"></span></div></label>';
		}
		return array( $ship, $pay . '</div></div>' );
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$woo  = zt_is_woo() && WC()->cart;
		$demo = ZT_Context::demo() || ! $woo || ( $this->is_editor() && WC()->cart->is_empty() );
		if ( $woo && ! $demo && function_exists( 'is_wc_endpoint_url' ) && ( is_wc_endpoint_url( 'order-pay' ) || is_wc_endpoint_url( 'order-received' ) ) ) {
			echo '<div class="zt-woo-endpoint">' . do_shortcode( '[woocommerce_checkout]' ) . '</div>'; // phpcs:ignore
			return;
		}
		$cities = array();
		foreach ( ZT_Checkout::iran() as $code => $p ) {
			$cities[ $code ] = $p[1];
		}
		$action = $woo ? wc_get_checkout_url() : '#';
		echo '<form name="checkout" method="post" class="checkout woocommerce-checkout zt-co-form" action="' . esc_url( $action ) . '" enctype="multipart/form-data" novalidate data-zt-cities="' . esc_attr( wp_json_encode( $cities ) ) . '">';
		if ( $woo && ! $demo ) {
			wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' );
		}
		echo '<h2 class="zt-co-head">' . zt_icon( $s['i_info'] ) . ' ' . esc_html( $s['h_info'] ) . '</h2>'; // phpcs:ignore
		echo '<div class="zt-co-box"><div class="zt-form-grid">';
		if ( $woo ) {
			echo ZT_Checkout::render_fields(); // phpcs:ignore
		}
		echo '</div></div>';
		if ( $demo ) {
			list( $ship, $pay ) = $this->demo_options();
		} else {
			WC()->cart->calculate_totals();
			$ship = ZT_Checkout::render_shipping();
			$pay  = ZT_Checkout::render_payment();
		}
		if ( $demo || WC()->cart->needs_shipping() ) {
			echo '<h2 class="zt-co-head">' . zt_icon( $s['i_ship'] ) . ' ' . esc_html( $s['h_ship'] ) . '</h2>'; // phpcs:ignore
			echo $ship; // phpcs:ignore
		}
		echo '<h2 class="zt-co-head">' . zt_icon( $s['i_pay'] ) . ' ' . esc_html( $s['h_pay'] ) . '</h2>'; // phpcs:ignore
		echo $pay; // phpcs:ignore
		if ( zt_opt( 'checkout.note_enabled', 1 ) ) {
			echo '<h2 class="zt-co-head">' . zt_icon( $s['i_note'] ) . ' ' . esc_html( $s['h_note'] ) . '</h2>'; // phpcs:ignore
			echo '<textarea class="zt-textarea" name="order_comments" id="order_comments" placeholder="' . esc_attr( $s['note_ph'] ) . '"></textarea>';
		}
		if ( $woo && ! $demo && wc_terms_and_conditions_checkbox_enabled() ) {
			echo '<label class="zt-check zt-co-terms"><input type="checkbox" name="terms" id="terms" value="1"><span class="zt-box">' . zt_icon( 'check' ) . '</span>' . wp_kses_post( wc_replace_policy_page_link_placeholders( wc_get_terms_and_conditions_checkbox_text() ) ) . '</label><input type="hidden" name="terms-field" value="1">';
		}
		echo '<div class="zt-co-submit"><p>' . esc_html( zt_opt( 'checkout.submit_note' ) ) . '</p>';
		echo '<button type="submit" class="zt-btn zt-btn--primary zt-btn--lg" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( $s['submit'] ) . '">' . esc_html( $s['submit'] ) . ' ' . zt_icon( 'arrow-left' ) . '</button></div>'; // phpcs:ignore
		echo '</form>';
	}
}
