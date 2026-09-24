<?php
/**
 * Blog: post grid (blog page / archives / related posts).
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Post_Grid
 */
class ZT_W_Post_Grid extends ZT_Widget_Base {

	protected $zt_group = 'blog';
	protected $zt_icon  = 'eicon-posts-grid';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-post-grid';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'شبکه مقالات';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'منبع' );
		$this->ctl( 'mode', 'select', 'منبع مقالات', 'page', array( 'options' => array( 'page' => 'همه مقالات (صفحه مجله)', 'main' => 'کوئری اصلی (آرشیو دسته / برچسب / نویسنده / جستجو)', 'related' => 'مقالات مرتبط با مقاله فعلی' ) ) );
		$cats = array();
		foreach ( get_categories( array( 'hide_empty' => false ) ) as $c ) {
			$cats[ $c->term_id ] = $c->name;
		}
		$this->ctl( 'cats', 'select2', 'محدود به دسته‌ها', array(), array( 'options' => $cats, 'multiple' => true, 'label_block' => true, 'condition' => array( 'mode' => 'page' ) ) );
		$this->ctl( 'limit', 'number', 'تعداد در هر صفحه', 9, array( 'condition' => array( 'mode!' => 'main' ) ) );
		$this->ctl( 'empty', 'text', 'متن نبود مقاله', 'مقاله‌ای پیدا نشد.' );
		$this->end_controls_section();

		$this->section( 'c2', 'نمایش' );
		$this->ctl( 'toolbar', 'switch', 'دسته‌بندی‌ها (چیپ)', true );
		$this->ctl( 'all_text', 'text', 'متن «همه»', 'همه مقالات', array( 'condition' => array( 'toolbar' => 'yes' ) ) );
		$this->ctl( 'pagination', 'switch', 'صفحه‌بندی', true );
		$this->ctl( 'more', 'text', 'متن لینک ادامه', 'مطالعه مقاله' );
		$this->ctl( 'tag', 'switch', 'برچسب دسته روی تصویر', true );
		$this->ctl( 'meta', 'switch', 'تاریخ و زمان مطالعه', true );
		$this->ctl( 'excerpt', 'switch', 'خلاصه مقاله', true );
		$this->ctl( 'excerpt_len', 'number', 'طول خلاصه (کلمه)', 22 );
		$this->end_controls_section();

