<?php
/**
 * Pages: 404 block.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Not_Found
 */
class ZT_W_Not_Found extends ZT_Widget_Base {

	protected $zt_group = 'pages';
	protected $zt_icon  = 'eicon-error-404';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-not-found';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'صفحه پیدا نشد (۴۰۴)';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'محتوا' );
		$this->ctl( 'img', 'media', 'تصویر', 'branch-soft.png' );
		$this->ctl( 'code', 'text', 'عدد', '۴۰۴' );
		$this->ctl( 'title', 'text', 'عنوان', 'صفحه‌ای که دنبالش بودید پیدا نشد' );
		$this->ctl( 'text', 'textarea', 'توضیح', 'ممکن است آدرس را اشتباه وارد کرده باشید یا این صفحه جابه‌جا شده باشد. از جستجو یا لینک‌های زیر استفاده کنید.' );
		$this->ctl( 'search', 'switch', 'فرم جستجو', true );
		$this->ctl( 'search_ph', 'text', 'متن جستجو', 'جستجوی محصولات…' );
		$this->ctl( 'btn1', 'text', 'دکمه اول', 'بازگشت به خانه' );
		$this->ctl( 'btn1_link', 'url', 'لینک دکمه اول', '{{home}}' );
		$this->ctl( 'btn2', 'text', 'دکمه دوم', 'مشاهده فروشگاه' );
		$this->ctl( 'btn2_link', 'url', 'لینک دکمه دوم', '{{shop}}' );
		$this->end_controls_section();
		$this->style_section(
			'st',
			'۴۰۴',
			array(
				array( 'box', 'کادر', '.zt-404', array( 'bg', 'radius', 'padding', 'margin' ) ),
				array( 'code', 'عدد', '.zt-404__code', array( 'typo', 'color' ) ),
				array( 'title', 'عنوان', '.zt-404 h1', array( 'typo', 'color' ) ),
				array( 'text', 'توضیح', '.zt-404 p', array( 'typo', 'color' ) ),
				array( 'btn', 'دکمه‌ها', '.zt-404 .zt-btn', self::fx( 'button' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		echo '<section class="zt-404">';
		if ( ! empty( $s['img']['url'] ) ) {
			echo '<img class="zt-orn" src="' . esc_url( zt_img_url( $s['img'] ) ) . '" alt="">';
		}
		echo '<div class="zt-404__code">' . esc_html( $s['code'] ) . '</div><h1>' . esc_html( $s['title'] ) . '</h1><p>' . esc_html( $s['text'] ) . '</p>';
		if ( 'yes' === $s['search'] ) {
			echo '<form class="zt-404__search" role="search" action="' . esc_url( home_url( '/' ) ) . '"><input class="zt-input" type="search" name="s" placeholder="' . esc_attr( $s['search_ph'] ) . '">' . ( zt_is_woo() ? '<input type="hidden" name="post_type" value="product">' : '' ) . '<button class="zt-btn zt-btn--primary" type="submit" aria-label="جستجو">' . zt_icon( 'search' ) . '</button></form>'; // phpcs:ignore
		}
		echo '<div class="zt-404__btns">';
		if ( $s['btn1'] ) {
			echo '<a class="zt-btn zt-btn--primary"' . zt_link_attrs( $s['btn1_link'] ) . '>' . zt_icon( 'home' ) . ' ' . esc_html( $s['btn1'] ) . '</a>'; // phpcs:ignore
		}
		if ( $s['btn2'] ) {
			echo '<a class="zt-btn zt-btn--ghost"' . zt_link_attrs( $s['btn2_link'] ) . '>' . zt_icon( 'bag' ) . ' ' . esc_html( $s['btn2'] ) . '</a>'; // phpcs:ignore
		}
		echo '</div></section>';
	}
}
