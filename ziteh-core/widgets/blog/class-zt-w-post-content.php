<?php
/**
 * Blog: post content (typography of the design).
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Post_Content
 */
class ZT_W_Post_Content extends ZT_Widget_Base {

	protected $zt_group = 'blog';
	protected $zt_icon  = 'eicon-post-content';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-post-content';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'محتوای مقاله';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'محتوا' );
		$this->ctl( 'toc', 'switch', 'فهرست مطالب خودکار (از تیترهای H2)', true );
		$this->ctl( 'toc_title', 'text', 'عنوان فهرست', 'آنچه در این مقاله می‌خوانید' );
		$this->end_controls_section();
		$this->style_section(
			'st',
			'تایپوگرافی',
			array(
				array( 'prose', 'متن', '.zt-prose', array( 'typo', 'color', 'max_width' ) ),
				array( 'h2', 'تیتر ۲', '.zt-prose h2', array( 'typo', 'color', 'margin' ) ),
				array( 'h3', 'تیتر ۳', '.zt-prose h3', array( 'typo', 'color', 'margin' ) ),
				array( 'a', 'لینک', '.zt-prose a', array( 'color', 'hover_color' ) ),
				array( 'quote', 'نقل‌قول', '.zt-prose blockquote', array( 'typo', 'color', 'bg', 'radius', 'padding' ) ),
				array( 'img', 'تصاویر', '.zt-prose img', array( 'radius' ) ),
				array( 'toc', 'فهرست مطالب', '.zt-toc', array( 'bg', 'radius', 'padding' ) ),
			)
		);
	}

	/**
	 * Add ids to h2 and collect a TOC.
	 *
	 * @param string $html Html.
	 * @return array [ html, toc ]
	 */
	private function toc( $html ) {
		$toc  = array();
		$used = array();
		$html = preg_replace_callback(
			'#<h2([^>]*)>(.*?)</h2>#si',
			function ( $m ) use ( &$toc, &$used ) {
				$text = wp_strip_all_tags( $m[2] );
				if ( preg_match( '/id=["\']([^"\']+)/', $m[1], $idm ) ) {
					$id = $idm[1];
					$at = $m[1];
				} else {
					$id = 'zt-h-' . ( count( $toc ) + 1 );
					$at = $m[1] . ' id="' . $id . '"';
				}
				$used[] = $id;
				$toc[]  = array( $id, $text );
				return '<h2' . $at . '>' . $m[2] . '</h2>';
			},
			$html
		);
		return array( $html, $toc );
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$p = ZT_Context::post();
		if ( ! $p ) {
			echo '<div class="zt-prose"><p>محتوای مقاله اینجا نمایش داده می‌شود. این یک متن نمونه برای پیش‌نمایش تایپوگرافی است.</p><h2>یک تیتر نمونه</h2><p>مراقبت از پوست، از عادت‌های کوچک شروع می‌شود.</p></div>';
			return;
		}
		static $depth = 0;
		if ( $depth > 0 ) {
			return;
		}
		++$depth;
		$GLOBALS['post'] = $p; // phpcs:ignore
		setup_postdata( $p );
		$html = apply_filters( 'the_content', $p->post_content ); // phpcs:ignore
		wp_reset_postdata();
		--$depth;
		$toc = array();
		if ( 'yes' === $s['toc'] ) {
			list( $html, $toc ) = $this->toc( $html );
		}
		if ( count( $toc ) >= 2 ) {
			echo '<nav class="zt-toc"><b>' . zt_icon( 'menu' ) . ' ' . esc_html( $s['toc_title'] ) . '</b><ol>'; // phpcs:ignore
			foreach ( $toc as $t ) {
				echo '<li><a href="#' . esc_attr( $t[0] ) . '">' . esc_html( $t[1] ) . '</a></li>';
			}
			echo '</ol></nav>';
		}
		echo '<div class="zt-prose">' . $html . '</div>'; // phpcs:ignore
	}
}
