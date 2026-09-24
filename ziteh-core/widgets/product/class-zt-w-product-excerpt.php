<?php
/**
 * Product: short description.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Product_Excerpt
 */
class ZT_W_Product_Excerpt extends ZT_Widget_Base {

	protected $zt_group = 'product';
	protected $zt_icon  = 'eicon-product-description';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-product-excerpt';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'توضیح کوتاه محصول';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'توضیح' );
		$this->ctl( 'fallback', 'textarea', 'متن نمونه (بدون محصول)', 'شامپویی تخصصی برای کاهش ریزش مو و تقویت ریشه و فولیکول‌مو پی‌مو. مناسب استفاده روزانه، برای خانم ها و آقایان.' );
		$this->end_controls_section();
		$this->style_section( 'st', 'توضیح', array( array( 'p', 'متن', '.zt-pdp__desc', array_merge( self::fx( 'text' ), array( 'max_width' ) ) ) ) );
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$p    = ZT_Context::product();
		$text = $p ? $p->get_short_description() : $s['fallback'];
		if ( '' === trim( wp_strip_all_tags( $text ) ) ) {
			return;
		}
		echo '<p class="zt-pdp__desc">' . wp_kses_post( trim( preg_replace( '#</?p[^>]*>#', ' ', $text ) ) ) . '</p>';
	}
}
