<?php
/**
 * Widgets manager: registers the Ziteh Elementor category, enqueues shared
 * assets (Vazirmatn font, widget CSS/JS) and loads/registers every widget.
 *
 * @package Ziteh_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Ziteh_Widgets_Manager
 */
class Ziteh_Widgets_Manager {

	/**
	 * Singleton instance.
	 *
	 * @var Ziteh_Widgets_Manager|null
	 */
	private static $instance = null;

	/**
	 * List of widget slugs. Each maps to widgets/class-ziteh-{slug}.php
	 * containing a class named Ziteh_{StudlySlug}_Widget.
	 *
	 * @var string[]
	 */
	private $widgets = array(
		'topbar',
		'header',
		'hero',
		'features',
		'story',
		'categories',
		'routine',
		'products',
		'single-product',
		'product-details',
		'related-products',
		'product-reviews',
		'store-services',
		'offers',
		'quiz',
		'brands',
		'blog',
		'consultation',
		'testimonials',
		'instagram',
		'newsletter',
		'footer',
	);

	/**
	 * Get the singleton instance.
	 *
	 * @return Ziteh_Widgets_Manager
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor. Wires up the Elementor hooks.
	 */
	private function __construct() {
		add_action( 'elementor/elements/categories_registered', array( $this, 'register_category' ) );
		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );

