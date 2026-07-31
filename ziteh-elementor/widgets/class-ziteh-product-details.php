<?php
/**
 * Reference-faithful WooCommerce product details tabs.
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
 * Class Ziteh_Product_Details_Widget.
 */
class Ziteh_Product_Details_Widget extends Ziteh_Widget_Base {

	public function get_name() {
		return 'ziteh-product-details';
	}

	public function get_title() {
		return esc_html__( '۲. توضیحات و ویژگی‌های محصول', 'ziteh' );
	}

	public function get_icon() {
		return 'eicon-product-tabs';
	}

	public function get_keywords() {
		return array_merge( parent::get_keywords(), array( 'product details', 'tabs', 'ingredients', 'woocommerce', 'توضیحات', 'ترکیبات', 'نحوه استفاده' ) );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_product', array( 'label' => esc_html__( 'محصول و داده‌ها', 'ziteh' ), 'tab' => Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'product_id', array( 'label' => esc_html__( 'شناسه محصول برای پیش‌نمایش', 'ziteh' ), 'type' => Controls_Manager::NUMBER, 'min' => 1, 'description' => esc_html__( 'در صفحه محصول خالی بگذارید تا محصول جاری استفاده شود.', 'ziteh' ) ) );
		$this->add_control( 'description_source', array( 'label' => esc_html__( 'منبع توضیحات', 'ziteh' ), 'type' => Controls_Manager::SELECT, 'default' => 'woocommerce', 'options' => array( 'woocommerce' => esc_html__( 'توضیحات WooCommerce', 'ziteh' ), 'custom' => esc_html__( 'محتوای سفارشی', 'ziteh' ) ) ) );
		$this->add_control( 'description_heading', array( 'label' => esc_html__( 'عنوان توضیحات', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'قدرت طبیعت برای مویی سالم‌تر', 'ziteh' ), 'label_block' => true ) );
		$this->add_control( 'custom_description', array( 'label' => esc_html__( 'توضیحات سفارشی', 'ziteh' ), 'type' => Controls_Manager::WYSIWYG, 'default' => '<p>' . esc_html__( 'شامپو تقویت‌کننده زیته با ترکیبی از عصاره‌های گیاهی و مواد مؤثره طبیعی، کاهش ریزش مو کمک کرده و باعث تقویت ریشه و افزایش استحکام تارهای مو می‌شود.', 'ziteh' ) . '</p><p>' . esc_html__( 'استفاده منظم از این شامپو، موهایی سالم‌تر، پرپشت‌تر و درخشان‌تر برای شما به ارمغان می‌آورد.', 'ziteh' ) . '</p>', 'condition' => array( 'description_source' => 'custom' ) ) );
		$this->add_control( 'description_word_limit', array( 'label' => esc_html__( 'حداکثر واژه توضیحات ووکامرس', 'ziteh' ), 'type' => Controls_Manager::NUMBER, 'min' => 30, 'max' => 300, 'default' => 85, 'condition' => array( 'description_source' => 'woocommerce' ), 'description' => esc_html__( 'برای حفظ ارتفاع دقیق UI، متن بلند محصول به این تعداد واژه محدود می‌شود.', 'ziteh' ) ) );
		$this->add_control( 'ingredients_content', array( 'label' => esc_html__( 'محتوای ترکیبات', 'ziteh' ), 'type' => Controls_Manager::WYSIWYG, 'default' => '<p>' . esc_html__( 'ترکیبات کلیدی محصول شامل عصاره رزماری، مواد شوینده ملایم گیاهی و عوامل تقویت‌کننده ساقه و ریشه مو است.', 'ziteh' ) . '</p>' ) );
		$this->add_control( 'usage_content', array( 'label' => esc_html__( 'محتوای نحوه استفاده', 'ziteh' ), 'type' => Controls_Manager::WYSIWYG, 'default' => '<p>' . esc_html__( 'مقدار مناسبی از شامپو را روی موی خیس ماساژ دهید، دو تا سه دقیقه صبر کنید و سپس کاملاً آبکشی نمایید.', 'ziteh' ) . '</p>' ) );
		$this->add_control( 'reviews_limit', array( 'label' => esc_html__( 'تعداد دیدگاه قابل نمایش', 'ziteh' ), 'type' => Controls_Manager::NUMBER, 'min' => 1, 'max' => 20, 'default' => 5 ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_tabs', array( 'label' => esc_html__( 'تب‌ها', 'ziteh' ), 'tab' => Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'description_label', array( 'label' => esc_html__( 'برچسب توضیحات', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'توضیحات', 'ziteh' ) ) );
		$this->add_control( 'ingredients_label', array( 'label' => esc_html__( 'برچسب ترکیبات', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'ترکیبات', 'ziteh' ) ) );
		$this->add_control( 'usage_label', array( 'label' => esc_html__( 'برچسب نحوه استفاده', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'نحوه استفاده', 'ziteh' ) ) );
		$this->add_control( 'reviews_label', array( 'label' => esc_html__( 'برچسب نظرات', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'نظرات', 'ziteh' ) ) );
		$this->add_control( 'default_tab', array( 'label' => esc_html__( 'تب فعال اولیه', 'ziteh' ), 'type' => Controls_Manager::SELECT, 'default' => 'description', 'options' => array( 'description' => esc_html__( 'توضیحات', 'ziteh' ), 'ingredients' => esc_html__( 'ترکیبات', 'ziteh' ), 'usage' => esc_html__( 'نحوه استفاده', 'ziteh' ), 'reviews' => esc_html__( 'نظرات', 'ziteh' ) ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_media', array( 'label' => esc_html__( 'تصویر معرفی', 'ziteh' ), 'tab' => Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'image', array( 'label' => esc_html__( 'تصویر ترکیبات', 'ziteh' ), 'type' => Controls_Manager::MEDIA, 'default' => array( 'url' => ZITEH_EL_URL . 'assets/images/product-details-rosemary.png' ) ) );
		$this->add_control( 'image_alt', array( 'label' => esc_html__( 'متن جایگزین تصویر', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'رزماری و سرم گیاهی شفاف', 'ziteh' ) ) );
		$this->add_control( 'image_loading', array( 'label' => esc_html__( 'بارگذاری تصویر', 'ziteh' ), 'type' => Controls_Manager::SELECT, 'default' => 'lazy', 'options' => array( 'lazy' => 'Lazy', 'eager' => 'Eager' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_benefits', array( 'label' => esc_html__( 'مزیت‌های پایین سکشن', 'ziteh' ), 'tab' => Controls_Manager::TAB_CONTENT ) );
		$benefit = new Repeater();
		$benefit->add_control( 'title', array( 'label' => esc_html__( 'عنوان', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'مزیت محصول', 'ziteh' ), 'label_block' => true ) );
		$benefit->add_control( 'icon', array( 'label' => esc_html__( 'آیکن', 'ziteh' ), 'type' => Controls_Manager::ICONS, 'default' => array( 'value' => 'fas fa-leaf', 'library' => 'fa-solid' ) ) );
		$this->add_control( 'benefits', array( 'label' => esc_html__( 'فهرست مزایا', 'ziteh' ), 'type' => Controls_Manager::REPEATER, 'fields' => $benefit->get_controls(), 'title_field' => '{{{ title }}}', 'default' => array(
			array( 'title' => esc_html__( 'مناسب انواع مو', 'ziteh' ), 'icon' => array( 'value' => 'fas fa-glasses', 'library' => 'fa-solid' ) ),
			array( 'title' => esc_html__( 'بدون پارابن', 'ziteh' ), 'icon' => array( 'value' => 'fas fa-flask', 'library' => 'fa-solid' ) ),
			array( 'title' => esc_html__( 'فرمول گیاهی', 'ziteh' ), 'icon' => array( 'value' => 'fas fa-seedling', 'library' => 'fa-solid' ) ),
			array( 'title' => esc_html__( 'بدون سولفات', 'ziteh' ), 'icon' => array( 'value' => 'fas fa-atom', 'library' => 'fa-solid' ) ),
		) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_layout', array( 'label' => esc_html__( 'چیدمان', 'ziteh' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_responsive_control( 'max_width', array( 'label' => esc_html__( 'حداکثر عرض', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'size_units' => array( 'px' ), 'range' => array( 'px' => array( 'min' => 760, 'max' => 1680 ) ), 'default' => array( 'size' => 1320, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-pd' => '--ziteh-pd-max:{{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'content_gap', array( 'label' => esc_html__( 'فاصله تصویر و متن', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 12, 'max' => 100 ) ), 'default' => array( 'size' => 76, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-pd' => '--ziteh-pd-gap:{{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'image_width', array( 'label' => esc_html__( 'عرض تصویر', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 360, 'max' => 760 ) ), 'default' => array( 'size' => 580, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-pd' => '--ziteh-pd-image:{{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'section_padding', array( 'label' => esc_html__( 'فاصله داخلی قاب', 'ziteh' ), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', '%' ), 'default' => array( 'top' => 30, 'right' => 30, 'bottom' => 18, 'left' => 30, 'unit' => 'px', 'isLinked' => false ), 'selectors' => array( '{{WRAPPER}} .ziteh-pd__body' => 'padding:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_colors', array( 'label' => esc_html__( 'رنگ‌ها و سطوح', 'ziteh' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$colors = array( 'background_color' => array( 'پس‌زمینه', '#fbfaf7', '--ziteh-pd-bg' ), 'surface_color' => array( 'سطح کارت', '#ffffff', '--ziteh-pd-surface' ), 'primary_color' => array( 'رنگ اصلی', '#78866f', '--ziteh-pd-primary' ), 'heading_color' => array( 'رنگ عنوان', '#444740', '--ziteh-pd-heading' ), 'text_color' => array( 'رنگ متن', '#7e807a', '--ziteh-pd-text' ), 'border_color' => array( 'رنگ خطوط', '#e9e6df', '--ziteh-pd-border' ) );
		foreach ( $colors as $key => $data ) {
			$this->add_control( $key, array( 'label' => esc_html( $data[0] ), 'type' => Controls_Manager::COLOR, 'default' => $data[1], 'selectors' => array( '{{WRAPPER}} .ziteh-pd' => $data[2] . ':{{VALUE}};' ) ) );
		}
		$this->add_responsive_control( 'radius', array( 'label' => esc_html__( 'گردی گوشه‌ها', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 48 ) ), 'default' => array( 'size' => 24, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-pd' => '--ziteh-pd-radius:{{SIZE}}{{UNIT}};' ) ) );
		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array( 'name' => 'section_shadow', 'label' => esc_html__( 'سایه قاب', 'ziteh' ), 'selector' => '{{WRAPPER}} .ziteh-pd__body' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_typography', array( 'label' => esc_html__( 'تایپوگرافی', 'ziteh' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'tabs_typography', 'label' => esc_html__( 'تب‌ها', 'ziteh' ), 'selector' => '{{WRAPPER}} .ziteh-pd__tab' ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'heading_typography', 'label' => esc_html__( 'عنوان محتوا', 'ziteh' ), 'selector' => '{{WRAPPER}} .ziteh-pd__heading' ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'body_typography', 'label' => esc_html__( 'متن محتوا', 'ziteh' ), 'selector' => '{{WRAPPER}} .ziteh-pd__copy' ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'benefit_typography', 'label' => esc_html__( 'متن مزایا', 'ziteh' ), 'selector' => '{{WRAPPER}} .ziteh-pd__benefit strong' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_accessibility', array( 'label' => esc_html__( 'SEO و دسترس‌پذیری', 'ziteh' ), 'tab' => Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'heading_tag', array( 'label' => esc_html__( 'تگ عنوان محتوا', 'ziteh' ), 'type' => Controls_Manager::SELECT, 'default' => 'h2', 'options' => array( 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4', 'div' => 'DIV' ) ) );
		$this->add_control( 'section_label', array( 'label' => esc_html__( 'برچسب دسترس‌پذیری سکشن', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'اطلاعات تکمیلی محصول', 'ziteh' ) ) );
		$this->end_controls_section();
	}

	/**
	 * Resolve current or preview product.
	 *
	 * @param array $settings Widget settings.
	 * @return WC_Product|false
	 */
	private function resolve_product( $settings ) {
		if ( ! function_exists( 'wc_get_product' ) ) {
			return false;
		}
		$product_id = ! empty( $settings['product_id'] ) ? absint( $settings['product_id'] ) : 0;
		if ( ! $product_id && function_exists( 'is_product' ) && is_product() ) {
			$product_id = get_queried_object_id();
		}
		if ( ! $product_id && 'product' === get_post_type( get_the_ID() ) ) {
			$product_id = get_the_ID();
		}
		if ( ! $product_id && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			$ids = wc_get_products( array( 'limit' => 1, 'status' => 'publish', 'return' => 'ids' ) );
			$product_id = $ids ? (int) reset( $ids ) : 0;
		}
		return $product_id ? wc_get_product( $product_id ) : false;
	}

	protected function render() {
		$settings    = $this->get_settings_for_display();
		$product     = $this->resolve_product( $settings );
		$widget_id   = 'ziteh-pd-' . $this->get_id();
		$active      = in_array( $settings['default_tab'], array( 'description', 'ingredients', 'usage', 'reviews' ), true ) ? $settings['default_tab'] : 'description';
		$heading_tag = in_array( $settings['heading_tag'], array( 'h2', 'h3', 'h4', 'div' ), true ) ? $settings['heading_tag'] : 'h2';
		$review_count = $product ? $product->get_review_count() : 0;
		$description  = 'custom' === $settings['description_source'] || ! $product ? $settings['custom_description'] : $product->get_description();
		$image_url    = ! empty( $settings['image']['url'] ) ? $settings['image']['url'] : ZITEH_EL_URL . 'assets/images/product-details-rosemary.png';
		if ( ! $description && $product ) {
			$description = $product->get_short_description();
		}
		if ( $product && 'woocommerce' === $settings['description_source'] && ! empty( $settings['description_word_limit'] ) ) {
			$description = '<p>' . esc_html( wp_trim_words( wp_strip_all_tags( $description ), max( 30, (int) $settings['description_word_limit'] ), '…' ) ) . '</p>';
		}
		$tabs = array(
			'description' => $settings['description_label'],
			'ingredients' => $settings['ingredients_label'],
			'usage'       => $settings['usage_label'],
			'reviews'     => $settings['reviews_label'] . ' (' . $review_count . ')',
		);
		?>
		<section id="<?php echo esc_attr( $widget_id ); ?>" class="ziteh-pd" data-ziteh-product-details aria-label="<?php echo esc_attr( $settings['section_label'] ); ?>">
			<div class="ziteh-pd__container">
				<div class="ziteh-pd__tabs" role="tablist" aria-label="<?php echo esc_attr( $settings['section_label'] ); ?>">
					<?php foreach ( $tabs as $key => $label ) : $is_active = $active === $key; ?>
						<button id="<?php echo esc_attr( $widget_id . '-tab-' . $key ); ?>" class="ziteh-pd__tab<?php echo $is_active ? ' is-active' : ''; ?>" type="button" role="tab" aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr( $widget_id . '-panel-' . $key ); ?>" tabindex="<?php echo $is_active ? '0' : '-1'; ?>" data-ziteh-pd-tab="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></button>
					<?php endforeach; ?>
				</div>
				<div class="ziteh-pd__body">
					<div id="<?php echo esc_attr( $widget_id . '-panel-description' ); ?>" class="ziteh-pd__panel ziteh-pd__panel--description<?php echo 'description' === $active ? ' is-active' : ''; ?>" role="tabpanel" aria-labelledby="<?php echo esc_attr( $widget_id . '-tab-description' ); ?>" data-ziteh-pd-panel="description"<?php echo 'description' === $active ? '' : ' hidden'; ?>>
						<div class="ziteh-pd__visual"><img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $settings['image_alt'] ); ?>" loading="<?php echo esc_attr( $settings['image_loading'] ); ?>" decoding="async"></div>
						<div class="ziteh-pd__content"><<?php echo esc_attr( $heading_tag ); ?> class="ziteh-pd__heading"><?php echo esc_html( $settings['description_heading'] ); ?></<?php echo esc_attr( $heading_tag ); ?>><div class="ziteh-pd__copy"><?php echo wp_kses_post( wpautop( $description ) ); ?></div></div>
					</div>
					<div id="<?php echo esc_attr( $widget_id . '-panel-ingredients' ); ?>" class="ziteh-pd__panel ziteh-pd__panel--text<?php echo 'ingredients' === $active ? ' is-active' : ''; ?>" role="tabpanel" aria-labelledby="<?php echo esc_attr( $widget_id . '-tab-ingredients' ); ?>" data-ziteh-pd-panel="ingredients"<?php echo 'ingredients' === $active ? '' : ' hidden'; ?>><div class="ziteh-pd__copy"><?php echo wp_kses_post( wpautop( $settings['ingredients_content'] ) ); ?></div></div>
					<div id="<?php echo esc_attr( $widget_id . '-panel-usage' ); ?>" class="ziteh-pd__panel ziteh-pd__panel--text<?php echo 'usage' === $active ? ' is-active' : ''; ?>" role="tabpanel" aria-labelledby="<?php echo esc_attr( $widget_id . '-tab-usage' ); ?>" data-ziteh-pd-panel="usage"<?php echo 'usage' === $active ? '' : ' hidden'; ?>><div class="ziteh-pd__copy"><?php echo wp_kses_post( wpautop( $settings['usage_content'] ) ); ?></div></div>
					<div id="<?php echo esc_attr( $widget_id . '-panel-reviews' ); ?>" class="ziteh-pd__panel ziteh-pd__panel--reviews<?php echo 'reviews' === $active ? ' is-active' : ''; ?>" role="tabpanel" aria-labelledby="<?php echo esc_attr( $widget_id . '-tab-reviews' ); ?>" data-ziteh-pd-panel="reviews"<?php echo 'reviews' === $active ? '' : ' hidden'; ?>><?php $this->render_reviews( $product, (int) $settings['reviews_limit'] ); ?></div>
					<?php if ( ! empty( $settings['benefits'] ) ) : ?>
						<ul class="ziteh-pd__benefits">
							<?php foreach ( $settings['benefits'] as $item ) : ?>
								<li class="ziteh-pd__benefit">
									<span class="ziteh-pd__benefit-icon"><?php Ziteh_Icons::render_control( $item['icon'], 'leaf' ); ?></span>
									<strong><?php echo esc_html( $item['title'] ); ?></strong>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			</div>
		</section>
		<?php
	}

	/**
	 * Render product reviews.
	 *
	 * @param WC_Product|false $product Product.
	 * @param int              $limit   Maximum reviews.
	 */
	private function render_reviews( $product, $limit ) {
		if ( ! $product ) {
			echo '<p class="ziteh-pd__empty">' . esc_html__( 'برای نمایش دیدگاه‌ها یک محصول انتخاب کنید.', 'ziteh' ) . '</p>';
			return;
		}
		$reviews = get_comments( array( 'post_id' => $product->get_id(), 'status' => 'approve', 'type' => 'review', 'number' => max( 1, $limit ) ) );
		if ( ! $reviews ) {
			echo '<p class="ziteh-pd__empty">' . esc_html__( 'هنوز دیدگاهی برای این محصول ثبت نشده است.', 'ziteh' ) . '</p>';
			return;
		}
		echo '<div class="ziteh-pd__reviews">';
		foreach ( $reviews as $review ) {
			$rating = (int) get_comment_meta( $review->comment_ID, 'rating', true );
			// phpcs:ignore WordPress.Security.EscapeOutput -- Ziteh_Icons::stars() escapes its own attributes.
			echo '<article class="ziteh-pd__review"><div class="ziteh-pd__review-head"><strong>' . esc_html( get_comment_author( $review ) ) . '</strong>' . ( $rating ? Ziteh_Icons::stars( $rating, 1 ) : '' ) . '</div><p>' . esc_html( $review->comment_content ) . '</p></article>';
		}
		echo '</div>';
	}
}
