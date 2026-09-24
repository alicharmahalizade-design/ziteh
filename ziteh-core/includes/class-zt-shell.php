<?php
/**
 * The mobile "app shell" (app bar, tab bar, drawer, search pane, sheets,
 * toasts, action bar), the icon sprite and page-context body classes.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_Shell
 */
class ZT_Shell {

	/**
	 * Sprite printed?
	 *
	 * @var bool
	 */
	private static $sprite_done = false;

	/**
	 * Cached page type.
	 *
	 * @var string|null
	 */
	private static $type = null;

	/**
	 * Hooks.
	 */
	public static function init() {
		add_filter( 'body_class', array( __CLASS__, 'body_class' ) );
		add_action( 'wp_body_open', array( __CLASS__, 'print_sprite' ), 1 );
		add_action( 'wp_footer', array( __CLASS__, 'print_sprite' ), 1 );
		add_action( 'wp_footer', array( __CLASS__, 'render' ), 5 );
	}

	/**
	 * Mark that an icon was used (sprite printing is unconditional on the front
	 * end, this is kept for API symmetry / future lazy printing).
	 */
	public static function need_sprite() {}

	/**
	 * Print the SVG sprite once.
	 */
	public static function print_sprite() {
		if ( self::$sprite_done || is_admin() ) {
			return;
		}
		self::$sprite_done = true;
		echo file_get_contents( ZT_PATH . 'assets/icons/sprite.svg' ); // phpcs:ignore -- static bundled SVG.
	}

	/**
	 * Is the design active on the front end?
	 *
	 * @return bool
	 */
	public static function design_on() {
		return (bool) zt_opt( 'general.enable_design', 1 );
	}

	/**
	 * Is the mobile shell active?
	 *
	 * @return bool
	 */
	public static function shell_on() {
		return self::design_on() && zt_opt( 'shell.enabled', 1 );
	}

	/**
	 * Current page type: home|shop|product|cart|checkout|panel|tracking|routine|blog|post|inner.
	 *
	 * @return string
	 */
	public static function page_type() {
		if ( null !== self::$type ) {
			return self::$type;
		}
		$type = 'inner';
		$id   = get_queried_object_id();
		$over = zt_page_setting( 'page_type' );
		if ( $over && 'auto' !== $over ) {
			$type = $over;
		} elseif ( is_front_page() || ( $id && (int) zt_opt( 'pages.home' ) === $id ) ) {
			$type = 'home';
		} elseif ( zt_is_woo() && is_product() ) {
			$type = 'product';
		} elseif ( zt_is_woo() && is_cart() ) {
			$type = 'cart';
		} elseif ( zt_is_woo() && is_checkout() ) {
			$type = 'checkout';
		} elseif ( zt_is_woo() && is_account_page() ) {
			$type = 'panel';
		} elseif ( zt_is_woo() && ( is_shop() || is_product_taxonomy() ) ) {
			$type = 'shop';
		} elseif ( $id && (int) zt_opt( 'pages.tracking' ) === $id ) {
			$type = 'tracking';
		} elseif ( $id && (int) zt_opt( 'pages.routine' ) === $id ) {
			$type = 'routine';
		} elseif ( $id && (int) zt_opt( 'pages.shop' ) === $id ) {
			$type = 'shop';
		} elseif ( is_singular( 'post' ) ) {
			$type = 'post';
		} elseif ( is_home() || is_category() || is_tag() || ( $id && (int) zt_opt( 'pages.blog' ) === $id ) ) {
			$type = 'blog';
		} elseif ( is_singular( 'zt_template' ) ) {
			$tt   = get_post_meta( get_the_ID(), '_zt_tpl_type', true );
			$type = 'single_product' === $tt ? 'product' : 'inner';
		}
		self::$type = apply_filters( 'zt_page_type', $type );
		return self::$type;
	}

