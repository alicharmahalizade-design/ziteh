<?php
/**
 * Product: category label.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Product_Category
 */
class ZT_W_Product_Category extends ZT_Widget_Base {

	protected $zt_group = 'product';
	protected $zt_icon  = 'eicon-product-categories';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-product-category';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'دسته محصول';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'دسته' );
		$this->ctl( 'icon', 'icon', 'آیکون', 'leaf' );
		$this->ctl( 'link', 'switch', 'لینک به دسته', true );
		$this->ctl( 'fallback', 'text', 'متن نمونه (بدون محصول)', 'مراقبت مو' );
		$this->end_controls_section();
		$this->style_section( 'st', 'دسته', array( array( 'cat', 'برچسب', '.zt-pdp__cat', array( 'typo', 'color', 'hover_color', 'margin', 'gap' ) ), array( 'icon', 'آیکون', '.zt-pdp__cat svg', array( 'size', 'color' ) ) ) );
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$name = $s['fallback'];
		$url  = '';
		$p    = ZT_Context::product();
		if ( $p ) {
			$terms = get_the_terms( $p->get_id(), 'product_cat' );
			if ( ! $terms || is_wp_error( $terms ) ) {
				return;
			}
			$name = $terms[0]->name;
			$url  = get_term_link( $terms[0] );
		}
		$in = zt_icon( $s['icon'] ) . ' ' . esc_html( $name );
		if ( $url && 'yes' === $s['link'] ) {
			echo '<a class="zt-pdp__cat" href="' . esc_url( $url ) . '">' . $in . '</a>'; // phpcs:ignore
		} else {
			echo '<span class="zt-pdp__cat">' . $in . '</span>'; // phpcs:ignore
		}
	}
}