		// Frontend + editor assets.
		add_action( 'elementor/frontend/after_enqueue_styles', array( $this, 'enqueue_styles' ) );
		add_action( 'elementor/frontend/after_register_scripts', array( $this, 'register_scripts' ) );
		add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );

		// Optimisation: defer the widgets script so it never blocks render.
		add_filter( 'script_loader_tag', array( $this, 'defer_script' ), 10, 2 );

		// The app-like product layer is gated by a body class rather than a
		// separate stylesheet, so the markup is styled correctly on first paint
		// instead of flashing the desktop composition first.
		add_filter( 'body_class', array( $this, 'app_body_class' ) );
	}

	/**
	 * Add defer to the widgets script tag.
	 *
	 * @param string $tag    Script tag HTML.
	 * @param string $handle Script handle.
	 * @return string
	 */
	public function defer_script( $tag, $handle ) {
		if ( in_array( $handle, array( 'ziteh-widgets', 'ziteh-product-app' ), true ) && false === strpos( $tag, 'defer' ) ) {
			$tag = str_replace( ' src=', ' defer src=', $tag );
		}
		return $tag;
	}

	/**
	 * Register a dedicated "زیته" category so the widgets group together in the
	 * Elementor panel.
	 *
	 * @param \Elementor\Elements_Manager $elements_manager Elementor elements manager.
	 */
	public function register_category( $elements_manager ) {
		$elements_manager->add_category(
			'ziteh-product-page',
			array(
				'title' => esc_html__( 'محصول تکی زیته', 'ziteh' ),
				'icon'  => 'eicon-single-product',
			)
		);

		$elements_manager->add_category(
			'ziteh',
			array(
				'title' => esc_html__( 'هسته زیته', 'ziteh' ),
				'icon'  => 'eicon-leaf',
			)
		);
	}

	/**
	 * Register shared frontend/editor styles (fonts + widgets stylesheet).
	 */
	public function enqueue_styles() {
		$external_font = ! class_exists( 'Ziteh_Settings' ) || Ziteh_Settings::feature_enabled( 'external_font' );
		wp_register_style(
			'ziteh-fonts',
			$external_font ? ZITEH_EL_URL . 'assets/fonts/vazirmatn.css' : false,
			array(),
			ZITEH_EL_VERSION
		);

		wp_register_style(
			'ziteh-widgets',
			ZITEH_EL_URL . 'assets/css/ziteh-widgets.css',
			array( 'ziteh-fonts' ),
			ZITEH_EL_VERSION
		);

		/*
		 * The single-product layer is a separate file that depends on — and so
		 * always prints after — the shared widget stylesheet. It carries its own
		 * scoped reset and prefixes every selector with the Elementor widget
		 * wrapper, which is what keeps theme defaults out of the product page.
		 */
		wp_register_style(
			'ziteh-single-product',
			ZITEH_EL_URL . 'assets/css/ziteh-single-product.css',
			array( 'ziteh-widgets' ),
			ZITEH_EL_VERSION
		);

		if ( $this->should_load_assets() ) {
			wp_enqueue_style( 'ziteh-fonts' );
			wp_enqueue_style( 'ziteh-widgets' );
			wp_enqueue_style( 'ziteh-single-product' );
		}
	}

	/**
	 * Register the widgets JS with Elementor's frontend so it can be enqueued
	 * as a dependency of individual widgets.
	 */
	public function register_scripts() {
		wp_register_script(
			'ziteh-widgets',
			ZITEH_EL_URL . 'assets/js/ziteh-widgets.js',
			array(),
			ZITEH_EL_VERSION,
			true
		);

		wp_register_script(
			'ziteh-product-app',
			ZITEH_EL_URL . 'assets/js/ziteh-product-app.js',
			array( 'ziteh-widgets' ),
			ZITEH_EL_VERSION,
			true
		);
	}

	/**
	 * Flag the app-like product experience for CSS.
	 *
	 * @param string[] $classes Body classes.
	 * @return string[]
	 */
	public function app_body_class( $classes ) {
		if ( $this->product_app_enabled() ) {
			$classes[] = 'ziteh-app';
		}
		return $classes;
	}

	/**
	 * Whether the app-like product experience should load.
	 *
	 * @return bool
	 */
	private function product_app_enabled() {
		return ! class_exists( 'Ziteh_Settings' ) || Ziteh_Settings::feature_enabled( 'product_app', true );
	}

	/**
	 * Make sure the widgets JS is available on the front-end too.
	 */
	public function enqueue_frontend_scripts() {
		if ( ! $this->should_load_assets() ) {
			return;
		}
		wp_enqueue_script(
			'ziteh-widgets',
			ZITEH_EL_URL . 'assets/js/ziteh-widgets.js',
			array(),
			ZITEH_EL_VERSION,
			true
		);

		if ( $this->product_app_enabled() ) {
			wp_enqueue_script(
				'ziteh-product-app',
				ZITEH_EL_URL . 'assets/js/ziteh-product-app.js',
				array( 'ziteh-widgets' ),
				ZITEH_EL_VERSION,
				true
			);
		}
	}

	/**
	 * Decide whether shared assets should be loaded before Elementor discovers a
	 * widget dependency. Smart mode covers normal pages while widget-level
	 * dependencies remain the safety net for Theme Builder templates.
	 *
	 * @return bool
	 */
	private function should_load_assets() {
		if ( is_admin() || ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) ) {
			return true;
		}

		$settings = class_exists( 'Ziteh_Settings' ) ? Ziteh_Settings::get() : array( 'asset_mode' => 'global' );
		if ( 'global' === ( isset( $settings['asset_mode'] ) ? $settings['asset_mode'] : 'global' ) ) {
			return true;
		}

		if ( function_exists( 'is_product' ) && is_product() ) {
			return true;
		}

		$post_id = get_queried_object_id();
		if ( $post_id ) {
			$data = (string) get_post_meta( $post_id, '_elementor_data', true );
			if ( false !== strpos( $data, 'ziteh-' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Load and register every Ziteh widget with Elementor.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
	 */
	public function register_widgets( $widgets_manager ) {
		// Load the shared base class first.
		require_once ZITEH_EL_PATH . 'widgets/class-ziteh-widget-base.php';

		foreach ( $this->widgets as $slug ) {
			$file = ZITEH_EL_PATH . 'widgets/class-ziteh-' . $slug . '.php';
			if ( ! file_exists( $file ) ) {
				continue;
			}
			require_once $file;

			$class = 'Ziteh_' . str_replace( ' ', '_', ucwords( str_replace( '-', ' ', $slug ) ) ) . '_Widget';
			if ( class_exists( $class ) ) {
				$widgets_manager->register( new $class() );
			}
		}
	}
}
