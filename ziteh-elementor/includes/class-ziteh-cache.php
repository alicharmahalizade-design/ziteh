<?php
/**
 * Cache buster.
 *
 * Purges the caches of the common WordPress caching plugins (and Elementor's
 * generated CSS) automatically when the plugin is updated or its settings are
 * saved, plus a manual "clear cache" button. This means users don't have to
 * remember to purge after an update.
 *
 * @package Ziteh_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Ziteh_Cache
 */
class Ziteh_Cache {

	/**
	 * Singleton instance.
	 *
	 * @var Ziteh_Cache|null
	 */
	private static $instance = null;

	/**
	 * Option key storing the last-seen plugin version.
	 */
	const VERSION_OPTION = 'ziteh_installed_version';

	/**
	 * Admin-post action for the manual button.
	 */
	const ACTION = 'ziteh_purge_cache';

	/**
	 * Get the singleton instance.
	 *
	 * @return Ziteh_Cache
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor. Wires the auto-purge triggers.
	 */
	private function __construct() {
		// Auto-purge when the plugin version changes (i.e. after an update).
		add_action( 'admin_init', array( $this, 'maybe_purge_on_update' ) );

		// Auto-purge when Ziteh settings are saved.
		add_action( 'update_option_ziteh_settings', array( $this, 'maybe_purge_on_settings' ), 10, 2 );
		add_action( 'add_option_ziteh_settings', array( $this, 'maybe_purge_on_settings' ), 10, 2 );

		// Manual button handler.
		add_action( 'admin_post_' . self::ACTION, array( $this, 'handle_manual' ) );
	}

	/**
	 * Purge after a settings save only when the performance preference allows it.
	 *
	 * @param mixed $old_value Previous option value (or option name on add hook).
	 * @param mixed $new_value New option value.
	 */
	public function maybe_purge_on_settings( $old_value = null, $new_value = null ) {
		unset( $old_value );
		$settings = is_array( $new_value ) ? $new_value : get_option( 'ziteh_settings', array() );
		if ( ! isset( $settings['auto_cache_purge'] ) || 'off' !== $settings['auto_cache_purge'] ) {
			self::purge();
		}
	}

	/**
	 * Compare the stored version with the current one and purge on change.
	 */
	public function maybe_purge_on_update() {
		$stored = get_option( self::VERSION_OPTION );
		if ( ZITEH_EL_VERSION !== $stored ) {
			self::purge();
			update_option( self::VERSION_OPTION, ZITEH_EL_VERSION );
		}
	}

	/**
	 * Handle the manual "clear cache" button.
	 */
	public function handle_manual() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'دسترسی کافی ندارید.', 'ziteh' ) );
		}
		check_admin_referer( self::ACTION );
		self::purge();
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'         => 'ziteh-settings',
					'ziteh_purged' => '1',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Render the manual clear-cache form (called from the settings screen).
	 */
	public static function render_button() {
		?>
		<hr>
		<h2><?php esc_html_e( 'پاک‌سازی کش', 'ziteh' ); ?></h2>
		<p><?php esc_html_e( 'کش به‌صورت خودکار هنگام آپدیت افزونه و ذخیره‌ی تنظیمات پاک می‌شود. در صورت نیاز می‌توانید همین حالا هم دستی پاک کنید.', 'ziteh' ); ?></p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="<?php echo esc_attr( self::ACTION ); ?>">
			<?php wp_nonce_field( self::ACTION ); ?>
			<button type="submit" class="button button-secondary"><?php esc_html_e( 'پاک کردن کش', 'ziteh' ); ?></button>
		</form>
		<?php
	}

	/**
	 * Purge every cache we can reach. Safe to call anytime — each integration is
	 * guarded so missing plugins are simply skipped.
	 */
	public static function purge() {
		// WordPress object cache.
		if ( function_exists( 'wp_cache_flush' ) ) {
			wp_cache_flush();
		}

		// Elementor generated CSS files.
		if ( class_exists( '\Elementor\Plugin' ) ) {
			try {
				$el = \Elementor\Plugin::$instance;
				if ( isset( $el->files_manager ) ) {
					$el->files_manager->clear_cache();
				}
			} catch ( \Exception $e ) {
				unset( $e );
			}
		}

		// WP Rocket.
		if ( function_exists( 'rocket_clean_domain' ) ) {
			rocket_clean_domain();
		}
		if ( function_exists( 'rocket_clean_minify' ) ) {
			rocket_clean_minify();
		}

		// W3 Total Cache.
		if ( function_exists( 'w3tc_flush_all' ) ) {
			w3tc_flush_all();
		}

		// WP Super Cache.
		if ( function_exists( 'wp_cache_clear_cache' ) ) {
			wp_cache_clear_cache();
		}

		// WP Fastest Cache.
		if ( function_exists( 'wpfc_clear_all_cache' ) ) {
			wpfc_clear_all_cache( true );
		} else {
			global $wp_fastest_cache;
			if ( is_object( $wp_fastest_cache ) && method_exists( $wp_fastest_cache, 'deleteCache' ) ) {
				$wp_fastest_cache->deleteCache( true );
			}
		}

		// Autoptimize.
		if ( class_exists( 'autoptimizeCache' ) && method_exists( 'autoptimizeCache', 'clearall' ) ) {
			autoptimizeCache::clearall();
		}

		// SiteGround SG Optimizer.
		if ( function_exists( 'sg_cachepress_purge_cache' ) ) {
			sg_cachepress_purge_cache();
		}

		// Comet Cache.
		if ( class_exists( 'comet_cache' ) && method_exists( 'comet_cache', 'clear' ) ) {
			comet_cache::clear();
		}

		// Hook-based purges (LiteSpeed, Cache Enabler, Breeze, Swift, Cloudflare
		// helpers, etc.). do_action is a no-op when nothing is listening.
		do_action( 'litespeed_purge_all' );
		do_action( 'cache_enabler_clear_complete_cache' );
		do_action( 'breeze_clear_all_cache' );
		do_action( 'swift_performance_clear_all_cache' );
		do_action( 'wpo_cache_flush' ); // WP-Optimize.
		do_action( 'nginx_helper_purge_all' );

		// A generic hook so site owners can wire their own purge if needed.
		do_action( 'ziteh_purge_cache' );
	}
}
