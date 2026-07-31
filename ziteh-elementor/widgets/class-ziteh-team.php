<?php
/**
 * Team grid for the About page.
 *
 * @package Ziteh_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;

/**
 * Class Ziteh_Team_Widget.
 */
class Ziteh_Team_Widget extends Ziteh_Widget_Base {

	public function get_name() {
		return 'ziteh-team';
	}

	public function get_title() {
		return esc_html__( 'زیته | تیم ما', 'ziteh' );
	}

	public function get_icon() {
		return 'eicon-person';
	}

	public function get_keywords() {
		return array_merge( parent::get_keywords(), array( 'team', 'about', 'people', 'تیم', 'درباره ما', 'همکاران' ) );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_head', array( 'label' => esc_html__( 'سرتیتر', 'ziteh' ), 'tab' => Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'eyebrow', array( 'label' => esc_html__( 'پیش‌عنوان', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'آدم‌های زیته', 'ziteh' ) ) );
		$this->add_control( 'title', array( 'label' => esc_html__( 'عنوان', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'تیمی که پشت هر محصول ایستاده', 'ziteh' ), 'label_block' => true ) );
		$this->add_control( 'description', array( 'label' => esc_html__( 'توضیح', 'ziteh' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 2, 'default' => esc_html__( 'کارشناسان فرمولاسیون، مشاوران پوست و مو و تیم پشتیبانی که هر روز پاسخگوی شما هستند.', 'ziteh' ) ) );
		$this->add_control( 'heading_tag', array( 'label' => esc_html__( 'تگ عنوان', 'ziteh' ), 'type' => Controls_Manager::SELECT, 'default' => 'h2', 'options' => array( 'h2' => 'H2', 'h3' => 'H3', 'div' => 'DIV' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_members', array( 'label' => esc_html__( 'اعضا', 'ziteh' ), 'tab' => Controls_Manager::TAB_CONTENT ) );
		$member = new Repeater();
		$member->add_control( 'photo', array( 'label' => esc_html__( 'تصویر', 'ziteh' ), 'type' => Controls_Manager::MEDIA ) );
		$member->add_control( 'name', array( 'label' => esc_html__( 'نام', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'نام همکار', 'ziteh' ), 'label_block' => true ) );
		$member->add_control( 'role', array( 'label' => esc_html__( 'سمت', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'کارشناس', 'ziteh' ), 'label_block' => true ) );
		$member->add_control( 'bio', array( 'label' => esc_html__( 'معرفی کوتاه', 'ziteh' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 2 ) );
		$member->add_control( 'link', array( 'label' => esc_html__( 'لینک', 'ziteh' ), 'type' => Controls_Manager::URL, 'placeholder' => 'https://' ) );
		$this->add_control(
			'members',
			array(
				'label'       => esc_html__( 'فهرست اعضا', 'ziteh' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $member->get_controls(),
				'title_field' => '{{{ name }}}',
				'default'     => array(
					array( 'name' => esc_html__( 'دکتر سارا کریمی', 'ziteh' ), 'role' => esc_html__( 'مسئول فنی و فرمولاسیون', 'ziteh' ), 'bio' => esc_html__( 'داروساز، با تمرکز بر ترکیبات گیاهی مراقبت از مو.', 'ziteh' ) ),
					array( 'name' => esc_html__( 'مهدی رستمی', 'ziteh' ), 'role' => esc_html__( 'مشاور پوست و مو', 'ziteh' ), 'bio' => esc_html__( 'پاسخگوی پرسش‌های تخصصی مشتریان در تمام روزهای هفته.', 'ziteh' ) ),
					array( 'name' => esc_html__( 'نگار احمدی', 'ziteh' ), 'role' => esc_html__( 'کنترل کیفیت', 'ziteh' ), 'bio' => esc_html__( 'بررسی اصالت و کیفیت هر محموله پیش از ورود به انبار.', 'ziteh' ) ),
					array( 'name' => esc_html__( 'امیر توکلی', 'ziteh' ), 'role' => esc_html__( 'پشتیبانی مشتریان', 'ziteh' ), 'bio' => esc_html__( 'پیگیری سفارش‌ها از ثبت تا تحویل.', 'ziteh' ) ),
				),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section( 'section_layout', array( 'label' => esc_html__( 'چیدمان', 'ziteh' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_responsive_control( 'max_width', array( 'label' => esc_html__( 'حداکثر عرض', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'size_units' => array( 'px' ), 'range' => array( 'px' => array( 'min' => 760, 'max' => 1680 ) ), 'default' => array( 'size' => 1240, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-tm' => '--ziteh-tm-max: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'columns', array( 'label' => esc_html__( 'تعداد ستون', 'ziteh' ), 'type' => Controls_Manager::SELECT, 'default' => '4', 'tablet_default' => '2', 'mobile_default' => '1', 'options' => array( '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴' ), 'selectors' => array( '{{WRAPPER}} .ziteh-tm' => '--ziteh-tm-cols: {{VALUE}};' ) ) );
		$this->add_responsive_control( 'gap', array( 'label' => esc_html__( 'فاصله', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 8, 'max' => 60 ) ), 'default' => array( 'size' => 20, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-tm' => '--ziteh-tm-gap: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_control( 'photo_ratio', array( 'label' => esc_html__( 'نسبت تصویر', 'ziteh' ), 'type' => Controls_Manager::SELECT, 'default' => '1 / 1', 'options' => array( '1 / 1' => esc_html__( 'مربع', 'ziteh' ), '4 / 5' => '۴:۵', '3 / 4' => '۳:۴' ), 'selectors' => array( '{{WRAPPER}} .ziteh-tm' => '--ziteh-tm-ratio: {{VALUE}};' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_colors', array( 'label' => esc_html__( 'رنگ‌ها', 'ziteh' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$colors = array(
			'background_color' => array( 'پس‌زمینه', '#faf9f6', '--ziteh-tm-bg' ),
			'surface_color'    => array( 'سطح کارت', '#ffffff', '--ziteh-tm-surface' ),
			'primary_color'    => array( 'رنگ اصلی', '#6f7d5f', '--ziteh-tm-primary' ),
			'heading_color'    => array( 'رنگ عنوان', '#333830', '--ziteh-tm-heading' ),
			'text_color'       => array( 'رنگ متن', '#787b74', '--ziteh-tm-text' ),
			'border_color'     => array( 'رنگ خطوط', '#e9e6df', '--ziteh-tm-border' ),
		);
		foreach ( $colors as $key => $data ) {
			$this->add_control( $key, array( 'label' => esc_html( $data[0] ), 'type' => Controls_Manager::COLOR, 'default' => $data[1], 'selectors' => array( '{{WRAPPER}} .ziteh-tm' => $data[2] . ': {{VALUE}};' ) ) );
		}
		$this->add_responsive_control( 'radius', array( 'label' => esc_html__( 'گردی کارت', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 36 ) ), 'default' => array( 'size' => 16, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-tm' => '--ziteh-tm-radius: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array( 'name' => 'card_shadow', 'label' => esc_html__( 'سایه کارت', 'ziteh' ), 'selector' => '{{WRAPPER}} .ziteh-tm__card' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_typography', array( 'label' => esc_html__( 'تایپوگرافی', 'ziteh' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'title_typography', 'label' => esc_html__( 'عنوان', 'ziteh' ), 'selector' => '{{WRAPPER}} .ziteh-tm__title' ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'name_typography', 'label' => esc_html__( 'نام عضو', 'ziteh' ), 'selector' => '{{WRAPPER}} .ziteh-tm__name' ) );
		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		if ( empty( $settings['members'] ) ) {
			return;
		}
		$tag = in_array( $settings['heading_tag'], array( 'h2', 'h3', 'div' ), true ) ? $settings['heading_tag'] : 'h2';
		?>
		<section class="ziteh-tm" dir="rtl" data-ziteh-team>
			<div class="ziteh-tm__container">
				<header class="ziteh-tm__head">
					<?php if ( ! empty( $settings['eyebrow'] ) ) : ?>
						<span class="ziteh-tm__eyebrow"><?php Ziteh_Icons::render( 'leaf' ); ?><span><?php echo esc_html( $settings['eyebrow'] ); ?></span></span>
					<?php endif; ?>
					<<?php echo esc_attr( $tag ); ?> class="ziteh-tm__title"><?php echo esc_html( $settings['title'] ); ?></<?php echo esc_attr( $tag ); ?>>
					<?php if ( ! empty( $settings['description'] ) ) : ?>
						<p class="ziteh-tm__description"><?php echo esc_html( $settings['description'] ); ?></p>
					<?php endif; ?>
				</header>

				<ul class="ziteh-tm__grid">
					<?php
					foreach ( $settings['members'] as $member ) :
						$has_link = ! empty( $member['link']['url'] );
						$tag_name = $has_link ? 'a' : 'div';
						?>
						<li>
							<<?php echo esc_attr( $tag_name ); ?> class="ziteh-tm__card"
								<?php if ( $has_link ) : ?>
									href="<?php echo esc_url( $member['link']['url'] ); ?>"
									<?php echo ! empty( $member['link']['is_external'] ) ? ' target="_blank" rel="noopener"' : ''; ?>
								<?php endif; ?>>
								<span class="ziteh-tm__photo">
									<?php if ( ! empty( $member['photo']['url'] ) ) : ?>
										<img src="<?php echo esc_url( $member['photo']['url'] ); ?>" alt="<?php echo esc_attr( $member['name'] ); ?>" loading="lazy" decoding="async">
									<?php else : ?>
										<span class="ziteh-tm__photo-fallback"><?php Ziteh_Icons::render( 'user' ); ?></span>
									<?php endif; ?>
								</span>
								<span class="ziteh-tm__body">
									<strong class="ziteh-tm__name"><?php echo esc_html( $member['name'] ); ?></strong>
									<?php if ( ! empty( $member['role'] ) ) : ?>
										<span class="ziteh-tm__role"><?php echo esc_html( $member['role'] ); ?></span>
									<?php endif; ?>
									<?php if ( ! empty( $member['bio'] ) ) : ?>
										<span class="ziteh-tm__bio"><?php echo esc_html( $member['bio'] ); ?></span>
									<?php endif; ?>
								</span>
							</<?php echo esc_attr( $tag_name ); ?>>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
		<?php
	}
}
