<?php
/**
 * Home: mobile category circles.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Mobile_Cats
 */
class ZT_W_Mobile_Cats extends ZT_Widget_Base {

	protected $zt_group = 'home';
	protected $zt_icon  = 'eicon-gallery-group';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-mobile-cats';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'دایره‌های دسته‌بندی (فقط موبایل)';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'دسته‌ها' );
		$this->category_source_controls(
			array(
				array( 'name' => 'مراقبت پوست', 'img' => 'cat-1.jpg', 'url' => '{{shop}}' ),
				array( 'name' => 'مراقبت مو', 'img' => 'cat-2.jpg', 'url' => '{{shop}}' ),
				array( 'name' => 'مراقبت کودک', 'img' => 'cat-3.jpg', 'url' => '{{shop}}' ),
				array( 'name' => 'بهداشت فردی', 'img' => 'cat-4.jpg', 'url' => '{{shop}}' ),
				array( 'name' => 'سلامت دندان', 'img' => 'cat-5.jpg', 'url' => '{{shop}}' ),
				array( 'name' => 'دور چشم', 'img' => 'cat-6.jpg', 'url' => '{{shop}}' ),
			)
		);
		$this->end_controls_section();
		$this->style_section(
			'st',
			'دایره‌ها',
			array(
				array( 'row', 'ردیف', '.zt-mcats', array( 'gap', 'padding' ) ),
				array( 'item', 'آیتم', '.zt-mcats a', array( 'typo', 'color', 'width' ) ),
				array( 'img', 'تصویر', '.zt-mcats a span', array( 'size', 'radius', 'shadow', 'bg' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		echo '<div class="zt-mhome zt-mhome--cats"><nav class="zt-mcats" aria-label="دسته‌بندی‌ها">';
		foreach ( $this->get_categories_items( $s ) as $c ) {
			echo '<a href="' . esc_url( $c['url'] ) . '"><span>' . ( $c['img'] ? '<img src="' . esc_url( $c['img'] ) . '" alt="">' : '' ) . '</span>' . esc_html( $c['name'] ) . '</a>';
		}
		echo '</nav></div>';
	}
}
