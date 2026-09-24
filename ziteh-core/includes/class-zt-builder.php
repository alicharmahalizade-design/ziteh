<?php
/**
 * One-click builder: creates every page and template of the design as
 * Elementor documents made of Ziteh widgets, assigns WooCommerce pages,
 * the front page and default templates, and sets up shipping.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_Builder
 */
class ZT_Builder {

	/* ------------------------------------------------------------ helpers */

	/**
	 * Random Elementor element id.
	 *
	 * @return string
	 */
	private static function eid() {
		return substr( md5( uniqid( '', true ) . wp_rand() ), 0, 7 );
	}

	/**
	 * Widget element.
	 *
	 * @param string $type     Widget name.
	 * @param array  $settings Settings.
	 * @return array
	 */
	public static function w( $type, $settings = array() ) {
		return array(
			'id'         => self::eid(),
			'elType'     => 'widget',
			'widgetType' => $type,
			'settings'   => (object) $settings,
			'elements'   => array(),
		);
	}

	/**
	 * Container element (no padding / gap, full width).
	 *
	 * @param array  $children Children.
	 * @param string $layout   Ziteh layout preset value.
	 * @param string $tag      HTML tag.
	 * @param array  $extra    Extra settings.
	 * @return array
	 */
	public static function con( $children, $layout = 'con', $tag = '', $extra = array() ) {
		$zero     = array(
			'unit'     => 'px',
			'top'      => '0',
			'right'    => '0',
			'bottom'   => '0',
			'left'     => '0',
			'isLinked' => true,
		);
		$settings = array_merge(
			array(
				'content_width'  => 'full',
				'flex_direction' => 'column',
				'padding'        => $zero,
				'flex_gap'       => array(
					'size'     => 0,
					'column'   => '0',
					'row'      => '0',
					'unit'     => 'px',
					'isLinked' => true,
				),
				'zt_layout'      => $layout,
			),
			$extra
		);
		if ( $tag ) {
			$settings['html_tag'] = $tag;
		}
		return array(
			'id'       => self::eid(),
			'elType'   => 'container',
			'isInner'  => false,
			'settings' => (object) $settings,
			'elements' => $children,
		);
	}

	/**
	 * Main page container (1440 / 1240).
	 *
	 * @param array $children Children.
	 * @param bool  $narrow   Narrow.
	 * @return array
	 */
	public static function main( $children, $narrow = true ) {
		return self::con( $children, $narrow ? 'container zt-container--narrow zt-con' : 'container zt-con', 'main' );
	}

	/**
	 * Margin value for style controls.
	 *
	 * @param int $t Top.
	 * @param int $r Right.
	 * @param int $b Bottom.
	 * @param int $l Left.
	 * @return array
	 */
	public static function box( $t, $r, $b, $l ) {
		return array(
			'unit'     => 'px',
			'top'      => (string) $t,
			'right'    => (string) $r,
			'bottom'   => (string) $b,
			'left'     => (string) $l,
			'isLinked' => false,
		);
	}

	/**
	 * Trust bar items per page (as in the design).
	 *
	 * @param string $set Set name.
	 * @param array  $m   Margin [t,r,b,l].
	 * @return array
	 */
	public static function trust( $set, $m ) {
		$sets = array(
			'product' => array( array( 'truck-fast', 'ارسال سریع', 'ارسال ۲ تا ۳ روز کاری' ), array( 'lock', 'پرداخت امن', 'درگاه بانکی معتبر' ), array( 'bag', 'بسته‌بندی ایمن', 'بسته‌بندی استاندارد و امن' ), array( 'headset', 'پشتیبانی', 'پاسخگوی آنلاین' ) ),
			'cart'    => array( array( 'truck-fast', 'ارسال سریع', 'ارسال ۲ تا ۳ روز کاری' ), array( 'shield', 'ضمانت اصالت', 'ضمانت اصالت و کیفیت کالا' ), array( 'whatsapp', 'پشتیبانی', 'پاسخگوی آنلاین' ), array( 'truck', 'بسته‌بندی ایمن', 'بسته‌بندی استاندارد و امن' ) ),
			'default' => array( array( 'truck-fast', 'ارسال سریع', 'ارسال ۲ تا ۳ روز کاری' ), array( 'lock', 'پرداخت امن', 'درگاه بانکی معتبر' ), array( 'headset', 'پشتیبانی', 'پاسخگوی آنلاین' ), array( 'shield', 'ضمانت اصالت', 'تمامی محصولات اورجینال' ) ),
		);
		$items = array();
		foreach ( $sets[ $set ] as $i ) {
			$items[] = array(
				'_id'   => self::eid(),
				'icon'  => zt_icon_default( $i[0] ),
				'title' => $i[1],
				'sub'   => $i[2],
				'url'   => array( 'url' => '' ),
			);
		}
		return self::w(
			'zt-trust',
			array(
				'items'           => $items,
				'st_box_margin'   => self::box( $m[0], $m[1], $m[2], $m[3] ),
			)
		);
	}

