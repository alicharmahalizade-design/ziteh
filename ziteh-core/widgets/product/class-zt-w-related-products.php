<?php
/**
 * Product: related products carousel (small cards).
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Related_Products
 */
class ZT_W_Related_Products extends ZT_Widget_Base {

	protected $zt_group = 'product';
	protected $zt_icon  = 'eicon-product-related';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-related-products';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'محصولات مرتبط (کارت کوچک)';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'محصولات' );
		$this->product_source_controls(
			array(
				array( 'title' => 'روغن تقویت ریشه مو زیته', 'cat' => '', 'img' => 'rel-1.jpg', 'price' => 395000, 'regular' => 0, 'exp' => '۱۴۰۸/۰۳', 'url' => '{{shop}}', 'pid' => 0 ),
				array( 'title' => 'شامپو روزانه ملایم زیته', 'cat' => '', 'img' => 'rel-2.jpg', 'price' => 285000, 'regular' => 0, 'exp' => '۱۴۰۷/۱۰', 'url' => '{{shop}}', 'pid' => 0 ),
				array( 'title' => 'لوسیون تقویت مو زیته', 'cat' => '', 'img' => 'rel-3.jpg', 'price' => 375000, 'regular' => 0, 'exp' => '۱۴۰۸/۰۱', 'url' => '{{shop}}', 'pid' => 0 ),
				array( 'title' => 'ماسک مو تقویت‌کننده زیته', 'cat' => '', 'img' => 'rel-4.jpg', 'price' => 475000, 'regular' => 0, 'exp' => '۱۴۰۷/۰۸', 'url' => '{{shop}}', 'pid' => 0 ),
				array( 'title' => 'سرم ضد ریزش مو زیته', 'cat' => '', 'img' => 'rel-5.jpg', 'price' => 395000, 'regular' => 0, 'exp' => '۱۴۰۸/۰۴', 'url' => '{{shop}}', 'pid' => 0 ),
			),
			array(
				'query' => 'related',
				'limit' => 10,
			)
		);
		$this->ctl( 'exp', 'switch', 'نمایش تاریخ انقضا', true );
		$this->ctl( 'exp_label', 'text', 'برچسب انقضا', 'انقضا' );
		$this->ctl( 'arrows', 'switch', 'فلش‌ها', true );
		$this->end_controls_section();
		$this->style_section(
			'st',
			'کارت‌ها',
			array(
				array( 'track', 'شبکه', '.zt-rel-track', self::fx( 'grid' ) ),
				array( 'card', 'کارت', '.zt-relcard', array( 'bg', 'radius', 'shadow' ) ),
				array( 'img', 'تصویر', '.zt-relcard__img', array( 'ratio', 'bg' ) ),
				array( 'body', 'بدنه', '.zt-relcard__body', array( 'padding' ) ),
				array( 'title', 'عنوان', '.zt-relcard h4', array( 'typo', 'color', 'min_height' ) ),
				array( 'price', 'قیمت', '.zt-relcard__foot b', array( 'typo', 'color' ) ),
				array( 'btn', 'دکمه سبد', '.zt-relcard__foot button', array( 'color', 'size' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$items = $this->get_products( $s );
		if ( ! $items ) {
			echo ZT_Parts::empty_marker(); // phpcs:ignore
			return;
		}
		echo '<div style="position:relative" data-zt-carousel><div class="zt-rel-track" data-zt-track>';
		foreach ( $items as $it ) {
			echo ZT_Parts::rel_card( $it, array( 'exp' => 'yes' === $s['exp'], 'exp_label' => $s['exp_label'] ) ); // phpcs:ignore
		}
		echo '</div>';
		if ( 'yes' === $s['arrows'] ) {
			$this->carousel_arrows( '50%' );
		}
		echo '</div>';
	}
}
