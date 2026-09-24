<?php
/**
 * Global: divider heading (line — title — line).
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Rule_Title
 */
class ZT_W_Rule_Title extends ZT_Widget_Base {

	protected $zt_group = 'global';
	protected $zt_icon  = 'eicon-divider';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-rule-title';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'عنوان با خط جداکننده';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'محتوا' );
		$this->ctl( 'title', 'text', 'عنوان', 'محصولات مرتبط' );
		$this->ctl( 'icon', 'icon', 'آیکون', 'leaf' );
		$this->end_controls_section();
		$this->style_section(
			'st',
			'عنوان',
			array(
				array( 'box', 'کادر', '.zt-rule-title', array( 'margin', 'gap' ) ),
				array( 'line', 'خطوط', '.zt-rule-title::before, .zt-rule-title::after', array( 'bg', 'height' ) ),
				array( 'title', 'عنوان', '.zt-rule-title span', array( 'typo', 'color' ) ),
				array( 'icon', 'آیکون', '.zt-rule-title svg', array( 'size', 'color' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		echo '<div class="zt-rule-title"><span>' . zt_icon( $s['icon'] ) . ' ' . esc_html( $s['title'] ) . '</span></div>'; // phpcs:ignore
	}
}