	/**
	 * Green newsletter as used in inner pages.
	 *
	 * @param string $text Text.
	 * @param string $ph   Placeholder.
	 * @return array
	 */
	public static function news_green( $text = 'جدیدترین محصولات، مقالات و تخفیف‌ها را مستقیم در ایمیل خود دریافت کنید.', $ph = 'ایمیل خود را وارد کنید...' ) {
		return self::w(
			'zt-newsletter',
			array(
				'variant'        => 'green',
				'text'           => $text,
				'placeholder'    => $ph,
				'st_box_margin'  => self::box( 26, 0, 46, 0 ),
			)
		);
	}

	/* -------------------------------------------------------- definitions */

	/**
	 * Pages to build.
	 *
	 * @return array key => [title, slug, elements, meta]
	 */
	public static function pages() {
		$crumb = function ( $sep = '/', $cur = '' ) {
			return self::w( 'zt-breadcrumb', array( 'sep' => $sep, 'current' => $cur ) );
		};
		$head  = function ( $title, $sub = '' ) {
			return self::w( 'zt-page-head', array( 'title' => $title, 'sub' => $sub ) );
		};

		$p = array();

		$p['home'] = array(
			'title'    => 'خانه',
			'slug'     => 'home',
			'elements' => array(
				self::con(
					array(
						self::w( 'zt-mobile-search' ),
						self::w( 'zt-mobile-hero' ),
						self::w( 'zt-mobile-cats' ),
						self::w( 'zt-mobile-trust' ),
						self::w( 'zt-hero' ),
						self::w( 'zt-announcement' ),
						self::w( 'zt-story' ),
						self::w( 'zt-pledge' ),
						self::w( 'zt-categories' ),
						self::w( 'zt-offers' ),
						self::w( 'zt-routine' ),
						self::w( 'zt-products' ),
						self::w( 'zt-brands', array( 'wrap_pt' => array( 'unit' => 'px', 'size' => 0 ) ) ),
						self::w( 'zt-blog' ),
						self::w( 'zt-consult' ),
						self::w( 'zt-testimonials' ),
						self::w( 'zt-instagram' ),
						self::w(
							'zt-newsletter',
							array(
								'variant'    => 'cream',
								'in_section' => 'yes',
								'title'      => 'خبرنامه زیته',
								'text'       => 'جدیدترین محصولات، مقالات و تخفیف‌ها را مستقیماً در ایمیل خود دریافت کنید.',
								'wrap_pt'    => array( 'unit' => 'px', 'size' => 0 ),
							)
						),
					)
				),
			),
			'meta'     => array(),
		);

		$p['shop'] = array(
			'title'    => 'فروشگاه',
			'slug'     => 'shop',
			'elements' => array(),
			'meta'     => array(),
		);

		$p['cart'] = array(
			'title'    => 'سبد خرید',
			'slug'     => 'cart',
			'elements' => array(
				self::main(
					array(
						$crumb( '›' ),
						$head( 'سبد خرید', 'محصولات مورد علاقه‌تان را بررسی و سفارش دهید.' ),
						self::con(
							array(
								self::con( array( self::w( 'zt-cart-tiers' ), self::w( 'zt-cart-items' ), self::w( 'zt-cart-samples' ) ), 'stack zt-con' ),
								self::con( array( self::w( 'zt-cart-summary' ) ), 'stack zt-con', 'aside' ),
							),
							'shop-grid zt-con'
						),
						self::trust( 'cart', array( 34, 0, 0, 0 ) ),
						self::news_green( 'از تخفیف‌های ویژه، محصولات جدید و مطالب مراقبت از پوست باخبر شوید.', 'ایمیل خود را وارد کنید ...' ),
					)
				),
			),
			'meta'     => array(
				'footer_width' => 'narrow',
				'footer_copy'  => 'کلیه حقوق این وب‌سایت متعلق به زیته است.',
			),
		);

		$p['checkout'] = array(
			'title'    => 'صورت‌حساب',
			'slug'     => 'checkout',
			'elements' => array(
				self::main(
					array(
						$crumb( '/', 'صورت حساب' ),
						$head( 'صورت حساب', 'اطلاعات خود را تکمیل کنید و سفارش خود را ثبت کنید.' ),
						self::con(
							array(
								self::con( array( self::w( 'zt-checkout-steps' ), self::w( 'zt-checkout-form' ) ), 'stack zt-con' ),
								self::con( array( self::w( 'zt-checkout-summary' ), self::w( 'zt-checkout-safe' ) ), 'stack zt-con', 'aside' ),
							),
							'co-grid zt-con'
						),
						self::trust( 'default', array( 40, 0, 26, 0 ) ),
					)
				),
			),
			'meta'     => array(
				'footer_style' => 'cream',
				'footer_box'   => 1240,
				'footer_copy'  => 'کلیه حقوق محفوظ است برای زیته',
			),
		);

		$p['account'] = array(
			'title'    => 'پنل کاربری',
			'slug'     => 'my-account',
			'elements' => array(
				self::main(
					array(
						$crumb( '/', 'پنل کاربری' ),
						self::w( 'zt-page-head', array( 'title' => 'پنل کاربری', 'sub' => '', 'dynamic' => '' ) ),
						self::con(
							array(
								self::w( 'zt-account-menu' ),
								self::con(
									array(
										self::w( 'zt-account-login' ),
										self::w( 'zt-account-profile' ),
										self::con(
											array(
												self::con( array( self::w( 'zt-account-orders' ), self::w( 'zt-account-quick' ) ), 'stack zt-con' ),
												self::con( array( self::w( 'zt-account-favs' ), self::w( 'zt-account-address' ) ), 'stack zt-con' ),
											),
											'dash-grid zt-con'
										),
										self::w( 'zt-account-endpoint' ),
									),
									'stack zt-con'
								),
							),
							'panel-grid zt-con'
						),
						self::trust( 'default', array( 34, 0, 26, 0 ) ),
					),
					false
				),
			),
			'meta'     => array(
				'footer_style' => 'cream',
				'footer_box'   => 1440,
				'footer_copy'  => 'کلیه حقوق محفوظ است برای زیته',
			),
		);

		$p['tracking'] = array(
			'title'    => 'پیگیری سفارش',
			'slug'     => 'tracking',
			'elements' => array(
				self::main(
					array(
						$crumb(),
						$head( 'پیگیری سفارش', 'وضعیت سفارش خود را در این صفحه مشاهده کنید.' ),
						self::w( 'zt-order-lookup' ),
						self::w( 'zt-order-header' ),
						self::w( 'zt-order-timeline' ),
						self::con(
							array(
								self::w( 'zt-order-items' ),
								self::con( array( self::w( 'zt-order-address' ), self::w( 'zt-order-support' ) ), 'stack zt-con', 'aside' ),
							),
							'trk-grid zt-con'
						),
						self::w( 'zt-order-shipment' ),
						self::trust( 'default', array( 22, 0, 26, 0 ) ),
					)
				),
			),
			'meta'     => array(
				'page_type'    => 'tracking',
				'footer_style' => 'cream',
				'footer_box'   => 1240,
				'footer_copy'  => 'کلیه حقوق محفوظ است برای زیته',
			),
		);

		$p['routine'] = array(
			'title'    => 'راهنمای روتین مراقبت از پوست',
			'slug'     => 'routine',
			'elements' => array(
				self::main(
					array(
						$crumb( '/', 'راهنمای روتین' ),
						$head( 'راهنمای روتین مراقبت از پوست', 'مراقبت از پوست، از عادت‌های کوچک شروع می‌شود.' ),
						self::con(
							array(
								self::w( 'zt-guide-lead' ),
								self::w( 'zt-guide-note' ),
								self::w( 'zt-guide-steps' ),
								self::w( 'zt-guide-steps', array( 'title' => 'روتین شب', 'icon' => zt_icon_default( 'moon' ), 'steps' => ZT_W_Guide_Steps_Defaults::pm() ) ),
								self::w( 'zt-guide-faq' ),
								self::w( 'zt-guide-tips' ),
								self::w( 'zt-button', array( 'st_wrap_margin' => self::box( 38, 0, 8, 0 ) ) ),
							),
							'guide zt-con',
							'article'
						),
						self::trust( 'default', array( 34, 0, 26, 0 ) ),
					)
				),
			),
			'meta'     => array(
				'page_type'    => 'routine',
				'mobile_title' => 'راهنمای روتین',
				'footer_width' => 'narrow',
				'footer_copy'  => 'کلیه حقوق محفوظ است برای زیته.',
			),
		);

		$p['blog'] = array(
			'title'    => 'مجله زیته',
			'slug'     => 'mag',
			'elements' => array(
				self::main(
					array(
						$crumb(),
						$head( 'مجله زیته', 'مقالات تخصصی مراقبت از پوست، مو و سلامت' ),
						self::w( 'zt-post-grid' ),
						self::news_green(),
					),
					false
				),
			),
			'meta'     => array( 'page_type' => 'blog' ),
		);

		$p['about'] = array(
			'title'    => 'درباره ما',
			'slug'     => 'about',
			'elements' => array(
				self::con(
					array(
						self::main( array( $crumb(), $head( 'درباره زیته', 'جوانه‌ای برای مراقبت از خودت' ) ), false ),
						self::w( 'zt-story', array( 'mobile_short' => '' ) ),
						self::w( 'zt-pledge' ),
						self::w( 'zt-brands' ),
						self::main( array( self::trust( 'default', array( 0, 0, 46, 0 ) ) ), false ),
					)
				),
			),
			'meta'     => array(),
		);

		$p['contact'] = array(
			'title'    => 'تماس با ما',
			'slug'     => 'contact',
			'elements' => array(
				self::main(
					array(
						$crumb(),
						$head( 'تماس با ما', 'سؤال، پیشنهاد یا انتقادی دارید؟ خوشحال می‌شویم از شما بشنویم.' ),
						self::con( array( self::w( 'zt-contact-info' ), self::w( 'zt-contact-form' ) ), 'dash-grid zt-con' ),
						self::trust( 'default', array( 34, 0, 26, 0 ) ),
					)
				),
			),
			'meta'     => array( 'footer_width' => 'narrow' ),
		);

		$p['terms'] = array(
			'title'    => 'قوانین و شرایط',
			'slug'     => 'terms',
			'elements' => array(
				self::main(
					array(
						$crumb(),
						$head( 'قوانین و شرایط', 'لطفاً پیش از ثبت سفارش، قوانین و شرایط استفاده از زیته را مطالعه کنید.' ),
						self::con( array( self::w( 'zt-rich-text' ) ), 'guide zt-con', 'article' ),
						self::trust( 'default', array( 34, 0, 26, 0 ) ),
					)
				),
			),
			'meta'     => array( 'footer_width' => 'narrow' ),
		);

		return $p;
	}

