<?php
/**
 * Account: side menu.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/common.php';

/**
 * Class ZT_W_Account_Menu
 */
class ZT_W_Account_Menu extends ZT_Widget_Base {

	protected $zt_group = 'account';
	protected $zt_icon  = 'eicon-nav-menu';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-account-menu';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'منوی پنل کاربری';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'آیتم‌ها' );
		$this->ctl( 'notice', 'notice', '', 'لینک‌ها: {{account}} پیشخوان، {{orders}} سفارش‌ها، {{wishlist}} علاقه‌مندی‌ها، {{addresses}} آدرس‌ها، {{edit-account}} اطلاعات حساب، {{reviews}} نظرات من، {{coupons}} کدهای تخفیف، {{tracking}}، {{contact}}، {{logout}} خروج. «کلید بخش» برای حالت فعال آیتم است (dashboard، orders، zt-wishlist، edit-address، edit-account، zt-reviews، zt-coupons).' );
		$this->repeater(
			'items',
			'آیتم‌ها',
			array(
				array( 'icon', 'icon', 'آیکون', 'grid' ),
				array( 'text', 'text', 'عنوان', 'آیتم' ),
				array( 'link', 'url', 'لینک', '{{account}}' ),
				array( 'ep', 'text', 'کلید بخش (برای حالت فعال)', '' ),
				array( 'danger', 'switch', 'نمایش قرمز (خروج)', false ),
			),
			array(
				array( 'icon' => 'grid', 'text' => 'پیشخوان', 'link' => '{{account}}', 'ep' => 'dashboard' ),
				array( 'icon' => 'clipboard', 'text' => 'سفارش‌های من', 'link' => '{{orders}}', 'ep' => 'orders,view-order' ),
				array( 'icon' => 'truck-fast', 'text' => 'پیگیری سفارش', 'link' => '{{tracking}}', 'ep' => '' ),
				array( 'icon' => 'heart', 'text' => 'علاقه‌مندی‌ها', 'link' => '{{wishlist}}', 'ep' => 'zt-wishlist' ),
				array( 'icon' => 'pin', 'text' => 'آدرس‌ها', 'link' => '{{addresses}}', 'ep' => 'edit-address' ),
				array( 'icon' => 'user', 'text' => 'اطلاعات حساب', 'link' => '{{edit-account}}', 'ep' => 'edit-account' ),
				array( 'icon' => 'headset', 'text' => 'تیکت‌های من', 'link' => '{{contact}}', 'ep' => '' ),
				array( 'icon' => 'star-o', 'text' => 'نظرات من', 'link' => '{{reviews}}', 'ep' => 'zt-reviews' ),
				array( 'icon' => 'settings', 'text' => 'تنظیمات', 'link' => '{{edit-account}}', 'ep' => '' ),
				array( 'icon' => 'logout', 'text' => 'خروج از حساب', 'link' => '{{logout}}', 'ep' => '', 'danger' => 'yes' ),
			),
			'text'
		);
		$this->ctl( 'guest', 'switch', 'نمایش برای کاربر مهمان', false );
		$this->end_controls_section();
		$this->style_section(
			'st',
			'منو',
			array(
				array( 'box', 'کادر', '.zt-sidemenu', array( 'bg', 'border', 'radius', 'padding', 'shadow' ) ),
				array( 'item', 'آیتم', '.zt-sidemenu a', array( 'typo', 'color', 'hover_color', 'bg_hover', 'height', 'radius' ) ),
				array( 'ic', 'آیکون', '.zt-sidemenu a svg', array( 'size', 'color' ) ),
				array( 'active', 'آیتم فعال', '.zt-sidemenu a.zt-is-active', array( 'color', 'bg', 'shadow' ) ),
				array( 'danger', 'آیتم خروج', '.zt-sidemenu a.zt-danger', array( 'color' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$mode = ZT_Acc::mode();
		if ( 'guest' === $mode && 'yes' !== $s['guest'] ) {
			echo ZT_Parts::empty_marker(); // phpcs:ignore
			return;
		}
		$ep = ZT_Acc::endpoint();
		$ep = '' === $ep ? 'dashboard' : $ep;
		echo '<aside class="zt-sidemenu">';
		foreach ( (array) $s['items'] as $i => $it ) {
			$keys   = array_filter( array_map( 'trim', explode( ',', (string) $it['ep'] ) ) );
			$active = 'sample' === $mode ? 0 === $i : in_array( $ep, $keys, true );
			$cls    = trim( ( $active ? 'zt-is-active' : '' ) . ( 'yes' === $it['danger'] ? ' zt-danger' : '' ) );
			echo '<a' . zt_link_attrs( $it['link'] ) . ( $cls ? ' class="' . esc_attr( $cls ) . '"' : '' ) . '>' . zt_icon( $it['icon'] ) . '<span>' . esc_html( $it['text'] ) . '</span></a>'; // phpcs:ignore
		}
		echo '</aside>';
	}
}
