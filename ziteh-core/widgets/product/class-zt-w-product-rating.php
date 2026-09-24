<?php
/**
 * Product: rating line.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Product_Rating
 */
class ZT_W_Product_Rating extends ZT_Widget_Base {

	protected $zt_group = 'product';
	protected $zt_icon  = 'eicon-product-rating';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-product-rating';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'امتیاز محصول';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'امتیاز' );
		$this->ctl( 'score_tpl', 'text', 'قالب امتیاز', '{avg} از ۵' );
		$this->ctl( 'count_tpl', 'text', 'قالب تعداد', '({count} نظرات)' );
		$this->ctl( 'hide_empty', 'switch', 'پنهان وقتی نظری نیست', false );
		$this->end_controls_section();
		$this->style_section(
			'st',
			'امتیاز',
			array(
				array( 'box', 'کادر', '.zt-pdp__rate', array( 'typo', 'color', 'margin', 'gap', 'bg', 'radius', 'padding' ) ),
				array( 'stars', 'ستاره‌ها', '.zt-pdp__rate .zt-stars', array( 'color' ) ),
				array( 'score', 'امتیاز', '.zt-pdp__rate b', array( 'typo', 'color' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$p   = ZT_Context::product();
		$avg = $p ? (float) $p->get_average_rating() : 4.8;
		$cnt = $p ? (int) $p->get_review_count() : 25;
		if ( ! $cnt && 'yes' === $s['hide_empty'] ) {
			return;
		}
		$avg_s = rtrim( rtrim( number_format( $avg, 1, '.', '' ), '0' ), '.' );
		echo '<a class="zt-pdp__rate" href="#zt-reviews">' . ZT_Parts::stars( $avg ) . '<b>' . esc_html( zt_fa( str_replace( '{avg}', $avg_s, $s['score_tpl'] ) ) ) . '</b><span>' . esc_html( zt_fa( str_replace( '{count}', $cnt, $s['count_tpl'] ) ) ) . '</span></a>'; // phpcs:ignore
	}
}