	/**
	 * Templates to build.
	 *
	 * @return array slot => [type, title, elements, meta]
	 */
	public static function templates() {
		$t = array();

		$t['header'] = array( 'header', 'هدر زیته', array( self::con( array( self::w( 'zt-header' ) ) ) ), array() );
		$t['footer'] = array( 'footer', 'فوتر زیته', array( self::con( array( self::w( 'zt-footer' ) ) ) ), array() );

		$t['single_product'] = array(
			'single_product',
			'قالب تک‌محصول زیته',
			array(
				self::con(
					array(
						self::con(
							array(
								self::w( 'zt-product-gallery' ),
								self::con(
									array(
										self::w( 'zt-breadcrumb', array( 'sep' => '/' ) ),
										self::w( 'zt-product-category' ),
										self::w( 'zt-product-title' ),
										self::w( 'zt-product-rating' ),
										self::w( 'zt-product-excerpt' ),
										self::w( 'zt-product-features' ),
										self::w( 'zt-product-shipping' ),
									),
									'pdp__info zt-con'
								),
								self::w( 'zt-product-buybox' ),
							),
							'pdp zt-con'
						),
						self::w( 'zt-product-tabs' ),
						self::w( 'zt-rule-title', array( 'title' => 'محصولات مرتبط' ) ),
						self::w( 'zt-related-products' ),
						self::w( 'zt-rule-title', array( 'title' => 'نظرات کاربران' ) ),
						self::w( 'zt-product-reviews' ),
						self::trust( 'product', array( 46, 0, 0, 0 ) ),
						self::news_green(),
					),
					'container zt-con',
					'main'
				),
			),
			array( 'footer_copy' => 'کلیه حقوق محفوظ است برای زیته.' ),
		);

		$t['product_archive'] = array(
			'product_archive',
			'قالب آرشیو محصولات زیته',
			array(
				self::main(
					array(
						self::w( 'zt-breadcrumb' ),
						self::w( 'zt-page-head', array( 'dynamic' => 'yes', 'sub' => 'بهترین محصولات مراقبتی، با ضمانت اصالت و ارسال سریع' ) ),
						self::w( 'zt-product-grid' ),
						self::trust( 'product', array( 46, 0, 0, 0 ) ),
						self::news_green(),
					),
					false
				),
			),
			array( 'page_type' => 'shop' ),
		);

		$t['single_post'] = array(
			'single_post',
			'قالب تک‌نوشته زیته',
			array(
				self::main(
					array(
						self::w( 'zt-breadcrumb' ),
						self::w( 'zt-post-header' ),
						self::w( 'zt-post-content' ),
						self::w( 'zt-post-footer' ),
						self::w( 'zt-rule-title', array( 'title' => 'مقالات مرتبط' ) ),
						self::w( 'zt-post-grid', array( 'mode' => 'related', 'limit' => 3, 'toolbar' => '', 'pagination' => '' ) ),
						self::news_green(),
					)
				),
			),
			array( 'page_type' => 'post' ),
		);

		$t['post_archive'] = array(
			'post_archive',
			'قالب آرشیو مقالات زیته',
			array(
				self::main(
					array(
						self::w( 'zt-breadcrumb' ),
						self::w( 'zt-page-head', array( 'dynamic' => 'yes', 'sub' => '' ) ),
						self::w( 'zt-post-grid', array( 'mode' => 'main' ) ),
						self::news_green(),
					),
					false
				),
			),
			array( 'page_type' => 'blog' ),
		);

		$t['404'] = array(
			'page_404',
			'قالب ۴۰۴ زیته',
			array(
				self::main(
					array(
						self::w( 'zt-not-found' ),
						self::w( 'zt-rule-title', array( 'title' => 'شاید این‌ها را دوست داشته باشید' ) ),
						self::w( 'zt-related-products', array( 'q_type' => 'latest', 'src' => 'auto' ) ),
						self::trust( 'default', array( 46, 0, 26, 0 ) ),
					)
				),
			),
			array(),
		);

		$t['search'] = array(
			'search',
			'قالب نتایج جستجو زیته',
			array(
				self::main(
					array(
						self::w( 'zt-breadcrumb' ),
						self::w( 'zt-page-head', array( 'dynamic' => 'yes', 'sub' => '' ) ),
						self::w( 'zt-product-grid', array( 'toolbar_cats' => '' ) ),
						self::trust( 'default', array( 46, 0, 26, 0 ) ),
					),
					false
				),
			),
			array( 'page_type' => 'shop' ),
		);

		return $t;
	}

