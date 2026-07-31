<?php
/**
 * Theme setup, assets and Elementor integration.
 *
 * @package Ziteh_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Ziteh_Theme.
 */
class Ziteh_Theme {

	/**
	 * Singleton instance.
	 *
	 * @var Ziteh_Theme|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Ziteh_Theme
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'after_setup_theme', array( $this, 'setup' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );
		add_action( 'widgets_init', array( $this, 'sidebars' ) );
		add_action( 'elementor/theme/register_locations', array( $this, 'register_locations' ) );
		add_filter( 'body_class', array( $this, 'body_class' ) );
		add_filter( 'excerpt_more', array( $this, 'excerpt_more' ) );
		add_filter( 'nav_menu_link_attributes', array( $this, 'menu_link_attributes' ), 10, 3 );
	}

	/**
	 * Theme supports.
	 */
	public function setup() {
		load_theme_textdomain( 'ziteh-theme', ZITEH_THEME_DIR . '/languages' );

		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'align-wide' );
		add_theme_support( 'editor-styles' );
		add_theme_support( 'custom-logo', array( 'height' => 64, 'width' => 220, 'flex-height' => true, 'flex-width' => true ) );
		add_theme_support(
			'html5',
			array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
		);

		// WooCommerce. The Ziteh product widgets replace the native product
		// template, but declaring support keeps WooCommerce from warning and
		// keeps cart, checkout and account pages working normally.
		add_theme_support( 'woocommerce' );
		add_theme_support( 'wc-product-gallery-zoom' );
		add_theme_support( 'wc-product-gallery-lightbox' );
		add_theme_support( 'wc-product-gallery-slider' );

		/*
		 * A private flag the Ziteh Core plugin looks for. When this theme is
		 * active the plugin can skip the aggressive `!important` container
		 * neutralisation it needs under third-party themes: there is nothing
		 * hostile left to neutralise, and those overrides cost specificity that
		 * makes later customisation harder.
		 */
		add_theme_support( 'ziteh-native' );

		register_nav_menus(
			array(
				'primary' => __( 'منوی اصلی', 'ziteh-theme' ),
				'footer'  => __( 'منوی فوتر', 'ziteh-theme' ),
			)
		);

		add_theme_support(
			'editor-color-palette',
			array(
				array( 'name' => __( 'سبز زیته', 'ziteh-theme' ), 'slug' => 'ziteh-primary', 'color' => '#6f7d5f' ),
				array( 'name' => __( 'سبز تیره', 'ziteh-theme' ), 'slug' => 'ziteh-primary-dark', 'color' => '#55603a' ),
				array( 'name' => __( 'کرم', 'ziteh-theme' ), 'slug' => 'ziteh-cream', 'color' => '#faf9f6' ),
				array( 'name' => __( 'زغالی', 'ziteh-theme' ), 'slug' => 'ziteh-ink', 'color' => '#333830' ),
				array( 'name' => __( 'سفید', 'ziteh-theme' ), 'slug' => 'ziteh-white', 'color' => '#ffffff' ),
			)
		);

		// A sensible content width keeps oEmbeds and wide images from
		// overflowing the fallback templates.
		if ( ! isset( $GLOBALS['content_width'] ) ) {
			$GLOBALS['content_width'] = 760;
		}
	}

	/**
	 * Front-end assets.
	 */
	public function assets() {
		wp_enqueue_style( 'ziteh-theme', ZITEH_THEME_URI . '/assets/css/theme.css', array(), ZITEH_THEME_VERSION );

		// The plugin ships Vazirmatn. Only load a fallback face when it is not
		// active, so the font is never fetched twice.
		if ( ! defined( 'ZITEH_EL_VERSION' ) ) {
			wp_add_inline_style( 'ziteh-theme', ':root{--ziteh-font:"Vazirmatn",Tahoma,"Segoe UI",sans-serif}' );
		}

		wp_enqueue_script( 'ziteh-theme', ZITEH_THEME_URI . '/assets/js/theme.js', array(), ZITEH_THEME_VERSION, true );

		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
	}

	/**
	 * Footer widget areas.
	 */
	public function sidebars() {
		for ( $i = 1; $i <= 3; $i++ ) {
			register_sidebar(
				array(
					'name'          => sprintf( /* translators: %d: column number */ __( 'ستون فوتر %d', 'ziteh-theme' ), $i ),
					'id'            => 'ziteh-footer-' . $i,
					'description'   => __( 'فقط زمانی دیده می‌شود که فوتر المنتوری نساخته باشید.', 'ziteh-theme' ),
					'before_widget' => '<section id="%1$s" class="ziteh-site-widget %2$s">',
					'after_widget'  => '</section>',
					'before_title'  => '<h2 class="ziteh-site-widget__title">',
					'after_title'   => '</h2>',
				)
			);
		}
	}

	/**
	 * Elementor Theme Builder locations.
	 *
	 * @param \ElementorPro\Modules\ThemeBuilder\Classes\Locations_Manager $manager Locations manager.
	 */
	public function register_locations( $manager ) {
		$manager->register_all_core_location();
	}

	/**
	 * Body classes.
	 *
	 * @param string[] $classes Existing classes.
	 * @return string[]
	 */
	public function body_class( $classes ) {
		$classes[] = 'ziteh-site';

		if ( ! is_active_sidebar( 'ziteh-footer-1' ) ) {
			$classes[] = 'ziteh-site--no-footer-widgets';
		}

		return $classes;
	}

	/**
	 * Ellipsis instead of the default bracketed hellip.
	 *
	 * @return string
	 */
	public function excerpt_more() {
		return '…';
	}

	/**
	 * Mark the current menu item for CSS and assistive tech.
	 *
	 * @param array    $atts Link attributes.
	 * @param WP_Post  $item Menu item.
	 * @param stdClass $args Menu args.
	 * @return array
	 */
	public function menu_link_attributes( $atts, $item, $args ) {
		unset( $args );

		if ( ! empty( $item->current ) ) {
			$atts['aria-current'] = 'page';
		}

		return $atts;
	}

	/**
	 * Render an Elementor Theme Builder location when one exists.
	 *
	 * @param string $location Location slug.
	 * @return bool True when Elementor rendered something.
	 */
	public static function do_location( $location ) {
		return function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( $location );
	}
}
