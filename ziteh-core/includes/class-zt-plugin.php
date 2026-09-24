<?php
/**
 * Main plugin class — loads modules and verifies the environment.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_Plugin
 */
final class ZT_Plugin {

	/**
	 * Instance.
	 *
	 * @var ZT_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Singleton.
	 *
	 * @return ZT_Plugin
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
		$inc = ZT_PATH . 'includes/';
		require_once $inc . 'class-zt-assets.php';
		require_once $inc . 'class-zt-shell.php';
		require_once $inc . 'class-zt-context.php';
		require_once $inc . 'class-zt-parts.php';
		require_once $inc . 'class-zt-templates.php';
		require_once $inc . 'class-zt-meta-fields.php';
		require_once $inc . 'class-zt-newsletter.php';
		require_once $inc . 'class-zt-contact.php';
		require_once $inc . 'class-zt-ajax.php';

		ZT_Assets::init();
		ZT_Shell::init();
		ZT_Templates::init();
		ZT_Meta_Fields::init();
		ZT_Newsletter::init();
		ZT_Contact::init();
		ZT_Ajax::init();

		if ( is_admin() ) {
			require_once $inc . 'class-zt-admin.php';
			require_once $inc . 'class-zt-builder.php';
			require_once $inc . 'class-zt-demo.php';
			ZT_Admin::init();
		}

		add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ), 20 );
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'admin_notices', array( $this, 'notices' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( ZT_FILE ), array( $this, 'action_links' ) );
	}

	/**
	 * Boot integrations that depend on other plugins.
	 */
	public function on_plugins_loaded() {
		if ( zt_is_woo() || class_exists( 'WooCommerce' ) ) {
			$woo = ZT_PATH . 'includes/woo/';
			require_once $woo . 'class-zt-woo.php';
			ZT_Woo::init();
		}
		if ( did_action( 'elementor/loaded' ) && defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, ZT_MIN_ELEMENTOR, '>=' ) ) {
			require_once ZT_PATH . 'includes/class-zt-elementor.php';
			ZT_Elementor::init();
		}
	}

	/**
	 * Translations.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'ziteh-core', false, dirname( plugin_basename( ZT_FILE ) ) . '/languages' );
	}

	/**
	 * Environment notices.
	 */
	public function notices() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		if ( ! did_action( 'elementor/loaded' ) ) {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'افزونه «هسته زیته» برای کار به افزونه المنتور نیاز دارد. لطفاً المنتور را نصب و فعال کنید.', 'ziteh-core' ) . '</p></div>';
		} elseif ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, ZT_MIN_ELEMENTOR, '<' ) ) {
			/* translators: %s: version */
			echo '<div class="notice notice-error"><p>' . esc_html( sprintf( __( 'هسته زیته به المنتور نسخه %s یا بالاتر نیاز دارد.', 'ziteh-core' ), ZT_MIN_ELEMENTOR ) ) . '</p></div>';
		}
		if ( ! class_exists( 'WooCommerce' ) ) {
			echo '<div class="notice notice-warning"><p>' . esc_html__( 'برای فعال شدن فروشگاه، سبد خرید، صورت‌حساب و پیگیری سفارش زیته، افزونه ووکامرس را نصب و فعال کنید.', 'ziteh-core' ) . '</p></div>';
		}
		if ( get_option( 'zt_just_activated' ) ) {
			delete_option( 'zt_just_activated' );
			echo '<div class="notice notice-success is-dismissible"><p>' . wp_kses_post( sprintf( 'هسته زیته فعال شد. برای ساخت خودکار تمام صفحات، به <a href="%s">زیته ← ابزارها</a> بروید.', esc_url( admin_url( 'admin.php?page=ziteh-core&tab=tools' ) ) ) ) . '</p></div>';
		}
	}

	/**
	 * Settings link on the plugins screen.
	 *
	 * @param array $links Links.
	 * @return array
	 */
	public function action_links( $links ) {
		array_unshift( $links, '<a href="' . esc_url( admin_url( 'admin.php?page=ziteh-core' ) ) . '">' . esc_html__( 'تنظیمات', 'ziteh-core' ) . '</a>' );
		return $links;
	}

	/**
	 * Activation.
	 */
	public static function activate() {
		require_once ZT_PATH . 'includes/class-zt-newsletter.php';
		ZT_Newsletter::install();

		// Let Elementor edit Ziteh templates.
		$cpts = get_option( 'elementor_cpt_support', array( 'page', 'post' ) );
		if ( ! is_array( $cpts ) ) {
			$cpts = array( 'page', 'post' );
		}
		foreach ( array( 'page', 'post', 'zt_template' ) as $pt ) {
			if ( ! in_array( $pt, $cpts, true ) ) {
				$cpts[] = $pt;
			}
		}
		update_option( 'elementor_cpt_support', $cpts );

		if ( zt_opt( 'general.disable_elementor_defaults', 1 ) ) {
			update_option( 'elementor_disable_color_schemes', 'yes' );
			update_option( 'elementor_disable_typography_schemes', 'yes' );
		}
		update_option( 'zt_just_activated', 1 );
		update_option( 'zt_flush_rewrite', 1 );
	}

	/**
	 * Deactivation.
	 */
	public static function deactivate() {
		flush_rewrite_rules();
	}
}
