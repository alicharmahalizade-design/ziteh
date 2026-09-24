<?php
/**
 * Checkout: step bar.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Checkout_Steps
 */
class ZT_W_Checkout_Steps extends ZT_Widget_Base {

	protected $zt_group = 'checkout';
	protected $zt_icon  = 'eicon-progress-tracker';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-checkout-steps';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'مراحل خرید';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'مراحل' );
		$this->ctl( 'steps', 'textarea', 'مراحل (هر خط یک مرحله — خالی = از تنظیمات)', '' );
		$this->ctl( 'active', 'number', 'مرحله فعال', 1, array( 'min' => 1, 'max' => 8 ) );
		$this->end_controls_section();
		$this->style_section(
			'st',
			'مراحل',
			array(
				array( 'bar', 'کادر', '.zt-steps-bar', array( 'bg', 'border', 'radius', 'padding', 'margin' ) ),
				array( 'line', 'خط', '.zt-steps-bar ol::before', array( 'bg' ) ),
				array( 'fill', 'خط فعال', '.zt-steps-bar ol::after', array( 'bg' ) ),
				array( 'num', 'دایره', '.zt-stepli i', array( 'size', 'bg', 'color', 'border_color', 'typo' ) ),
				array( 'numa', 'دایره فعال', '.zt-stepli.zt-is-active i', array( 'bg', 'color', 'border_color' ) ),
				array( 'lbl', 'برچسب', '.zt-stepli span', array( 'typo', 'color' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$steps  = zt_lines( '' !== trim( $s['steps'] ) ? $s['steps'] : zt_opt( 'checkout.steps' ) );
		$active = max( 1, (int) $s['active'] );
		echo '<div class="zt-steps-bar"><ol>';
		foreach ( $steps as $i => $st ) {
			echo '<li class="zt-stepli' . ( $i + 1 === $active ? ' zt-is-active' : '' ) . '"><i>' . esc_html( zt_fa( $i + 1 ) ) . '</i><span>' . esc_html( $st ) . '</span></li>';
		}
		echo '</ol></div>';
	}
}
