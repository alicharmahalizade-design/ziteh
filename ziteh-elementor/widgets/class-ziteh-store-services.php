<?php
/**
 * Store services and trust benefits strip.
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

/** Store services widget. */
class Ziteh_Store_Services_Widget extends Ziteh_Widget_Base {

	public function get_name() { return 'ziteh-store-services'; }
	public function get_title() { return esc_html__( '۵. خدمات و مزایای خرید', 'ziteh' ); }
	public function get_icon() { return 'eicon-icon-box'; }
	public function get_keywords() { return array_merge( parent::get_keywords(), array( 'service', 'trust', 'benefit', 'ارسال', 'پشتیبانی', 'مزایا', 'خدمات فروشگاه' ) ); }

	protected function register_controls() {
		$this->start_controls_section( 'section_items', array( 'label' => esc_html__( 'خدمات', 'ziteh' ), 'tab' => Controls_Manager::TAB_CONTENT ) );
		$repeater = new Repeater();
		$repeater->add_control( 'selected_icon', array( 'label' => esc_html__( 'آیکن', 'ziteh' ), 'type' => Controls_Manager::ICONS, 'default' => array( 'value' => 'fas fa-truck-fast', 'library' => 'fa-solid' ) ) );
		$repeater->add_control( 'title', array( 'label' => esc_html__( 'عنوان', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'عنوان خدمت', 'ziteh' ), 'label_block' => true ) );
		$repeater->add_control( 'description', array( 'label' => esc_html__( 'توضیح کوتاه', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'توضیحات این خدمت', 'ziteh' ), 'label_block' => true ) );
		$repeater->add_control( 'link', array( 'label' => esc_html__( 'لینک', 'ziteh' ), 'type' => Controls_Manager::URL, 'placeholder' => 'https://example.com' ) );
		$repeater->add_control( 'aria_label', array( 'label' => esc_html__( 'برچسب دسترس‌پذیری', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'description' => esc_html__( 'در صورت خالی بودن از عنوان استفاده می‌شود.', 'ziteh' ) ) );
		$this->add_control( 'items', array(
			'label' => esc_html__( 'آیتم‌ها', 'ziteh' ), 'type' => Controls_Manager::REPEATER, 'fields' => $repeater->get_controls(), 'title_field' => '{{{ title }}}',
			'default' => array(
				array( 'selected_icon' => array( 'value' => 'fas fa-truck-fast', 'library' => 'fa-solid' ), 'title' => esc_html__( 'ارسال سریع', 'ziteh' ), 'description' => esc_html__( 'تحویل ۱ تا ۳ روز کاری', 'ziteh' ) ),
				array( 'selected_icon' => array( 'value' => 'fas fa-rotate-right', 'library' => 'fa-solid' ), 'title' => esc_html__( 'بازگشت آسان', 'ziteh' ), 'description' => esc_html__( '۷ روز ضمانت بازگشت کالا', 'ziteh' ) ),
				array( 'selected_icon' => array( 'value' => 'fas fa-box', 'library' => 'fa-solid' ), 'title' => esc_html__( 'بسته‌بندی ایمن', 'ziteh' ), 'description' => esc_html__( 'محصولات با بسته‌بندی استاندارد', 'ziteh' ) ),
				array( 'selected_icon' => array( 'value' => 'fas fa-headset', 'library' => 'fa-solid' ), 'title' => esc_html__( 'پشتیبانی حرفه‌ای', 'ziteh' ), 'description' => esc_html__( 'پشتیبانی قبل و بعد از خرید', 'ziteh' ) ),
			),
		) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_behavior', array( 'label' => esc_html__( 'رفتار و دسترس‌پذیری', 'ziteh' ), 'tab' => Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'section_label', array( 'label' => esc_html__( 'برچسب سکشن', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'خدمات و مزایای فروشگاه', 'ziteh' ) ) );
		$this->add_control( 'mobile_layout', array( 'label' => esc_html__( 'چیدمان موبایل', 'ziteh' ), 'type' => Controls_Manager::SELECT, 'default' => 'scroll', 'options' => array( 'scroll' => esc_html__( 'اسکرول افقی', 'ziteh' ), 'grid' => esc_html__( 'شبکه دو ستونه', 'ziteh' ), 'stack' => esc_html__( 'تک‌ستونه', 'ziteh' ) ), 'prefix_class' => 'ziteh-ss-mobile-' ) );
		$this->add_control( 'open_external', array( 'label' => esc_html__( 'اعمال تنظیمات لینک هر آیتم', 'ziteh' ), 'type' => Controls_Manager::RAW_HTML, 'raw' => esc_html__( 'هدف و nofollow از تنظیمات لینک داخل هر آیتم خوانده می‌شود.', 'ziteh' ), 'content_classes' => 'elementor-panel-alert elementor-panel-alert-info' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_layout', array( 'label' => esc_html__( 'چیدمان', 'ziteh' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_responsive_control( 'max_width', array( 'label' => esc_html__( 'حداکثر عرض', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'size_units' => array( 'px' ), 'range' => array( 'px' => array( 'min' => 600, 'max' => 1800 ) ), 'default' => array( 'size' => 860, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-ss' => '--ziteh-ss-max:{{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'min_height', array( 'label' => esc_html__( 'حداقل ارتفاع', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 48, 'max' => 180 ) ), 'default' => array( 'size' => 59, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-ss' => '--ziteh-ss-height:{{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'item_gap', array( 'label' => esc_html__( 'فاصله آیکن و متن', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 4, 'max' => 40 ) ), 'default' => array( 'size' => 18, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-ss' => '--ziteh-ss-gap:{{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'horizontal_padding', array( 'label' => esc_html__( 'فاصله افقی داخلی', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 4, 'max' => 60 ) ), 'default' => array( 'size' => 18, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-ss' => '--ziteh-ss-pad:{{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'icon_size', array( 'label' => esc_html__( 'اندازه آیکن', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 18, 'max' => 64 ) ), 'default' => array( 'size' => 28, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-ss' => '--ziteh-ss-icon:{{SIZE}}{{UNIT}};' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_appearance', array( 'label' => esc_html__( 'رنگ‌ها و سطح', 'ziteh' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$colors = array(
			'background_color' => array( 'پس‌زمینه', '#f5f2ed', '--ziteh-ss-bg' ), 'title_color' => array( 'عنوان', '#68735d', '--ziteh-ss-title' ),
			'description_color' => array( 'توضیحات', '#9b9a94', '--ziteh-ss-desc' ), 'icon_color' => array( 'آیکن', '#78866f', '--ziteh-ss-icon-color' ),
			'divider_color' => array( 'جداکننده', '#e7e3dc', '--ziteh-ss-divider' ), 'hover_color' => array( 'پس‌زمینه هاور', '#eeece5', '--ziteh-ss-hover' ),
		);
		foreach ( $colors as $key => $data ) { $this->add_control( $key, array( 'label' => esc_html( $data[0] ), 'type' => Controls_Manager::COLOR, 'default' => $data[1], 'selectors' => array( '{{WRAPPER}} .ziteh-ss' => $data[2] . ':{{VALUE}};' ) ) ); }
		$this->add_responsive_control( 'radius', array( 'label' => esc_html__( 'گردی نوار', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 60 ) ), 'default' => array( 'size' => 18, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-ss' => '--ziteh-ss-radius:{{SIZE}}{{UNIT}};' ) ) );
		$this->add_control( 'show_dividers', array( 'label' => esc_html__( 'نمایش جداکننده‌ها', 'ziteh' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes', 'prefix_class' => 'ziteh-ss-dividers-' ) );
		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array( 'name' => 'strip_shadow', 'label' => esc_html__( 'سایه نوار', 'ziteh' ), 'selector' => '{{WRAPPER}} .ziteh-ss__inner' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_typography', array( 'label' => esc_html__( 'تایپوگرافی', 'ziteh' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'title_typography', 'label' => esc_html__( 'عنوان', 'ziteh' ), 'selector' => '{{WRAPPER}} .ziteh-ss__title' ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'description_typography', 'label' => esc_html__( 'توضیحات', 'ziteh' ), 'selector' => '{{WRAPPER}} .ziteh-ss__description' ) );
		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$items = ! empty( $settings['items'] ) ? $settings['items'] : array();
		if ( ! $items ) { return; }
		?>
		<section class="ziteh-ss" dir="rtl" style="--ziteh-ss-count:<?php echo esc_attr( count( $items ) ); ?>" aria-label="<?php echo esc_attr( $settings['section_label'] ); ?>">
			<div class="ziteh-ss__inner" role="list">
				<?php foreach ( $items as $index => $item ) :
					$has_link = ! empty( $item['link']['url'] );
					$tag = $has_link ? 'a' : 'div';
					$aria = ! empty( $item['aria_label'] ) ? $item['aria_label'] : $item['title'];
					$attrs = $has_link ? ' href="' . esc_url( $item['link']['url'] ) . '"' : '';
					$attrs .= $has_link && ! empty( $item['link']['is_external'] ) ? ' target="_blank"' : '';
					$rel = array(); if ( $has_link && ! empty( $item['link']['nofollow'] ) ) { $rel[] = 'nofollow'; } if ( $has_link && ! empty( $item['link']['is_external'] ) ) { $rel[] = 'noopener'; }
					$attrs .= $rel ? ' rel="' . esc_attr( implode( ' ', $rel ) ) . '"' : '';
					?>
					<<?php echo esc_attr( $tag ); ?> class="ziteh-ss__item elementor-repeater-item-<?php echo esc_attr( isset( $item['_id'] ) ? $item['_id'] : $index ); ?>" role="listitem" aria-label="<?php echo esc_attr( $aria ); ?>"<?php echo $attrs; // phpcs:ignore WordPress.Security.EscapeOutput ?> data-ziteh-service-index="<?php echo esc_attr( $index ); ?>">
						<span class="ziteh-ss__icon" aria-hidden="true"><?php Ziteh_Icons::render_control( $item['selected_icon'], 'truck' ); ?></span>
						<span class="ziteh-ss__content"><strong class="ziteh-ss__title"><?php echo esc_html( $item['title'] ); ?></strong><span class="ziteh-ss__description"><?php echo esc_html( $item['description'] ); ?></span></span>
					</<?php echo esc_attr( $tag ); ?>>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
}
