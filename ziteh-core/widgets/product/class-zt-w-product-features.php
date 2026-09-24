<?php
/**
 * Product: feature list (icon + text).
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Product_Features
 */
class ZT_W_Product_Features extends ZT_Widget_Base {

	protected $zt_group = 'product';
	protected $zt_icon  = 'eicon-bullet-list';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-product-features';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'ویژگی‌های محصول';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'ویژگی‌ها' );
		$this->ctl( 'src', 'select', 'منبع', 'meta', array( 'options' => array( 'meta' => 'فیلد «ویژگی‌ها»ی محصول', 'manual' => 'دستی (برای همه محصولات)' ) ) );
		$this->repeater(
			'items',
			'ویژگی‌ها (دستی / نمونه)',
			array(
				array( 'icon', 'icon', 'آیکون', 'check-circle' ),
				array( 'text', 'text', 'متن', '' ),
			),
			array(
				array( 'icon' => 'ban', 'text' => 'کاهش محسوس ریزش مو از ریشه' ),
				array( 'icon' => 'droplet', 'text' => 'تقویت فولیکول‌ها و افزایش رشد مو' ),
				array( 'icon' => 'check-circle', 'text' => 'ترکیبات ملایم، مناسب پوست سر حساس' ),
				array( 'icon' => 'waves', 'text' => 'مناسب انواع مو و استفاده روزانه' ),
			),
			'{{{ text }}}'
		);
		$this->end_controls_section();
		$this->style_section(
			'st',
			'ویژگی‌ها',
			array(
				array( 'list', 'فهرست', '.zt-pdp__feats', array( 'margin', 'gap', 'bg', 'radius', 'padding' ) ),
				array( 'item', 'آیتم', '.zt-pdp__feats li', array( 'typo', 'color', 'gap' ) ),
				array( 'icon', 'آیکون', '.zt-pdp__feats svg', array( 'size', 'color' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$rows = array();
		$p    = ZT_Context::product();
		if ( 'meta' === $s['src'] && $p ) {
			foreach ( ZT_Product_Meta::pairs( get_post_meta( $p->get_id(), '_zt_features', true ) ) as $pr ) {
				$rows[] = array( $pr[0], $pr[1] );
			}
			if ( ! $rows ) {
				return;
			}
		} else {
			foreach ( (array) $s['items'] as $r ) {
				$rows[] = array( $r['icon'], $r['text'] );
			}
		}
		echo '<ul class="zt-pdp__feats">';
		foreach ( $rows as $r ) {
			echo '<li>' . zt_icon( $r[0] ) . ' ' . esc_html( $r[1] ) . '</li>'; // phpcs:ignore
		}
		echo '</ul>';
	}
}
