<?php
/**
 * Home: desktop hero slider.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Hero
 */
class ZT_W_Hero extends ZT_Widget_Base {

	protected $zt_group = 'home';
	protected $zt_icon  = 'eicon-slider-full-screen';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-hero';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'هیرو اسلایدر (دسکتاپ)';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'اسلایدها' );
		$this->repeater(
			'slides',
			'اسلایدها',
			array(
				array( 'img', 'media', 'تصویر', 'hero.jpg' ),
				array( 'logo', 'switch', 'نمایش لوگوی بزرگ', false ),
				array( 'title', 'text', 'عنوان', '' ),
				array( 'text', 'textarea', 'متن (برای شکستن خط از <br> استفاده کنید)', '' ),
				array( 'btn', 'text', 'متن دکمه', '' ),
				array( 'icon', 'icon', 'آیکون دکمه', 'leaf' ),
				array( 'url', 'url', 'لینک دکمه', '{{shop}}' ),
			),
			array(
				array( 'img' => 'hero.jpg', 'logo' => true, 'title' => 'جوانه‌ای برای مراقبت از خودت', 'text' => 'انتخابی آگاهانه از بهترین محصولات بهداشتی و مراقبتی،<br>برای زیبایی، سلامت و آرامش تو.', 'btn' => 'همین حالا خرید کن', 'icon' => 'leaf', 'url' => '{{shop}}' ),
				array( 'img' => 'consult.jpg', 'logo' => false, 'title' => 'روتین مناسب پوستت را پیدا کن', 'text' => 'با یک روتین ساده و مداوم شروع کن؛<br>راهنمای کامل صبح و شب را بخوان.', 'btn' => 'راهنمای روتین', 'icon' => 'droplet', 'url' => '{{routine}}' ),
				array( 'img' => 'p-desc.jpg', 'logo' => false, 'title' => 'هرچه بیشتر بخری، بیشتر تخفیف بگیر', 'text' => 'تا ۵٪ تخفیف پلکانی،<br>به‌علاوه‌ی ارسال رایگان از ۵,۰۰۰,۰۰۰ تومان.', 'btn' => 'مشاهده تخفیف‌ها', 'icon' => 'tag', 'url' => '{{cart}}' ),
			),
			'{{{ title }}}'
		);
		$this->ctl( 'branch', 'media', 'شاخه تزئینی', 'hero-branch.png' );
		$this->ctl( 'logo_text', 'text', 'متن لوگوی بزرگ (خالی = نام برند)', '' );
		$this->ctl( 'autoplay', 'switch', 'پخش خودکار', true );
		$this->ctl( 'interval', 'number', 'فاصله پخش (میلی‌ثانیه)', 5200, array( 'condition' => array( 'autoplay' => 'yes' ) ) );
		$this->ctl( 'arrows', 'switch', 'فلش‌ها', true );
		$this->ctl( 'dots', 'switch', 'نقطه‌ها', true );
		$this->ctl( 'aria', 'text', 'برچسب دسترسی‌پذیری', 'بنرهای زیته' );
		$this->end_controls_section();

		$this->style_section(
			'st',
			'هیرو',
			array(
				array( 'hero', 'بخش', '.zt-hero, .zt-hero__slide', array( 'bg', 'min_height' ) ),
				array( 'glow', 'هاله پایین', '.zt-hero::after', array( 'display', 'opacity' ) ),
				array( 'media', 'تصویر', '.zt-hero__media', array( 'width' ) ),
				array( 'body', 'ستون متن', '.zt-hero__body', array( 'padding', 'align' ) ),
				array( 'logo', 'لوگوی بزرگ', '.zt-hero__logo b', array( 'typo', 'color' ) ),
				array( 'title', 'عنوان', '.zt-hero h2', self::fx( 'text' ) ),
				array( 'text', 'متن', '.zt-hero p', array_merge( self::fx( 'text' ), array( 'max_width' ) ) ),
				array( 'btn', 'دکمه', '.zt-hero .zt-btn', self::fx( 'button' ) ),
				array( 'branch', 'شاخه', '.zt-hero__branch', array( 'width', 'opacity', 'display' ) ),
				array( 'nav', 'فلش‌ها', '.zt-hero__nav', array( 'size', 'color', 'bg', 'display' ) ),
				array( 'dot', 'نقطه‌ها', '.zt-hero__dots button', array( 'bg', 'size', 'display' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$slides = (array) $s['slides'];
		$auto   = 'yes' === $s['autoplay'] ? (int) $s['interval'] : 0;
		echo '<section class="zt-hero" data-zt-mslider data-zt-autoplay="' . esc_attr( $auto ) . '" aria-label="' . esc_attr( $s['aria'] ) . '"><div class="zt-hero__track" data-zt-mtrack>';
		$logo = '' !== $s['logo_text'] ? $s['logo_text'] : zt_opt( 'general.brand_name', 'زیته' );
		foreach ( $slides as $i => $sl ) {
			echo '<article class="zt-hero__slide">';
			echo '<figure class="zt-hero__media">' . zt_img( $sl['img'], $sl['title'], 0 === $i ? array( 'loading' => 'eager', 'fetchpriority' => 'high' ) : array() ) . '</figure>'; // phpcs:ignore
			if ( ! empty( $s['branch']['url'] ) ) {
				echo '<img class="zt-hero__branch" src="' . esc_url( zt_img_url( $s['branch'] ) ) . '" alt="">';
			}
			echo '<div class="zt-container"><div class="zt-hero__wrap"><div class="zt-hero__body">';
			if ( 'yes' === $sl['logo'] ) {
				echo '<div class="zt-hero__logo"><b>' . esc_html( $logo ) . '</b>' . zt_icon( 'sprout', array( 'class' => 'zt-sprout' ) ) . '</div>'; // phpcs:ignore
			}
			echo '<h2>' . esc_html( $sl['title'] ) . '</h2>';
			if ( '' !== $sl['text'] ) {
				echo '<p>' . zt_kses( $sl['text'] ) . '</p>'; // phpcs:ignore
			}
			if ( '' !== $sl['btn'] ) {
				echo '<a' . zt_link_attrs( $sl['url'] ) . ' class="zt-btn zt-btn--primary">' . zt_icon( $sl['icon'] ) . ' ' . esc_html( $sl['btn'] ) . '</a>'; // phpcs:ignore
			}
			echo '</div></div></div></article>';
		}
		echo '</div>';
		if ( 'yes' === $s['arrows'] && count( $slides ) > 1 ) {
			echo '<button class="zt-iconbtn zt-hero__nav zt-hero__nav--prev" data-zt-mdir="prev" aria-label="قبلی">' . zt_icon( 'chev-left' ) . '</button>'; // phpcs:ignore
			echo '<button class="zt-iconbtn zt-hero__nav zt-hero__nav--next" data-zt-mdir="next" aria-label="بعدی">' . zt_icon( 'chev-right' ) . '</button>'; // phpcs:ignore
		}
		if ( 'yes' === $s['dots'] && count( $slides ) > 1 ) {
			echo '<div class="zt-hero__dots" data-zt-mdots>';
			foreach ( $slides as $i => $sl ) {
				echo '<button' . ( 0 === $i ? ' class="zt-is-active"' : '' ) . ' aria-label="' . esc_attr( 'اسلاید ' . zt_fa( $i + 1 ) ) . '"></button>';
			}
			echo '</div>';
		}
		echo '</section>';
	}
}
