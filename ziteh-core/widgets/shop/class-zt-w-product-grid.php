<?php
/**
 * Shop: product grid (archive / category / search / custom query) with
 * category chips, sorting, result count and pagination.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Product_Grid
 */
class ZT_W_Product_Grid extends ZT_Widget_Base {

	protected $zt_group = 'shop';
	protected $zt_icon  = 'eicon-products';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-product-grid';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'شبکه محصولات (فروشگاه)';
	}

	/**
	 * Sort options.
	 *
	 * @return array
	 */
	public static function sort_options() {
		return array(
			'menu_order' => 'پیش‌فرض',
			'date'       => 'جدیدترین',
			'popularity' => 'پرفروش‌ترین',
			'rating'     => 'بیشترین امتیاز',
			'price'      => 'ارزان‌ترین',
			'price-desc' => 'گران‌ترین',
		);
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'منبع' );
		$this->ctl( 'mode', 'select', 'منبع محصولات', 'main', array( 'options' => array( 'main' => 'کوئری اصلی صفحه (فروشگاه / دسته / جستجو)', 'custom' => 'کوئری سفارشی' ) ) );
		$cats = array();
		if ( taxonomy_exists( 'product_cat' ) ) {
			foreach ( (array) get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false, 'number' => 200 ) ) as $t ) {
				if ( is_object( $t ) ) {
					$cats[ $t->term_id ] = $t->name;
				}
			}
		}
		$this->ctl( 'q_cats', 'select2', 'محدود به دسته‌ها', array(), array( 'options' => $cats, 'multiple' => true, 'label_block' => true, 'condition' => array( 'mode' => 'custom' ) ) );
		$this->ctl( 'q_sale', 'switch', 'فقط محصولات تخفیف‌دار', false, array( 'condition' => array( 'mode' => 'custom' ) ) );
		$this->ctl( 'q_orderby', 'select', 'مرتب‌سازی پیش‌فرض', 'date', array( 'options' => self::sort_options(), 'condition' => array( 'mode' => 'custom' ) ) );
		$this->ctl( 'per_page', 'number', 'تعداد در هر صفحه', 12, array( 'condition' => array( 'mode' => 'custom' ), 'description' => 'در کوئری اصلی، از «ووکامرس ← تنظیمات ← محصولات» یا تنظیمات خواندن وردپرس پیروی می‌کند.' ) );
		$this->ctl( 'empty', 'text', 'متن نبود محصول', 'محصولی با این مشخصات پیدا نشد.' );
		$this->end_controls_section();

		$this->section( 'c2', 'نوار ابزار' );
		$this->ctl( 'toolbar', 'switch', 'نمایش نوار ابزار', true );
		$this->ctl( 'toolbar_cats', 'switch', 'دسته‌بندی‌ها (چیپ)', true, array( 'condition' => array( 'toolbar' => 'yes' ) ) );
		$this->ctl( 'all_text', 'text', 'متن «همه»', 'همه محصولات', array( 'condition' => array( 'toolbar' => 'yes' ) ) );
		$this->ctl( 'sale_chip', 'text', 'چیپ تخفیف‌دارها (خالی = مخفی)', 'تخفیف‌دارها', array( 'condition' => array( 'toolbar' => 'yes' ) ) );
		$this->ctl( 'count', 'switch', 'تعداد نتایج', true, array( 'condition' => array( 'toolbar' => 'yes' ) ) );
		$this->ctl( 'count_tpl', 'text', 'متن تعداد', '{n} محصول', array( 'condition' => array( 'toolbar' => 'yes' ) ) );
		$this->ctl( 'sort', 'switch', 'مرتب‌سازی', true, array( 'condition' => array( 'toolbar' => 'yes' ) ) );
		$this->ctl( 'pagination', 'switch', 'صفحه‌بندی', true );
		$this->end_controls_section();

		$this->section( 'c3', 'کارت محصول' );
		$this->ctl( 'badge', 'switch', 'نشان درصد تخفیف', true );
		$this->ctl( 'exp', 'switch', 'نشان تاریخ انقضا', true );
		$this->ctl( 'exp_label', 'text', 'برچسب انقضا', 'انقضا' );
		$this->ctl( 'stock', 'switch', 'هشدار موجودی کم', true );
		$this->ctl( 'stock_max', 'number', 'حداکثر موجودی برای هشدار', 3 );
		$this->ctl( 'add_icon', 'icon', 'آیکون دکمه سبد', 'cart' );
		$this->end_controls_section();

		$this->style_section(
			'st',
			'نوار ابزار و صفحه‌بندی',
			array(
				array( 'bar', 'نوار ابزار', '.zt-shopbar', array( 'bg', 'border', 'radius', 'padding', 'margin' ) ),
				array( 'chip', 'چیپ', '.zt-shopchip', array( 'typo', 'color', 'bg', 'border', 'radius', 'height' ) ),
				array( 'chipa', 'چیپ فعال', '.zt-shopchip.zt-is-active', array( 'color', 'bg' ) ),
				array( 'cnt', 'تعداد', '.zt-shopbar__count', array( 'typo', 'color' ) ),
				array( 'grid', 'شبکه', '.zt-pgrid', array( 'columns', 'gap', 'margin' ) ),
				array( 'pg', 'صفحه‌بندی', '.zt-pager a, .zt-pager span', array( 'typo', 'color', 'bg', 'radius', 'size' ) ),
				array( 'pga', 'صفحه فعال', '.zt-pager .current', array( 'color', 'bg' ) ),
			)
		);
		$this->style_section(
			'stp',
			'کارت محصول',
			array(
				array( 'card', 'کارت', '.zt-prod', array( 'bg', 'radius', 'shadow' ) ),
				array( 'pimg', 'تصویر', '.zt-prod__img', array( 'ratio', 'bg' ) ),
				array( 'pbody', 'بدنه', '.zt-prod__body', array( 'padding' ) ),
				array( 'pcat', 'دسته', '.zt-prod__cat', self::fx( 'text' ) ),
				array( 'ptitle', 'عنوان', '.zt-prod__title', array( 'typo', 'color', 'hover_color', 'min_height' ) ),
				array( 'pprice', 'قیمت', '.zt-prod__price b', array( 'typo', 'color' ) ),
				array( 'pdel', 'قیمت قبلی', '.zt-prod__price del', array( 'typo', 'color', 'display' ) ),
				array( 'padd', 'دکمه سبد', '.zt-prod__add', array( 'size', 'bg', 'bg_hover', 'color', 'radius' ) ),
				array( 'pbadge', 'نشان تخفیف', '.zt-badge-off', self::fx( 'badge' ) ),
				array( 'pexp', 'نشان انقضا', '.zt-exp', array( 'typo', 'bg', 'display' ) ),
			)
		);
	}

	/**
	 * Is the main query a product listing?
	 *
	 * @return bool
	 */
	private function main_is_products() {
		if ( ! zt_is_woo() ) {
			return false;
		}
		if ( is_shop() || is_product_taxonomy() ) {
			return true;
		}
		global $wp_query;
		return is_search() && $wp_query && 'product' === $wp_query->get( 'post_type' );
	}

	/**
	 * Current page number.
	 *
	 * @return int
	 */
	private static function paged() {
		return max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
	}

	/**
	 * Custom query.
	 *
	 * @param array $s Settings.
	 * @return array [ ids, total, pages ]
	 */
	private function custom_query( $s ) {
		$req     = wp_unslash( $_GET ); // phpcs:ignore
		$orderby = isset( $req['orderby'] ) ? sanitize_key( $req['orderby'] ) : $s['q_orderby'];
		$args    = array(
			'status'   => 'publish',
			'limit'    => max( 1, (int) $s['per_page'] ),
			'page'     => self::paged(),
			'paginate' => true,
			'return'   => 'ids',
			'visibility' => 'catalog',
		);
		switch ( $orderby ) {
			case 'popularity':
				$args['orderby']  = 'meta_value_num';
				$args['meta_key'] = 'total_sales'; // phpcs:ignore
				$args['order']    = 'DESC';
				break;
			case 'rating':
				$args['orderby']  = 'meta_value_num';
				$args['meta_key'] = '_wc_average_rating'; // phpcs:ignore
				$args['order']    = 'DESC';
				break;
			case 'price':
			case 'price-desc':
				$args['orderby']  = 'meta_value_num';
				$args['meta_key'] = '_price'; // phpcs:ignore
				$args['order']    = 'price' === $orderby ? 'ASC' : 'DESC';
				break;
			case 'menu_order':
				$args['orderby'] = 'menu_order title';
				$args['order']   = 'ASC';
				break;
			default:
				$args['orderby'] = 'date';
				$args['order']   = 'DESC';
		}
		$slugs = array();
		if ( ! empty( $req['zt_cat'] ) ) {
			$slugs = array( sanitize_title( $req['zt_cat'] ) );
		} elseif ( ! empty( $s['q_cats'] ) ) {
			foreach ( (array) $s['q_cats'] as $tid ) {
				$t = get_term( (int) $tid, 'product_cat' );
				if ( $t && ! is_wp_error( $t ) ) {
					$slugs[] = $t->slug;
				}
			}
		}
		if ( $slugs ) {
			$args['category'] = $slugs;
		}
		if ( 'yes' === $s['q_sale'] || ! empty( $req['on_sale'] ) ) {
			$ids             = wc_get_product_ids_on_sale();
			$args['include'] = $ids ? $ids : array( 0 );
		}
		$r = wc_get_products( $args );
		return array( $r->products, (int) $r->total, (int) $r->max_num_pages );
	}

	/**
	 * Toolbar.
	 *
	 * @param array $s     Settings.
	 * @param int   $total Total.
	 * @param bool  $main  Main query mode.
	 */
	private function toolbar( $s, $total, $main ) {
		$req = wp_unslash( $_GET ); // phpcs:ignore
		echo '<div class="zt-shopbar">';
		if ( 'yes' === $s['toolbar_cats'] ) {
			$current = is_product_category() ? get_queried_object_id() : 0;
			$cur_cat = isset( $req['zt_cat'] ) ? sanitize_title( $req['zt_cat'] ) : '';
			$sale    = ! empty( $req['on_sale'] );
			$base    = $main ? zt_page_url( 'shop' ) : remove_query_arg( array( 'zt_cat', 'on_sale', 'paged' ), get_pagenum_link( 1, false ) );
			echo '<div class="zt-shopbar__cats">';
			echo '<a class="zt-shopchip' . ( ! $current && ! $cur_cat && ! $sale ? ' zt-is-active' : '' ) . '" href="' . esc_url( $base ) . '">' . esc_html( $s['all_text'] ) . '</a>';
			$terms = get_terms(
				array(
					'taxonomy'   => 'product_cat',
					'hide_empty' => true,
					'parent'     => 0,
					'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
					'orderby'    => 'menu_order',
					'number'     => 20,
				)
			);
			foreach ( is_array( $terms ) ? $terms : array() as $t ) {
				$url    = $main ? get_term_link( $t ) : add_query_arg( 'zt_cat', $t->slug, $base );
				$active = $main ? ( $current && ( $current === $t->term_id || term_is_ancestor_of( $t->term_id, $current, 'product_cat' ) ) ) : $cur_cat === $t->slug;
				echo '<a class="zt-shopchip' . ( $active ? ' zt-is-active' : '' ) . '" href="' . esc_url( is_wp_error( $url ) ? '#' : $url ) . '">' . esc_html( $t->name ) . '</a>';
			}
			if ( '' !== $s['sale_chip'] ) {
				echo '<a class="zt-shopchip' . ( $sale ? ' zt-is-active' : '' ) . '" href="' . esc_url( add_query_arg( 'on_sale', 1, $base ) ) . '">' . zt_icon( 'percent' ) . ' ' . esc_html( $s['sale_chip'] ) . '</a>'; // phpcs:ignore
			}
			echo '</div>';
		}
		echo '<div class="zt-shopbar__side">';
		if ( 'yes' === $s['count'] ) {
			echo '<span class="zt-shopbar__count">' . esc_html( zt_fa( str_replace( '{n}', $total, $s['count_tpl'] ) ) ) . '</span>';
		}
		if ( 'yes' === $s['sort'] ) {
			$cur = isset( $req['orderby'] ) ? sanitize_key( $req['orderby'] ) : ( $main ? 'menu_order' : $s['q_orderby'] );
			echo '<label class="zt-shopbar__sort">' . zt_icon( 'sliders' ) . '<select class="zt-select" data-zt-orderby aria-label="مرتب‌سازی">'; // phpcs:ignore
			foreach ( self::sort_options() as $k => $v ) {
				echo '<option value="' . esc_attr( $k ) . '"' . selected( $cur, $k, false ) . '>' . esc_html( $v ) . '</option>';
			}
			echo '</select></label>';
		}
		echo '</div></div>';
	}

	/**
	 * Pagination.
	 *
	 * @param int  $pages Pages.
	 * @param bool $main  Main query.
	 */
	public static function pager( $pages, $main = true ) {
		if ( $pages < 2 ) {
			return;
		}
		$args = array(
			'total'     => $pages,
			'current'   => self::paged(),
			'type'      => 'array',
			'prev_text' => zt_icon( 'chev-right' ),
			'next_text' => zt_icon( 'chev-left' ),
			'end_size'  => 1,
			'mid_size'  => 1,
		);
		if ( ! $main ) {
			$args['base']   = add_query_arg( 'paged', '%#%' );
			$args['format'] = '';
		}
		$links = paginate_links( $args );
		if ( ! $links ) {
			return;
		}
		echo '<nav class="zt-pager" aria-label="صفحه‌بندی">';
		foreach ( $links as $l ) {
			echo preg_replace_callback( '/>(\d+)</', function ( $m ) { // phpcs:ignore
				return '>' . zt_fa( $m[1] ) . '<';
			}, $l );
		}
		echo '</nav>';
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		if ( ! zt_is_woo() ) {
			echo '<p class="zt-dcard__empty">ووکامرس فعال نیست.</p>';
			return;
		}
		$main = 'main' === $s['mode'] && $this->main_is_products();
		if ( $main ) {
			global $wp_query;
			$ids   = array();
			foreach ( $wp_query->posts as $p ) {
				if ( 'product' === get_post_type( $p ) ) {
					$ids[] = is_object( $p ) ? $p->ID : (int) $p;
				}
			}
			$total = (int) $wp_query->found_posts;
			$pages = (int) $wp_query->max_num_pages;
		} else {
			if ( 'main' === $s['mode'] && empty( $s['per_page'] ) ) {
				$s['per_page'] = 12;
			}
			list( $ids, $total, $pages ) = $this->custom_query( $s );
		}
		if ( function_exists( 'wc_print_notices' ) && ! $this->is_editor() ) {
			echo '<div class="zt-wc-notices">';
			wc_print_notices();
			echo '</div>';
		}
		if ( 'yes' === $s['toolbar'] ) {
			$this->toolbar( $s, $total, $main );
		}
		if ( ! $ids ) {
			echo '<div class="zt-cart-empty zt-pgrid-empty"><span class="zt-cart-empty__ic">' . zt_icon( 'search' ) . '</span><b>' . esc_html( $s['empty'] ) . '</b><a class="zt-btn zt-btn--primary" href="' . esc_url( zt_page_url( 'shop' ) ) . '">مشاهده همه محصولات</a></div>'; // phpcs:ignore
			return;
		}
		$opts = array(
			'badge'     => 'yes' === $s['badge'],
			'exp'       => 'yes' === $s['exp'],
			'exp_label' => $s['exp_label'],
			'stock'     => 'yes' === $s['stock'],
			'stock_max' => (int) $s['stock_max'],
			'add_icon'  => $s['add_icon'],
		);
		echo '<div class="zt-pgrid">';
		foreach ( $ids as $id ) {
			$it = ZT_Parts::product_item( $id );
			if ( $it ) {
				echo ZT_Parts::product_card( $it, $opts ); // phpcs:ignore
			}
		}
		echo '</div>';
		if ( 'yes' === $s['pagination'] ) {
			self::pager( $pages, $main );
		}
	}
}
