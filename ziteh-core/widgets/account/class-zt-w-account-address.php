<?php
/**
 * Account: address preview.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/common.php';

/**
 * Class ZT_W_Account_Address
 */
class ZT_W_Account_Address extends ZT_Widget_Base {

	protected $zt_group = 'account';
	protected $zt_icon  = 'eicon-map-pin';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-account-address';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'آدرس‌های من (پیشخوان)';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'محتوا' );
		$this->ctl( 'icon', 'icon', 'آیکون', 'pin' );
		$this->ctl( 'title', 'text', 'عنوان', 'آدرس‌های من' );
		$this->ctl( 'head_link_text', 'text', 'لینک سربرگ', 'مدیریت آدرس‌ها' );
		$this->ctl( 'link', 'url', 'لینک', '{{addresses}}' );
		$this->ctl( 'addr_icon', 'icon', 'آیکون آدرس', 'home' );
		$this->ctl( 'l_post', 'text', 'برچسب کد پستی', 'کد پستی:' );
		$this->ctl( 'l_phone', 'text', 'برچسب تلفن', 'تلفن:' );
		$this->ctl( 'empty', 'text', 'متن نبود آدرس', 'هنوز آدرسی ثبت نکرده‌اید.' );
		$this->ctl( 'empty_btn', 'text', 'دکمه افزودن آدرس', 'افزودن آدرس' );
		$this->end_controls_section();
		$this->style_section(
			'st',
			'کارت',
			array(
				array( 'box', 'کادر', '.zt-dcard', array( 'bg', 'border', 'radius', 'padding', 'margin' ) ),
				array( 'title', 'عنوان', '.zt-dcard__head h3', array( 'typo', 'color' ) ),
				array( 'ic', 'آیکون آدرس', '.zt-addr__ic', array( 'size', 'bg', 'color', 'radius' ) ),
				array( 'body', 'متن', '.zt-addr__body', array( 'typo', 'color' ) ),
				array( 'lbl', 'برچسب‌ها', '.zt-addr__body span', array( 'color' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		if ( ! ZT_Acc::dashboard() ) {
			echo ZT_Parts::empty_marker(); // phpcs:ignore
			return;
		}
		$link = zt_url( $s['link'] );
		if ( 'sample' === ZT_Acc::mode() ) {
			$addr  = 'تهران، خیابان پاسداران، خیابان گل نبی، کوچه دوم، پلاک ۱۲، واحد ۳';
			$post  = '۱۹۸۳۷۵۶۳۱۱';
			$phone = '۰۹۱۲ ۱۲۳ ۴۵۶۷';
			$edit  = '#';
		} else {
			$c     = new WC_Customer( get_current_user_id() );
			$iran  = ZT_Checkout::iran();
			$state = isset( $iran[ $c->get_billing_state() ] ) ? $iran[ $c->get_billing_state() ][0] : $c->get_billing_state();
			$addr  = implode( '، ', array_filter( array( $state, $c->get_billing_city(), $c->get_billing_address_1(), $c->get_billing_address_2() ) ) );
			$post  = zt_fa( $c->get_billing_postcode() );
			$phone = zt_fa( $c->get_billing_phone() );
			$edit  = wc_get_endpoint_url( 'edit-address', 'billing', wc_get_page_permalink( 'myaccount' ) );
		}
		echo '<section class="zt-dcard">' . ZT_Acc::head( $s, $link ); // phpcs:ignore
		if ( '' === trim( $addr ) ) {
			echo '<p class="zt-dcard__empty">' . esc_html( $s['empty'] ) . '</p><a class="zt-btn zt-btn--soft zt-btn--sm" href="' . esc_url( $edit ) . '">' . zt_icon( 'plus' ) . ' ' . esc_html( $s['empty_btn'] ) . '</a></section>'; // phpcs:ignore
			return;
		}
		echo '<div class="zt-addr"><div class="zt-addr__ic">' . zt_icon( $s['addr_icon'] ) . '</div><div class="zt-addr__body"><p>' . esc_html( $addr ) . '</p>'; // phpcs:ignore
		if ( $post ) {
			echo '<div><span>' . esc_html( $s['l_post'] ) . '</span> ' . esc_html( $post ) . '</div>';
		}
		if ( $phone ) {
			echo '<div><span>' . esc_html( $s['l_phone'] ) . '</span> ' . esc_html( $phone ) . '</div>';
		}
		echo '</div><button type="button" class="zt-more" data-zt-href="' . esc_url( $edit ) . '" aria-label="ویرایش آدرس">' . zt_icon( 'dots' ) . '</button></div></section>'; // phpcs:ignore
	}
}
