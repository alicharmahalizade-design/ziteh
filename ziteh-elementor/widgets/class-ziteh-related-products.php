<?php
/**
 * Related products carousel for WooCommerce product pages.
 *
 * @package Ziteh_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;

/**
 * Class Ziteh_Related_Products_Widget.
 */
class Ziteh_Related_Products_Widget extends Ziteh_Widget_Base {

	public function get_name() {
		return 'ziteh-related-products';
	}

	public function get_title() {
		return esc_html__( '۳. محصولات مرتبط', 'ziteh' );
	}

	public function get_icon() {
		return 'eicon-products';
	}

	public function get_keywords() {
		return array_merge( parent::get_keywords(), array( 'related products', 'carousel', 'woocommerce', 'محصولات مرتبط', 'اسلایدر محصول' ) );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_heading', array( 'label' => esc_html__( 'سربرگ', 'ziteh' ), 'tab' => Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'title', array( 'label' => esc_html__( 'عنوان', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'محصولات مرتبط', 'ziteh' ) ) );
		$this->add_control( 'show_leaf', array( 'label' => esc_html__( 'نمایش آیکن برگ', 'ziteh' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'show_lines', array( 'label' => esc_html__( 'نمایش خطوط تزئینی', 'ziteh' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_source', array( 'label' => esc_html__( 'منبع محصولات', 'ziteh' ), 'tab' => Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'source', array( 'label' => esc_html__( 'منبع', 'ziteh' ), 'type' => Controls_Manager::SELECT, 'default' => 'related', 'options' => array(
			'related'     => esc_html__( 'مرتبط با محصول جاری', 'ziteh' ),
			'category'    => esc_html__( 'دسته‌بندی انتخابی', 'ziteh' ),
			'latest'      => esc_html__( 'جدیدترین محصولات', 'ziteh' ),
			'featured'    => esc_html__( 'محصولات ویژه', 'ziteh' ),
			'on_sale'     => esc_html__( 'محصولات تخفیف‌دار', 'ziteh' ),
			'best_selling'=> esc_html__( 'پرفروش‌ترین‌ها', 'ziteh' ),
			'manual'      => esc_html__( 'انتخاب دستی با شناسه', 'ziteh' ),
		) ) );
		$this->add_control( 'product_id', array( 'label' => esc_html__( 'شناسه محصول مرجع برای پیش‌نمایش', 'ziteh' ), 'type' => Controls_Manager::NUMBER, 'min' => 1, 'condition' => array( 'source' => 'related' ), 'description' => esc_html__( 'در صفحه محصول خالی بگذارید.', 'ziteh' ) ) );
		$this->add_control( 'category', array( 'label' => esc_html__( 'دسته‌بندی‌ها', 'ziteh' ), 'type' => Controls_Manager::SELECT2, 'multiple' => true, 'label_block' => true, 'options' => $this->product_categories(), 'condition' => array( 'source' => 'category' ) ) );
		$this->add_control( 'manual_ids', array( 'label' => esc_html__( 'شناسه محصولات', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'label_block' => true, 'placeholder' => '12, 25, 31', 'description' => esc_html__( 'شناسه‌ها را با ویرگول جدا کنید.', 'ziteh' ), 'condition' => array( 'source' => 'manual' ) ) );
		$this->add_control( 'count', array( 'label' => esc_html__( 'تعداد محصولات', 'ziteh' ), 'type' => Controls_Manager::NUMBER, 'min' => 1, 'max' => 24, 'default' => 10 ) );
		$this->add_control( 'order', array( 'label' => esc_html__( 'ترتیب', 'ziteh' ), 'type' => Controls_Manager::SELECT, 'default' => 'DESC', 'options' => array( 'DESC' => esc_html__( 'نزولی', 'ziteh' ), 'ASC' => esc_html__( 'صعودی', 'ziteh' ) ), 'condition' => array( 'source!' => array( 'related', 'manual' ) ) ) );
		$this->add_control( 'exclude_current', array( 'label' => esc_html__( 'حذف محصول جاری', 'ziteh' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes', 'condition' => array( 'source!' => 'related' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_card_content', array( 'label' => esc_html__( 'محتوای کارت', 'ziteh' ), 'tab' => Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'show_price', array( 'label' => esc_html__( 'نمایش قیمت', 'ziteh' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'show_cart', array( 'label' => esc_html__( 'نمایش افزودن به سبد', 'ziteh' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'image_loading', array( 'label' => esc_html__( 'بارگذاری تصاویر', 'ziteh' ), 'type' => Controls_Manager::SELECT, 'default' => 'lazy', 'options' => array( 'lazy' => 'Lazy', 'eager' => 'Eager' ) ) );
		$this->add_control( 'empty_text', array( 'label' => esc_html__( 'پیام نبود محصول', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'محصول مرتبطی پیدا نشد.', 'ziteh' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_slider', array( 'label' => esc_html__( 'اسلایدر', 'ziteh' ), 'tab' => Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'show_arrows', array( 'label' => esc_html__( 'نمایش فلش‌ها', 'ziteh' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'scroll_step', array( 'label' => esc_html__( 'تعداد حرکت در هر کلیک', 'ziteh' ), 'type' => Controls_Manager::NUMBER, 'min' => 1, 'max' => 5, 'default' => 1 ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_layout', array( 'label' => esc_html__( 'چیدمان', 'ziteh' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_responsive_control( 'max_width', array( 'label' => esc_html__( 'حداکثر عرض', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'size_units' => array( 'px' ), 'range' => array( 'px' => array( 'min' => 760, 'max' => 1680 ) ), 'default' => array( 'size' => 1100, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-rp' => '--ziteh-rp-max:{{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'visible_cards', array( 'label' => esc_html__( 'کارت‌های قابل نمایش', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 1, 'max' => 6, 'step' => .1 ) ), 'default' => array( 'size' => 5 ), 'tablet_default' => array( 'size' => 3 ), 'mobile_default' => array( 'size' => 1.35 ), 'selectors' => array( '{{WRAPPER}} .ziteh-rp' => '--ziteh-rp-visible:{{SIZE}};' ) ) );
		$this->add_responsive_control( 'gap', array( 'label' => esc_html__( 'فاصله کارت‌ها', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 4, 'max' => 48 ) ), 'default' => array( 'size' => 16, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-rp' => '--ziteh-rp-gap:{{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'image_ratio', array( 'label' => esc_html__( 'نسبت تصویر', 'ziteh' ), 'type' => Controls_Manager::SELECT, 'default' => '1.22 / 1', 'options' => array( '1.22 / 1' => esc_html__( 'مطابق طرح', 'ziteh' ), '1 / 1' => esc_html__( 'مربع', 'ziteh' ), '4 / 5' => esc_html__( 'عمودی', 'ziteh' ) ), 'selectors' => array( '{{WRAPPER}} .ziteh-rp' => '--ziteh-rp-ratio:{{VALUE}};' ) ) );
		$this->add_control( 'image_fit', array( 'label' => esc_html__( 'نحوه نمایش تصویر', 'ziteh' ), 'type' => Controls_Manager::SELECT, 'default' => 'cover', 'options' => array( 'cover' => esc_html__( 'پوشش کامل', 'ziteh' ), 'contain' => esc_html__( 'نمایش کامل', 'ziteh' ) ), 'selectors' => array( '{{WRAPPER}} .ziteh-rp' => '--ziteh-rp-fit:{{VALUE}};' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_style', array( 'label' => esc_html__( 'رنگ‌ها و کارت', 'ziteh' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$colors = array( 'background_color' => array( 'پس‌زمینه سکشن', '#fbfaf7', '--ziteh-rp-bg' ), 'card_color' => array( 'پس‌زمینه کارت', '#ffffff', '--ziteh-rp-card' ), 'image_bg' => array( 'پس‌زمینه تصویر', '#eeeae4', '--ziteh-rp-image-bg' ), 'primary_color' => array( 'رنگ اصلی', '#7d8a70', '--ziteh-rp-primary' ), 'heading_color' => array( 'رنگ عنوان', '#465044', '--ziteh-rp-heading' ), 'text_color' => array( 'رنگ متن', '#696d66', '--ziteh-rp-text' ), 'border_color' => array( 'رنگ خط', '#e8e5de', '--ziteh-rp-border' ) );
		foreach ( $colors as $key => $data ) {
			$this->add_control( $key, array( 'label' => esc_html( $data[0] ), 'type' => Controls_Manager::COLOR, 'default' => $data[1], 'selectors' => array( '{{WRAPPER}} .ziteh-rp' => $data[2] . ':{{VALUE}};' ) ) );
		}
		$this->add_responsive_control( 'radius', array( 'label' => esc_html__( 'گردی کارت', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 36 ) ), 'default' => array( 'size' => 10, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-rp' => '--ziteh-rp-radius:{{SIZE}}{{UNIT}};' ) ) );
		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array( 'name' => 'card_shadow', 'label' => esc_html__( 'سایه کارت', 'ziteh' ), 'selector' => '{{WRAPPER}} .ziteh-rp__card' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_typography', array( 'label' => esc_html__( 'تایپوگرافی', 'ziteh' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'heading_typography', 'label' => esc_html__( 'عنوان سکشن', 'ziteh' ), 'selector' => '{{WRAPPER}} .ziteh-rp__heading' ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'title_typography', 'label' => esc_html__( 'عنوان محصول', 'ziteh' ), 'selector' => '{{WRAPPER}} .ziteh-rp__title' ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'price_typography', 'label' => esc_html__( 'قیمت', 'ziteh' ), 'selector' => '{{WRAPPER}} .ziteh-rp__price' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_accessibility', array( 'label' => esc_html__( 'SEO و دسترس‌پذیری', 'ziteh' ), 'tab' => Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'heading_tag', array( 'label' => esc_html__( 'تگ عنوان', 'ziteh' ), 'type' => Controls_Manager::SELECT, 'default' => 'h2', 'options' => array( 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4', 'div' => 'DIV' ) ) );
		$this->add_control( 'section_label', array( 'label' => esc_html__( 'برچسب سکشن', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'محصولات مرتبط', 'ziteh' ) ) );
		$this->end_controls_section();
	}

	/** Product category options. */
	private function product_categories() {
		$options = array();
		if ( ! taxonomy_exists( 'product_cat' ) ) {
			return $options;
		}
		$terms = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false, 'number' => 200 ) );
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$options[ $term->slug ] = $term->name;
			}
		}
		return $options;
	}

	/** Resolve current/preview product ID. */
	private function current_product_id( $settings ) {
		if ( ! empty( $settings['product_id'] ) ) {
			return absint( $settings['product_id'] );
		}
		if ( function_exists( 'is_product' ) && is_product() ) {
			return get_queried_object_id();
		}
		if ( 'product' === get_post_type( get_the_ID() ) ) {
			return get_the_ID();
		}
		if ( \Elementor\Plugin::$instance->editor->is_edit_mode() && function_exists( 'wc_get_products' ) ) {
			$ids = wc_get_products( array( 'limit' => 1, 'status' => 'publish', 'return' => 'ids' ) );
			return $ids ? (int) reset( $ids ) : 0;
		}
		return 0;
	}

	/** Query configured products. */
	private function products( $settings ) {
		if ( ! function_exists( 'wc_get_products' ) ) {
			return array();
		}
		$count      = max( 1, min( 24, (int) $settings['count'] ) );
		$current_id = $this->current_product_id( $settings );
		$source     = $settings['source'];
		$ids        = array();

		if ( 'related' === $source && $current_id ) {
			$ids = wc_get_related_products( $current_id, $count );
		} elseif ( 'manual' === $source ) {
			$ids = array_filter( array_map( 'absint', preg_split( '/\s*,\s*/', (string) $settings['manual_ids'] ) ) );
		} else {
			$args = array( 'limit' => $count, 'status' => 'publish', 'order' => 'ASC' === $settings['order'] ? 'ASC' : 'DESC', 'orderby' => 'date', 'return' => 'ids' );
			if ( 'category' === $source && ! empty( $settings['category'] ) ) {
				$args['category'] = (array) $settings['category'];
			} elseif ( 'featured' === $source ) {
				$args['featured'] = true;
			} elseif ( 'on_sale' === $source ) {
				$args['include'] = array_slice( wc_get_product_ids_on_sale(), 0, $count );
			} elseif ( 'best_selling' === $source ) {
				$args['orderby'] = 'popularity';
			}
			if ( 'yes' === $settings['exclude_current'] && $current_id ) {
				$args['exclude'] = array( $current_id );
			}
			$ids = wc_get_products( $args );
		}

		$products = array();
		foreach ( array_slice( array_values( array_unique( array_map( 'absint', $ids ) ) ), 0, $count ) as $id ) {
			$product = wc_get_product( $id );
			if ( $product && $product->is_visible() ) {
				$products[] = $product;
			}
		}
		return $products;
	}

	protected function render() {
		$settings    = $this->get_settings_for_display();
		$products    = $this->products( $settings );
		$heading_tag = in_array( $settings['heading_tag'], array( 'h2', 'h3', 'h4', 'div' ), true ) ? $settings['heading_tag'] : 'h2';
		$widget_id   = 'ziteh-rp-' . $this->get_id();
		?>
		<section id="<?php echo esc_attr( $widget_id ); ?>" class="ziteh-rp" data-ziteh-related-products data-ziteh-rp-step="<?php echo esc_attr( max( 1, (int) $settings['scroll_step'] ) ); ?>" aria-label="<?php echo esc_attr( $settings['section_label'] ); ?>">
			<div class="ziteh-rp__container">
				<div class="ziteh-rp__head<?php echo 'yes' === $settings['show_lines'] ? ' has-lines' : ''; ?>"><<?php echo esc_attr( $heading_tag ); ?> class="ziteh-rp__heading"><?php echo esc_html( $settings['title'] ); ?></<?php echo esc_attr( $heading_tag ); ?>><?php if ( 'yes' === $settings['show_leaf'] ) : ?><i class="fas fa-leaf" aria-hidden="true"></i><?php endif; ?></div>
				<?php if ( $products ) : ?>
					<div class="ziteh-rp__shell">
						<?php if ( 'yes' === $settings['show_arrows'] ) : ?><button class="ziteh-rp__arrow ziteh-rp__arrow--prev" type="button" data-ziteh-rp-prev aria-label="<?php esc_attr_e( 'محصولات قبلی', 'ziteh' ); ?>"><i class="fas fa-chevron-right" aria-hidden="true"></i></button><?php endif; ?>
						<div class="ziteh-rp__viewport" data-ziteh-rp-viewport tabindex="0" aria-label="<?php echo esc_attr( $settings['section_label'] ); ?>"><div class="ziteh-rp__track">
							<?php foreach ( $products as $index => $product ) : $this->render_card( $product, $settings, $index ); endforeach; ?>
						</div></div>
						<?php if ( 'yes' === $settings['show_arrows'] ) : ?><button class="ziteh-rp__arrow ziteh-rp__arrow--next" type="button" data-ziteh-rp-next aria-label="<?php esc_attr_e( 'محصولات بعدی', 'ziteh' ); ?>"><i class="fas fa-chevron-left" aria-hidden="true"></i></button><?php endif; ?>
					</div>
				<?php else : ?><p class="ziteh-rp__empty"><?php echo esc_html( $settings['empty_text'] ); ?></p><?php endif; ?>
			</div>
		</section>
		<?php
	}

	/** Render one WooCommerce card. */
	private function render_card( $product, $settings, $index ) {
		$cart_classes = 'ziteh-rp__cart product_type_' . $product->get_type();
		if ( $product->supports( 'ajax_add_to_cart' ) && $product->is_purchasable() && $product->is_in_stock() ) {
			$cart_classes .= ' add_to_cart_button ajax_add_to_cart';
		}
		?>
		<article class="ziteh-rp__card">
			<a class="ziteh-rp__image" href="<?php echo esc_url( $product->get_permalink() ); ?>" aria-label="<?php echo esc_attr( $product->get_name() ); ?>"><?php echo $product->get_image( 'woocommerce_thumbnail', array( 'loading' => 0 === $index && 'eager' === $settings['image_loading'] ? 'eager' : 'lazy', 'decoding' => 'async', 'alt' => $product->get_name() ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
			<div class="ziteh-rp__content"><a class="ziteh-rp__title" href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a><div class="ziteh-rp__foot">
				<?php if ( 'yes' === $settings['show_price'] ) : ?><span class="ziteh-rp__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span><?php endif; ?>
				<?php if ( 'yes' === $settings['show_cart'] ) : ?><a class="<?php echo esc_attr( $cart_classes ); ?>" href="<?php echo esc_url( $product->add_to_cart_url() ); ?>" data-quantity="1" data-product_id="<?php echo esc_attr( $product->get_id() ); ?>" data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>" rel="nofollow" aria-label="<?php echo esc_attr( $product->add_to_cart_description() ); ?>"><i class="fas fa-shopping-cart" aria-hidden="true"></i><span class="screen-reader-text"><?php echo esc_html( $product->add_to_cart_text() ); ?></span></a><?php endif; ?>
			</div></div>
		</article>
		<?php
	}
}
