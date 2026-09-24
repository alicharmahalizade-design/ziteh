<?php
/**
 * Global: newsletter box (green / cream).
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Newsletter
 */
class ZT_W_Newsletter extends ZT_Widget_Base {

	protected $zt_group = 'global';
	protected $zt_icon  = 'eicon-mail';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-newsletter';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'خبرنامه';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'محتوا' );
		$this->ctl( 'variant', 'select', 'سبک', 'green', array( 'options' => array( 'green' => 'سبز با شاخه تزئینی', 'cream' => 'کرم' ) ) );
		$this->ctl( 'in_section', 'switch', 'قرار گرفتن داخل بخش تمام‌عرض (مثل صفحه اصلی)', false );
		$this->ctl( 'orn', 'media', 'تصویر تزئینی', 'branch-light.png', array( 'condition' => array( 'variant' => 'green' ) ) );
		$this->ctl( 'icon', 'icon', 'آیکون عنوان', 'leaf' );
		$this->ctl( 'title', 'text', 'عنوان', 'در خبرنامه زیته عضو شوید' );
		$this->ctl( 'text', 'textarea', 'توضیح', 'جدیدترین محصولات، مقالات و تخفیف‌ها را مستقیم در ایمیل خود دریافت کنید.' );
		$this->ctl( 'placeholder', 'text', 'متن راهنمای فیلد', 'ایمیل خود را وارد کنید...' );
		$this->ctl( 'button', 'text', 'متن دکمه', 'عضویت' );
		$this->end_controls_section();
		$this->section_wrap_controls( 'white' );
		$this->style_section(
			'st',
			'خبرنامه',
			array(
				array( 'box', 'کادر', '.zt-news', array( 'bg', 'radius', 'padding', 'margin', 'shadow', 'gap' ) ),
				array( 'title', 'عنوان', '.zt-news h3', self::fx( 'text' ) ),
				array( 'ticon', 'آیکون عنوان', '.zt-news h3 svg', array( 'size', 'color' ) ),
				array( 'text', 'توضیح', '.zt-news p', self::fx( 'text' ) ),
				array( 'form', 'فرم', '.zt-news form', array( 'bg', 'radius', 'padding', 'width', 'shadow' ) ),
				array( 'input', 'فیلد ایمیل', '.zt-news form input', array( 'typo', 'color', 'height' ) ),
				array( 'btn', 'دکمه', '.zt-news form button', self::fx( 'button' ) ),
				array( 'orn', 'تصویر تزئینی', '.zt-news .zt-orn', array( 'width', 'opacity', 'display' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$green = 'green' === $s['variant'];
		if ( 'yes' === $s['in_section'] ) {
			$this->section_open( $s );
		}
		echo '<div class="zt-news ' . ( $green ? 'zt-news--green' : 'zt-news--cream' ) . ( 'yes' === $s['in_section'] ? $this->reveal( $s ) : '' ) . '">';
		if ( $green && ! empty( $s['orn']['url'] ) ) {
			echo '<img class="zt-orn" src="' . esc_url( zt_img_url( $s['orn'] ) ) . '" alt="">';
		}
		$ic = $green ? array( 'width' => '20', 'height' => '20' ) : array( 'class' => 'zt-leaf', 'width' => '20', 'height' => '20', 'style' => 'color:var(--zt-green)' );
		echo '<div><h3>' . zt_icon( $s['icon'], $ic ) . ' ' . esc_html( $s['title'] ) . '</h3><p>' . esc_html( $s['text'] ) . '</p></div>'; // phpcs:ignore
		echo '<form data-zt-newsletter novalidate><input type="email" name="email" inputmode="email" autocomplete="email" enterkeyhint="go" placeholder="' . esc_attr( $s['placeholder'] ) . '" required><button type="submit">' . esc_html( $s['button'] ) . '</button></form>';
		echo '</div>';
		if ( 'yes' === $s['in_section'] ) {
			$this->section_close( $s );
		}
	}
}