	/**
	 * Tab bar key for the current page.
	 *
	 * @return string
	 */
	public static function tab_key() {
		$map = array(
			'home'     => 'home',
			'routine'  => 'home',
			'shop'     => 'shop',
			'product'  => 'shop',
			'cart'     => 'cart',
			'checkout' => 'cart',
			'panel'    => 'account',
			'tracking' => 'account',
		);
		if ( zt_is_woo() && is_account_page() && function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'zt-wishlist' ) ) {
			return 'wish';
		}
		$t = self::page_type();
		return isset( $map[ $t ] ) ? $map[ $t ] : '';
	}

	/**
	 * Bottom bar type.
	 *
	 * @return string tabbar|actionbar
	 */
	public static function bar_type() {
		$id   = get_queried_object_id();
		$over = zt_page_setting( 'bottom_bar' );
		if ( in_array( $over, array( 'tabbar', 'actionbar', 'none' ), true ) ) {
			return $over;
		}
		if ( zt_opt( 'shell.actionbar', 1 ) && in_array( self::page_type(), array( 'product', 'cart', 'checkout' ), true ) ) {
			if ( 'checkout' === self::page_type() && zt_is_woo() && ( is_wc_endpoint_url( 'order-received' ) || is_wc_endpoint_url( 'order-pay' ) ) ) {
				return 'tabbar';
			}
			return 'actionbar';
		}
		return 'tabbar';
	}

	/**
	 * Body classes.
	 *
	 * @param array $classes Classes.
	 * @return array
	 */
	public static function body_class( $classes ) {
		if ( ! self::design_on() ) {
			return $classes;
		}
		$classes[] = 'zt-site';
		$classes[] = 'zt-page-' . self::page_type();
		$classes[] = ( is_user_logged_in() || ZT_Context::demo() ) ? 'zt-logged-in' : 'zt-guest';
		if ( self::shell_on() ) {
			$bar = self::bar_type();
			if ( 'none' !== $bar ) {
				$classes[] = 'zt-has-' . $bar;
			}
			$classes[] = 'zt-shell';
		}
		return $classes;
	}

	/**
	 * Title shown in the mobile app bar.
	 *
	 * @return string
	 */
	public static function title() {
		$id  = get_queried_object_id();
		$ttl = zt_page_setting( 'mobile_title' );
		if ( $ttl ) {
			return $ttl;
		}
		if ( is_singular() ) {
			$p = get_queried_object();
			if ( $p && 'product' === $p->post_type ) {
				$short = get_post_meta( $p->ID, '_zt_short_title', true );
				return $short ? $short : get_the_title( $p );
			}
			return get_the_title( $p );
		}
		if ( is_archive() ) {
			return wp_strip_all_tags( get_the_archive_title() );
		}
		if ( is_search() ) {
			return 'جستجو';
		}
		return zt_opt( 'general.brand_name', 'زیته' );
	}

	/**
	 * Output the whole shell.
	 */
	public static function render() {
		if ( ! self::shell_on() || is_admin() ) {
			return;
		}
		$brand = zt_opt( 'general.brand_name', 'زیته' );
		$count = zt_cart_count();
		$cnt   = zt_fa( $count );
		$vis   = $count > 0 ? '' : ' style="visibility:hidden"';
		echo '<div class="zt-w zt-shell-root">';

		if ( zt_opt( 'general.fx_progress', 1 ) ) {
			echo '<div class="zt-scrollprog" aria-hidden="true"><i data-zt-scrollprog></i></div>';
		}

		/* app bar */
		echo '<header class="zt-appbar" data-zt-appbar>';
		echo '<button class="zt-appbar__btn" data-zt-drawer-open aria-label="منو" data-zt-only="home">' . zt_icon( 'menu' ) . '</button>';
		echo '<button class="zt-appbar__btn" data-zt-back aria-label="بازگشت" data-zt-only="inner">' . zt_icon( 'chev-right' ) . '</button>';
		echo '<a class="zt-appbar__logo" href="' . esc_url( home_url( '/' ) ) . '" data-zt-only="home"><b>' . esc_html( $brand ) . '</b></a>';
		echo '<h2 class="zt-appbar__title" data-zt-appbar-title data-zt-only="inner">' . esc_html( self::title() ) . '</h2>';
		echo '<div class="zt-appbar__actions">';
		echo '<button class="zt-appbar__btn" data-zt-search-open aria-label="جستجو">' . zt_icon( 'search' ) . '</button>';
		echo '<a class="zt-appbar__btn" href="' . esc_url( zt_page_url( 'cart' ) ) . '" aria-label="سبد خرید">' . zt_icon( 'bag' ) . '<span class="zt-appbar__count" data-zt-cart-badge' . $vis . '>' . esc_html( $cnt ) . '</span></a>';
		echo '</div></header>';

		/* tab bar */
		$active = self::tab_key();
		echo '<nav class="zt-tabbar" data-zt-tabbar aria-label="ناوبری اصلی">';
		foreach ( (array) zt_opt( 'shell.tabs', array() ) as $t ) {
			$key = isset( $t['key'] ) ? $t['key'] : 'custom';
			$url = zt_url( isset( $t['url'] ) ? $t['url'] : '#' );
			$cls = 'zt-tab' . ( $active === $key ? ' zt-is-active' : '' );
			if ( 'cart' === $key ) {
				echo '<a href="' . esc_url( $url ) . '" class="' . esc_attr( $cls ) . ' zt-tab--fab" data-zt-tab="cart" aria-label="' . esc_attr( $t['label'] ) . '">';
				echo '<span class="zt-tab__fab">' . zt_icon( $t['icon'] ? $t['icon'] : 'bag' ) . '<i class="zt-tab__count" data-zt-cart-badge' . $vis . '>' . esc_html( $cnt ) . '</i></span>';
				echo '<span class="zt-tab__t">' . esc_html( $t['label'] ) . '</span></a>';
			} else {
				echo '<a href="' . esc_url( $url ) . '" class="' . esc_attr( $cls ) . '" data-zt-tab="' . esc_attr( $key ) . '">';
				echo '<span class="zt-tab__ic">' . zt_icon( $t['icon'] ) . '</span><span class="zt-tab__t">' . esc_html( $t['label'] ) . '</span></a>';
			}
		}
		echo '</nav>';

		/* drawer */
		echo '<div class="zt-scrim" data-zt-scrim hidden></div>';
		echo '<aside class="zt-drawer" data-zt-drawer hidden aria-label="' . esc_attr( 'منوی ' . $brand ) . '">';
		echo '<div class="zt-drawer__head"><a href="' . esc_url( zt_page_url( 'account' ) ) . '" class="zt-drawer__user"><span class="zt-drawer__ava">' . zt_icon( 'user' ) . '</span>';
		if ( is_user_logged_in() ) {
			$u = wp_get_current_user();
			echo '<span><b>' . esc_html( $u->display_name ) . '</b><small>مشاهده حساب کاربری</small></span>';
		} else {
			echo '<span><b>' . esc_html( zt_opt( 'shell.drawer_title' ) ) . '</b><small>' . esc_html( zt_opt( 'shell.drawer_sub' ) ) . '</small></span>';
		}
		echo '</a><button class="zt-drawer__close" data-zt-drawer-close aria-label="بستن">' . zt_icon( 'x' ) . '</button></div>';
		echo '<nav class="zt-drawer__nav">';
		foreach ( (array) zt_opt( 'shell.drawer_menu', array() ) as $m ) {
			$children = self::drawer_children( isset( $m['children'] ) ? $m['children'] : '' );
			if ( $children ) {
				echo '<div class="zt-drawer__acc" data-zt-drawer-acc><button>' . zt_icon( $m['icon'] ) . ' ' . esc_html( $m['label'] ) . ' ' . zt_icon( 'chev-down', array( 'class' => 'zt-chev' ) ) . '</button>';
				echo '<div class="zt-drawer__sub"><div>';
				foreach ( $children as $c ) {
					echo '<a href="' . esc_url( $c[1] ) . '">' . esc_html( $c[0] ) . '</a>';
				}
				echo '</div></div></div>';
			} else {
				echo '<a href="' . esc_url( zt_url( $m['url'] ) ) . '">' . zt_icon( $m['icon'] ) . ' ' . esc_html( $m['label'] ) . '</a>';
			}
		}
		echo '</nav><div class="zt-drawer__foot"><div class="zt-drawer__socials">';
		foreach ( (array) zt_opt( 'shell.drawer_socials', array() ) as $so ) {
			echo '<a href="' . esc_url( zt_url( $so['url'] ) ) . '" aria-label="' . esc_attr( $so['label'] ) . '" target="_blank" rel="noopener">' . zt_icon( $so['icon'] ) . '</a>';
		}
		echo '</div><p>' . esc_html( zt_opt( 'shell.drawer_footer' ) ) . '</p></div></aside>';

		/* search pane */
		echo '<div class="zt-searchpane" data-zt-searchpane hidden>';
		echo '<div class="zt-searchpane__bar"><button class="zt-appbar__btn" data-zt-search-close aria-label="بستن">' . zt_icon( 'chev-right' ) . '</button>';
		echo '<form class="zt-searchpane__field" role="search" action="' . esc_url( home_url( '/' ) ) . '" method="get" data-zt-search-form>' . zt_icon( 'search' );
		echo '<input type="search" name="s" placeholder="' . esc_attr( zt_opt( 'shell.search_placeholder' ) ) . '" data-zt-search-input enterkeyhint="search" autocomplete="off">';
		if ( zt_is_woo() ) {
			echo '<input type="hidden" name="post_type" value="product">';
		}
		echo '<button type="button" data-zt-search-clear aria-label="پاک کردن" hidden>' . zt_icon( 'x' ) . '</button></form></div>';
		echo '<div class="zt-searchpane__body"><section data-zt-search-idle>';
		echo '<h3>' . zt_icon( 'history' ) . ' ' . esc_html( zt_opt( 'shell.search_recent_title' ) ) . '</h3><div class="zt-chips" data-zt-search-recent data-default="' . esc_attr( wp_json_encode( zt_lines( zt_opt( 'shell.search_recent_default' ) ) ) ) . '">';
		foreach ( zt_lines( zt_opt( 'shell.search_recent_default' ) ) as $chip ) {
			echo '<button class="zt-chipbtn">' . esc_html( $chip ) . '</button>';
		}
		echo '</div><h3>' . zt_icon( 'fire' ) . ' ' . esc_html( zt_opt( 'shell.search_popular_title' ) ) . '</h3><div class="zt-chips">';
		foreach ( zt_lines( zt_opt( 'shell.search_popular' ) ) as $chip ) {
			echo '<button class="zt-chipbtn">' . esc_html( $chip ) . '</button>';
		}
		echo '</div></section><section data-zt-search-results hidden><h3 data-zt-search-count></h3><div class="zt-sresults" data-zt-search-list></div>';
		echo '<p class="zt-sempty" data-zt-search-empty hidden>' . esc_html( zt_opt( 'shell.search_empty' ) ) . '</p></section></div></div>';

		/* quick add sheet */
		echo '<div class="zt-sheet" id="zt-sheet-quickadd" data-zt-sheet hidden aria-label="افزودن سریع به سبد"><div class="zt-sheet__panel"><div class="zt-sheet__grab" data-zt-sheet-grab></div><div class="zt-sheet__body">';
		echo '<div class="zt-qa"><img data-zt-qa-img src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" alt=""><div><span class="zt-qa__cat" data-zt-qa-cat></span><b data-zt-qa-title></b><div class="zt-qa__price"><span data-zt-qa-price></span> <small>' . esc_html( zt_currency() ) . '</small></div></div></div>';
		echo '<div class="zt-qa__row"><span>تعداد</span><div class="zt-qty" data-zt-qa-qty data-zt-qty><button data-zt-step="-1" aria-label="کاهش">' . zt_icon( 'minus' ) . '</button><span>' . esc_html( zt_fa( 1 ) ) . '</span><button data-zt-step="1" aria-label="افزایش">' . zt_icon( 'plus' ) . '</button></div></div>';
		echo '<div class="zt-qa__note">' . zt_icon( 'truck-fast' ) . ' ' . esc_html( zt_opt( 'shell.qa_note' ) ) . '</div></div>';
		echo '<div class="zt-sheet__foot"><button class="zt-btn zt-btn--ghost" data-zt-sheet-close>انصراف</button><button class="zt-btn zt-btn--primary" data-zt-qa-add>' . zt_icon( 'bag' ) . ' افزودن به سبد</button></div></div></div>';

		/* advice sheet */
		echo '<div class="zt-sheet" id="zt-sheet-advice" data-zt-sheet hidden aria-label="' . esc_attr( zt_opt( 'shell.advice_title' ) ) . '"><div class="zt-sheet__panel"><div class="zt-sheet__grab" data-zt-sheet-grab></div><div class="zt-sheet__body">';
		echo '<h3 class="zt-sheetttl">' . esc_html( zt_opt( 'shell.advice_title' ) ) . '</h3><p style="font-size:13.5px;color:var(--zt-muted);line-height:2;margin-bottom:18px">' . esc_html( zt_opt( 'shell.advice_text' ) ) . '</p><div class="zt-advice">';
		foreach ( (array) zt_opt( 'shell.advice_items', array() ) as $a ) {
			echo '<a href="' . esc_url( zt_url( $a['url'] ) ) . '" target="_blank" rel="noopener"><i>' . zt_icon( $a['icon'] ) . '</i><span><b>' . esc_html( $a['title'] ) . '</b><small>' . esc_html( $a['sub'] ) . '</small></span><span class="zt-go">' . zt_icon( 'chev-left' ) . '</span></a>';
		}
		echo '</div></div><div class="zt-sheet__foot" style="grid-template-columns:1fr"><button class="zt-btn zt-btn--ghost" data-zt-sheet-close>بستن</button></div></div></div>';

		/* action bar */
		if ( 'actionbar' === self::bar_type() ) {
			self::render_actionbar();
		}

		echo '<div class="zt-toasts" data-zt-toasts aria-live="polite"></div>';
		if ( zt_opt( 'general.fx_totop', 1 ) ) {
			echo '<button class="zt-fab-top" data-zt-totop aria-label="بازگشت به بالا">' . zt_icon( 'arrow-up' ) . '</button>';
		}
		echo '<span class="zt-cart-count-data" data-count="' . esc_attr( $count ) . '" hidden></span>';
		echo '</div>';
	}

	/**
	 * Drawer sub-items: "label|url" lines or "auto" (product categories).
	 *
	 * @param string $text Lines.
	 * @return array [ [label, url], ... ]
	 */
	private static function drawer_children( $text ) {
		$text = trim( (string) $text );
		if ( '' === $text ) {
			return array();
		}
		$out = array();
		if ( 'auto' === $text && taxonomy_exists( 'product_cat' ) ) {
			$terms = get_terms(
				array(
					'taxonomy'   => 'product_cat',
					'hide_empty' => true,
					'parent'     => 0,
					'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
				)
			);
			foreach ( is_array( $terms ) ? $terms : array() as $t ) {
				$out[] = array( $t->name, get_term_link( $t ) );
			}
			return $out;
		}
		foreach ( zt_lines( $text ) as $l ) {
			$p     = array_map( 'trim', explode( '|', $l, 2 ) );
			$out[] = array( $p[0], zt_url( isset( $p[1] ) ? $p[1] : '#' ) );
		}
		return $out;
	}

	/**
	 * Sticky purchase bar (mobile) for product / cart / checkout.
	 */
	public static function render_actionbar() {
		$type = self::page_type();
		echo '<div class="zt-actionbar" data-zt-ab>';
		if ( 'product' === $type ) {
			$product = zt_is_woo() ? ZT_Context::product() : null;
			$pid     = $product ? $product->get_id() : 0;
			$on      = $pid && class_exists( 'ZT_Wishlist' ) && ZT_Wishlist::has( $pid );
			echo '<button class="zt-actionbar__wish' . ( $on ? ' zt-is-on' : '' ) . '" data-zt-wish="' . esc_attr( $pid ) . '" aria-label="افزودن به علاقه‌مندی">' . zt_icon( $on ? 'heart-fill' : 'heart' ) . '</button>';
			echo '<div class="zt-actionbar__price">';
			if ( $product ) {
				$reg  = (float) $product->get_regular_price();
				$sale = (float) $product->get_price();
				if ( $product->is_on_sale() && $reg > $sale && ! $product->is_type( 'variable' ) ) {
					echo '<del>' . esc_html( zt_money( $reg, true ) ) . '</del>';
				}
				echo '<b data-zt-ab-price>' . wp_kses_post( zt_price( $sale, 'i' ) ) . '</b>';
			} else {
				echo '<del>' . esc_html( zt_fa( '450,000' ) ) . '</del><b>' . esc_html( zt_fa( '385,000' ) ) . ' <i>' . esc_html( zt_currency() ) . '</i></b>';
			}
			echo '</div>';
			$disabled = $product && ! $product->is_purchasable() ? ' disabled' : '';
			echo '<button class="zt-btn zt-btn--primary" data-zt-ab-add="' . esc_attr( $pid ) . '"' . $disabled . '>' . zt_icon( 'bag' ) . ' ' . esc_html( zt_opt( 'shell.ab_product' ) ) . '</button>';
		} else {
			$total = 0;
			if ( ZT_Context::demo() ) {
				$total = ( 'cart' === $type ? 1910000 : 1485000 ) * max( 1, (float) zt_opt( 'general.price_divisor', 1 ) );
			} elseif ( zt_is_woo() && WC()->cart ) {
				WC()->cart->calculate_totals();
				$total = 'cart' === $type ? (float) WC()->cart->get_total( 'edit' ) - (float) WC()->cart->get_shipping_total() - (float) WC()->cart->get_shipping_tax() : (float) WC()->cart->get_total( 'edit' );
			}
			echo '<div class="zt-actionbar__price"><small>' . esc_html( zt_opt( 'shell.ab_total' ) ) . '</small><b><span data-zt-ab-total>' . esc_html( zt_money( $total, true ) ) . '</span> <i>' . esc_html( zt_currency() ) . '</i></b></div>';
			if ( 'cart' === $type ) {
				echo '<a class="zt-btn zt-btn--primary" href="' . esc_url( zt_page_url( 'checkout' ) ) . '" data-zt-ab-checkout>' . esc_html( zt_opt( 'shell.ab_cart' ) ) . ' ' . zt_icon( 'arrow-left' ) . '</a>';
			} else {
				echo '<button type="button" class="zt-btn zt-btn--primary" data-zt-ab-pay>' . esc_html( zt_opt( 'shell.ab_checkout' ) ) . ' ' . zt_icon( 'arrow-left' ) . '</button>';
			}
		}
		echo '</div>';
	}
}