		$this->style_section(
			'st',
			'شبکه و کارت',
			array(
				array( 'grid', 'شبکه', '.zt-posts', array( 'columns', 'gap', 'margin' ) ),
				array( 'chip', 'چیپ', '.zt-shopchip', array( 'typo', 'color', 'bg', 'border', 'radius', 'height' ) ),
				array( 'chipa', 'چیپ فعال', '.zt-shopchip.zt-is-active', array( 'color', 'bg' ) ),
				array( 'card', 'کارت', '.zt-post', array( 'bg', 'radius', 'shadow' ) ),
				array( 'img', 'تصویر', '.zt-post__img', array( 'ratio', 'radius', 'margin' ) ),
				array( 'ptag', 'برچسب', '.zt-post__tag', self::fx( 'badge' ) ),
				array( 'title', 'عنوان', '.zt-post h3', array( 'typo', 'color', 'hover_color' ) ),
				array( 'meta', 'تاریخ', '.zt-post__meta', array( 'typo', 'color' ) ),
				array( 'exc', 'خلاصه', '.zt-post__excerpt', array( 'typo', 'color' ) ),
				array( 'more', 'لینک ادامه', '.zt-post .zt-link-more', array( 'typo', 'color', 'hover_color' ) ),
				array( 'pg', 'صفحه‌بندی', '.zt-pager a, .zt-pager span', array( 'typo', 'color', 'bg', 'radius', 'size' ) ),
			)
		);
	}

	/**
	 * Reading time in minutes.
	 *
	 * @param WP_Post $p Post.
	 * @return int
	 */
	public static function reading_time( $p ) {
		$words = count( preg_split( '/\s+/u', trim( wp_strip_all_tags( $p->post_content ) ) ) );
		return max( 1, (int) ceil( $words / 200 ) );
	}

	/**
	 * Card html for a post.
	 *
	 * @param WP_Post $p Post.
	 * @param array   $s Settings.
	 * @return string
	 */
	private function card( $p, $s ) {
		$it   = ZT_Parts::post_item( $p );
		$html = ZT_Parts::post_card( $it, array( 'more' => $s['more'], 'tag' => 'yes' === $s['tag'] ) );
		$add  = '';
		if ( 'yes' === $s['meta'] ) {
			$add .= '<div class="zt-post__meta">' . zt_icon( 'calendar' ) . ' ' . esc_html( zt_jdate( 'j F Y', get_post_time( 'U', true, $p ) ) ) . '<i></i>' . zt_icon( 'clock' ) . ' ' . esc_html( zt_fa( self::reading_time( $p ) ) ) . ' دقیقه مطالعه</div>';
		}
		if ( 'yes' === $s['excerpt'] ) {
			$exc  = has_excerpt( $p ) ? $p->post_excerpt : wp_strip_all_tags( strip_shortcodes( $p->post_content ) );
			$add .= '<p class="zt-post__excerpt">' . esc_html( wp_trim_words( $exc, max( 5, (int) $s['excerpt_len'] ), '…' ) ) . '</p>';
		}
		if ( $add ) {
			$html = preg_replace( '#(<div class="zt-post__body"><a [^>]+><h3>.*?</h3></a>)#su', '$1' . str_replace( '$', '\\$', $add ), $html, 1 );
		}
		return $html;
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$req   = wp_unslash( $_GET ); // phpcs:ignore
		$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
		$main  = false;
		if ( 'main' === $s['mode'] && ( is_home() || is_archive() || is_search() ) && ! $this->is_editor() ) {
			global $wp_query;
			$q    = $wp_query;
			$main = true;
		} else {
			$args = array(
				'post_type'           => 'post',
				'post_status'         => 'publish',
				'posts_per_page'      => max( 1, (int) $s['limit'] ),
				'paged'               => $paged,
				'ignore_sticky_posts' => 'related' === $s['mode'],
			);
			if ( 'related' === $s['mode'] ) {
				$cur = ZT_Context::post();
				if ( $cur ) {
					$args['post__not_in'] = array( $cur->ID );
					$cats                 = wp_get_post_categories( $cur->ID );
					if ( $cats ) {
						$args['category__in'] = $cats;
					}
				}
				$args['paged'] = 1;
			} elseif ( ! empty( $req['zt_pcat'] ) ) {
				$args['category_name'] = sanitize_title( $req['zt_pcat'] );
			} elseif ( ! empty( $s['cats'] ) ) {
				$args['category__in'] = array_map( 'intval', (array) $s['cats'] );
			}
			$q = new WP_Query( $args );
		}
		if ( 'yes' === $s['toolbar'] && 'related' !== $s['mode'] ) {
			$current = is_category() ? get_queried_object_id() : 0;
			$cur_cat = isset( $req['zt_pcat'] ) ? sanitize_title( $req['zt_pcat'] ) : '';
			$base    = zt_page_url( 'blog' );
			echo '<div class="zt-shopbar zt-shopbar--blog"><div class="zt-shopbar__cats">';
			echo '<a class="zt-shopchip' . ( ! $current && ! $cur_cat ? ' zt-is-active' : '' ) . '" href="' . esc_url( $base ) . '">' . esc_html( $s['all_text'] ) . '</a>';
			foreach ( get_categories( array( 'hide_empty' => true, 'parent' => 0, 'number' => 20 ) ) as $c ) {
				$url    = $main ? get_category_link( $c ) : add_query_arg( 'zt_pcat', $c->slug, $base );
				$active = $main ? $current === $c->term_id : $cur_cat === $c->slug;
				echo '<a class="zt-shopchip' . ( $active ? ' zt-is-active' : '' ) . '" href="' . esc_url( $url ) . '">' . esc_html( $c->name ) . '</a>';
			}
			echo '</div></div>';
		}
		if ( ! $q->have_posts() ) {
			if ( 'related' === $s['mode'] ) {
				echo ZT_Parts::empty_marker(); // phpcs:ignore
			} else {
				echo '<p class="zt-dcard__empty zt-posts-empty">' . esc_html( $s['empty'] ) . '</p>';
			}
			return;
		}
		echo '<div class="zt-posts zt-posts--grid">';
		foreach ( $q->posts as $p ) {
			echo $this->card( $p, $s ); // phpcs:ignore
		}
		echo '</div>';
		if ( 'yes' === $s['pagination'] && 'related' !== $s['mode'] && class_exists( 'ZT_W_Product_Grid' ) ) {
			ZT_W_Product_Grid::pager( (int) $q->max_num_pages, $main );
		}
	}
}
