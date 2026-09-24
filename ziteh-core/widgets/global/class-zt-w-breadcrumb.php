<?php
/**
 * Global: breadcrumb.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Breadcrumb
 */
class ZT_W_Breadcrumb extends ZT_Widget_Base {

	protected $zt_group = 'global';
	protected $zt_icon  = 'eicon-product-breadcrumbs';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-breadcrumb';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'مسیر راهنما (Breadcrumb)';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'مسیر' );
		$this->ctl( 'mode', 'select', 'حالت', 'auto', array( 'options' => array( 'auto' => 'خودکار (بر اساس صفحه/محصول/دسته)', 'manual' => 'دستی' ) ) );
		$this->ctl( 'home', 'text', 'عنوان خانه', 'خانه' );
		$this->ctl( 'sep', 'text', 'جداکننده', '/' );
		$this->ctl( 'current', 'text', 'عنوان صفحه فعلی (خالی = عنوان صفحه)', '', array( 'condition' => array( 'mode' => 'auto' ) ) );
		$this->repeater(
			'items',
			'آیتم‌های میانی',
			array(
				array( 'label', 'text', 'عنوان', '' ),
				array( 'url', 'url', 'لینک', '#' ),
			),
			array(),
			'{{{ label }}}',
			array( 'condition' => array( 'mode' => 'manual' ) )
		);
		$this->ctl( 'last', 'text', 'آیتم آخر (صفحه فعلی)', '', array( 'condition' => array( 'mode' => 'manual' ) ) );
		$this->end_controls_section();
		$this->style_section(
			'st',
			'مسیر',
			array(
				array( 'nav', 'کادر', '.zt-crumb', array( 'typo', 'color', 'padding', 'margin', 'gap', 'justify' ) ),
				array( 'link', 'لینک‌ها', '.zt-crumb a', array( 'color', 'hover_color' ) ),
				array( 'sep', 'جداکننده', '.zt-crumb .zt-sep', array( 'color', 'typo' ) ),
				array( 'cur', 'صفحه فعلی', '.zt-crumb .zt-cur', array( 'color', 'typo' ) ),
			)
		);
	}

	/**
	 * Auto trail.
	 *
	 * @param array $s Settings.
	 * @return array [ [label, url], ... ] last is current (url empty).
	 */
	private function trail( $s ) {
		$t   = array();
		$cur = '' !== $s['current'] ? $s['current'] : '';
		$p   = ZT_Context::product();
		if ( ! $p && ZT_Context::demo() && 'product' === ZT_Shell::page_type() ) {
			return array( array( 'مراقبت مو', '#' ), array( $cur ? $cur : 'شامپو تقویت کننده و ضد ریزش مو', '' ) );
		}
		if ( $p && ( is_product() || ZT_Context::editing_template() ) ) {
			$terms = get_the_terms( $p->get_id(), 'product_cat' );
			if ( $terms && ! is_wp_error( $terms ) ) {
				$term = $terms[0];
				foreach ( array_reverse( get_ancestors( $term->term_id, 'product_cat' ) ) as $a ) {
					$at  = get_term( $a, 'product_cat' );
					$t[] = array( $at->name, get_term_link( $at ) );
				}
				$t[] = array( $term->name, get_term_link( $term ) );
			}
			$short = get_post_meta( $p->get_id(), '_zt_crumb_title', true );
			$t[]   = array( $cur ? $cur : ( $short ? $short : $p->get_name() ), '' );
			return $t;
		}
		if ( is_tax() || is_category() || is_tag() ) {
			$term = get_queried_object();
			foreach ( array_reverse( get_ancestors( $term->term_id, $term->taxonomy ) ) as $a ) {
				$at  = get_term( $a, $term->taxonomy );
				$t[] = array( $at->name, get_term_link( $at ) );
			}
			$t[] = array( $cur ? $cur : $term->name, '' );
			return $t;
		}
		if ( is_singular( 'post' ) ) {
			$t[]  = array( 'مجله', zt_page_url( 'blog' ) );
			$cats = get_the_category();
			if ( $cats ) {
				$t[] = array( $cats[0]->name, get_category_link( $cats[0] ) );
			}
			$t[] = array( $cur ? $cur : get_the_title(), '' );
			return $t;
		}
		if ( zt_is_woo() && is_checkout() ) {
			$t[] = array( 'سبد خرید', zt_page_url( 'cart' ) );
		}
		if ( (int) get_queried_object_id() === (int) zt_opt( 'pages.tracking' ) ) {
			$t[] = array( 'حساب کاربری', zt_page_url( 'account' ) );
		}
		if ( is_search() ) {
			$t[] = array( $cur ? $cur : 'جستجو: ' . get_search_query(), '' );
			return $t;
		}
		if ( is_404() ) {
			$t[] = array( $cur ? $cur : 'صفحه پیدا نشد', '' );
			return $t;
		}
		if ( zt_is_woo() && is_shop() ) {
			$t[] = array( $cur ? $cur : ( wc_get_page_id( 'shop' ) > 0 ? get_the_title( wc_get_page_id( 'shop' ) ) : 'فروشگاه' ), '' );
			return $t;
		}
		$id  = get_queried_object_id();
		$t[] = array( $cur ? $cur : ( $id ? get_the_title( $id ) : wp_get_document_title() ), '' );
		return $t;
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		if ( 'manual' === $s['mode'] ) {
			$trail = array();
			foreach ( (array) $s['items'] as $it ) {
				$trail[] = array( $it['label'], zt_url( $it['url'] ) );
			}
			$trail[] = array( $s['last'], '' );
		} else {
			$trail = $this->trail( $s );
		}
		$sep = '<span class="zt-sep">' . esc_html( $s['sep'] ) . '</span>';
		echo '<nav class="zt-crumb" aria-label="مسیر"><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html( $s['home'] ) . '</a>';
		foreach ( $trail as $it ) {
			echo $sep; // phpcs:ignore
			if ( '' === $it[1] ) {
				echo '<span class="zt-cur">' . esc_html( $it[0] ) . '</span>';
			} else {
				echo '<a href="' . esc_url( $it[1] ) . '">' . esc_html( $it[0] ) . '</a>';
			}
		}
		echo '</nav>';
	}
}
