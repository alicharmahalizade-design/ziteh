<?php
/**
 * Advanced WooCommerce single-product experience.
 *
 * @package Ziteh_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Repeater;

/**
 * Class Ziteh_Single_Product_Widget
 */
class Ziteh_Single_Product_Widget extends Ziteh_Widget_Base {

	public function get_name() {
		return 'ziteh-single-product';
	}

	public function get_title() {
		return esc_html__( '۱. بخش اصلی و خرید محصول', 'ziteh' );
	}

	public function get_icon() {
		return 'eicon-single-product';
	}

	public function get_keywords() {
		return array_merge( parent::get_keywords(), array( 'product', 'woocommerce', 'single', 'محصول', 'ووکامرس' ) );
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_product',
			array(
				'label' => esc_html__( 'محصول و نمایش', 'ziteh' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'product_id',
			array(
				'label'       => esc_html__( 'شناسه محصول برای پیش‌نمایش', 'ziteh' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 1,
				'description' => esc_html__( 'در صفحه محصول خالی بگذارید تا محصول جاری خودکار نمایش داده شود.', 'ziteh' ),
			)
		);

		$this->add_control(
			'show_breadcrumbs',
			array(
				'label'        => esc_html__( 'نمایش مسیر راهنما', 'ziteh' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'show_sale_badge',
			array(
				'label'        => esc_html__( 'نمایش نشان تخفیف', 'ziteh' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'show_rating',
			array(
				'label'        => esc_html__( 'نمایش امتیاز و دیدگاه', 'ziteh' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'show_short_description',
			array(
				'label'        => esc_html__( 'نمایش توضیح کوتاه', 'ziteh' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
		$this->add_control(
			'short_description_words',
			array(
				'label'     => esc_html__( 'حداکثر واژه توضیح کوتاه', 'ziteh' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 12,
				'max'       => 100,
				'default'   => 34,
				'condition' => array( 'show_short_description' => 'yes' ),
			)
		);

		$this->add_control(
			'show_details',
			array(
				'label'        => esc_html__( 'نمایش تب‌های اطلاعات', 'ziteh' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'description'  => esc_html__( 'قدیمی: در ساختار جدید از ویجت مستقل «توضیحات و ویژگی‌های محصول» استفاده کنید.', 'ziteh' ),
			)
		);

		$this->add_control(
			'show_related',
			array(
				'label'        => esc_html__( 'نمایش محصولات مرتبط', 'ziteh' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
				'description'  => esc_html__( 'قدیمی: در ساختار جدید از ویجت مستقل «محصولات مرتبط» استفاده کنید.', 'ziteh' ),
			)
		);

		$this->add_control(
			'related_count',
			array(
				'label'     => esc_html__( 'تعداد محصولات مرتبط', 'ziteh' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 2,
				'max'       => 8,
				'default'   => 4,
				'condition' => array( 'show_related' => 'yes' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_content',
			array(
				'label' => esc_html__( 'متن و معرفی محصول', 'ziteh' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control( 'eyebrow', array( 'label' => esc_html__( 'دسته/پیش‌عنوان', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'placeholder' => esc_html__( 'مثلاً مراقبت مو', 'ziteh' ) ) );
		$this->add_control( 'english_title', array( 'label' => esc_html__( 'عنوان انگلیسی', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'placeholder' => esc_html__( 'از نام محصول استفاده نمی‌شود؛ اختیاری است', 'ziteh' ) ) );
		$this->add_control( 'panel_badge', array( 'label' => esc_html__( 'نشان کارت خرید', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'پیشنهاد ویژه', 'ziteh' ) ) );
		$this->add_control( 'panel_title', array( 'label' => esc_html__( 'عنوان کارت خرید', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'زیته', 'ziteh' ) ) );
		$this->add_control( 'panel_description', array( 'label' => esc_html__( 'توضیح کارت خرید', 'ziteh' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 2, 'default' => esc_html__( 'محصولی از طبیعت برای مراقبت از شما', 'ziteh' ) ) );
		$this->add_control( 'add_to_cart_text', array( 'label' => esc_html__( 'متن دکمه خرید', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'افزودن به سبد خرید', 'ziteh' ) ) );
		$this->add_control( 'wishlist_text', array( 'label' => esc_html__( 'متن علاقه‌مندی', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'افزودن به لیست علاقه‌مندی', 'ziteh' ) ) );

		$this->end_controls_section();

		$this->start_controls_section( 'section_highlights', array( 'label' => esc_html__( 'ویژگی‌های کلیدی', 'ziteh' ), 'tab' => Controls_Manager::TAB_CONTENT ) );
		$highlight = new Repeater();
		$highlight->add_control( 'text', array( 'label' => esc_html__( 'متن ویژگی', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'ویژگی تخصصی محصول', 'ziteh' ), 'label_block' => true ) );
		$highlight->add_control( 'icon', array( 'label' => esc_html__( 'آیکن', 'ziteh' ), 'type' => Controls_Manager::ICONS, 'default' => array( 'value' => 'far fa-check-circle', 'library' => 'fa-regular' ) ) );
		$this->add_control( 'highlights', array( 'label' => esc_html__( 'فهرست ویژگی‌ها', 'ziteh' ), 'type' => Controls_Manager::REPEATER, 'fields' => $highlight->get_controls(), 'title_field' => '{{{ text }}}', 'default' => array( array( 'text' => esc_html__( 'کاهش محسوس ریزش مو از ریشه', 'ziteh' ), 'icon' => array( 'value' => 'far fa-check-circle', 'library' => 'fa-regular' ) ), array( 'text' => esc_html__( 'تقویت فولیکول‌ها و افزایش رشد مو', 'ziteh' ), 'icon' => array( 'value' => 'far fa-check-circle', 'library' => 'fa-regular' ) ), array( 'text' => esc_html__( 'ترکیبات ملایم مناسب پوست سر حساس', 'ziteh' ), 'icon' => array( 'value' => 'far fa-check-circle', 'library' => 'fa-regular' ) ), array( 'text' => esc_html__( 'مناسب انواع مو و استفاده روزانه', 'ziteh' ), 'icon' => array( 'value' => 'far fa-check-circle', 'library' => 'fa-regular' ) ) ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_shipping', array( 'label' => esc_html__( 'ارسال و تحویل', 'ziteh' ), 'tab' => Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'shipping_title', array( 'label' => esc_html__( 'عنوان ارسال', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'ارسال سریع و مطمئن', 'ziteh' ) ) );
		$this->add_control( 'shipping_text', array( 'label' => esc_html__( 'توضیح ارسال', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'ارسال به سراسر ایران در ۲ تا ۳ روز کاری', 'ziteh' ) ) );
		$this->add_control( 'shipping_icon', array( 'label' => esc_html__( 'آیکن ارسال', 'ziteh' ), 'type' => Controls_Manager::ICONS, 'default' => array( 'value' => 'fas fa-truck', 'library' => 'fa-solid' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_trust', array( 'label' => esc_html__( 'اعتمادسازی کارت خرید', 'ziteh' ), 'tab' => Controls_Manager::TAB_CONTENT ) );
		$trust = new Repeater();
		$trust->add_control( 'title', array( 'label' => esc_html__( 'عنوان', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'ضمانت اصالت', 'ziteh' ), 'label_block' => true ) );
		$trust->add_control( 'icon', array( 'label' => esc_html__( 'آیکن', 'ziteh' ), 'type' => Controls_Manager::ICONS, 'default' => array( 'value' => 'fas fa-award', 'library' => 'fa-solid' ) ) );
		$this->add_control( 'trust_items', array( 'label' => esc_html__( 'آیتم‌های اعتماد', 'ziteh' ), 'type' => Controls_Manager::REPEATER, 'fields' => $trust->get_controls(), 'title_field' => '{{{ title }}}', 'default' => array( array( 'title' => esc_html__( 'پرداخت امن', 'ziteh' ), 'icon' => array( 'value' => 'fas fa-shield-alt', 'library' => 'fa-solid' ) ), array( 'title' => esc_html__( 'ضمانت اصالت', 'ziteh' ), 'icon' => array( 'value' => 'fas fa-award', 'library' => 'fa-solid' ) ), array( 'title' => esc_html__( '۷ روز بازگشت کالا', 'ziteh' ), 'icon' => array( 'value' => 'fas fa-redo-alt', 'library' => 'fa-solid' ) ) ) ) );

		$this->end_controls_section();

		$this->start_controls_section( 'section_layout', array( 'label' => esc_html__( 'چیدمان', 'ziteh' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_responsive_control( 'max_width', array( 'label' => esc_html__( 'حداکثر عرض سکشن', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'size_units' => array( 'px' ), 'range' => array( 'px' => array( 'min' => 900, 'max' => 1680 ) ), 'default' => array( 'size' => 1280, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-sp' => '--ziteh-sp-max: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'purchase_width', array( 'label' => esc_html__( 'عرض کارت خرید', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 240, 'max' => 390 ) ), 'default' => array( 'size' => 300, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-sp' => '--ziteh-sp-buy: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'info_width', array( 'label' => esc_html__( 'عرض اطلاعات محصول', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 280, 'max' => 460 ) ), 'default' => array( 'size' => 350, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-sp' => '--ziteh-sp-info: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'column_gap', array( 'label' => esc_html__( 'فاصله ستون‌ها', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 8, 'max' => 72 ) ), 'default' => array( 'size' => 38, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-sp' => '--ziteh-sp-gap: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_control( 'image_ratio', array( 'label' => esc_html__( 'نسبت تصویر اصلی', 'ziteh' ), 'type' => Controls_Manager::SELECT, 'default' => '1 / 1.05', 'options' => array( '1 / 1.05' => esc_html__( 'مطابق طرح', 'ziteh' ), '1 / 1' => esc_html__( 'مربع', 'ziteh' ), '4 / 5' => esc_html__( 'عمودی', 'ziteh' ), '16 / 13' => esc_html__( 'افقی', 'ziteh' ) ), 'selectors' => array( '{{WRAPPER}} .ziteh-sp' => '--ziteh-sp-ratio: {{VALUE}};' ) ) );
		$this->add_control( 'image_fit', array( 'label' => esc_html__( 'نحوه نمایش تصویر', 'ziteh' ), 'type' => Controls_Manager::SELECT, 'default' => 'cover', 'options' => array( 'cover' => esc_html__( 'پوشش کامل', 'ziteh' ), 'contain' => esc_html__( 'نمایش کامل محصول', 'ziteh' ) ), 'selectors' => array( '{{WRAPPER}} .ziteh-sp' => '--ziteh-sp-fit: {{VALUE}};' ) ) );
		$this->add_control( 'sticky_purchase', array( 'label' => esc_html__( 'کارت خرید چسبان', 'ziteh' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_style', array( 'label' => esc_html__( 'رنگ‌ها و سطوح', 'ziteh' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		foreach ( array( 'page_bg' => array( 'پس‌زمینه سکشن', '#fbfaf7', '--ziteh-sp-bg' ), 'primary_color' => array( 'رنگ اصلی', '#748067', '--ziteh-sp-primary' ), 'heading_color' => array( 'رنگ تیتر', '#282b25', '--ziteh-sp-heading' ), 'text_color' => array( 'رنگ متن', '#6f716a', '--ziteh-sp-text' ), 'card_bg' => array( 'پس‌زمینه کارت', '#ffffff', '--ziteh-sp-card' ), 'border_color' => array( 'رنگ خطوط', '#e8e5df', '--ziteh-sp-border' ), 'rating_color' => array( 'رنگ ستاره', '#f5ae00', '--ziteh-sp-star' ) ) as $key => $data ) {
			$this->add_control( $key, array( 'label' => esc_html( $data[0] ), 'type' => Controls_Manager::COLOR, 'default' => $data[1], 'selectors' => array( '{{WRAPPER}} .ziteh-sp' => $data[2] . ': {{VALUE}};' ) ) );
		}
		$this->add_responsive_control( 'card_radius', array( 'label' => esc_html__( 'گردی کارت‌ها', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 48 ) ), 'default' => array( 'size' => 24, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-sp' => '--ziteh-sp-radius: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array( 'name' => 'purchase_shadow', 'label' => esc_html__( 'سایه کارت خرید', 'ziteh' ), 'selector' => '{{WRAPPER}} .ziteh-sp__purchase' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_typography', array( 'label' => esc_html__( 'تایپوگرافی', 'ziteh' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'title_typography', 'label' => esc_html__( 'عنوان محصول', 'ziteh' ), 'selector' => '{{WRAPPER}} .ziteh-sp__title' ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'body_typography', 'label' => esc_html__( 'متن توضیحات', 'ziteh' ), 'selector' => '{{WRAPPER}} .ziteh-sp__excerpt, {{WRAPPER}} .ziteh-sp__highlight' ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'price_typography', 'label' => esc_html__( 'قیمت', 'ziteh' ), 'selector' => '{{WRAPPER}} .ziteh-sp__price' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_seo', array( 'label' => esc_html__( 'SEO و دسترسی‌پذیری', 'ziteh' ), 'tab' => Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'heading_tag', array( 'label' => esc_html__( 'تگ عنوان محصول', 'ziteh' ), 'type' => Controls_Manager::SELECT, 'default' => 'h1', 'options' => array( 'h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3', 'div' => 'DIV' ) ) );
		$this->add_control( 'image_alt', array( 'label' => esc_html__( 'متن جایگزین تصویر اصلی', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'description' => esc_html__( 'خالی باشد از نام محصول استفاده می‌شود.', 'ziteh' ) ) );
		$this->add_control( 'main_image_loading', array( 'label' => esc_html__( 'اولویت بارگذاری تصویر اصلی', 'ziteh' ), 'type' => Controls_Manager::SELECT, 'default' => 'eager', 'options' => array( 'eager' => esc_html__( 'بالا — eager', 'ziteh' ), 'lazy' => esc_html__( 'عادی — lazy', 'ziteh' ) ) ) );
		$this->add_control( 'enable_schema', array( 'label' => esc_html__( 'Product Schema خارج از صفحه محصول', 'ziteh' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => '', 'description' => esc_html__( 'در صفحه اصلی محصول خاموش بماند چون WooCommerce اسکیما را تولید می‌کند.', 'ziteh' ) ) );
		$this->add_control( 'show_meta', array( 'label' => esc_html__( 'نمایش SKU و دسته‌بندی', 'ziteh' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => '' ) );
		$this->end_controls_section();
	}

	/**
	 * Find the current or preview product.
	 *
	 * @param array $settings Widget settings.
	 * @return WC_Product|false
	 */
	private function resolve_product( $settings ) {
		if ( ! function_exists( 'wc_get_product' ) ) {
			return false;
		}

		$product_id = ! empty( $settings['product_id'] ) ? (int) $settings['product_id'] : 0;

		if ( ! $product_id && function_exists( 'is_product' ) && is_product() ) {
			$product_id = get_queried_object_id();
		}

		if ( ! $product_id ) {
			$current_id = get_the_ID();
			if ( 'product' === get_post_type( $current_id ) ) {
				$product_id = $current_id;
			}
		}

		if ( ! $product_id && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			$ids = wc_get_products(
				array(
					'status' => 'publish',
					'limit'  => 1,
					'return' => 'ids',
				)
			);
			$product_id = ! empty( $ids ) ? (int) $ids[0] : 0;
		}

		return $product_id ? wc_get_product( $product_id ) : false;
	}

	/**
	 * Gallery image IDs with the featured image first.
	 *
	 * @param WC_Product $product Product.
	 * @return int[]
	 */
	private function gallery_ids( $product ) {
		$ids = array_filter( array_merge( array( $product->get_image_id() ), $product->get_gallery_image_ids() ) );
		return array_values( array_unique( array_map( 'absint', $ids ) ) );
	}

	/**
	 * Best-effort brand label.
	 *
	 * @param WC_Product $product Product.
	 * @return string
	 */
	private function brand( $product ) {
		foreach ( array( 'pa_brand', 'product_brand', 'pwb-brand' ) as $taxonomy ) {
			$terms = get_the_terms( $product->get_id(), $taxonomy );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				return $terms[0]->name;
			}
		}
		return '';
	}

	/**
	 * Discount percentage.
	 *
	 * @param WC_Product $product Product.
	 * @return int
	 */
	private function discount( $product ) {
		$regular = (float) $product->get_regular_price();
		$active  = (float) $product->get_price();
		return ( $regular > 0 && $active > 0 && $active < $regular )
			? (int) round( ( ( $regular - $active ) / $regular ) * 100 )
			: 0;
	}

	protected function render() {
		$settings         = $this->get_settings_for_display();
		$resolved_product = $this->resolve_product( $settings );

		if ( ! $resolved_product ) {
			echo '<div class="ziteh-sp__notice">' . esc_html__( 'برای نمایش صفحه محصول، ووکامرس را فعال و یک محصول انتخاب کنید.', 'ziteh' ) . '</div>';
			return;
		}

		if ( $resolved_product->is_type( 'variable' ) ) {
			wp_enqueue_script( 'wc-add-to-cart-variation' );
		}

		global $product;
		$previous_product = $product;
		$product          = wc_get_product( $resolved_product->get_id() );

		$gallery_ids = $this->gallery_ids( $product );
		$brand       = $this->brand( $product );
		$discount    = $this->discount( $product );
		$average     = (float) $product->get_average_rating();
		$rating_qty  = (int) $product->get_rating_count();
		$heading_tag = in_array( $settings['heading_tag'], array( 'h1', 'h2', 'h3', 'div' ), true ) ? $settings['heading_tag'] : 'h1';
		$eyebrow     = ! empty( $settings['eyebrow'] ) ? $settings['eyebrow'] : ( $brand ? $brand : esc_html__( 'محصول زیته', 'ziteh' ) );
		$review_id   = 'ziteh-sp-reviews-' . $this->get_id();
		$sticky      = 'yes' === $settings['sticky_purchase'] ? ' is-sticky' : '';
		$image_alt   = ! empty( $settings['image_alt'] ) ? $settings['image_alt'] : $product->get_name();
		?>
		<article class="ziteh-sp" data-ziteh-single-product>
			<div class="ziteh-sp__container">
				<div class="ziteh-sp__hero">
					<section class="ziteh-sp__gallery" aria-label="<?php esc_attr_e( 'گالری تصاویر محصول', 'ziteh' ); ?>">
						<div class="ziteh-sp__stage">
							<?php if ( $discount && 'yes' === $settings['show_sale_badge'] ) : ?>
								<span class="ziteh-sp__sale"><?php echo esc_html( sprintf( __( '%d٪ تخفیف', 'ziteh' ), $discount ) ); ?></span>
							<?php endif; ?>
							<?php if ( $gallery_ids ) : ?>
								<?php foreach ( $gallery_ids as $index => $image_id ) : ?>
									<div class="ziteh-sp__slide<?php echo 0 === $index ? ' is-active' : ''; ?>" data-ziteh-sp-slide="<?php echo esc_attr( $index ); ?>" aria-hidden="<?php echo 0 === $index ? 'false' : 'true'; ?>">
										<?php
										echo wp_get_attachment_image(
											$image_id,
											'woocommerce_single',
											false,
											array(
												'alt'      => 0 === $index ? $image_alt : $product->get_name(),
												'loading'  => 0 === $index ? $settings['main_image_loading'] : 'lazy',
												'decoding' => 'async',
											)
										); // phpcs:ignore WordPress.Security.EscapeOutput
										?>
									</div>
								<?php endforeach; ?>
							<?php else : ?>
								<div class="ziteh-sp__slide is-active"><?php echo wc_placeholder_img( 'woocommerce_single' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
							<?php endif; ?>
							<?php if ( count( $gallery_ids ) > 1 ) : ?>
								<button class="ziteh-sp__gallery-arrow ziteh-sp__gallery-arrow--prev" type="button" data-ziteh-sp-prev aria-label="<?php esc_attr_e( 'تصویر قبلی', 'ziteh' ); ?>"><?php Ziteh_Icons::render( 'chevron-right' ); ?></button>
								<button class="ziteh-sp__gallery-arrow ziteh-sp__gallery-arrow--next" type="button" data-ziteh-sp-next aria-label="<?php esc_attr_e( 'تصویر بعدی', 'ziteh' ); ?>"><?php Ziteh_Icons::render( 'chevron-left' ); ?></button>
							<?php endif; ?>
						</div>

						<?php if ( count( $gallery_ids ) > 1 ) : ?>
							<div class="ziteh-sp__thumbs" role="tablist" aria-label="<?php esc_attr_e( 'تصاویر محصول', 'ziteh' ); ?>">
								<?php foreach ( $gallery_ids as $index => $image_id ) : ?>
									<button class="ziteh-sp__thumb<?php echo 0 === $index ? ' is-active' : ''; ?>" type="button" role="tab" aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>" tabindex="<?php echo 0 === $index ? '0' : '-1'; ?>" data-ziteh-sp-thumb="<?php echo esc_attr( $index ); ?>">
										<?php echo wp_get_attachment_image( $image_id, 'woocommerce_gallery_thumbnail', false, array( 'alt' => sprintf( __( 'تصویر %d از %s', 'ziteh' ), $index + 1, $product->get_name() ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
									</button>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</section>

					<section class="ziteh-sp__summary">
						<?php if ( 'yes' === $settings['show_breadcrumbs'] ) : ?>
							<nav class="ziteh-sp__breadcrumbs" aria-label="<?php esc_attr_e( 'مسیر راهنمای محصول', 'ziteh' ); ?>"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'خانه', 'ziteh' ); ?></a><span>/</span><a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'فروشگاه', 'ziteh' ); ?></a><span>/</span><span aria-current="page"><?php echo esc_html( wp_trim_words( $product->get_name(), 4 ) ); ?></span></nav>
						<?php endif; ?>
						<div class="ziteh-sp__eyebrow"><?php Ziteh_Icons::render( 'leaf' ); ?><span><?php echo esc_html( $eyebrow ); ?></span></div>
						<<?php echo esc_attr( $heading_tag ); ?> class="ziteh-sp__title"><?php echo esc_html( $product->get_name() ); ?></<?php echo esc_attr( $heading_tag ); ?>>
						<?php if ( ! empty( $settings['english_title'] ) ) : ?><div class="ziteh-sp__english" lang="en"><?php echo esc_html( $settings['english_title'] ); ?></div><?php endif; ?>

						<?php if ( 'yes' === $settings['show_rating'] ) : ?>
							<div class="ziteh-sp__rating">
								<?php echo Ziteh_Icons::stars( $average, $rating_qty ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
								<?php if ( $rating_qty > 0 ) : ?>
									<span class="ziteh-sp__rating-value"><?php echo esc_html( sprintf( __( '%s از ۵', 'ziteh' ), number_format_i18n( $average, 1 ) ) ); ?></span>
								<?php else : ?>
									<span class="ziteh-sp__no-rating"><?php esc_html_e( 'بدون امتیاز', 'ziteh' ); ?></span>
								<?php endif; ?>
								<a class="ziteh-sp__rating-link" href="#<?php echo esc_attr( $review_id ); ?>" data-ziteh-sp-reviews-link><?php echo esc_html( sprintf( _n( '(%s نظر)', '(%s نظر)', $product->get_review_count(), 'ziteh' ), number_format_i18n( $product->get_review_count() ) ) ); ?></a>
							</div>
						<?php endif; ?>

						<?php if ( 'yes' === $settings['show_short_description'] && $product->get_short_description() ) : ?>
							<div class="ziteh-sp__excerpt"><p><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $product->get_short_description() ), max( 12, (int) $settings['short_description_words'] ), '…' ) ); ?></p></div>
						<?php endif; ?>
						<?php if ( ! empty( $settings['highlights'] ) ) : ?>
							<ul class="ziteh-sp__highlights">
								<?php foreach ( $settings['highlights'] as $item ) : ?>
									<li class="ziteh-sp__highlight">
										<span class="ziteh-sp__highlight-icon"><?php Ziteh_Icons::render_control( $item['icon'], 'check-circle' ); ?></span>
										<span class="ziteh-sp__highlight-text"><?php echo esc_html( $item['text'] ); ?></span>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
						<div class="ziteh-sp__shipping">
							<span class="ziteh-sp__shipping-icon"><?php Ziteh_Icons::render_control( $settings['shipping_icon'], 'truck' ); ?></span>
							<span class="ziteh-sp__shipping-copy"><strong><?php echo esc_html( $settings['shipping_title'] ); ?></strong><small><?php echo esc_html( $settings['shipping_text'] ); ?></small></span>
							<span class="ziteh-sp__shipping-chevron"><?php Ziteh_Icons::render( 'chevron-down' ); ?></span>
						</div>
						<?php if ( 'yes' === $settings['show_meta'] ) : ?><div class="ziteh-sp__meta">
							<?php if ( $product->get_sku() ) : ?><span><?php esc_html_e( 'کد محصول:', 'ziteh' ); ?> <strong><?php echo esc_html( $product->get_sku() ); ?></strong></span><?php endif; ?>
							<?php echo wc_get_product_category_list( $product->get_id(), '، ', '<span>' . esc_html__( 'دسته‌بندی: ', 'ziteh' ), '</span>' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</div><?php endif; ?>
					</section>

					<aside class="ziteh-sp__purchase<?php echo esc_attr( $sticky ); ?>" aria-label="<?php esc_attr_e( 'خرید محصول', 'ziteh' ); ?>">
						<?php if ( ! empty( $settings['panel_badge'] ) ) : ?><span class="ziteh-sp__recommendation"><?php echo esc_html( $settings['panel_badge'] ); ?></span><?php endif; ?>
						<div class="ziteh-sp__purchase-brand"><?php Ziteh_Icons::render( 'leaf' ); ?><strong><?php echo esc_html( $settings['panel_title'] ); ?></strong></div>
						<p class="ziteh-sp__purchase-description"><?php echo esc_html( $settings['panel_description'] ); ?></p>
						<div class="ziteh-sp__purchase-separator"></div>
						<div class="ziteh-sp__price">
							<?php if ( $discount ) : ?><span class="ziteh-sp__purchase-discount"><?php echo esc_html( sprintf( __( '%d٪ تخفیف', 'ziteh' ), $discount ) ); ?></span><?php endif; ?>
							<?php echo wp_kses_post( $product->get_price_html() ); ?>
						</div>
						<div class="ziteh-sp__stock"><?php echo wp_kses_post( wc_get_stock_html( $product ) ); ?></div>
						<div class="ziteh-sp__buy">
							<?php
							$button_filter = static function () use ( $settings ) { return $settings['add_to_cart_text']; };
							add_filter( 'woocommerce_product_single_add_to_cart_text', $button_filter );
							woocommerce_template_single_add_to_cart();
							remove_filter( 'woocommerce_product_single_add_to_cart_text', $button_filter );
							?>
						</div>
						<button class="ziteh-sp__wish" type="button" data-ziteh-wish="<?php echo esc_attr( $product->get_id() ); ?>" aria-label="<?php echo esc_attr( $settings['wishlist_text'] ); ?>"><?php Ziteh_Icons::render( 'heart' ); ?><span><?php echo esc_html( $settings['wishlist_text'] ); ?></span></button>
						<?php if ( ! empty( $settings['trust_items'] ) ) : ?>
							<ul class="ziteh-sp__trust">
								<?php foreach ( $settings['trust_items'] as $item ) : ?>
									<li>
										<span class="ziteh-sp__trust-icon"><?php Ziteh_Icons::render_control( $item['icon'], 'shield' ); ?></span>
										<strong><?php echo esc_html( $item['title'] ); ?></strong>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</aside>
				</div>

				<?php $separate_product_widgets = class_exists( 'Ziteh_Settings' ) && Ziteh_Settings::feature_enabled( 'product_isolation', true ); ?>
				<?php if ( ! $separate_product_widgets && 'yes' === $settings['show_details'] ) : ?><?php $this->render_details( $product, $review_id ); ?><?php endif; ?>

				<?php if ( ! $separate_product_widgets && 'yes' === $settings['show_related'] ) : ?>
					<?php $this->render_related( $product, (int) $settings['related_count'] ); ?>
				<?php endif; ?>
			</div>
			<?php if ( 'yes' === $settings['enable_schema'] && ( ! function_exists( 'is_product' ) || ! is_product() ) ) : ?><?php $this->render_schema( $product, $gallery_ids ); ?><?php endif; ?>
		</article>
		<?php
		$product = $previous_product;
	}

	/**
	 * Product details tabs.
	 *
	 * @param WC_Product $product   Product.
	 * @param string     $review_id Unique review-panel anchor.
	 */
	private function render_details( $product, $review_id ) {
		ob_start();
		wc_display_product_attributes( $product );
		$attributes_html = ob_get_clean();
		$reviews         = get_comments(
			array(
				'post_id' => $product->get_id(),
				'status'  => 'approve',
				'type'    => 'review',
				'number'  => 5,
			)
		);
		?>
		<section class="ziteh-sp__details">
			<div class="ziteh-sp__tabs" role="tablist" aria-label="<?php esc_attr_e( 'اطلاعات محصول', 'ziteh' ); ?>">
				<button class="ziteh-sp__tab is-active" type="button" role="tab" aria-selected="true" data-ziteh-sp-tab="description"><?php esc_html_e( 'توضیحات', 'ziteh' ); ?></button>
				<button class="ziteh-sp__tab" type="button" role="tab" aria-selected="false" data-ziteh-sp-tab="attributes"><?php esc_html_e( 'مشخصات', 'ziteh' ); ?></button>
				<button class="ziteh-sp__tab" type="button" role="tab" aria-selected="false" data-ziteh-sp-tab="reviews"><?php esc_html_e( 'دیدگاه‌ها', 'ziteh' ); ?> <span><?php echo esc_html( $product->get_review_count() ); ?></span></button>
			</div>

			<div class="ziteh-sp__panel is-active" role="tabpanel" data-ziteh-sp-panel="description">
				<?php echo wp_kses_post( wpautop( $product->get_description() ? $product->get_description() : $product->get_short_description() ) ); ?>
			</div>
			<div class="ziteh-sp__panel" role="tabpanel" hidden data-ziteh-sp-panel="attributes">
				<?php echo wp_kses_post( $attributes_html ); ?>
			</div>
			<div id="<?php echo esc_attr( $review_id ); ?>" class="ziteh-sp__panel" role="tabpanel" hidden data-ziteh-sp-panel="reviews">
				<?php if ( $reviews ) : ?>
					<div class="ziteh-sp__reviews">
						<?php foreach ( $reviews as $review ) : ?>
							<article class="ziteh-sp__review">
								<div><?php echo get_avatar( $review, 52 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
								<div>
									<strong><?php echo esc_html( get_comment_author( $review ) ); ?></strong>
									<?php
									$review_rating = (int) get_comment_meta( $review->comment_ID, 'rating', true );
									if ( $review_rating ) {
										echo Ziteh_Icons::stars( $review_rating, 1 ); // phpcs:ignore WordPress.Security.EscapeOutput
									}
									?>
									<p><?php echo esc_html( $review->comment_content ); ?></p>
								</div>
							</article>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<p class="ziteh-sp__empty"><?php esc_html_e( 'هنوز دیدگاهی برای این محصول ثبت نشده است.', 'ziteh' ); ?></p>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}

	/**
	 * Optional Product JSON-LD for placements outside native product pages.
	 * Native WooCommerce product pages already provide structured data.
	 *
	 * @param WC_Product $product     Product.
	 * @param int[]      $gallery_ids Gallery attachment IDs.
	 */
	private function render_schema( $product, $gallery_ids ) {
		$images = array();
		foreach ( $gallery_ids as $image_id ) {
			$url = wp_get_attachment_image_url( $image_id, 'full' );
			if ( $url ) {
				$images[] = $url;
			}
		}
		$schema = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'Product',
			'name'        => $product->get_name(),
			'url'         => $product->get_permalink(),
			'description' => wp_strip_all_tags( $product->get_short_description() ),
			'sku'         => $product->get_sku(),
			'image'       => $images,
			'offers'      => array(
				'@type'         => 'Offer',
				'priceCurrency' => get_woocommerce_currency(),
				'price'         => $product->get_price(),
				'availability'  => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
				'url'           => $product->get_permalink(),
			),
		);
		if ( $product->get_rating_count() > 0 ) {
			$schema['aggregateRating'] = array(
				'@type'       => 'AggregateRating',
				'ratingValue' => $product->get_average_rating(),
				'reviewCount' => $product->get_review_count(),
			);
		}
		echo '<script type="application/ld+json">' . wp_json_encode( array_filter( $schema ) ) . '</script>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}

	/**
	 * Related product cards.
	 *
	 * @param WC_Product $product Product.
	 * @param int        $count   Count.
	 */
	private function render_related( $product, $count ) {
		$ids = wc_get_related_products( $product->get_id(), max( 2, $count ) );
		if ( ! $ids ) {
			return;
		}
		?>
		<section class="ziteh-sp__related">
			<h2 class="ziteh-section-title ziteh-section-title--start">
				<?php Ziteh_Icons::render( 'leaf', array( 'class' => 'ziteh-i ziteh-section-title__leaf' ) ); ?>
				<?php esc_html_e( 'محصولات مرتبط', 'ziteh' ); ?>
			</h2>
			<div class="ziteh-sp__related-grid">
				<?php foreach ( $ids as $id ) : ?>
					<?php $related = wc_get_product( $id ); ?>
					<?php if ( ! $related || ! $related->is_visible() ) { continue; } ?>
					<a class="ziteh-sp__related-card" href="<?php echo esc_url( $related->get_permalink() ); ?>">
						<span class="ziteh-sp__related-image"><?php echo $related->get_image( 'woocommerce_thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
						<strong><?php echo esc_html( $related->get_name() ); ?></strong>
						<span class="ziteh-sp__related-price"><?php echo wp_kses_post( $related->get_price_html() ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
}
