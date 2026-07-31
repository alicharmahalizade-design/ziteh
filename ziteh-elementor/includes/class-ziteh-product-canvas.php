<?php
/**
 * Isolated visual canvas for Elementor single-product templates.
 *
 * @package Ziteh_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Removes theme/WooCommerce presentation without changing product behavior. */
class Ziteh_Product_Canvas {

	/** @var Ziteh_Product_Canvas|null */
	private static $instance = null;

	/** Singleton. */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/** Register isolation hooks early enough to intercept WooCommerce styles. */
	private function __construct() {
		add_filter( 'woocommerce_enqueue_styles', array( $this, 'filter_woocommerce_styles' ), 999 );
		add_filter( 'body_class', array( $this, 'body_classes' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_canvas' ), 999 );
	}

	/** Whether the isolated product canvas applies to this request. */
	private function active() {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return false;
		}
		$is_product = function_exists( 'is_product' ) && is_product();
		$is_preview = false;
		if ( isset( $_GET['elementor-preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$preview_id   = absint( $_GET['elementor-preview'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$preview_data = $preview_id ? (string) get_post_meta( $preview_id, '_elementor_data', true ) : '';
			$is_preview   = false !== strpos( $preview_data, 'ziteh-single-product' ) || false !== strpos( $preview_data, 'ziteh-product-details' );
		}
		return ( $is_product || $is_preview ) && ( ! class_exists( 'Ziteh_Settings' ) || Ziteh_Settings::feature_enabled( 'product_isolation', true ) );
	}

	/** Stop WooCommerce's visual stylesheet bundle on isolated product pages. */
	public function filter_woocommerce_styles( $styles ) {
		return $this->active() ? array() : $styles;
	}

	/** Add stable selectors for scoped CSS and debugging. */
	public function body_classes( $classes ) {
		if ( $this->active() ) {
			$classes[] = 'ziteh-product-canvas';
			$classes[] = 'ziteh-product-canvas-v1';

			if ( $this->native_theme() ) {
				$classes[] = 'ziteh-product-canvas-native';
			}
		}
		return array_unique( $classes );
	}

	/**
	 * Whether the active theme is built for Ziteh.
	 *
	 * The Ziteh theme declares `ziteh-native` support. When it is active there is
	 * no hostile page shell left to neutralise, so the canvas stands its
	 * `!important` container overrides down. Those overrides are not free: they
	 * burn specificity the site owner would otherwise have for their own
	 * customisation, which is how the 3.8.0 width lock ended up disabling every
	 * Elementor size control.
	 *
	 * @return bool
	 */
	private function native_theme() {
		return current_theme_supports( 'ziteh-native' );
	}

	/** Load the final, high-priority canvas and remove known Woo block skins. */
	public function enqueue_canvas() {
		if ( ! $this->active() ) {
			return;
		}

		foreach ( array( 'woocommerce-general', 'woocommerce-layout', 'woocommerce-smallscreen', 'wc-blocks-style', 'wc-blocks-packages-style', 'wc-blocks-vendors-style', 'wc-blocks-checkout-style', 'wc-blocks-cart-style' ) as $handle ) {
			wp_dequeue_style( $handle );
		}

		wp_enqueue_style(
			'ziteh-product-canvas',
			ZITEH_EL_URL . 'assets/css/ziteh-product-canvas.css',
			array( 'ziteh-widgets' ),
			ZITEH_EL_VERSION
		);
	}
}
