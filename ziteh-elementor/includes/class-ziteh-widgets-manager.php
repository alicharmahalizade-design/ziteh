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
		'story',
		'categories',
		'routine',
		'products',
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
	}

	/**
	 * Register a dedicated "زیته" category so the widgets group together in the
	 * Elementor panel.
	 *
	 * @param \Elementor\Elements_Manager $elements_manager Elementor elements manager.
	 */
	public function register_category( $elements_manager ) {
		$elements_manager->add_category(
			'ziteh',
			array(
				'title' => esc_html__( 'زیته', 'ziteh' ),
				'icon'  => 'eicon-leaf',
			)
		);
	}

	/**
	 * Register shared frontend/editor styles (fonts + widgets stylesheet).
	 */
	public function enqueue_styles() {
		wp_enqueue_style(
			'ziteh-fonts',
			ZITEH_EL_URL . 'assets/fonts/vazirmatn.css',
			array(),
			ZITEH_EL_VERSION
		);

		wp_enqueue_style(
			'ziteh-widgets',
			ZITEH_EL_URL . 'assets/css/ziteh-widgets.css',
			array( 'ziteh-fonts' ),
			ZITEH_EL_VERSION
		);
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
	}

	/**
	 * Make sure the widgets JS is available on the front-end too.
	 */
	public function enqueue_frontend_scripts() {
		wp_enqueue_script(
			'ziteh-widgets',
			ZITEH_EL_URL . 'assets/js/ziteh-widgets.js',
			array(),
			ZITEH_EL_VERSION,
			true
		);
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
