<?php
/**
 * Home: consultation CTA banner.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Consult
 */
class ZT_W_Consult extends ZT_Widget_Base {

	protected $zt_group = 'home';
	protected $zt_icon  = 'eicon-call-to-action';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-consult';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'بنر مشاوره';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'محتوا' );
		$this->ctl( 'icon', 'icon', 'آیکون عنوان', 'leaf' );
		$this->ctl( 'title', 'text', 'عنوان', 'از اینجا شروع کن' );
		$this->ctl( 'text', 'textarea', 'متن', 'نمی‌دانی چه محصولی برای پوستت مناسبه؟ سؤالت را از ما بپرس؛ در واتساپ، تلگرام یا بله پاسخ می‌دهیم.' );
		$this->ctl( 'btn', 'text', 'متن دکمه', 'شروع مشاوره پوستی' );
		$this->ctl( 'btn_icon', 'icon', 'آیکون دکمه', 'headset' );
		$this->ctl( 'action', 'select', 'عملکرد دکمه', 'advice', array( 'options' => array( 'advice' => 'باز کردن شیت مشاوره (واتساپ/تلگرام/بله)', 'link' => 'لینک' ) ) );
		$this->ctl( 'link', 'url', 'لینک', '{{contact}}', array( 'condition' => array( 'action' => 'link' ) ) );
		$this->ctl( 'img', 'media', 'تصویر', 'consult.jpg' );
		$this->ctl( 'img_alt', 'text', 'متن جایگزین تصویر', 'مشاوره پوستی زیته' );
		$this->end_controls_section();
		$this->section_wrap_controls( 'white' );
		$this->style_section(
			'st',
			'بنر',
			array(
				array( 'box', 'کادر', '.zt-consult', array( 'bg', 'radius', 'padding', 'gap', 'shadow' ) ),
				array( 'title', 'عنوان', '.zt-consult h2', self::fx( 'text' ) ),
				array( 'text', 'متن', '.zt-consult p', self::fx( 'text' ) ),
				array( 'btn', 'دکمه', '.zt-consult .zt-btn', self::fx( 'button' ) ),
				array( 'img', 'تصویر', '.zt-consult img', self::fx( 'image' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$this->section_open( $s );
		echo '<div class="zt-consult' . esc_attr( $this->reveal( $s ) ) . '"><div>';
		echo '<h2>' . zt_icon( $s['icon'], array( 'class' => 'zt-leaf', 'width' => '21', 'height' => '21', 'style' => 'color:var(--zt-green)' ) ) . ' ' . esc_html( $s['title'] ) . '</h2>'; // phpcs:ignore
		echo '<p>' . zt_kses( $s['text'] ) . '</p>'; // phpcs:ignore
		if ( 'link' === $s['action'] ) {
			echo '<a' . zt_link_attrs( $s['link'] ) . ' class="zt-btn zt-btn--primary">' . zt_icon( $s['btn_icon'] ) . ' ' . esc_html( $s['btn'] ) . '</a>'; // phpcs:ignore
		} else {
			echo '<button class="zt-btn zt-btn--primary" data-zt-sheet-open="#zt-sheet-advice">' . zt_icon( $s['btn_icon'] ) . ' ' . esc_html( $s['btn'] ) . '</button>'; // phpcs:ignore
		}
		echo '</div>' . zt_img( $s['img'], $s['img_alt'] ) . '</div>'; // phpcs:ignore
		$this->section_close( $s );
	}
}
