<?php
/**
 * Global: desktop header (top bar + navigation).
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Header
 */
class ZT_W_Header extends ZT_Widget_Base {

	protected $zt_group = 'global';
	protected $zt_icon  = 'eicon-header';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-header';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'هدر زیته (دسکتاپ)';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c_layout', 'چیدمان' );
		$this->ctl(
			'layout',
			'select',
			'چیدمان',
			'auto',
			array(
				'options'     => array(
					'auto'  => 'خودکار (صفحه اصلی / داخلی)',
					'home'  => 'صفحه اصلی: جستجو وسط، لوگو در منو',
					'inner' => 'داخلی: لوگو وسط، منو وسط‌چین',
				),
				'description' => 'در تنظیمات هر صفحه (زیته) هم قابل تغییر است.',
			)
		);
		$this->ctl( 'notice', 'notice', '', 'روی موبایل (زیر ۹۰۰ پیکسل) این هدر پنهان و اپ‌بار اپلیکیشنی نمایش داده می‌شود (تنظیمات: زیته ← اپلیکیشن موبایل).' );
		$this->end_controls_section();

		$this->section( 'c_logo', 'لوگو' );
		$this->ctl( 'logo_type', 'select', 'نوع لوگو', 'text', array( 'options' => array( 'text' => 'متنی', 'image' => 'تصویر' ) ) );
		$this->ctl( 'logo_text', 'text', 'متن لوگو (خالی = نام برند در تنظیمات)', '', array( 'condition' => array( 'logo_type' => 'text' ) ) );
		$this->ctl( 'logo_sprout', 'switch', 'نمایش جوانه روی لوگو', true, array( 'condition' => array( 'logo_type' => 'text' ) ) );
		$this->ctl( 'logo_img', 'media', 'تصویر لوگو', '', array( 'condition' => array( 'logo_type' => 'image' ) ) );
		$this->ctl( 'logo_link', 'url', 'لینک لوگو', '{{home}}' );
		$this->end_controls_section();

		$this->section( 'c_account', 'حساب کاربری' );
		$this->ctl( 'acc_icon', 'icon', 'آیکون', 'user' );
		$this->ctl( 'acc_guest', 'text', 'متن کاربر مهمان', 'ورود / ثبت‌نام' );
		$this->ctl( 'acc_user', 'text', 'متن کاربر واردشده', 'حساب کاربری من' );
		$this->ctl( 'acc_link', 'url', 'لینک', '{{account}}' );
		$this->ctl( 'acc_chip', 'switch', 'نمایش کارت خوشامد در صفحات حساب کاربری', true );
		$this->ctl( 'acc_chip_sub', 'text', 'زیرعنوان کارت خوشامد', 'خوش آمدید' );
		$this->end_controls_section();

		$this->section( 'c_search', 'جستجو' );
		$this->ctl( 'search_show', 'switch', 'نمایش جستجو', true );
		$this->ctl( 'search_ph', 'text', 'متن راهنما', 'جستجو در فروشگاه...' );
		$this->ctl( 'search_products', 'switch', 'فقط جستجو در محصولات', true );
		$this->end_controls_section();

		$this->section( 'c_cart', 'سبد خرید' );
		$this->ctl( 'cart_icon', 'icon', 'آیکون', 'bag' );
		$this->ctl( 'cart_label', 'text', 'متن (فقط چیدمان صفحه اصلی)', 'سبد خرید' );
		$this->ctl( 'cart_link', 'url', 'لینک', '{{cart}}' );
		$this->end_controls_section();

		$this->section( 'c_menu', 'منو' );
		$menus = array( '' => '— منوی سفارشی پایین —' );
		foreach ( wp_get_nav_menus() as $m ) {
			$menus[ $m->term_id ] = $m->name;
		}
		$this->ctl( 'menu_wp', 'select', 'منوی وردپرس', '', array( 'options' => $menus ) );
		$this->repeater(
			'menu',
			'آیتم‌ها',
			array(
				array( 'label', 'text', 'عنوان', 'آیتم' ),
				array( 'url', 'url', 'لینک', '#' ),
				array( 'children', 'textarea', 'زیرمنو (هر خط: عنوان|لینک — «auto» = دسته‌های محصول)', '' ),
			),
			array(
				array( 'label' => 'خانه', 'url' => '{{home}}', 'children' => '' ),
				array( 'label' => 'فروشگاه', 'url' => '{{shop}}', 'children' => '' ),
				array( 'label' => 'دسته‌بندی‌ها', 'url' => '#', 'children' => 'auto' ),
				array( 'label' => 'وبلاگ', 'url' => '{{blog}}', 'children' => '' ),
				array( 'label' => 'درباره ما', 'url' => '{{about}}', 'children' => '' ),
				array( 'label' => 'تماس با ما', 'url' => '{{contact}}', 'children' => '' ),
			),
			'{{{ label }}}',
			array( 'condition' => array( 'menu_wp' => '' ) )
		);
		$this->end_controls_section();

		$this->style_section(
			'bar',
			'نوار بالا',
			array(
				array( 'hdr', 'هدر', '.zt-header', array( 'bg', 'shadow' ) ),
				array( 'top', 'ردیف بالا', '.zt-header__top', array( 'height', 'border_color', 'gap' ) ),
				array( 'logo', 'لوگو', '.zt-logo b', array( 'typo', 'color' ) ),
				array( 'logoimg', 'تصویر لوگو', '.zt-logo img', array( 'height', 'width' ) ),
				array( 'hicon', 'حساب کاربری', '.zt-hicon', self::fx( 'link' ) ),
				array( 'search', 'جستجو', '.zt-search', array( 'bg', 'border', 'radius', 'height', 'max_width', 'padding' ) ),
				array( 'cart', 'سبد', '.zt-cart-btn', array( 'color', 'hover_color' ) ),
				array( 'count', 'شمارنده سبد', '.zt-cart-btn .zt-count', array( 'typo', 'color', 'bg', 'size' ) ),
			)
		);
		$this->style_section(
			'nav',
			'منو',
			array(
				array( 'navbar', 'نوار منو', '.zt-header__nav', array( 'height', 'bg', 'border_color' ) ),
				array( 'nav', 'چینش', '.zt-nav', array( 'gap', 'justify' ) ),
				array( 'item', 'آیتم‌ها', '.zt-nav > a, .zt-nav__dd > a', array( 'typo', 'color', 'hover_color' ) ),
				array( 'active', 'آیتم فعال', '.zt-nav a.zt-is-active', array( 'color' ) ),
				array( 'line', 'خط زیر آیتم فعال', '.zt-nav a.zt-is-active::after', array( 'bg', 'height' ) ),
			)
		);
	}

	/**
	 * Resolve layout.
	 *
	 * @param array $s Settings.
	 * @return string
	 */
	private function layout( $s ) {
		$over = zt_page_setting( 'header_layout' );
		if ( in_array( $over, array( 'home', 'inner' ), true ) ) {
			return $over;
		}
		if ( 'auto' !== $s['layout'] ) {
			return $s['layout'];
		}
		return 'home' === ZT_Shell::page_type() ? 'home' : 'inner';
	}

	/**
	 * Menu items [label, url, children[], active].
	 *
	 * @param array $s Settings.
	 * @return array
	 */
	private function items( $s ) {
		$out  = array();
		$here = trailingslashit( strtok( ( is_ssl() ? 'https://' : 'http://' ) . ( isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '' ) . ( isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '' ), '?' ) );
		if ( ! empty( $s['menu_wp'] ) ) {
			$tree = array();
			foreach ( (array) wp_get_nav_menu_items( (int) $s['menu_wp'] ) as $mi ) {
				if ( ! $mi->menu_item_parent ) {
					$tree[ $mi->ID ] = array( $mi->title, $mi->url, array(), in_array( 'current-menu-item', (array) $mi->classes, true ) || trailingslashit( $mi->url ) === $here );
				}
			}
			foreach ( (array) wp_get_nav_menu_items( (int) $s['menu_wp'] ) as $mi ) {
				if ( $mi->menu_item_parent && isset( $tree[ $mi->menu_item_parent ] ) ) {
					$tree[ $mi->menu_item_parent ][2][] = array( $mi->title, $mi->url );
				}
			}
			return array_values( $tree );
		}
		foreach ( (array) $s['menu'] as $m ) {
			$url      = zt_url( $m['url'] );
			$children = array();
			$ch       = trim( (string) $m['children'] );
			if ( 'auto' === $ch && taxonomy_exists( 'product_cat' ) ) {
				$terms = get_terms(
					array(
						'taxonomy'   => 'product_cat',
						'hide_empty' => false,
						'parent'     => 0,
						'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
						'number'     => 20,
					)
				);
				foreach ( is_array( $terms ) ? $terms : array() as $t ) {
					$children[] = array( $t->name, get_term_link( $t ) );
				}
			} elseif ( '' !== $ch && 'auto' !== $ch ) {
				foreach ( zt_lines( $ch ) as $l ) {
					$p          = array_map( 'trim', explode( '|', $l, 2 ) );
					$children[] = array( $p[0], zt_url( isset( $p[1] ) ? $p[1] : '#' ) );
				}
			}
			$active = '#' !== $url && trailingslashit( strtok( $url, '?' ) ) === $here;
			$out[]  = array( $m['label'], $url, $children, $active, '' !== $ch );
		}
		return $out;
	}

	/**
	 * Logo markup.
	 *
	 * @param array  $s     Settings.
	 * @param string $style Inline style.
	 * @return string
	 */
	private function logo( $s, $style = '' ) {
		$attr = zt_link_attrs( $s['logo_link'] ) . ' class="zt-logo"' . ( $style ? ' style="' . esc_attr( $style ) . '"' : '' );
		if ( 'image' === $s['logo_type'] && ! empty( $s['logo_img']['url'] ) ) {
			return '<a' . $attr . '>' . zt_img( $s['logo_img'], zt_opt( 'general.brand_name' ), array( 'loading' => 'eager' ) ) . '</a>';
		}
		$global = zt_opt( 'general.logo_image' );
		if ( 'text' === $s['logo_type'] && '' === $s['logo_text'] && $global ) {
			return '<a' . $attr . '><img src="' . esc_url( $global ) . '" alt="' . esc_attr( zt_opt( 'general.brand_name' ) ) . '"></a>';
		}
		$txt = '' !== $s['logo_text'] ? $s['logo_text'] : zt_opt( 'general.brand_name', 'زیته' );
		return '<a' . $attr . '><b>' . esc_html( $txt ) . '</b>' . ( 'yes' === $s['logo_sprout'] ? zt_icon( 'sprout', array( 'class' => 'zt-sprout' ) ) : '' ) . '</a>';
	}

	/**
	 * Search form.
	 *
	 * @param array  $s     Settings.
	 * @param string $style Inline style.
	 * @return string
	 */
	private function search( $s, $style = '' ) {
		if ( 'yes' !== $s['search_show'] ) {
			return '';
		}
		$out  = '<form class="zt-search" role="search" method="get" action="' . esc_url( home_url( '/' ) ) . '"' . ( $style ? ' style="' . esc_attr( $style ) . '"' : '' ) . '>';
		$out .= zt_icon( 'search' ) . '<input type="search" name="s" enterkeyhint="search" placeholder="' . esc_attr( $s['search_ph'] ) . '" value="' . esc_attr( get_search_query() ) . '">';
		if ( 'yes' === $s['search_products'] && zt_is_woo() ) {
			$out .= '<input type="hidden" name="post_type" value="product">';
		}
		return $out . '</form>';
	}

	/**
	 * Cart button.
	 *
	 * @param array  $s     Settings.
	 * @param bool   $label With label.
	 * @param string $style Inline style.
	 * @return string
	 */
	private function cart( $s, $label, $style = '' ) {
		$count = ( zt_is_woo() && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
		$out   = '<a' . zt_link_attrs( $s['cart_link'] ) . ' class="zt-cart-btn"' . ( $style ? ' style="' . esc_attr( $style ) . '"' : '' ) . '>';
		$out  .= '<span class="zt-count" data-zt-cart-badge data-zt-keep>' . esc_html( zt_fa( $count ) ) . '</span>' . zt_icon( $s['cart_icon'] );
		if ( $label && '' !== $s['cart_label'] ) {
			$out .= '<span>' . esc_html( $s['cart_label'] ) . '</span>';
		}
		return $out . '</a>';
	}

	/**
	 * Account link / user chip.
	 *
	 * @param array $s    Settings.
	 * @param bool  $chip Show chip.
	 * @return string
	 */
	private function account( $s, $chip ) {
		if ( $chip ) {
			$u    = wp_get_current_user();
			$name = $u->first_name ? $u->first_name : $u->display_name;
			if ( ! is_user_logged_in() ) {
				$name = 'نرگس';
			}
			$greet = str_replace( '{name}', $name, zt_opt( 'tracking.greeting', 'سلام {name} عزیز' ) );
			return '<a' . zt_link_attrs( $s['acc_link'] ) . ' class="zt-userchip"><span class="zt-ava">' . zt_icon( 'user' ) . '</span><span class="zt-txt"><b>' . esc_html( $greet ) . '</b><span>' . esc_html( $s['acc_chip_sub'] ) . '</span></span>' . zt_icon( 'chev-down', array( 'class' => 'zt-chev' ) ) . '</a>';
		}
		$txt = is_user_logged_in() ? $s['acc_user'] : $s['acc_guest'];
		if ( 'cart' === ZT_Shell::page_type() && ! is_user_logged_in() && $this->is_editor() ) {
			$txt = $s['acc_user'];
		}
		return '<a' . zt_link_attrs( $s['acc_link'] ) . ' class="zt-hicon">' . zt_icon( $s['acc_icon'] ) . '<span>' . esc_html( $txt ) . '</span></a>';
	}

	/**
	 * Navigation links.
	 *
	 * @param array $items Items.
	 * @return string
	 */
	private function nav_links( $items ) {
		$out = '';
		foreach ( $items as $it ) {
			$cls = $it[3] ? ' class="zt-is-active"' : '';
			if ( ! empty( $it[2] ) || ! empty( $it[4] ) ) {
				$out .= '<span class="zt-nav__dd"><a href="' . esc_url( $it[1] ) . '"' . $cls . '>' . esc_html( $it[0] ) . ' ' . zt_icon( 'chev-down' ) . '</a>';
				if ( ! empty( $it[2] ) ) {
					$out .= '<span class="zt-nav__menu">';
					foreach ( $it[2] as $c ) {
						$out .= '<a href="' . esc_url( $c[1] ) . '">' . esc_html( $c[0] ) . '</a>';
					}
					$out .= '</span>';
				}
				$out .= '</span>';
			} else {
				$out .= '<a href="' . esc_url( $it[1] ) . '"' . $cls . '>' . esc_html( $it[0] ) . '</a>';
			}
		}
		return $out;
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$layout = $this->layout( $s );
		$chip   = 'yes' === $s['acc_chip'] && 'panel' === ZT_Shell::page_type() && ( is_user_logged_in() || $this->is_editor() || ! zt_is_woo() );
		$items  = $this->items( $s );

		echo '<header class="zt-header">';
		echo '<div class="zt-container"><div class="zt-header__top"' . ( $chip ? ' style="height:86px"' : '' ) . '>';
		if ( 'home' === $layout ) {
			echo $this->account( $s, $chip ); // phpcs:ignore
			echo '<div class="zt-spacer"></div>';
			echo $this->search( $s ); // phpcs:ignore
			echo '<div class="zt-spacer"></div>';
			echo $this->cart( $s, true ); // phpcs:ignore
		} else {
			echo $this->account( $s, $chip ); // phpcs:ignore
			echo '<div class="zt-spacer"></div>';
			echo $this->logo( $s ); // phpcs:ignore
			echo '<div class="zt-spacer"></div>';
			echo $this->search( $s, 'max-width:' . ( $chip ? 290 : 270 ) . 'px' ); // phpcs:ignore
			echo $this->cart( $s, false, 'margin-inline-start:26px' ); // phpcs:ignore
		}
		echo '</div></div>';
		echo '<div class="zt-header__nav"><div class="zt-container" style="height:100%">';
		if ( 'home' === $layout ) {
			echo '<nav class="zt-nav zt-nav--start" style="justify-content:flex-start">';
			echo $this->logo( $s, 'margin-left:34px' ); // phpcs:ignore
		} else {
			echo '<nav class="zt-nav zt-nav--center">';
		}
		echo $this->nav_links( $items ); // phpcs:ignore
		echo '</nav></div></div></header>';
	}
}
