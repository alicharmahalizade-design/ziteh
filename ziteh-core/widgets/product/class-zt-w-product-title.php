<?php
/**
 * Product: title + English name.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Product_Title
 */
class ZT_W_Product_Title extends ZT_Widget_Base {

	protected $zt_group = 'product';
	protected $zt_icon  = 'eicon-product-title';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-product-title';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'عنوان محصول';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'عنوان' );
		$this->ctl( 'tag', 'select', 'تگ', 'h1', array( 'options' => array( 'h1' => 'H1', 'h2' => 'H2' ) ) );
		$this->ctl( 'en', 'switch', 'نمایش نام انگلیسی', true );
		$this->end_controls_section();
		$this->style_section( 'st', 'عنوان', array( array( 'title', 'عنوان', '.zt-pdp-title', self::fx( 'text' ) ), array( 'en', 'نام انگلیسی', '.zt-pdp__en', self::fx( 'text' ) ) ) );
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$p     = ZT_Context::product();
		$title = $p ? $p->get_name() : 'شامپو تقویت‌کننده و ضد ریزش موی زیته';
		$en    = $p ? get_post_meta( $p->get_id(), '_zt_en_name', true ) : 'Anti Hair Fall Shock Shampoo';
		$tag   = 'h2' === $s['tag'] ? 'h2' : 'h1';
		echo '<' . $tag . ' class="zt-pdp-title">' . esc_html( $title ) . '</' . $tag . '>'; // phpcs:ignore
		if ( 'yes' === $s['en'] && $en ) {
			echo '<div class="zt-pdp__en">' . esc_html( $en ) . '</div>';
		}
	}
}
