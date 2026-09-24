<?php
/**
 * Account: quick access links.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/common.php';

/**
 * Class ZT_W_Account_Quick
 */
class ZT_W_Account_Quick extends ZT_Widget_Base {

	protected $zt_group = 'account';
	protected $zt_icon  = 'eicon-apps';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-account-quick';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'دسترسی سریع';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'محتوا' );
		$this->ctl( 'icon', 'icon', 'آیکون', 'zap' );
		$this->ctl( 'title', 'text', 'عنوان', 'دسترسی سریع' );
		$this->ctl( 'always', 'switch', 'نمایش در همه بخش‌های پنل', false );
		$this->repeater(
			'items',
			'لینک‌ها',
			array(
				array( 'icon', 'icon', 'آیکون', 'user' ),
				array( 'text', 'text', 'عنوان', 'لینک' ),
				array( 'link', 'url', 'لینک', '{{account}}' ),
			),
			array(
				array( 'icon' => 'user', 'text' => 'ویرایش پروفایل', 'link' => '{{edit-account}}' ),
				array( 'icon' => 'key', 'text' => 'تغییر رمز عبور', 'link' => '{{edit-account}}#password_current' ),
				array( 'icon' => 'pin', 'text' => 'آدرس‌های من', 'link' => '{{addresses}}' ),
				array( 'icon' => 'tag', 'text' => 'کدهای تخفیف', 'link' => '{{coupons}}' ),
				array( 'icon' => 'headset', 'text' => 'تیکت جدید', 'link' => '{{contact}}' ),
			),
			'text'
		);
		$this->end_controls_section();
		$this->style_section(
			'st',
			'کارت',
			array(
				array( 'box', 'کادر', '.zt-dcard', array( 'bg', 'border', 'radius', 'padding', 'margin' ) ),
				array( 'title', 'عنوان', '.zt-dcard__head h3', array( 'typo', 'color' ) ),
				array( 'grid', 'چیدمان', '.zt-quick', array( 'columns', 'gap' ) ),
				array( 'item', 'آیتم', '.zt-quick a', array( 'bg', 'bg_hover', 'radius', 'padding' ) ),
				array( 'ic', 'آیکون', '.zt-quick svg', array( 'size', 'color' ) ),
				array( 'txt', 'متن', '.zt-quick span', array( 'typo', 'color' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		if ( 'guest' === ZT_Acc::mode() || ( 'yes' !== $s['always'] && ! ZT_Acc::dashboard() ) ) {
			echo ZT_Parts::empty_marker(); // phpcs:ignore
			return;
		}
		echo '<section class="zt-dcard">' . ZT_Acc::head( $s, '' ) . '<div class="zt-quick">'; // phpcs:ignore
		foreach ( (array) $s['items'] as $it ) {
			echo '<a' . zt_link_attrs( $it['link'] ) . '>' . zt_icon( $it['icon'] ) . '<span>' . esc_html( $it['text'] ) . '</span></a>'; // phpcs:ignore
		}
		echo '</div></section>';
	}
}
