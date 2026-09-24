<?php
/**
 * Account: content of the active WooCommerce endpoint (orders, addresses,
 * account details, wishlist, reviews, coupons, lost password …).
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/common.php';

/**
 * Class ZT_W_Account_Endpoint
 */
class ZT_W_Account_Endpoint extends ZT_Widget_Base {

	protected $zt_group = 'account';
	protected $zt_icon  = 'eicon-woocommerce';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-account-endpoint';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'محتوای بخش‌های حساب کاربری';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'تنظیمات' );
		$this->ctl( 'notice', 'notice', '', 'محتوای بخش فعال حساب کاربری (سفارش‌ها، جزئیات سفارش، آدرس‌ها، اطلاعات حساب، علاقه‌مندی‌ها، نظرات، کدهای تخفیف، بازیابی رمز) را با استایل زیته نمایش می‌دهد. در پیشخوان فقط پیام‌ها نمایش داده می‌شوند.' );
		$this->ctl( 'show_title', 'switch', 'نمایش عنوان بخش', true );
		$this->end_controls_section();
		$this->style_section(
			'st',
			'محتوا',
			array(
				array( 'box', 'کادر', '.zt-dcard', array( 'bg', 'border', 'radius', 'padding' ) ),
				array( 'title', 'عنوان', '.zt-dcard__head h3', array( 'typo', 'color' ) ),
				array( 'th', 'سرستون جدول', '.zt-wc table th', array( 'typo', 'color', 'bg' ) ),
				array( 'td', 'خانه‌های جدول', '.zt-wc table td', array( 'typo', 'color' ) ),
				array( 'input', 'فیلدها', '.zt-wc input.input-text, .zt-wc select, .zt-wc textarea', array( 'typo', 'bg', 'border_color', 'radius', 'height' ) ),
				array( 'btn', 'دکمه‌ها', '.zt-wc .button', self::fx( 'button' ) ),
			)
		);
	}

	/**
	 * Icon for an endpoint.
	 *
	 * @param string $ep Endpoint.
	 * @return string
	 */
	private function ep_icon( $ep ) {
		$map = array(
			'orders'          => 'clipboard',
			'view-order'      => 'package',
			'edit-address'    => 'pin',
			'edit-account'    => 'user',
			'downloads'       => 'upload',
			'payment-methods' => 'card',
			'lost-password'   => 'key',
		);
		return isset( $map[ $ep ] ) ? $map[ $ep ] : 'grid';
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$mode = ZT_Acc::mode();
		if ( 'sample' === $mode ) {
			if ( $this->is_editor() ) {
				echo '<div class="zt-editor-note">محتوای بخش‌های حساب کاربری (سفارش‌ها، آدرس‌ها، اطلاعات حساب و…) اینجا نمایش داده می‌شود.</div>';
			} else {
				echo ZT_Parts::empty_marker(); // phpcs:ignore
			}
			return;
		}
		$ep = ZT_Account::endpoint();
		ob_start();
		if ( 'guest' === $mode ) {
			if ( 'lost-password' === $ep && class_exists( 'WC_Shortcode_My_Account' ) ) {
				wc_print_notices();
				WC_Shortcode_My_Account::lost_password();
			}
		} elseif ( '' === $ep ) {
			wc_print_notices();
		} else {
			global $wp;
			$value = isset( $wp->query_vars[ $ep ] ) ? $wp->query_vars[ $ep ] : '';
			wc_print_notices();
			if ( has_action( 'woocommerce_account_' . $ep . '_endpoint' ) ) {
				do_action( 'woocommerce_account_' . $ep . '_endpoint', $value );
			}
		}
		$html = trim( ob_get_clean() );
		if ( '' === $html ) {
			echo ZT_Parts::empty_marker(); // phpcs:ignore
			return;
		}
		$own = in_array( $ep, array( ZT_Wishlist::EP, ZT_Account::EP_REVIEWS, ZT_Account::EP_COUPONS ), true );
		echo '<div class="zt-wc woocommerce zt-account-ep zt-account-ep--' . esc_attr( $ep ? $ep : 'dashboard' ) . '">';
		if ( $own || '' === $ep ) {
			echo $html; // phpcs:ignore
		} else {
			echo '<section class="zt-dcard">';
			if ( 'yes' === $s['show_title'] ) {
				$title = WC()->query->get_endpoint_title( $ep );
				if ( 'lost-password' === $ep ) {
					$title = 'بازیابی رمز عبور';
				}
				echo '<div class="zt-dcard__head"><h3>' . zt_icon( $this->ep_icon( $ep ) ) . ' ' . esc_html( $title ) . '</h3></div>'; // phpcs:ignore
			}
			echo $html . '</section>'; // phpcs:ignore
		}
		echo '</div>';
	}
}
