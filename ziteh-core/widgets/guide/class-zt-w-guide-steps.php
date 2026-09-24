<?php
/**
 * Guide: numbered steps (morning / night routine …).
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/defaults.php';

/**
 * Class ZT_W_Guide_Steps
 */
class ZT_W_Guide_Steps extends ZT_Widget_Base {

	protected $zt_group = 'guide';
	protected $zt_icon  = 'eicon-bullet-list';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-guide-steps';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'مراحل روتین';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'مراحل' );
		$this->ctl( 'title', 'text', 'عنوان', 'روتین صبح' );
		$this->ctl( 'icon', 'icon', 'آیکون عنوان', 'sun' );
		$this->ctl( 'tag', 'select', 'تگ عنوان', 'h2', array( 'options' => array( 'h2' => 'H2', 'h3' => 'H3', 'div' => 'div' ) ) );
		$this->repeater(
			'steps',
			'مراحل',
			array(
				array( 'title', 'text', 'عنوان', 'مرحله' ),
				array( 'text', 'textarea', 'توضیح', '' ),
				array( 'link', 'url', 'لینک محصول پیشنهادی (اختیاری)', '' ),
				array( 'link_text', 'text', 'متن لینک', '' ),
			),
			ZT_W_Guide_Steps_Defaults::am(),
			'title'
		);
		$this->end_controls_section();
		$this->style_section(
			'st',
			'مراحل',
			array(
				array( 'h', 'عنوان', '.zt-guide__h', array( 'typo', 'color', 'margin' ) ),
				array( 'hic', 'آیکون عنوان', '.zt-guide__h svg', array( 'size', 'color' ) ),
				array( 'list', 'لیست', '.zt-guide__steps', array( 'gap' ) ),
				array( 'li', 'کارت مرحله', '.zt-guide__steps li', array( 'bg', 'border', 'radius', 'padding' ) ),
				array( 'num', 'شماره', '.zt-guide__steps li::before', array( 'bg', 'color', 'radius' ) ),
				array( 'b', 'عنوان مرحله', '.zt-guide__steps li b', array( 'typo', 'color' ) ),
				array( 'p', 'توضیح', '.zt-guide__steps li p', self::fx( 'text' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$tag = in_array( $s['tag'], array( 'h2', 'h3', 'div' ), true ) ? $s['tag'] : 'h2';
		if ( '' !== $s['title'] ) {
			echo '<' . $tag . ' class="zt-guide__h">' . zt_icon( $s['icon'] ) . ' ' . esc_html( $s['title'] ) . '</' . $tag . '>'; // phpcs:ignore
		}
		echo '<ol class="zt-guide__steps">';
		foreach ( (array) $s['steps'] as $st ) {
			echo '<li><b>' . esc_html( $st['title'] ) . '</b>';
			if ( '' !== trim( (string) $st['text'] ) ) {
				echo '<p>' . esc_html( $st['text'] ) . '</p>';
			}
			if ( ! empty( $st['link']['url'] ) && ! empty( $st['link_text'] ) ) {
				echo '<a class="zt-link-more"' . zt_link_attrs( $st['link'] ) . '>' . esc_html( $st['link_text'] ) . ' ' . zt_icon( 'chev-left' ) . '</a>'; // phpcs:ignore
			}
			echo '</li>';
		}
		echo '</ol>';
	}
}
