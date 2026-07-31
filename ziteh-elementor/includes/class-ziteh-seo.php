<?php
/**
 * Technical output that complements an SEO plugin instead of competing with it.
 *
 * Ziteh is not an SEO plugin. Titles, meta descriptions, canonicals, sitemaps
 * and structured data belong to Rank Math, Yoast or whichever plugin the site
 * runs, and emitting a second copy of any of those is actively harmful. What is
 * left — and what no SEO plugin does — is telling the browser early about the
 * image that will be the Largest Contentful Paint.
 *
 * @package Ziteh_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Ziteh_Seo.
 */
class Ziteh_Seo {

	/**
	 * Singleton instance.
	 *
	 * @var Ziteh_Seo|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Ziteh_Seo
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
		// Priority 2: early enough to matter for LCP, late enough that charset
		// and viewport are already out.
		add_action( 'wp_head', array( $this, 'preload_product_image' ), 2 );
	}

	/**
	 * Preload the product's main gallery image.
	 *
	 * The preload carries the same srcset and sizes as the rendered `<img>`.
	 * Without them the browser would pick a different candidate for the preload
	 * than for the element and download the image twice — which would make the
	 * page slower, not faster.
	 */
	public function preload_product_image() {
		if ( ! function_exists( 'is_product' ) || ! is_product() ) {
			return;
		}

		if ( class_exists( 'Ziteh_Settings' ) && ! Ziteh_Settings::feature_enabled( 'seo_preload_lcp', true ) ) {
			return;
		}

		$product = function_exists( 'wc_get_product' ) ? wc_get_product( get_queried_object_id() ) : null;
		if ( ! $product ) {
			return;
		}

		$image_id = (int) $product->get_image_id();
		if ( ! $image_id ) {
			return;
		}

		$src = wp_get_attachment_image_src( $image_id, 'woocommerce_single' );
		if ( ! $src || empty( $src[0] ) ) {
			return;
		}

		$srcset = wp_get_attachment_image_srcset( $image_id, 'woocommerce_single' );
		$sizes  = wp_get_attachment_image_sizes( $image_id, 'woocommerce_single' );

		printf(
			'<link rel="preload" as="image" fetchpriority="high" href="%1$s"%2$s%3$s>' . "\n",
			esc_url( $src[0] ),
			$srcset ? ' imagesrcset="' . esc_attr( $srcset ) . '"' : '',
			$srcset && $sizes ? ' imagesizes="' . esc_attr( $sizes ) . '"' : ''
		);
	}
}
