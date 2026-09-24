<?php
/**
 * Tracking: status timeline.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/demo.php';

/**
 * Class ZT_W_Order_Timeline
 */
class ZT_W_Order_Timeline extends ZT_Widget_Base {

	protected $zt_group = 'tracking';
	protected $zt_icon  = 'eicon-time-line';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-order-timeline';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'تایم‌لاین وضعیت سفارش';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'تایم‌لاین' );
		$this->ctl( 'notice', 'notice', '', 'مراحل و وضعیت‌های متناظر در «زیته ← پیگیری و حساب کاربری» تنظیم می‌شوند.' );
		$this->end_controls_section();
		$this->style_section(
			'st',
			'تایم‌لاین',
			array(
				array( 'box', 'کادر', '.zt-timeline', array( 'bg', 'border', 'radius', 'padding', 'margin' ) ),
				array( 'line', 'خط', '.zt-timeline ol::before', array( 'bg' ) ),
				array( 'fill', 'خط طی‌شده', '.zt-timeline ol::after', array( 'bg' ) ),
				array( 'dot', 'دایره', '.zt-tl i', array( 'size', 'bg', 'color', 'border_color' ) ),
				array( 'cur', 'دایره مرحله فعلی', '.zt-tl.zt-is-current i', array( 'bg', 'color', 'shadow' ) ),
				array( 'title', 'عنوان', '.zt-tl b', array( 'typo', 'color' ) ),
				array( 'time', 'زمان', '.zt-tl span', array( 'typo', 'color' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$v = ZT_Order_View::get();
		if ( ! $v || ! $v['timeline'] ) {
			echo ZT_Parts::empty_marker(); // phpcs:ignore
			return;
		}
		$n     = count( $v['timeline'] );
		$style = $v['sample'] ? '' : ' style="--zt-tlw:' . esc_attr( round( ( $v['current'] + 0.5 ) / $n * 100, 3 ) ) . '%"';
		echo '<section class="zt-timeline"><ol' . $style . '>'; // phpcs:ignore
		foreach ( $v['timeline'] as $t ) {
			$cls = 'done' === $t['state'] ? 'zt-is-done' : ( 'current' === $t['state'] ? 'zt-is-current' : 'zt-is-idle' );
			if ( array_key_exists( 'time_s', $t ) ) {
				$time = $t['time_s'] ? esc_html( $t['time_s'][0] ) . '<br>' . esc_html( $t['time_s'][1] ) : '—';
			} else {
				$time = $t['time'] ? esc_html( zt_jdate( 'j F Y', $t['time'] ) ) . '<br>' . esc_html( zt_jdate( 'H:i', $t['time'] ) ) : '—';
			}
			echo '<li class="zt-tl ' . esc_attr( $cls ) . '"><i>' . zt_icon( $t['icon'] ) . '</i><b>' . esc_html( $t['title'] ) . '</b><span>' . $time . '</span></li>'; // phpcs:ignore
		}
		echo '</ol></section>';
	}
}
