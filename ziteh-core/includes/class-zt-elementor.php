<?php
/**
 * Elementor integration: categories (one per page), widgets, icon library,
 * per-page settings and dynamic tags.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_Elementor
 */
class ZT_Elementor {

	/**
	 * Widget groups => panel titles.
	 *
	 * @return array
	 */
	public static function groups() {
		return array(
			'global'   => 'زیته | عمومی (هدر، فوتر، مشترک)',
			'home'     => 'زیته | صفحه اصلی',
			'shop'     => 'زیته | فروشگاه و آرشیو',
			'product'  => 'زیته | صفحه محصول',
			'cart'     => 'زیته | سبد خرید',
			'checkout' => 'زیته | صورت‌حساب',
			'tracking' => 'زیته | پیگیری سفارش',
			'account'  => 'زیته | پنل کاربری',
			'guide'    => 'زیته | راهنمای روتین',
			'blog'     => 'زیته | وبلاگ و مجله',
			'pages'    => 'زیته | صفحات اطلاعاتی',
		);
	}

	/**
	 * Hooks.
	 */
	public static function init() {
		require_once ZT_PATH . 'includes/class-zt-parts.php';
		add_action( 'elementor/elements/categories_registered', array( __CLASS__, 'categories' ) );
		add_action( 'elementor/widgets/register', array( __CLASS__, 'widgets' ) );
		add_filter( 'elementor/icons_manager/additional_tabs', array( __CLASS__, 'icon_tab' ) );
		add_action( 'elementor/documents/register_controls', array( __CLASS__, 'document_controls' ) );
		add_action( 'elementor/dynamic_tags/register', array( __CLASS__, 'dynamic_tags' ) );
		add_action( 'elementor/editor/after_enqueue_scripts', array( __CLASS__, 'editor_scripts' ) );
		add_action( 'elementor/element/container/section_layout_container/after_section_end', array( __CLASS__, 'container_controls' ), 10, 2 );
	}

	/**
	 * Register categories.
	 *
	 * @param \Elementor\Elements_Manager $manager Manager.
	 */
	public static function categories( $manager ) {
		foreach ( self::groups() as $key => $title ) {
			$manager->add_category(
				'zt-' . $key,
				array(
					'title' => $title,
					'icon'  => 'eicon-leaf',
				)
			);
		}
	}

	/**
	 * Widget class files, grouped.
	 *
	 * @return array [ class => file ]
	 */
	public static function widget_files() {
		$out = array();
		foreach ( glob( ZT_PATH . 'widgets/*/class-zt-w-*.php' ) as $file ) {
			$base  = basename( $file, '.php' );
			$class = 'ZT_W_' . str_replace( ' ', '_', ucwords( str_replace( '-', ' ', substr( $base, strlen( 'class-zt-w-' ) ) ) ) );
			$out[ $class ] = $file;
		}
		return $out;
	}

	/**
	 * Register widgets.
	 *
	 * @param \Elementor\Widgets_Manager $manager Manager.
	 */
	public static function widgets( $manager ) {
		require_once ZT_PATH . 'widgets/class-zt-widget-base.php';
		foreach ( self::widget_files() as $class => $file ) {
			require_once $file;
			if ( class_exists( $class ) ) {
				$manager->register( new $class() );
			}
		}
	}

	/**
	 * Ziteh icons tab in the Elementor icon picker.
	 *
	 * @param array $tabs Tabs.
	 * @return array
	 */
	public static function icon_tab( $tabs ) {
		$tabs['zt-icons'] = array(
			'name'          => 'zt-icons',
			'label'         => 'آیکون‌های زیته',
			'url'           => ZT_URL . 'assets/css/zt-icons.css',
			'enqueue'       => array( ZT_URL . 'assets/css/zt-icons.css' ),
			'prefix'        => 'zti-',
			'displayPrefix' => 'zti',
			'labelIcon'     => 'zti zti-leaf',
			'ver'           => ZT_VERSION,
			'fetchJson'     => ZT_URL . 'assets/icons/icons.json',
			'native'        => false,
		);
		return $tabs;
	}

