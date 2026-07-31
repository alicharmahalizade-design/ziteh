<?php
/**
 * Front-end shell: prints the shared interactive containers once in the footer
 * (mini-cart drawer, Quick View modal, live-search overlay) and localises the
 * data the JS needs (AJAX url, nonce, WooCommerce urls).
 *
 * @package Ziteh_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Ziteh_Frontend
 */
class Ziteh_Frontend {

	/**
	 * Singleton instance.
	 *
	 * @var Ziteh_Frontend|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Ziteh_Frontend
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
		add_action( 'wp_enqueue_scripts', array( $this, 'localize' ), 20 );
		add_action( 'wp_footer', array( $this, 'render_shell' ) );
	}

	/**
	 * Pass runtime data to the widgets script.
	 */
	public function localize() {
		if ( ! wp_script_is( 'ziteh-widgets', 'registered' ) && ! wp_script_is( 'ziteh-widgets', 'enqueued' ) ) {
			return;
		}
		$shell_on = ! class_exists( 'Ziteh_Settings' ) || Ziteh_Settings::shell_enabled();
		$features = array(
			'quickview'    => $shell_on && ( ! class_exists( 'Ziteh_Settings' ) || Ziteh_Settings::feature_enabled( 'quickview' ) ),
			'liveSearch'   => $shell_on && ( ! class_exists( 'Ziteh_Settings' ) || Ziteh_Settings::feature_enabled( 'live_search' ) ),
			'cartDrawer'   => $shell_on && ( ! class_exists( 'Ziteh_Settings' ) || Ziteh_Settings::feature_enabled( 'cart_drawer' ) ),
			'wishlist'     => ! class_exists( 'Ziteh_Settings' ) || Ziteh_Settings::feature_enabled( 'wishlist' ),
			'quizProducts' => ! class_exists( 'Ziteh_Settings' ) || Ziteh_Settings::feature_enabled( 'quiz_products' ),
			'stickyHeader' => ! class_exists( 'Ziteh_Settings' ) || Ziteh_Settings::feature_enabled( 'sticky_header' ),
			'animations'   => ! class_exists( 'Ziteh_Settings' ) || Ziteh_Settings::feature_enabled( 'animations' ),
		);
		$data = array(
			'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'ziteh' ),
			'hasWc'    => class_exists( 'WooCommerce' ),
			'features' => $features,
			'i18n'     => array(
				'added'  => __( 'به سبد اضافه شد', 'ziteh' ),
				'error'  => __( 'خطایی رخ داد. دوباره تلاش کنید.', 'ziteh' ),
				'search' => __( 'جستجوی محصولات...', 'ziteh' ),
			),
		);
		if ( class_exists( 'WooCommerce' ) && function_exists( 'wc_get_cart_url' ) ) {
			$data['cartUrl'] = wc_get_cart_url();
		}
		wp_localize_script( 'ziteh-widgets', 'zitehData', $data );
	}

	/**
	 * Print the shared containers in the footer.
	 */
	public function render_shell() {
		// Avoid printing in the Elementor editor iframe / REST previews.
		if ( is_admin() ) {
			return;
		}

		// Smart asset mode: never print an unstyled shell on unrelated pages.
		if ( ! wp_style_is( 'ziteh-widgets', 'enqueued' ) && ! wp_script_is( 'ziteh-widgets', 'enqueued' ) ) {
			return;
		}

		// Respect the kill-switch: when off, print nothing at all.
		if ( class_exists( 'Ziteh_Settings' ) && ! Ziteh_Settings::shell_enabled() ) {
			return;
		}
		$has_wc   = class_exists( 'WooCommerce' );
		$quickview = ! class_exists( 'Ziteh_Settings' ) || Ziteh_Settings::feature_enabled( 'quickview' );
		$search    = ! class_exists( 'Ziteh_Settings' ) || Ziteh_Settings::feature_enabled( 'live_search' );
		$drawer    = ! class_exists( 'Ziteh_Settings' ) || Ziteh_Settings::feature_enabled( 'cart_drawer' );
		?>
		<!-- Ziteh interactive shell -->
		<div class="ziteh-overlay" data-ziteh-overlay hidden></div>

		<?php if ( $has_wc && $drawer ) : ?>
			<aside class="ziteh-drawer" data-ziteh-drawer aria-hidden="true" aria-label="<?php esc_attr_e( 'سبد خرید', 'ziteh' ); ?>">
				<?php
				if ( class_exists( 'Ziteh_Ajax' ) ) {
					Ziteh_Ajax::instance()->render_mini_cart();
				}
				?>
			</aside>
		<?php endif; ?>

		<?php if ( $quickview ) : ?>
		<div class="ziteh-modal" data-ziteh-modal aria-hidden="true" role="dialog" aria-modal="true">
			<div class="ziteh-modal__dialog">
				<button class="ziteh-modal__close" type="button" data-ziteh-modal-close aria-label="<?php esc_attr_e( 'بستن', 'ziteh' ); ?>">&times;</button>
				<div class="ziteh-modal__body" data-ziteh-modal-body></div>
				<div class="ziteh-modal__loader" data-ziteh-modal-loader hidden>
					<span class="ziteh-spinner"></span>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( $search ) : ?>
		<div class="ziteh-search-overlay" data-ziteh-search-overlay aria-hidden="true">
			<div class="ziteh-search-box">
				<button class="ziteh-search-box__close" type="button" data-ziteh-search-close aria-label="<?php esc_attr_e( 'بستن', 'ziteh' ); ?>">&times;</button>
				<form class="ziteh-search-box__form" role="search" onsubmit="return false;">
					<input type="search" class="ziteh-search-box__input" data-ziteh-search-input placeholder="<?php esc_attr_e( 'نام محصول را بنویسید...', 'ziteh' ); ?>" autocomplete="off">
				</form>
				<div class="ziteh-search-box__results" data-ziteh-search-results></div>
			</div>
		</div>
		<?php endif; ?>
		<?php
	}
}
