<?php
/**
 * Global: page heading (H1 with leaf + subtitle).
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Page_Head
 */
class ZT_W_Page_Head extends ZT_Widget_Base {

	protected $zt_group = 'global';
	protected $zt_icon  = 'eicon-t-letter';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-page-head';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'عنوان صفحه';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'محتوا' );
		$this->ctl( 'dynamic', 'switch', 'عنوان خودکار (عنوان صفحه / دسته / جستجو)', false );
		$this->ctl( 'title', 'text', 'عنوان', 'سبد خرید', array( 'condition' => array( 'dynamic!' => 'yes' ) ) );
		$this->ctl( 'icon', 'icon', 'آیکون', 'leaf' );
		$this->ctl( 'sub', 'textarea', 'زیرعنوان', 'محصولات مورد علاقه‌تان را بررسی و سفارش دهید.' );
		$this->ctl( 'tag', 'select', 'تگ', 'h1', array( 'options' => array( 'h1' => 'H1', 'h2' => 'H2', 'div' => 'div' ) ) );
		$this->end_controls_section();
		$this->style_section(
			'st',
			'عنوان صفحه',
			array(
				array( 'box', 'کادر', '.zt-page-head', array( 'padding', 'margin', 'align' ) ),
				array( 'title', 'عنوان', '.zt-page-head h1, .zt-page-head .zt-ph-title', array( 'typo', 'color', 'gap', 'justify' ) ),
				array( 'icon', 'آیکون', '.zt-page-head .zt-leaf', array( 'size', 'color', 'display' ) ),
				array( 'sub', 'زیرعنوان', '.zt-page-head p', self::fx( 'text' ) ),
			)
		);
	}

	/**
	 * Dynamic title.
	 *
	 * @return string
	 */
	private function dyn_title() {
		if ( is_search() ) {
			return 'نتایج جستجو برای «' . get_search_query() . '»';
		}
		if ( is_archive() ) {
			return wp_strip_all_tags( preg_replace( '/^[^:]+:\s*/u', '', get_the_archive_title() ) );
		}
		if ( zt_is_woo() && is_shop() ) {
			return get_the_title( wc_get_page_id( 'shop' ) );
		}
		if ( is_404() ) {
			return 'صفحه پیدا نشد';
		}
		if ( function_exists( 'is_wc_endpoint_url' ) && is_account_page() ) {
			$ep = WC()->query->get_current_endpoint();
			if ( $ep ) {
				return WC()->query->get_endpoint_title( $ep );
			}
		}
		return get_the_title( get_queried_object_id() );
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$title = 'yes' === $s['dynamic'] ? $this->dyn_title() : $s['title'];
		$tag   = in_array( $s['tag'], array( 'h1', 'h2', 'div' ), true ) ? $s['tag'] : 'h1';
		echo '<div class="zt-page-head"><' . $tag . ( 'h1' !== $tag ? ' class="zt-ph-title"' : '' ) . '>' . zt_icon( $s['icon'], array( 'class' => 'zt-leaf', 'width' => '26', 'height' => '26' ) ) . ' ' . esc_html( $title ) . '</' . $tag . '>'; // phpcs:ignore
		if ( '' !== trim( (string) $s['sub'] ) ) {
			echo '<p>' . zt_kses( $s['sub'] ) . '</p>'; // phpcs:ignore
		}
		echo '</div>';
	}
}