	/**
	 * Per-page settings (Elementor → page settings).
	 *
	 * @param \Elementor\Core\Base\Document $doc Document.
	 */
	public static function document_controls( $doc ) {
		if ( ! $doc instanceof \Elementor\Core\DocumentTypes\PageBase && ! $doc instanceof \Elementor\Core\Base\Document ) {
			return;
		}
		$doc->start_controls_section(
			'zt_page_section',
			array(
				'label' => 'تنظیمات صفحه زیته',
				'tab'   => \Elementor\Controls_Manager::TAB_SETTINGS,
			)
		);
		$doc->add_control(
			'zt_page_type',
			array(
				'label'   => 'نوع صفحه (برای رفتار موبایل)',
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
					''         => 'خودکار',
					'home'     => 'صفحه اصلی',
					'shop'     => 'فروشگاه',
					'product'  => 'محصول',
					'cart'     => 'سبد خرید',
					'checkout' => 'صورت‌حساب',
					'panel'    => 'پنل کاربری',
					'tracking' => 'پیگیری سفارش',
					'routine'  => 'راهنمای روتین',
					'blog'     => 'مجله',
					'inner'    => 'صفحه داخلی',
				),
			)
		);
		$doc->add_control(
			'zt_bottom_bar',
			array(
				'label'   => 'نوار پایین موبایل',
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
					''          => 'خودکار',
					'tabbar'    => 'تب‌بار',
					'actionbar' => 'نوار خرید',
					'none'      => 'هیچ',
				),
			)
		);
		$doc->add_control(
			'zt_mobile_title',
			array(
				'label' => 'عنوان اپ‌بار موبایل',
				'type'  => \Elementor\Controls_Manager::TEXT,
			)
		);
		$doc->add_control(
			'zt_header_layout',
			array(
				'label'   => 'چیدمان هدر',
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
					''      => 'خودکار',
					'home'  => 'صفحه اصلی (جستجو وسط، لوگو در منو)',
					'inner' => 'داخلی (لوگو وسط)',
				),
			)
		);
		$doc->add_control(
			'zt_footer_style',
			array(
				'label'   => 'سبک فوتر',
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
					''      => 'پیش‌فرض قالب فوتر',
					'white' => 'سفید تمام‌عرض',
					'cream' => 'کرم جعبه‌ای',
				),
			)
		);
		$doc->add_control(
			'zt_footer_width',
			array(
				'label'   => 'عرض فوتر',
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
					''       => 'پیش‌فرض',
					'wide'   => '۱۴۴۰',
					'narrow' => '۱۲۴۰',
				),
			)
		);
		$doc->add_control(
			'zt_footer_box',
			array(
				'label'       => 'حداکثر عرض جعبه فوتر (px)',
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'description' => 'خالی = پیش‌فرض قالب فوتر',
			)
		);
		$doc->add_control(
			'zt_footer_copy',
			array(
				'label' => 'متن کپی‌رایت در این صفحه',
				'type'  => \Elementor\Controls_Manager::TEXT,
			)
		);
		$doc->add_control(
			'zt_search_ph',
			array(
				'label' => 'متن جستجوی هدر در این صفحه',
				'type'  => \Elementor\Controls_Manager::TEXT,
			)
		);
		$doc->add_control(
			'zt_nav_rename',
			array(
				'label'       => 'عنوان منو در این صفحه',
				'type'        => \Elementor\Controls_Manager::TEXTAREA,
				'description' => 'هر خط: عنوان فعلی|عنوان جدید',
			)
		);
		$doc->end_controls_section();
	}

	/**
	 * Extra layout control on containers: apply a Ziteh layout preset.
	 *
	 * @param \Elementor\Element_Base $el   Element.
	 * @param array                   $args Args.
	 */
	public static function container_controls( $el, $args ) {
		$el->start_controls_section(
			'zt_layout_section',
			array(
				'label' => 'چیدمان زیته',
				'tab'   => \Elementor\Controls_Manager::TAB_LAYOUT,
			)
		);
		$el->add_control(
			'zt_layout',
			array(
				'label'        => 'قالب چیدمان طرح',
				'type'         => \Elementor\Controls_Manager::SELECT,
				'default'      => '',
				'prefix_class' => 'zt-',
				'options'      => array(
					''                                   => 'هیچ',
					'con'                                => 'بدون فاصله داخلی و بین آیتم‌ها',
					'container zt-con'                   => 'کانتینر صفحه (۱۴۴۰)',
					'container zt-container--narrow zt-con' => 'کانتینر باریک (۱۲۴۰)',
					'stack zt-con'                       => 'ستون بدون فاصله',
					'pdp zt-con'                         => 'شبکه محصول (گالری | اطلاعات | خرید)',
					'pdp__info zt-con'                   => 'ستون اطلاعات محصول',
					'shop-grid zt-con'                   => 'شبکه سبد خرید (محتوا | خلاصه)',
					'co-grid zt-con'                     => 'شبکه صورت‌حساب (فرم | خلاصه)',
					'panel-grid zt-con'                  => 'شبکه پنل (منو | محتوا)',
					'dash-grid zt-con'                   => 'شبکه داشبورد (دو ستون)',
					'trk-grid zt-con'                    => 'شبکه پیگیری (جزئیات | کناری)',
					'guide zt-con'                       => 'ستون راهنما (۸۲۰)',
				),
				'description'  => 'چیدمان‌های دقیق طرح، با همان رفتار ریسپانسیو.',
			)
		);
		$el->end_controls_section();
	}

	/**
	 * Dynamic tags.
	 *
	 * @param \Elementor\Core\DynamicTags\Manager $manager Manager.
	 */
	public static function dynamic_tags( $manager ) {
		require_once ZT_PATH . 'includes/class-zt-dynamic-tags.php';
		$manager->register_group( 'ziteh', array( 'title' => 'زیته' ) );
		$manager->register( new ZT_Tag_Meta() );
		$manager->register( new ZT_Tag_Meta_Url() );
		$manager->register( new ZT_Tag_Meta_Image() );
		$manager->register( new ZT_Tag_Price() );
		$manager->register( new ZT_Tag_Token_Url() );
	}

	/**
	 * Editor tweaks.
	 */
	public static function editor_scripts() {
		wp_enqueue_script( 'zt-editor', ZT_URL . 'assets/js/zt-editor.js', array( 'jquery' ), ZT_Assets::ver( 'assets/js/zt-editor.js' ), true );
	}
}
