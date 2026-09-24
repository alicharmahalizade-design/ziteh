<?php
/**
 * Global: section heading (.sec-head) + optional "view all" link.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Section_Title
 */
class ZT_W_Section_Title extends ZT_Widget_Base {

	protected $zt_group = 'global';
	protected $zt_icon  = 'eicon-heading';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-section-title';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'عنوان بخش (برگ + لینک)';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'عنوان' );
		$this->heading_controls( 'محصولات منتخب زیته', array( 'link_text' => 'مشاهده همه', 'link_url' => '{{shop}}' ) );
		$this->end_controls_section();
		$this->style_section(
			'st',
			'عنوان بخش',
			array(
				array( 'head', 'کادر', '.zt-sec-head', array( 'margin', 'gap', 'justify' ) ),
				array( 'title', 'عنوان', '.zt-sec-title', self::fx( 'text' ) ),
				array( 'icon', 'آیکون', '.zt-sec-title .zt-leaf', self::fx( 'icon' ) ),
				array( 'sub', 'زیرعنوان', '.zt-sec-sub', self::fx( 'text' ) ),
				array( 'more', 'لینک', '.zt-link-more', self::fx( 'link' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$this->heading_render( $s );
	}
}
