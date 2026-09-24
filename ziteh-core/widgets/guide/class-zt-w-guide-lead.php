<?php
/**
 * Guide: lead box.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Guide_Lead
 */
class ZT_W_Guide_Lead extends ZT_Widget_Base {

	protected $zt_group = 'guide';
	protected $zt_icon  = 'eicon-text';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-guide-lead';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'مقدمه راهنما';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'متن' );
		$this->ctl( 'text', 'wysiwyg', 'متن', "<p>داشتن یک روتین منظم می‌تواند به حفظ سلامت و ظاهر پوست کمک کند. لازم نیست از محصولات متعدد\n        استفاده کنید؛ انتخاب محصولات متناسب با نوع پوست و استفاده منظم از آن‌ها، معمولاً مؤثرتر از\n        یک روتین پیچیده است.</p>" );
		$this->end_controls_section();
		$this->style_section(
			'st',
			'کادر',
			array(
				array( 'box', 'کادر', '.zt-guide__lead', array( 'bg', 'border', 'radius', 'padding', 'margin' ) ),
				array( 'p', 'متن', '.zt-guide__lead p', self::fx( 'text' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$html = (string) $s['text'];
		if ( false === stripos( $html, '<p' ) ) {
			$html = wpautop( $html );
		}
		echo '<div class="zt-guide__lead">' . wp_kses_post( $html ) . '</div>';
	}
}
