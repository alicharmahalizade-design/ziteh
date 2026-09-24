<?php
/**
 * Home: mobile image-first slider.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Mobile_Hero
 */
class ZT_W_Mobile_Hero extends ZT_Widget_Base {

	protected $zt_group = 'home';
	protected $zt_icon  = 'eicon-device-mobile';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-mobile-hero';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'اسلایدر موبایل (فقط موبایل)';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'اسلایدها' );
		$this->repeater(
			'slides',
			'اسلایدها',
			array(
				array( 'img', 'media', 'تصویر', 'hero.jpg' ),
				array( 'tag_icon', 'icon', 'آیکون برچسب', 'sprout' ),
				array( 'tag', 'text', 'برچسب', '' ),
				array( 'title', 'text', 'عنوان', '' ),
				array( 'text', 'textarea', 'متن', '' ),
				array( 'btn', 'text', 'متن دکمه', '' ),
				array( 'url', 'url', 'لینک', '{{shop}}' ),
			),
			array(
				array( 'img' => 'hero.jpg', 'tag_icon' => 'sprout', 'tag' => 'تازه رسیده', 'title' => 'جوانه‌ای برای مراقبت از خودت', 'text' => 'انتخابی آگاهانه از بهترین محصولات بهداشتی و مراقبتی، برای زیبایی و آرامش تو.', 'btn' => 'همین حالا خرید کن', 'url' => '{{shop}}' ),
				array( 'img' => 'consult.jpg', 'tag_icon' => 'droplet', 'tag' => 'کوییز پوست', 'title' => 'روتین مناسب پوستت را پیدا کن', 'text' => 'با چند سؤال کوتاه، بهترین محصولات و روتین متناسب با پوست تو را پیشنهاد می‌دهیم.', 'btn' => 'شروع کوییز', 'url' => '{{routine}}' ),
				array( 'img' => 'p-desc.jpg', 'tag_icon' => 'tag', 'tag' => 'تخفیف پلکانی', 'title' => 'هرچه بیشتر بخری، بیشتر تخفیف بگیر', 'text' => 'تا ۱۰٪ تخفیف پلکانی، به‌علاوه‌ی ارسال رایگان از ۲,۵۰۰,۰۰۰ تومان.', 'btn' => 'مشاهده تخفیف‌ها', 'url' => '{{cart}}' ),
			),
			'{{{ title }}}'
		);
		$this->ctl( 'btn_icon', 'icon', 'آیکون دکمه‌ها', 'arrow-left' );
		$this->ctl( 'autoplay', 'switch', 'پخش خودکار', true );
		$this->ctl( 'interval', 'number', 'فاصله پخش (میلی‌ثانیه)', 5200, array( 'condition' => array( 'autoplay' => 'yes' ) ) );
		$this->ctl( 'aria', 'text', 'برچسب دسترسی‌پذیری', 'پیشنهادهای زیته' );
		$this->end_controls_section();
		$this->style_section(
			'st',
			'اسلایدر موبایل',
			array(
				array( 'slide', 'اسلاید', '.zt-mslide', array( 'bg_group', 'radius', 'shadow' ) ),
				array( 'img', 'تصویر', '.zt-mslide__img', array( 'height' ) ),
				array( 'tag', 'برچسب', '.zt-mslide__tag', self::fx( 'badge' ) ),
				array( 'title', 'عنوان', '.zt-mslide h2', self::fx( 'text' ) ),
				array( 'text', 'متن', '.zt-mslide p', self::fx( 'text' ) ),
				array( 'btn', 'دکمه', '.zt-mslide .zt-btn', self::fx( 'button' ) ),
				array( 'dot', 'نقطه‌ها', '.zt-mhero__dots button', array( 'bg' ) ),
				array( 'dota', 'نقطه فعال', '.zt-mhero__dots button.zt-is-active', array( 'bg' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$auto = 'yes' === $s['autoplay'] ? (int) $s['interval'] : 0;
		echo '<div class="zt-mhome zt-mhome--hero"><section class="zt-mhero" data-zt-mslider data-zt-autoplay="' . esc_attr( $auto ) . '" aria-label="' . esc_attr( $s['aria'] ) . '"><div class="zt-mhero__track" data-zt-mtrack>';
		foreach ( (array) $s['slides'] as $sl ) {
			echo '<article class="zt-mslide"><div class="zt-mslide__img">' . zt_img( $sl['img'], $sl['title'] ) . '</div><div class="zt-mslide__body">'; // phpcs:ignore
			if ( '' !== $sl['tag'] ) {
				echo '<span class="zt-mslide__tag">' . zt_icon( $sl['tag_icon'] ) . ' ' . esc_html( $sl['tag'] ) . '</span>'; // phpcs:ignore
			}
			echo '<h2>' . esc_html( $sl['title'] ) . '</h2>';
			if ( '' !== $sl['text'] ) {
				echo '<p>' . zt_kses( $sl['text'] ) . '</p>'; // phpcs:ignore
			}
			if ( '' !== $sl['btn'] ) {
				echo '<a' . zt_link_attrs( $sl['url'] ) . ' class="zt-btn zt-btn--primary">' . esc_html( $sl['btn'] ) . ' ' . zt_icon( $s['btn_icon'] ) . '</a>'; // phpcs:ignore
			}
			echo '</div></article>';
		}
		echo '</div><div class="zt-mhero__dots" data-zt-mdots>';
		foreach ( array_values( (array) $s['slides'] ) as $i => $sl ) {
			echo '<button' . ( 0 === $i ? ' class="zt-is-active"' : '' ) . ' aria-label="' . esc_attr( 'اسلاید ' . zt_fa( $i + 1 ) ) . '"></button>';
		}
		echo '</div></section></div>';
	}
}
