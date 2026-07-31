<?php
/**
 * Milestone timeline for the About page.
 *
 * @package Ziteh_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;

/**
 * Class Ziteh_Milestones_Widget.
 */
class Ziteh_Milestones_Widget extends Ziteh_Widget_Base {

	public function get_name() {
		return 'ziteh-milestones';
	}

	public function get_title() {
		return esc_html__( 'زیته | مسیر ما', 'ziteh' );
	}

	public function get_icon() {
		return 'eicon-time-line';
	}

	public function get_keywords() {
		return array_merge( parent::get_keywords(), array( 'timeline', 'about', 'history', 'مسیر', 'تاریخچه', 'درباره ما' ) );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_head', array( 'label' => esc_html__( 'سرتیتر', 'ziteh' ), 'tab' => Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'eyebrow', array( 'label' => esc_html__( 'پیش‌عنوان', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'مسیر زیته', 'ziteh' ) ) );
		$this->add_control( 'title', array( 'label' => esc_html__( 'عنوان', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'از یک ایده تا فروشگاه امروز', 'ziteh' ), 'label_block' => true ) );
		$this->add_control( 'heading_tag', array( 'label' => esc_html__( 'تگ عنوان', 'ziteh' ), 'type' => Controls_Manager::SELECT, 'default' => 'h2', 'options' => array( 'h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3', 'div' => 'DIV' ) ) );
		$this->add_control( 'layout', array( 'label' => esc_html__( 'چیدمان', 'ziteh' ), 'type' => Controls_Manager::SELECT, 'default' => 'timeline', 'options' => array( 'timeline' => esc_html__( 'خط زمانی عمودی', 'ziteh' ), 'cards' => esc_html__( 'کارت‌های افقی', 'ziteh' ) ), 'prefix_class' => 'ziteh-ms-layout-' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_items', array( 'label' => esc_html__( 'نقاط عطف', 'ziteh' ), 'tab' => Controls_Manager::TAB_CONTENT ) );
		$item = new Repeater();
		$item->add_control( 'year', array( 'label' => esc_html__( 'سال یا برچسب', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => '۱۴۰۰' ) );
		$item->add_control( 'title', array( 'label' => esc_html__( 'عنوان', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'یک قدم تازه', 'ziteh' ), 'label_block' => true ) );
		$item->add_control( 'text', array( 'label' => esc_html__( 'توضیح', 'ziteh' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 3, 'default' => esc_html__( 'شرح کوتاهی از آنچه در این مرحله اتفاق افتاد.', 'ziteh' ) ) );
		$item->add_control( 'icon', array( 'label' => esc_html__( 'آیکن', 'ziteh' ), 'type' => Controls_Manager::ICONS, 'default' => array( 'value' => 'fas fa-seedling', 'library' => 'fa-solid' ) ) );
		$this->add_control(
			'items',
			array(
				'label'       => esc_html__( 'فهرست', 'ziteh' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $item->get_controls(),
				'title_field' => '{{{ year }}} — {{{ title }}}',
				'default'     => array(
					array( 'year' => '۱۳۹۹', 'title' => esc_html__( 'شروع از یک نیاز ساده', 'ziteh' ), 'text' => esc_html__( 'پیدا کردن محصول مراقبتی اصل و مطمئن سخت بود؛ زیته از همین‌جا شکل گرفت.', 'ziteh' ), 'icon' => array( 'value' => 'fas fa-seedling', 'library' => 'fa-solid' ) ),
					array( 'year' => '۱۴۰۰', 'title' => esc_html__( 'اولین سبد محصولات', 'ziteh' ), 'text' => esc_html__( 'همکاری با برندهای معتبر و راه‌اندازی فرایند کنترل اصالت کالا.', 'ziteh' ), 'icon' => array( 'value' => 'fas fa-box', 'library' => 'fa-solid' ) ),
					array( 'year' => '۱۴۰۱', 'title' => esc_html__( 'مشاوره تخصصی رایگان', 'ziteh' ), 'text' => esc_html__( 'تیم مشاوره پوست و مو راه‌اندازی شد تا انتخاب محصول ساده‌تر شود.', 'ziteh' ), 'icon' => array( 'value' => 'fas fa-headset', 'library' => 'fa-solid' ) ),
					array( 'year' => '۱۴۰۳', 'title' => esc_html__( 'ارسال به سراسر ایران', 'ziteh' ), 'text' => esc_html__( 'گسترش انبار و تحویل سفارش‌ها در ۲ تا ۳ روز کاری به همه شهرها.', 'ziteh' ), 'icon' => array( 'value' => 'fas fa-truck-fast', 'library' => 'fa-solid' ) ),
				),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section( 'section_layout_style', array( 'label' => esc_html__( 'ابعاد', 'ziteh' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_responsive_control( 'max_width', array( 'label' => esc_html__( 'حداکثر عرض', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'size_units' => array( 'px' ), 'range' => array( 'px' => array( 'min' => 640, 'max' => 1400 ) ), 'default' => array( 'size' => 980, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-ms' => '--ziteh-ms-max: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'gap', array( 'label' => esc_html__( 'فاصله آیتم‌ها', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 8, 'max' => 60 ) ), 'default' => array( 'size' => 22, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-ms' => '--ziteh-ms-gap: {{SIZE}}{{UNIT}};' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_colors', array( 'label' => esc_html__( 'رنگ‌ها', 'ziteh' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$colors = array(
			'background_color' => array( 'پس‌زمینه', '#faf9f6', '--ziteh-ms-bg' ),
			'surface_color'    => array( 'سطح کارت', '#ffffff', '--ziteh-ms-surface' ),
			'primary_color'    => array( 'رنگ اصلی', '#6f7d5f', '--ziteh-ms-primary' ),
			'heading_color'    => array( 'رنگ عنوان', '#333830', '--ziteh-ms-heading' ),
			'text_color'       => array( 'رنگ متن', '#787b74', '--ziteh-ms-text' ),
			'border_color'     => array( 'رنگ خطوط', '#e9e6df', '--ziteh-ms-border' ),
		);
		foreach ( $colors as $key => $data ) {
			$this->add_control( $key, array( 'label' => esc_html( $data[0] ), 'type' => Controls_Manager::COLOR, 'default' => $data[1], 'selectors' => array( '{{WRAPPER}} .ziteh-ms' => $data[2] . ': {{VALUE}};' ) ) );
		}
		$this->add_responsive_control( 'radius', array( 'label' => esc_html__( 'گردی کارت', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 36 ) ), 'default' => array( 'size' => 16, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-ms' => '--ziteh-ms-radius: {{SIZE}}{{UNIT}};' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_typography', array( 'label' => esc_html__( 'تایپوگرافی', 'ziteh' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'title_typography', 'label' => esc_html__( 'عنوان سکشن', 'ziteh' ), 'selector' => '{{WRAPPER}} .ziteh-ms__title' ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'year_typography', 'label' => esc_html__( 'سال', 'ziteh' ), 'selector' => '{{WRAPPER}} .ziteh-ms__year' ) );
		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		if ( empty( $settings['items'] ) ) {
			return;
		}
		$tag = in_array( $settings['heading_tag'], array( 'h1', 'h2', 'h3', 'div' ), true ) ? $settings['heading_tag'] : 'h2';
		?>
		<section class="ziteh-ms" dir="rtl" data-ziteh-milestones>
			<div class="ziteh-ms__container">
				<header class="ziteh-ms__head">
					<?php if ( ! empty( $settings['eyebrow'] ) ) : ?>
						<span class="ziteh-ms__eyebrow"><?php Ziteh_Icons::render( 'leaf' ); ?><span><?php echo esc_html( $settings['eyebrow'] ); ?></span></span>
					<?php endif; ?>
					<<?php echo esc_attr( $tag ); ?> class="ziteh-ms__title"><?php echo esc_html( $settings['title'] ); ?></<?php echo esc_attr( $tag ); ?>>
				</header>

				<ol class="ziteh-ms__list">
					<?php foreach ( $settings['items'] as $item ) : ?>
						<li class="ziteh-ms__item">
							<span class="ziteh-ms__marker" aria-hidden="true"><?php Ziteh_Icons::render_control( $item['icon'], 'seedling' ); ?></span>
							<div class="ziteh-ms__card">
								<?php if ( ! empty( $item['year'] ) ) : ?>
									<span class="ziteh-ms__year"><?php echo esc_html( $item['year'] ); ?></span>
								<?php endif; ?>
								<strong class="ziteh-ms__item-title"><?php echo esc_html( $item['title'] ); ?></strong>
								<?php if ( ! empty( $item['text'] ) ) : ?>
									<p><?php echo esc_html( $item['text'] ); ?></p>
								<?php endif; ?>
							</div>
						</li>
					<?php endforeach; ?>
				</ol>
			</div>
		</section>
		<?php
	}
}
