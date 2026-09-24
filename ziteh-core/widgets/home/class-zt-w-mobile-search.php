<?php
/**
 * Home: mobile search entry.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Mobile_Search
 */
class ZT_W_Mobile_Search extends ZT_Widget_Base {

	protected $zt_group = 'home';
	protected $zt_icon  = 'eicon-search';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-mobile-search';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'ورودی جستجو (فقط موبایل)';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'جستجو' );
		$this->ctl( 'text', 'text', 'متن', 'دنبال چه محصولی می‌گردی؟' );
		$this->ctl( 'icon', 'icon', 'آیکون جستجو', 'search' );
		$this->ctl( 'icon2', 'icon', 'آیکون فیلتر', 'sliders' );
		$this->end_controls_section();
		$this->style_section(
			'st',
			'ورودی جستجو',
			array(
				array( 'box', 'کادر', '.zt-msearch', array( 'bg', 'border', 'radius', 'height', 'margin', 'padding', 'typo', 'color' ) ),
				array( 'ic', 'آیکون', '.zt-msearch > svg', array( 'size', 'color' ) ),
				array( 'ic2', 'دکمه فیلتر', '.zt-msearch i', array( 'bg', 'color', 'size', 'radius' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		echo '<div class="zt-mhome zt-mhome--search"><button class="zt-msearch" data-zt-search-open>' . zt_icon( $s['icon'] ) . '<span>' . esc_html( $s['text'] ) . '</span><i>' . zt_icon( $s['icon2'] ) . '</i></button></div>'; // phpcs:ignore
	}
}
