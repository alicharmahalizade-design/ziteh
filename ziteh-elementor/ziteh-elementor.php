<?php
/**
 * Plugin Name: Ziteh Elementor Widgets
 * Plugin URI:  https://ziteh.com
 * Description: مجموعه ویجت‌های اختصاصی المنتور برای پیاده‌سازی پیکسل‌به‌پیکسل صفحه اصلی فروشگاه زیته (تاپ‌بار، هدر، هیرو، دسته‌بندی، روتین، محصولات، برندها، مجله، مشاوره، نظرات، اینستاگرام، خبرنامه و فوتر).
 * Version:     2.0.1
 * Author:      Ziteh
 * Text Domain: ziteh
 * Domain Path: /languages
 * Requires Plugins: elementor
 * Elementor tested up to: 3.25
 *
 * @package Ziteh_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

define( 'ZITEH_EL_VERSION', '2.0.1' );
define( 'ZITEH_EL_FILE', __FILE__ );
define( 'ZITEH_EL_PATH', plugin_dir_path( __FILE__ ) );
define( 'ZITEH_EL_URL', plugin_dir_url( __FILE__ ) );
define( 'ZITEH_EL_MIN_ELEMENTOR', '3.5.0' );
define( 'ZITEH_EL_MIN_PHP', '7.4' );

/**
 * Main plugin bootstrap.
 *
 * Loads the widgets manager once Elementor is available and verifies the
 * environment (Elementor active, minimum versions) before doing anything.
 */
final class Ziteh_Elementor_Plugin {

	/**
	 * Single instance of the plugin.
	 *
	 * @var Ziteh_Elementor_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Ziteh_Elementor_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor. Hooks the boot routine to plugins_loaded.
	 */
	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ) );
		add_action( 'init', array( $this, 'load_textdomain' ) );
	}

	/**
	 * Load translations from /languages.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'ziteh', false, dirname( plugin_basename( ZITEH_EL_FILE ) ) . '/languages' );
	}

	/**
	 * Runs after all plugins are loaded. Verifies the environment and boots.
	 */
	public function on_plugins_loaded() {
		if ( ! $this->is_compatible() ) {
			return;
		}

		// Load the widgets manager.
		require_once ZITEH_EL_PATH . 'includes/class-ziteh-widgets-manager.php';
		Ziteh_Widgets_Manager::instance();

		// Central design settings (palette / radius / motion).
		require_once ZITEH_EL_PATH . 'includes/class-ziteh-settings.php';
		Ziteh_Settings::instance();

		// AJAX endpoints + WooCommerce cart glue.
		require_once ZITEH_EL_PATH . 'includes/class-ziteh-ajax.php';
		Ziteh_Ajax::instance();

		// Front-end interactive shell (drawer / modal / search).
		require_once ZITEH_EL_PATH . 'includes/class-ziteh-frontend.php';
		Ziteh_Frontend::instance();

		// Admin-only: the one-click ready-made template importer.
		if ( is_admin() ) {
			require_once ZITEH_EL_PATH . 'includes/class-ziteh-template-importer.php';
			Ziteh_Template_Importer::instance();
		}
	}

	/**
	 * Verify that the environment can run this plugin.
	 *
	 * @return bool
	 */
	public function is_compatible() {
		// Elementor installed and activated?
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', array( $this, 'notice_missing_elementor' ) );
			return false;
		}

		// Elementor version check.
		if ( ! version_compare( ELEMENTOR_VERSION, ZITEH_EL_MIN_ELEMENTOR, '>=' ) ) {
			add_action( 'admin_notices', array( $this, 'notice_minimum_elementor' ) );
			return false;
		}

		// PHP version check.
		if ( version_compare( PHP_VERSION, ZITEH_EL_MIN_PHP, '<' ) ) {
			add_action( 'admin_notices', array( $this, 'notice_minimum_php' ) );
			return false;
		}

		return true;
	}

	/**
	 * Admin notice: Elementor is not installed / active.
	 */
	public function notice_missing_elementor() {
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
		$message = esc_html__( 'پلاگین «ویجت‌های زیته» برای کار کردن به المنتور نیاز دارد. لطفاً ابتدا المنتور را نصب و فعال کنید.', 'ziteh' );
		printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', esc_html( $message ) );
	}

	/**
	 * Admin notice: Elementor version too old.
	 */
	public function notice_minimum_elementor() {
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
		$message = sprintf(
			/* translators: %s: minimum Elementor version */
			esc_html__( 'پلاگین «ویجت‌های زیته» به المنتور نسخه %s یا بالاتر نیاز دارد.', 'ziteh' ),
			ZITEH_EL_MIN_ELEMENTOR
		);
		printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', esc_html( $message ) );
	}

	/**
	 * Admin notice: PHP version too old.
	 */
	public function notice_minimum_php() {
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
		$message = sprintf(
			/* translators: %s: minimum PHP version */
			esc_html__( 'پلاگین «ویجت‌های زیته» به PHP نسخه %s یا بالاتر نیاز دارد.', 'ziteh' ),
			ZITEH_EL_MIN_PHP
		);
		printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>', esc_html( $message ) );
	}
}

Ziteh_Elementor_Plugin::instance();
