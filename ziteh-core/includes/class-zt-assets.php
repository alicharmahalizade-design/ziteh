<?php
/**
 * Styles / scripts, design tokens and theme-conflict handling.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_Assets
 */
class ZT_Assets {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ), 20 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'dequeue_theme' ), 999 );
		add_action( 'elementor/editor/after_enqueue_styles', array( __CLASS__, 'editor_styles' ) );
		add_action( 'elementor/preview/enqueue_styles', array( __CLASS__, 'enqueue' ) );

		// Hello Elementor — switch off every default style and its header/footer/title.
		add_filter( 'hello_elementor_enqueue_style', array( __CLASS__, 'hello_off' ) );
		add_filter( 'hello_elementor_enqueue_theme_style', array( __CLASS__, 'hello_off' ) );
		add_filter( 'hello_elementor_header_footer', array( __CLASS__, 'hello_hf_off' ) );
		add_filter( 'hello_elementor_page_title', array( __CLASS__, 'hello_hf_off' ) );
		add_filter( 'hello_elementor_add_description_meta_tag', '__return_false' );

		// WooCommerce default styles.
		add_filter( 'woocommerce_enqueue_styles', array( __CLASS__, 'woo_styles' ), 99 );

		add_action( 'zt_settings_saved', array( __CLASS__, 'on_settings_saved' ) );
	}

	/**
	 * Hello style filters.
	 *
	 * @param bool $enabled Enabled.
	 * @return bool
	 */
	public static function hello_off( $enabled ) {
		return zt_opt( 'general.disable_hello_styles', 1 ) ? false : $enabled;
	}

	/**
	 * Hello header/footer/page-title filters.
	 *
	 * @param bool $enabled Enabled.
	 * @return bool
	 */
	public static function hello_hf_off( $enabled ) {
		return zt_opt( 'general.disable_hello_header_footer', 1 ) ? false : $enabled;
	}

	/**
	 * WooCommerce default stylesheets.
	 *
	 * @param array $styles Styles.
	 * @return array
	 */
	public static function woo_styles( $styles ) {
		return zt_opt( 'general.disable_wc_styles', 1 ) ? array() : $styles;
	}

	/**
	 * Version string that changes whenever a file changes (cache busting).
	 *
	 * @param string $rel Relative path.
	 * @return string
	 */
	public static function ver( $rel ) {
		$f = ZT_PATH . $rel;
		return ZT_VERSION . ( file_exists( $f ) ? '.' . filemtime( $f ) : '' );
	}

	/**
	 * Register assets.
	 */
	public static function register() {
		$deps = array();
		if ( wp_style_is( 'elementor-frontend', 'registered' ) ) {
			// Elementor's frontend CSS must always print BEFORE ours (its generic
			// ".elementor img{height:auto}" would otherwise override the design).
			$deps[] = 'elementor-frontend';
		}
		wp_register_style( 'zt-globals', ZT_URL . 'assets/css/zt-globals.css', array(), self::ver( 'assets/css/zt-globals.css' ) );
		wp_register_style( 'zt-core', ZT_URL . 'assets/css/zt-core.css', array_merge( array( 'zt-globals' ), $deps ), self::ver( 'assets/css/zt-core.css' ) );
		wp_register_style( 'zt-extra', ZT_URL . 'assets/css/zt-extra.css', array( 'zt-core' ), self::ver( 'assets/css/zt-extra.css' ) );
		wp_register_style( 'zt-icons', ZT_URL . 'assets/css/zt-icons.css', array(), self::ver( 'assets/css/zt-icons.css' ) );
		wp_register_style( 'zt-vt', ZT_URL . 'assets/css/zt-vt.css', array( 'zt-core' ), self::ver( 'assets/css/zt-vt.css' ) );

		wp_register_script( 'zt-app', ZT_URL . 'assets/js/zt-app.js', array( 'jquery' ), self::ver( 'assets/js/zt-app.js' ), true );
	}

	/**
	 * Enqueue on the front end (and the Elementor preview).
	 */
	public static function enqueue() {
		if ( ! wp_style_is( 'zt-core', 'registered' ) ) {
			if ( did_action( 'elementor/loaded' ) && ! wp_style_is( 'elementor-frontend', 'registered' ) && isset( \Elementor\Plugin::$instance->frontend ) ) {
				\Elementor\Plugin::$instance->frontend->register_styles();
			}
			self::register();
		}
		wp_enqueue_style( 'zt-globals' );
		wp_enqueue_style( 'zt-core' );
		wp_enqueue_style( 'zt-extra' );
		wp_enqueue_style( 'zt-icons' );
		if ( zt_opt( 'general.fx_transitions', 1 ) && ! zt_is_editor() ) {
			wp_enqueue_style( 'zt-vt' );
		}
		wp_add_inline_style( 'zt-core', self::tokens_css() );

		if ( zt_is_woo() && ! is_admin() ) {
			wp_enqueue_script( 'wc-cart-fragments' );
		}
		wp_enqueue_script( 'zt-app' );
		wp_localize_script( 'zt-app', 'ZT', self::js_data() );
	}

	/**
	 * Dequeue Hello Elementor styles by handle (belt and braces for every Hello version).
	 */
	public static function dequeue_theme() {
		if ( zt_opt( 'general.disable_hello_styles', 1 ) ) {
			foreach ( array( 'hello-elementor', 'hello-elementor-theme-style', 'hello-elementor-header-footer', 'hello-elementor-child-style' ) as $h ) {
				if ( 'hello-elementor-child-style' === $h ) {
					continue; // never touch a user's child theme.
				}
				wp_dequeue_style( $h );
			}
		}
		if ( zt_opt( 'general.disable_wc_styles', 1 ) ) {
			foreach ( array( 'woocommerce-general', 'woocommerce-layout', 'woocommerce-smallscreen', 'woocommerce-blocktheme' ) as $h ) {
				wp_dequeue_style( $h );
			}
		}
	}

	/**
	 * Editor panel styles (icon picker + category look).
	 */
	public static function editor_styles() {
		wp_enqueue_style( 'zt-icons', ZT_URL . 'assets/css/zt-icons.css', array(), self::ver( 'assets/css/zt-icons.css' ) );
		wp_enqueue_style( 'zt-editor', ZT_URL . 'assets/css/zt-editor.css', array(), self::ver( 'assets/css/zt-editor.css' ) );
	}

	/**
	 * Design tokens from settings -> :root custom properties.
	 *
	 * @return string
	 */
	public static function tokens_css() {
		$css  = '';
		$vars = array();
		foreach ( ZT_Settings::schema()['general']['sections'] as $sec ) {
			foreach ( $sec['fields'] as $f ) {
				if ( ! empty( $f['var'] ) ) {
					$v = zt_opt( 'general.' . $f['key'], $f['default'] );
					if ( $v && strtolower( $v ) !== strtolower( $f['default'] ) ) {
						$vars[] = '--zt-' . $f['var'] . ':' . $v;
					}
				}
			}
		}
		$c = (int) zt_opt( 'general.container', 1440 );
		if ( $c && 1440 !== $c ) {
			$vars[] = '--zt-container:' . $c . 'px';
		}
		$g = zt_opt( 'general.gutter', 24 );
		if ( '' !== $g && 24 !== (int) $g ) {
			$vars[] = '--zt-gutter:' . (int) $g . 'px';
		}
		if ( 'theme' === zt_opt( 'general.font', 'dana' ) ) {
			$vars[] = '--zt-font:inherit';
		}
		if ( $vars ) {
			$css .= ':root{' . implode( ';', $vars ) . '}';
		}
		$n = (int) zt_opt( 'general.container_narrow', 1240 );
		if ( $n && 1240 !== $n ) {
			$css .= '.zt-container--narrow{max-width:' . $n . 'px}';
		}
		return $css;
	}

	/**
	 * Data for the front-end script.
	 *
	 * @return array
	 */
	public static function js_data() {
		$count = 0;
		if ( zt_is_woo() && WC()->cart ) {
			$count = WC()->cart->get_cart_contents_count();
		}
		return array(
			'ajax'     => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'zt-ajax' ),
			'woo'      => zt_is_woo(),
			'editor'   => zt_is_editor(),
			'count'    => $count,
			'loggedIn' => is_user_logged_in(),
			'fa'       => (bool) zt_opt( 'general.persian_digits', 1 ),
			'currency' => zt_currency(),
			'bp'       => 900,
			'urls'     => array(
				'home'     => zt_page_url( 'home' ),
				'shop'     => zt_page_url( 'shop' ),
				'cart'     => zt_page_url( 'cart' ),
				'checkout' => zt_page_url( 'checkout' ),
				'account'  => zt_page_url( 'account' ),
			),
			'fx'       => array(
				'reveal'   => (bool) zt_opt( 'general.fx_reveal', 1 ),
				'ripple'   => (bool) zt_opt( 'general.fx_ripple', 1 ),
				'skeleton' => (bool) zt_opt( 'general.fx_skeleton', 1 ),
				'toasts'   => (bool) zt_opt( 'general.fx_option_toasts', 1 ),
				'hideBar'  => (bool) zt_opt( 'shell.hide_appbar_on_scroll', 1 ),
			),
			'i18n'     => array(
				'added'       => zt_opt( 'shell.toast_added', '{n} عدد به سبد خرید اضافه شد' ),
				'viewCart'    => zt_opt( 'shell.toast_view_cart', 'مشاهده سبد' ),
				'cleared'     => zt_opt( 'cart.toast_cleared', 'سبد خرید خالی شد' ),
				'updated'     => zt_opt( 'cart.toast_updated', 'سبد خرید به‌روزرسانی شد' ),
				'wishOn'      => 'به لیست علاقه‌مندی اضافه شد',
				'wishOff'     => 'از لیست علاقه‌مندی حذف شد',
				'copied'      => 'شماره سفارش کپی شد',
				'results'     => '{n} نتیجه برای «{q}»',
				'selected'    => '{label}: {name} انتخاب شد',
				'error'       => 'خطایی رخ داد؛ دوباره تلاش کنید.',
				'items'       => '{n} کالا',
				'payDetails'  => 'جزئیات پرداخت',
				'close'       => 'بستن',
				'order'       => zt_opt( 'shell.ab_cart', 'ثبت سفارش' ),
				'city'        => 'انتخاب شهر',
				'peyk'        => '{name} برای شما انتخاب شد',
				'emptyCity'   => 'ابتدا استان را انتخاب کنید',
				'removed'     => 'از سبد خرید حذف شد',
			),
		);
	}

	/**
	 * After settings save: sync Elementor defaults + bust caches.
	 *
	 * @param string $tab Tab.
	 */
	public static function on_settings_saved( $tab ) {
		if ( 'general' === $tab && zt_opt( 'general.disable_elementor_defaults', 1 ) ) {
			update_option( 'elementor_disable_color_schemes', 'yes' );
			update_option( 'elementor_disable_typography_schemes', 'yes' );
		}
		if ( did_action( 'elementor/loaded' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}
		if ( function_exists( 'wp_cache_flush' ) ) {
			wp_cache_flush();
		}
		update_option( 'zt_flush_rewrite', 1 );
	}
}