	/* ------------------------------------------------------------ builder */

	/**
	 * Save Elementor data on a post.
	 *
	 * @param int   $post_id  Post.
	 * @param array $elements Elements.
	 * @param array $settings Page settings.
	 */
	public static function save_elementor( $post_id, $elements, $settings = array() ) {
		$json = wp_json_encode( $elements );
		update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $post_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.25.0' );
		update_post_meta( $post_id, '_elementor_data', wp_slash( $json ) );
		if ( $settings ) {
			update_post_meta( $post_id, '_elementor_page_settings', $settings );
		}
		if ( did_action( 'elementor/loaded' ) ) {
			$doc = \Elementor\Plugin::$instance->documents->get( $post_id, false );
			if ( $doc ) {
				try {
					$doc->save(
						array(
							'elements' => json_decode( $json, true ),
							'settings' => $settings,
						)
					);
				} catch ( \Throwable $e ) {
					// Raw meta is already stored above.
					unset( $e );
				}
			}
		}
	}

	/**
	 * Existing page to reuse for a page key: the WooCommerce page, a page built
	 * earlier by Ziteh, or an empty page with the same slug.
	 *
	 * @param string $key  Page key.
	 * @param string $slug Slug.
	 * @return int
	 */
	public static function adopt( $key, $slug ) {
		$wc = array(
			'shop'     => 'shop',
			'cart'     => 'cart',
			'checkout' => 'checkout',
			'account'  => 'myaccount',
		);
		if ( isset( $wc[ $key ] ) && function_exists( 'wc_get_page_id' ) ) {
			$id = (int) wc_get_page_id( $wc[ $key ] );
			if ( $id > 0 && 'publish' === get_post_status( $id ) ) {
				return $id;
			}
		}
		$found = get_posts(
			array(
				'post_type'   => 'page',
				'post_status' => array( 'publish', 'draft', 'private' ),
				'meta_key'    => '_zt_built', // phpcs:ignore
				'meta_value'  => $key, // phpcs:ignore
				'numberposts' => 1,
				'fields'      => 'ids',
			)
		);
		if ( $found ) {
			return (int) $found[0];
		}
		$page = get_page_by_path( $slug );
		if ( $page && 'page' === $page->post_type && '' === trim( wp_strip_all_tags( $page->post_content ) ) && ! get_post_meta( $page->ID, '_elementor_data', true ) ) {
			return (int) $page->ID;
		}
		return 0;
	}

