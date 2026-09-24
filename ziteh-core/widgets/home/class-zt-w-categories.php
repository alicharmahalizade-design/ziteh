<?php
/**
 * Home: popular categories grid.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Categories
 */
class ZT_W_Categories extends ZT_Widget_Base {

	protected $zt_group = 'home';
	protected $zt_icon  = 'eicon-gallery-grid';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-categories';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'دسته‌بندی‌های محبوب';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'عنوان' );
		$this->heading_controls( 'دسته‌بندی‌های محبوب', array( 'center' => true ) );
		$this->end_controls_section();
		$this->section( 'c2', 'دسته‌ها' );
		$this->category_source_controls(
			array(
				array( 'name' => 'مراقبت پوست', 'img' => 'cat-1.jpg', 'url' => '{{shop}}' ),
				array( 'name' => 'مراقبت مو', 'img' => 'cat-2.jpg', 'url' => '{{shop}}' ),
				array( 'name' => 'مراقبت کودک', 'img' => 'cat-3.jpg', 'url' => '{{shop}}' ),
				array( 'name' => 'بهداشت فردی', 'img' => 'cat-4.jpg', 'url' => '{{shop}}' ),
				array( 'name' => 'سلامت دندان', 'img' => 'cat-5.jpg', 'url' => '{{shop}}' ),
				array( 'name' => 'مراقبت دور چشم', 'img' => 'cat-6.jpg', 'url' => '{{shop}}' ),
			)
		);
		$this->ctl( 'arrows', 'switch', 'نمایش فلش‌ها', true );
		$this->ctl( 'zt_hide_mobile', 'switch', 'پنهان در موبایل (دایره‌های موبایل جایگزین می‌شوند)', true );
		$this->end_controls_section();
		$this->section_wrap_controls( 'cream' );
		$this->style_section(
			'st',
			'دسته‌ها',
			array(
				array( 'grid', 'شبکه', '.zt-cats', self::fx( 'grid' ) ),
				array( 'img', 'تصویر', '.zt-cat__img', array( 'radius', 'ratio', 'bg', 'shadow' ) ),
				array( 'name', 'عنوان', '.zt-cat__name', array( 'typo', 'color', 'hover_color', 'bg', 'radius', 'padding', 'margin', 'shadow' ) ),
				array( 'arrow', 'فلش‌ها', '.zt-arrows .zt-iconbtn', array( 'size', 'color', 'bg', 'display' ) ),
			)
		);
		$this->style_section( 'sth', 'عنوان بخش', array( array( 'title', 'عنوان', '.zt-sec-title', self::fx( 'text' ) ), array( 'head', 'کادر عنوان', '.zt-sec-head', array( 'margin' ) ) ) );
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$this->section_open( $s, '', 'yes' === $s['zt_hide_mobile'] ? ' data-zt-hide-mobile' : '' );
		$this->heading_render( $s );
		echo '<div class="zt-cats' . esc_attr( $this->reveal( $s ) ) . '" data-zt-scroller>';
		foreach ( $this->get_categories_items( $s ) as $c ) {
			echo '<a href="' . esc_url( $c['url'] ) . '" class="zt-cat"><span class="zt-cat__img">' . ( $c['img'] ? '<img src="' . esc_url( $c['img'] ) . '" alt="' . esc_attr( $c['name'] ) . '" loading="lazy">' : '' ) . '</span><span class="zt-cat__name">' . esc_html( $c['name'] ) . '</span></a>';
		}
		echo '</div>';
		if ( 'yes' === $s['arrows'] ) {
			echo '<div class="zt-arrows"><button class="zt-iconbtn" data-zt-scroll="prev" aria-label="قبلی">' . zt_icon( 'chev-right' ) . '</button><button class="zt-iconbtn" data-zt-scroll="next" aria-label="بعدی">' . zt_icon( 'chev-left' ) . '</button></div>'; // phpcs:ignore
		}
		$this->section_close( $s );
	}
}
