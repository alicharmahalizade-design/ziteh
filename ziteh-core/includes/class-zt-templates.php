<?php
/**
 * Ziteh template system (works with the free Elementor):
 *  - "zt_template" post type edited with Elementor (header, footer, single
 *    product, product archive, single post, post archive, 404, search, section)
 *  - page templates "زیته — کامل" (Ziteh header + content + footer) and
 *    "زیته — خالی" (content only)
 *  - routing: single product / archives / posts / 404 / search use the
 *    templates chosen in settings (per product override supported)
 *  - site-wide header/footer injection for Hello Elementor pages
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_Templates
 */
class ZT_Templates {

	const CPT = 'zt_template';

	/**
	 * True while one of our own full-page templates prints the page (so the
	 * wp_body_open / get_footer injectors don't print a second header/footer).
	 *
	 * @var bool
	 */
	public static $canvas = false;

	/**
	 * Header already printed.
	 *
	 * @var bool
	 */
	private static $header_done = false;

	/**
	 * Footer already printed.
	 *
	 * @var bool
	 */
	private static $footer_done = false;

	/**
	 * Template id used for the current request.
	 *
	 * @var int
	 */
	public static $current = 0;

	/**
	 * Types.
	 *
	 * @return array
	 */
	public static function types() {
		return array(
			'header'          => 'هدر',
			'footer'          => 'فوتر',
			'single_product'  => 'تک‌محصول',
			'product_archive' => 'آرشیو محصولات',
			'single_post'     => 'تک‌نوشته',
			'post_archive'    => 'آرشیو نوشته‌ها',
			'page_404'        => 'صفحه ۴۰۴',
			'search'          => 'نتایج جستجو',
			'section'         => 'بخش (قابل استفاده مجدد)',
		);
	}

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_filter( 'theme_page_templates', array( __CLASS__, 'page_templates' ) );
		add_filter( 'template_include', array( __CLASS__, 'template_include' ), 99 );
		add_action( 'wp_body_open', array( __CLASS__, 'inject_header' ), 20 );
		add_action( 'get_footer', array( __CLASS__, 'inject_footer' ), 5 );
		add_action( 'add_meta_boxes', array( __CLASS__, 'meta_boxes' ) );
		add_action( 'save_post', array( __CLASS__, 'save_meta' ), 10, 2 );
		add_filter( 'manage_' . self::CPT . '_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_' . self::CPT . '_posts_custom_column', array( __CLASS__, 'column' ), 10, 2 );
	}

	/**
	 * Register the post type.
	 */
	public static function register() {
		register_post_type(
			self::CPT,
			array(
				'labels'              => array(
					'name'          => 'قالب‌های زیته',
					'singular_name' => 'قالب زیته',
					'add_new'       => 'افزودن قالب',
					'add_new_item'  => 'افزودن قالب جدید',
					'edit_item'     => 'ویرایش قالب',
					'all_items'     => 'قالب‌ها',
					'menu_name'     => 'قالب‌ها',
				),
				'public'              => false,
				'publicly_queryable'  => true,
				'exclude_from_search' => true,
				'show_ui'             => true,
				'show_in_menu'        => 'ziteh-core',
				'show_in_nav_menus'   => false,
				'show_in_rest'        => true,
				'rewrite'             => false,
				'query_var'           => true,
				'supports'            => array( 'title', 'editor', 'elementor', 'author' ),
				'capability_type'     => 'page',
				'map_meta_cap'        => true,
			)
		);
		add_post_type_support( self::CPT, 'elementor' );

		if ( get_option( 'zt_flush_rewrite' ) ) {
			delete_option( 'zt_flush_rewrite' );
			add_action( 'wp_loaded', 'flush_rewrite_rules' );
		}
	}

	/**
	 * Page templates offered in the page editor.
	 *
	 * @param array $t Templates.
	 * @return array
	 */
	public static function page_templates( $t ) {
		$t['zt-canvas'] = 'زیته — کامل (هدر و فوتر زیته)';
		$t['zt-blank']  = 'زیته — خالی (بدون هدر و فوتر)';
		return $t;
	}

	/**
	 * Published template id for a settings slot.
	 *
	 * @param string $slot e.g. single_product.
	 * @return int
	 */
	public static function slot( $slot ) {
		$id = (int) zt_opt( 'pages.tpl_' . $slot, 0 );
		return ( $id && 'publish' === get_post_status( $id ) ) ? $id : 0;
	}

	/**
	 * Route requests to our templates.
	 *
	 * @param string $template Template path.
	 * @return string
	 */
	public static function template_include( $template ) {
		if ( is_singular( self::CPT ) ) {
			self::$current = get_the_ID();
			return ZT_PATH . 'templates/template-preview.php';
		}
		if ( is_page() ) {
			$slug = get_page_template_slug( get_queried_object_id() );
			if ( 'zt-canvas' === $slug ) {
				return ZT_PATH . 'templates/canvas.php';
			}
			if ( 'zt-blank' === $slug ) {
				return ZT_PATH . 'templates/blank.php';
			}
		}
		$tpl = 0;
		if ( zt_is_woo() && is_product() ) {
			$over = (int) get_post_meta( get_queried_object_id(), '_zt_template', true );
			$tpl  = ( $over && 'publish' === get_post_status( $over ) ) ? $over : self::slot( 'single_product' );
		} elseif ( zt_is_woo() && ( is_shop() || is_product_taxonomy() ) ) {
			$tpl = self::slot( 'product_archive' );
		} elseif ( is_search() ) {
			$tpl = self::slot( 'search' );
			if ( ! $tpl && zt_is_woo() && 'product' === get_query_var( 'post_type' ) ) {
				$tpl = self::slot( 'product_archive' );
			}
		} elseif ( is_404() ) {
			$tpl = self::slot( '404' );
		} elseif ( is_singular( 'post' ) ) {
			$tpl = self::slot( 'single_post' );
		} elseif ( is_home() || is_category() || is_tag() || is_author() || is_date() ) {
			$tpl = self::slot( 'post_archive' );
		}
		if ( $tpl ) {
			self::$current = $tpl;
			return ZT_PATH . 'templates/dynamic.php';
		}
		return $template;
	}

	/**
	 * Render a template's Elementor content.
	 *
	 * @param int  $id       Template id.
	 * @param bool $with_css Print its CSS inline.
	 */
	public static function render( $id, $with_css = true ) {
		$id = (int) $id;
		if ( ! $id ) {
			return;
		}
		if ( did_action( 'elementor/loaded' ) && \Elementor\Plugin::$instance->documents->get( $id ) && \Elementor\Plugin::$instance->documents->get( $id )->is_built_with_elementor() ) {
			echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $id, $with_css ); // phpcs:ignore
		} else {
			$p = get_post( $id );
			if ( $p ) {
				echo apply_filters( 'the_content', $p->post_content ); // phpcs:ignore
			}
		}
	}

	/**
	 * Print the header location.
	 */
	public static function render_header() {
		if ( self::$header_done ) {
			return;
		}
		self::$header_done = true;
		$id = self::slot( 'header' );
		if ( ! $id || self::is_type( get_the_ID(), 'header' ) && is_singular( self::CPT ) ) {
			return;
		}
		echo '<div class="zt-site-header">';
		self::render( $id );
		echo '</div>';
	}

	/**
	 * Print the footer location.
	 */
	public static function render_footer() {
		if ( self::$footer_done ) {
			return;
		}
		self::$footer_done = true;
		$id = self::slot( 'footer' );
		if ( ! $id ) {
			return;
		}
		echo '<div class="zt-site-footer">';
		self::render( $id );
		echo '</div>';
	}

	/**
	 * Template type check.
	 *
	 * @param int    $id   Id.
	 * @param string $type Type.
	 * @return bool
	 */
	public static function is_type( $id, $type ) {
		return $id && get_post_meta( $id, '_zt_tpl_type', true ) === $type;
	}

	/**
	 * Should the site-wide injection run on this request?
	 *
	 * @return bool
	 */
	private static function inject_allowed() {
		if ( self::$canvas || is_admin() || ! zt_opt( 'general.sitewide_header_footer', 1 ) || ! ZT_Shell::design_on() ) {
			return false;
		}
		if ( is_singular() && in_array( get_page_template_slug( get_queried_object_id() ), array( 'elementor_canvas', 'zt-blank' ), true ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Inject the header right after <body> on theme-rendered pages.
	 */
	public static function inject_header() {
		if ( self::inject_allowed() ) {
			self::render_header();
		}
	}

	/**
	 * Inject the footer before the theme footer is loaded.
	 */
	public static function inject_footer() {
		if ( self::inject_allowed() ) {
			self::render_footer();
		}
	}

	/**
	 * Meta boxes.
	 */
	public static function meta_boxes() {
		add_meta_box( 'zt-tpl-type', 'تنظیمات قالب زیته', array( __CLASS__, 'box_template' ), self::CPT, 'side', 'high' );
		if ( post_type_exists( 'product' ) ) {
			add_meta_box( 'zt-tpl-product', 'قالب زیته', array( __CLASS__, 'box_product' ), 'product', 'side', 'default' );
		}
	}

	/**
	 * Template settings box.
	 *
	 * @param WP_Post $post Post.
	 */
	public static function box_template( $post ) {
		wp_nonce_field( 'zt_tpl', 'zt_tpl_nonce' );
		$type = get_post_meta( $post->ID, '_zt_tpl_type', true );
		echo '<p><label><b>نوع قالب</b></label><br><select name="zt_tpl_type" style="width:100%">';
		foreach ( self::types() as $k => $v ) {
			echo '<option value="' . esc_attr( $k ) . '"' . selected( $type, $k, false ) . '>' . esc_html( $v ) . '</option>';
		}
		echo '</select></p>';
		echo '<p><label><b>محصول پیش‌نمایش (شناسه)</b></label><br><input type="number" name="zt_preview_id" value="' . esc_attr( get_post_meta( $post->ID, '_zt_preview_id', true ) ) . '" style="width:100%"><br><small>برای قالب تک‌محصول: هنگام ویرایش در المنتور اطلاعات این محصول نمایش داده می‌شود (خالی = آخرین محصول).</small></p>';
		$slot = array_search( $type, array( 'header' => 'header', 'footer' => 'footer', 'single_product' => 'single_product', 'product_archive' => 'product_archive', 'single_post' => 'single_post', 'post_archive' => 'post_archive', '404' => 'page_404', 'search' => 'search' ), true );
		if ( $slot ) {
			$is = (int) zt_opt( 'pages.tpl_' . $slot ) === $post->ID;
			echo '<p><label><input type="checkbox" name="zt_tpl_default" value="1"' . checked( $is, true, false ) . '> استفاده به‌عنوان قالب پیش‌فرض سایت</label></p>';
		}
	}

	/**
	 * Per-product template override box.
	 *
	 * @param WP_Post $post Post.
	 */
	public static function box_product( $post ) {
		wp_nonce_field( 'zt_tpl', 'zt_tpl_nonce' );
		$cur  = (int) get_post_meta( $post->ID, '_zt_template', true );
		$list = get_posts(
			array(
				'post_type'   => self::CPT,
				'numberposts' => 50,
				'meta_key'    => '_zt_tpl_type', // phpcs:ignore
				'meta_value'  => 'single_product', // phpcs:ignore
			)
		);
		echo '<select name="zt_product_template" style="width:100%"><option value="0">پیش‌فرض سایت</option>';
		foreach ( $list as $t ) {
			echo '<option value="' . esc_attr( $t->ID ) . '"' . selected( $cur, $t->ID, false ) . '>' . esc_html( $t->post_title ) . '</option>';
		}
		echo '</select>';
	}

	/**
	 * Save meta.
	 *
	 * @param int     $post_id Id.
	 * @param WP_Post $post    Post.
	 */
	public static function save_meta( $post_id, $post ) {
		if ( ! isset( $_POST['zt_tpl_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['zt_tpl_nonce'] ), 'zt_tpl' ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( self::CPT === $post->post_type ) {
			$type = isset( $_POST['zt_tpl_type'] ) ? sanitize_key( $_POST['zt_tpl_type'] ) : '';
			if ( isset( self::types()[ $type ] ) ) {
				update_post_meta( $post_id, '_zt_tpl_type', $type );
			}
			update_post_meta( $post_id, '_zt_preview_id', isset( $_POST['zt_preview_id'] ) ? absint( $_POST['zt_preview_id'] ) : 0 );
			if ( ! empty( $_POST['zt_tpl_default'] ) ) {
				$slot = 'page_404' === $type ? '404' : $type;
				ZT_Settings::set( 'pages.tpl_' . $slot, $post_id );
			}
		} elseif ( 'product' === $post->post_type && isset( $_POST['zt_product_template'] ) ) {
			update_post_meta( $post_id, '_zt_template', absint( $_POST['zt_product_template'] ) );
		}
	}

	/**
	 * List columns.
	 *
	 * @param array $c Columns.
	 * @return array
	 */
	public static function columns( $c ) {
		$new = array();
		foreach ( $c as $k => $v ) {
			$new[ $k ] = $v;
			if ( 'title' === $k ) {
				$new['zt_type']    = 'نوع';
				$new['zt_default'] = 'پیش‌فرض';
			}
		}
		return $new;
	}

	/**
	 * Column content.
	 *
	 * @param string $col Column.
	 * @param int    $id  Post id.
	 */
	public static function column( $col, $id ) {
		$type = get_post_meta( $id, '_zt_tpl_type', true );
		if ( 'zt_type' === $col ) {
			$t = self::types();
			echo esc_html( isset( $t[ $type ] ) ? $t[ $type ] : '—' );
		}
		if ( 'zt_default' === $col ) {
			$slot = 'page_404' === $type ? '404' : $type;
			echo (int) zt_opt( 'pages.tpl_' . $slot ) === (int) $id ? '<span class="dashicons dashicons-yes" style="color:#6B7457"></span>' : '';
		}
	}
}
