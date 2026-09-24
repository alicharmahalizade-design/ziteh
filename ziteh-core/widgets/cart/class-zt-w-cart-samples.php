<?php
/**
 * Cart: gift sample picker.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/demo.php';

/**
 * Class ZT_W_Cart_Samples
 */
class ZT_W_Cart_Samples extends ZT_Widget_Base {

	protected $zt_group = 'cart';
	protected $zt_icon  = 'eicon-gift';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-cart-samples';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'انتخاب سمپل هدیه';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'محتوا' );
		$this->ctl( 'notice', 'notice', '', 'سمپل‌ها، حداقل مبلغ و متن راهنما در «زیته ← سبد خرید ← سمپل هدیه» تنظیم می‌شوند. سمپل انتخاب‌شده به‌صورت یک ردیف رایگان به سفارش اضافه می‌شود.' );
		$this->ctl( 'icon', 'icon', 'آیکون عنوان', 'gift' );
		$this->ctl( 'title', 'text', 'عنوان', 'انتخاب سمپل هدیه' );
		$this->ctl( 'sub', 'text', 'زیرعنوان', 'یک نمونه محصول هدیه از بین گزینه‌های زیر انتخاب کنید (اختیاری)' );
		$this->ctl( 'note', 'switch', 'نمایش کارت راهنما', true );
		$this->end_controls_section();
		$this->style_section(
			'st',
			'سمپل‌ها',
			array(
				array( 'card', 'کارت', '.zt-pcard', array( 'bg', 'border', 'radius', 'padding', 'margin' ) ),
				array( 'title', 'عنوان', '.zt-pcard__title', self::fx( 'text' ) ),
				array( 'grid', 'شبکه', '.zt-gifts', self::fx( 'grid' ) ),
				array( 'gift', 'کارت سمپل', '.zt-gift', array( 'bg', 'border', 'radius', 'padding' ) ),
				array( 'gifts', 'کارت انتخاب‌شده', '.zt-gift.zt-is-selected', array( 'border_color', 'shadow' ) ),
				array( 'img', 'تصویر', '.zt-gift img', array( 'radius', 'ratio' ) ),
				array( 'name', 'نام', '.zt-gift b', array( 'typo', 'color' ) ),
				array( 'size', 'حجم', '.zt-gift span:not(.zt-radio)', array( 'typo', 'color' ) ),
				array( 'note', 'کارت راهنما', '.zt-gift--note', array( 'bg', 'color' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$all = class_exists( 'ZT_Samples' ) ? ZT_Samples::all() : (array) zt_opt( 'cart.samples', array() );
		if ( ! $all ) {
			echo ZT_Parts::empty_marker(); // phpcs:ignore
			return;
		}
		$demo     = ZT_Cart_Demo::on();
		$unlocked = $demo || ( class_exists( 'ZT_Samples' ) && ZT_Samples::unlocked() );
		$sel      = $demo ? -1 : ( class_exists( 'ZT_Samples' ) ? ZT_Samples::selected() : -1 );
		if ( ! $demo && zt_is_woo() && WC()->cart && WC()->cart->is_empty() && ! $this->is_editor() ) {
			echo ZT_Parts::empty_marker(); // phpcs:ignore
			return;
		}
		$min    = zt_money( (float) zt_opt( 'cart.samples_min', 0 ) );
		$locked = str_replace( '{amount}', $min, zt_opt( 'cart.samples_locked' ) );
		echo '<section class="zt-pcard"><h2 class="zt-pcard__title">' . zt_icon( $s['icon'] ) . ' ' . esc_html( $s['title'] ) . '</h2><p class="zt-pcard__sub">' . esc_html( $s['sub'] ) . '</p>'; // phpcs:ignore
		echo '<div class="zt-gifts' . ( $unlocked ? '' : ' zt-is-locked' ) . '" data-zt-radio-group data-zt-samples data-locked-msg="' . esc_attr( $locked ) . '">';
		foreach ( $all as $i => $g ) {
			$is = $i === $sel;
			echo '<label class="zt-gift' . ( $is ? ' zt-is-selected' : '' ) . '" data-zt-radio data-index="' . esc_attr( $i ) . '"><img src="' . esc_url( zt_img_url( $g['image'] ) ) . '" alt=""><b>' . esc_html( $g['title'] ) . '</b><span>' . esc_html( zt_fa( $g['size'] ) ) . '</span><input type="radio" name="zt_sample" value="' . esc_attr( $i ) . '" hidden' . checked( $is, true, false ) . '><span class="zt-radio"></span></label>';
		}
		if ( 'yes' === $s['note'] ) {
			// Design preview reproduces the sample text of the design literally ("۱,۰۰۰۰").
			$note_amount = ZT_Context::demo() ? '۱,۰۰۰۰' : $min;
			echo '<div class="zt-gift zt-gift--note"><p>' . esc_html( str_replace( '{amount}', $note_amount, zt_opt( 'cart.samples_note' ) ) ) . '</p>' . zt_icon( 'leaf' ) . '</div>'; // phpcs:ignore
		}
		echo '</div></section>';
	}
}
