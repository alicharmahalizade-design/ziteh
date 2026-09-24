<?php
/**
 * Global: trust bar (4 benefits).
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Trust
 */
class ZT_W_Trust extends ZT_Widget_Base {

	protected $zt_group = 'global';
	protected $zt_icon  = 'eicon-check-circle-o';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-trust';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'نوار اعتماد (مزیت‌ها)';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'مزیت‌ها' );
		$this->repeater(
			'items',
			'آیتم‌ها',
			array(
				array( 'icon', 'icon', 'آیکون', 'truck-fast' ),
				array( 'title', 'text', 'عنوان', 'ارسال سریع' ),
				array( 'sub', 'text', 'توضیح', '' ),
				array( 'url', 'url', 'لینک (اختیاری)', '' ),
			),
			array(
				array( 'icon' => 'truck-fast', 'title' => 'ارسال سریع', 'sub' => 'ارسال ۲ تا ۳ روز کاری', 'url' => '' ),
				array( 'icon' => 'lock', 'title' => 'پرداخت امن', 'sub' => 'درگاه بانکی معتبر', 'url' => '' ),
				array( 'icon' => 'headset', 'title' => 'پشتیبانی', 'sub' => 'پاسخگوی آنلاین', 'url' => '' ),
				array( 'icon' => 'shield', 'title' => 'ضمانت اصالت', 'sub' => 'تمامی محصولات اورجینال', 'url' => '' ),
			),
			'{{{ title }}}'
		);
		$this->end_controls_section();
		$this->style_section(
			'st',
			'نوار اعتماد',
			array(
				array( 'box', 'کادر', '.zt-trust', array( 'bg', 'radius', 'margin', 'padding', 'shadow', 'border', 'columns' ) ),
				array( 'item', 'هر آیتم', '.zt-trust__i', array( 'padding', 'gap', 'justify' ) ),
				array( 'sep', 'جداکننده', '.zt-trust__i + .zt-trust__i::before', array( 'bg', 'opacity', 'display' ) ),
				array( 'icon', 'آیکون', '.zt-trust__i svg', self::fx( 'icon' ) ),
				array( 'title', 'عنوان', '.zt-trust__i b', self::fx( 'text' ) ),
				array( 'sub', 'توضیح', '.zt-trust__i span', self::fx( 'text' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		echo '<div class="zt-trust">';
		foreach ( (array) $s['items'] as $it ) {
			$tag  = ! empty( $it['url']['url'] ) ? 'a' : 'div';
			$attr = 'a' === $tag ? zt_link_attrs( $it['url'] ) : '';
			echo '<' . $tag . $attr . ' class="zt-trust__i">' . zt_icon( $it['icon'] ) . '<div><b>' . esc_html( $it['title'] ) . '</b><span>' . esc_html( $it['sub'] ) . '</span></div></' . $tag . '>'; // phpcs:ignore
		}
		echo '</div>';
	}
}
