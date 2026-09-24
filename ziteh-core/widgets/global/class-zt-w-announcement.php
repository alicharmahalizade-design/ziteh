<?php
/**
 * Global: announcement strip (three messages with separators).
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Announcement
 */
class ZT_W_Announcement extends ZT_Widget_Base {

	protected $zt_group = 'global';
	protected $zt_icon  = 'eicon-alert';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-announcement';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'نوار اعلان';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'پیام‌ها' );
		$this->repeater(
			'items',
			'پیام‌ها',
			array(
				array( 'icon', 'icon', 'آیکون', 'leaf' ),
				array( 'text', 'text', 'متن', '' ),
				array( 'url', 'url', 'لینک (اختیاری)', '' ),
			),
			array(
				array( 'icon' => 'leaf', 'text' => 'از زیته تا خانه‌ی شما', 'url' => '' ),
				array( 'icon' => 'truck-fast', 'text' => 'ارسال به سراسر ایران با پست پیشتاز', 'url' => '' ),
				array( 'icon' => 'bike', 'text' => 'ارسال در لاهیجان با پیک', 'url' => '' ),
			),
			'{{{ text }}}'
		);
		$this->ctl( 'home_only_desktop', 'switch', 'روی موبایلِ صفحه اصلی پنهان شود (مانند طرح)', true );
		$this->end_controls_section();
		$this->style_section(
			'st',
			'نوار اعلان',
			array(
				array( 'bar', 'نوار', '.zt-ann', array( 'bg', 'color', 'padding', 'typo' ) ),
				array( 'item', 'پیام', '.zt-annrow li', array( 'typo', 'padding', 'gap' ) ),
				array( 'sep', 'جداکننده', '.zt-annrow li + li::before', array( 'bg', 'display' ) ),
				array( 'icon', 'آیکون', '.zt-annrow svg', array( 'size', 'opacity' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		echo '<div class="zt-ann' . ( 'yes' === $s['home_only_desktop'] ? '' : ' zt-ann--always' ) . '"><div class="zt-container"><ul class="zt-annrow">';
		foreach ( (array) $s['items'] as $it ) {
			$txt = zt_icon( $it['icon'] ) . ' ' . esc_html( $it['text'] );
			if ( ! empty( $it['url']['url'] ) ) {
				$txt = '<a' . zt_link_attrs( $it['url'] ) . ' style="display:contents">' . $txt . '</a>';
			}
			echo '<li>' . $txt . '</li>'; // phpcs:ignore
		}
		echo '</ul></div></div>';
	}
}