	/**
	 * Build (or rebuild) everything.
	 *
	 * @param array $opts overwrite (bool): rebuild existing pages too; front (bool) set front page; woo (bool) assign WC pages; only (array) keys.
	 * @return array Log lines.
	 */
	public static function build( $opts = array() ) {
		$o   = wp_parse_args(
			$opts,
			array(
				'overwrite' => false,
				'front'     => true,
				'woo'       => true,
				'only'      => array(),
			)
		);
		$log = array();
		require_once ZT_PATH . 'widgets/guide/defaults.php';

		foreach ( self::pages() as $key => $def ) {
			if ( $o['only'] && ! in_array( $key, $o['only'], true ) ) {
				continue;
			}
			$id = (int) zt_opt( 'pages.' . $key, 0 );
			if ( $id && ! get_post( $id ) ) {
				$id = 0;
			}
			$exists = (bool) $id;
			if ( ! $id ) {
				$id = self::adopt( $key, $def['slug'] );
			}
			if ( ! $id ) {
				$id = wp_insert_post(
					array(
						'post_type'   => 'page',
						'post_status' => 'publish',
						'post_title'  => $def['title'],
						'post_name'   => $def['slug'],
						'post_content' => '',
					)
				);
			}
			if ( ! $id || is_wp_error( $id ) ) {
				$log[] = 'خطا در ساخت صفحه ' . $def['title'];
				continue;
			}
			ZT_Settings::set( 'pages.' . $key, $id );
			update_post_meta( $id, '_zt_built', $key );
			if ( ! $exists || $o['overwrite'] ) {
				update_post_meta( $id, '_wp_page_template', 'zt-canvas' );
				$ps = array();
				foreach ( $def['meta'] as $mk => $mv ) {
					$ps[ 'zt_' . $mk ] = $mv;
					update_post_meta( $id, '_zt_' . $mk, $mv );
				}
				if ( $def['elements'] ) {
					self::save_elementor( $id, $def['elements'], $ps );
				} else {
					update_post_meta( $id, '_elementor_page_settings', $ps );
				}
				$sc = array(
					'cart'     => '[woocommerce_cart]',
					'checkout' => '[woocommerce_checkout]',
					'account'  => '[woocommerce_my_account]',
				);
				if ( isset( $sc[ $key ] ) ) {
					// Classic shortcode in post_content so WooCommerce treats the page as the classic cart/checkout/account.
					wp_update_post( array( 'ID' => $id, 'post_content' => $sc[ $key ] ) );
				}
				$log[] = ( $exists ? 'بازسازی شد: ' : 'ساخته شد: ' ) . $def['title'];
			} else {
				$log[] = 'موجود بود (بدون تغییر): ' . $def['title'];
			}
		}

		foreach ( self::templates() as $slot => $def ) {
			if ( $o['only'] && ! in_array( 'tpl_' . $slot, $o['only'], true ) ) {
				continue;
			}
			$id = (int) zt_opt( 'pages.tpl_' . $slot, 0 );
			if ( $id && ! get_post( $id ) ) {
				$id = 0;
			}
			$exists = (bool) $id;
			if ( ! $id ) {
				$id = wp_insert_post(
					array(
						'post_type'   => 'zt_template',
						'post_status' => 'publish',
						'post_title'  => $def[1],
					)
				);
			}
			if ( ! $id || is_wp_error( $id ) ) {
				continue;
			}
			update_post_meta( $id, '_zt_tpl_type', $def[0] );
			ZT_Settings::set( 'pages.tpl_' . $slot, $id );
			if ( ! $exists || $o['overwrite'] ) {
				$ps = array();
				foreach ( $def[3] as $mk => $mv ) {
					$ps[ 'zt_' . $mk ] = $mv;
					update_post_meta( $id, '_zt_' . $mk, $mv );
				}
				update_post_meta( $id, '_wp_page_template', 'zt-canvas' );
				self::save_elementor( $id, $def[2], $ps );
				$log[] = ( $exists ? 'قالب بازسازی شد: ' : 'قالب ساخته شد: ' ) . $def[1];
			}
		}

		if ( $o['front'] && ! $o['only'] ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', (int) zt_opt( 'pages.home' ) );
			$log[] = 'صفحه اصلی سایت تنظیم شد.';
		}
		if ( $o['woo'] && zt_is_woo() && ! $o['only'] ) {
			update_option( 'woocommerce_shop_page_id', (int) zt_opt( 'pages.shop' ) );
			update_option( 'woocommerce_cart_page_id', (int) zt_opt( 'pages.cart' ) );
			update_option( 'woocommerce_checkout_page_id', (int) zt_opt( 'pages.checkout' ) );
			update_option( 'woocommerce_myaccount_page_id', (int) zt_opt( 'pages.account' ) );
			if ( zt_opt( 'pages.terms' ) ) {
				update_option( 'woocommerce_terms_page_id', (int) zt_opt( 'pages.terms' ) );
			}
			$log[] = 'صفحات ووکامرس (فروشگاه، سبد، صورت‌حساب، حساب کاربری) تنظیم شدند.';
			$log   = array_merge( $log, self::setup_woo() );
		}
		if ( did_action( 'elementor/loaded' ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}
		flush_rewrite_rules();
		return $log;
	}

	/**
	 * WooCommerce defaults: Iran, Toman, Ziteh shipping zone.
	 *
	 * @return array Log.
	 */
	public static function setup_woo() {
		$log = array();
		if ( ! class_exists( 'WC_Shipping_Zones' ) ) {
			return $log;
		}
		if ( ! get_option( 'zt_woo_setup_done' ) ) {
			update_option( 'woocommerce_default_country', 'IR:THR' );
			update_option( 'woocommerce_allowed_countries', 'specific' );
			update_option( 'woocommerce_specific_allowed_countries', array( 'IR' ) );
			update_option( 'woocommerce_ship_to_countries', '' );
			update_option( 'woocommerce_currency', 'IRT' );
			update_option( 'woocommerce_price_num_decimals', 0 );
			update_option( 'woocommerce_price_thousand_sep', ',' );
			update_option( 'woocommerce_enable_coupons', 'yes' );
			update_option( 'woocommerce_calc_shipping', 'yes' );
			update_option( 'woocommerce_ship_to_destination', 'billing_only' );
			update_option( 'woocommerce_enable_guest_checkout', 'yes' );
			update_option( 'woocommerce_enable_myaccount_registration', 'yes' );
			update_option( 'woocommerce_registration_generate_password', 'no' );
			update_option( 'woocommerce_enable_reviews', 'yes' );
			update_option( 'woocommerce_review_rating_verification_required', 'no' );
			update_option( 'zt_woo_setup_done', 1 );
			$log[] = 'تنظیمات پایه ووکامرس (ایران، تومان، ارسال) اعمال شد.';
		}
		// Ziteh shipping zone.
		$found = false;
		foreach ( WC_Shipping_Zones::get_zones() as $z ) {
			foreach ( $z['shipping_methods'] as $m ) {
				if ( 'zt_shipping' === $m->id ) {
					$found = true;
				}
			}
		}
		if ( ! $found ) {
			$zone = new WC_Shipping_Zone();
			$zone->set_zone_name( 'ایران' );
			$zone->set_zone_order( 0 );
			$zone->add_location( 'IR', 'country' );
			$zone->save();
			$zone->add_shipping_method( 'zt_shipping' );
			$log[] = 'منطقه حمل‌ونقل «ایران» با روش «ارسال زیته» ساخته شد.';
		}
		return $log;
	}
}
