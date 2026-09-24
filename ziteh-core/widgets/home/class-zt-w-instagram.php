<?php
/**
 * Home: Instagram grid.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Instagram
 */
class ZT_W_Instagram extends ZT_Widget_Base {

	protected $zt_group = 'home';
	protected $zt_icon  = 'eicon-instagram-gallery';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-instagram';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'اینستاگرام';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'عنوان' );
		$this->heading_controls(
			'ما را در اینستاگرام دنبال کنید',
			array(
				'center' => true,
				'sub'    => 'ziteh@',
				'icon'   => 'instagram',
				'mb'     => '26px',
			)
		);
		$this->end_controls_section();
		$this->section( 'c2', 'تصاویر' );
		$this->ctl( 'profile', 'url', 'لینک پیج', 'https://instagram.com/ziteh' );
		$this->repeater(
			'posts',
			'پست‌ها',
			array(
				array( 'img', 'media', 'تصویر (خالی = کاشی با آیکون)', '' ),
				array( 'url', 'url', 'لینک پست', '#' ),
			),
			array_fill( 0, 6, array( 'img' => '', 'url' => '#' ) ),
			'پست'
		);
		$this->ctl( 'btn', 'text', 'متن دکمه', 'مشاهده بیشتر در اینستاگرام' );
		$this->end_controls_section();
		$this->section_wrap_controls( 'white' );
		$this->style_section(
			'st',
			'اینستاگرام',
			array(
				array( 'grid', 'شبکه', '.zt-insta-grid', self::fx( 'grid' ) ),
				array( 'tile', 'کاشی', '.zt-insta-grid a', array( 'bg', 'bg_hover', 'color', 'hover_color', 'radius', 'ratio' ) ),
				array( 'icon', 'آیکون', '.zt-insta-grid svg', array( 'size' ) ),
				array( 'btn', 'دکمه', '.zt-insta-foot .zt-btn', self::fx( 'button' ) ),
				array( 'title', 'عنوان بخش', '.zt-sec-title', self::fx( 'text' ) ),
				array( 'sub', 'آیدی', '.zt-sec-sub', self::fx( 'text' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$this->section_open( $s );
		$this->heading_render( $s );
		echo '<div class="zt-insta-grid' . esc_attr( $this->reveal( $s ) ) . '">';
		foreach ( (array) $s['posts'] as $p ) {
			$img = zt_img_url( $p['img'] );
			echo '<a' . zt_link_attrs( $p['url'] ) . ( $img ? ' style="overflow:hidden"' : '' ) . '>' . ( $img ? '<img src="' . esc_url( $img ) . '" alt="" loading="lazy" style="width:100%;height:100%;object-fit:cover">' : zt_icon( 'instagram' ) ) . '</a>'; // phpcs:ignore
		}
		echo '</div>';
		if ( '' !== $s['btn'] ) {
			echo '<div class="zt-insta-foot"><a' . zt_link_attrs( $s['profile'] ) . ' class="zt-btn zt-btn--ghost zt-btn--sm">' . zt_icon( 'instagram' ) . ' ' . esc_html( $s['btn'] ) . '</a></div>'; // phpcs:ignore
		}
		$this->section_close( $s );
	}
}
