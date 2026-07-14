<?php
/**
 * Features / USP bar widget — a trust strip of icon + title + subtitle items
 * (اصالت کالا، ارسال سریع، ضمانت بازگشت، پرداخت امن). Common on professional
 * Persian shops; boosts trust near the top or above the footer.
 *
 * @package Ziteh_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * Class Ziteh_Features_Widget
 */
class Ziteh_Features_Widget extends Ziteh_Widget_Base {

	/**
	 * @return string
	 */
	public function get_name() {
		return 'ziteh-features';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'زیته | نوار مزیت‌ها', 'ziteh' );
	}

	/**
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-icon-box';
	}

	/**
	 * Register the editable controls.
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'section_items',
			array(
				'label' => esc_html__( 'مزیت‌ها', 'ziteh' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$rep = new Repeater();
		$rep->add_control(
			'icon',
			array(
				'label'   => esc_html__( 'آیکون', 'ziteh' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'shield',
				'options' => array(
					'shield'  => esc_html__( 'سپر (اصالت/ضمانت)', 'ziteh' ),
					'spray'   => esc_html__( 'ارسال', 'ziteh' ),
					'clock'   => esc_html__( 'زمان', 'ziteh' ),
					'heart'   => esc_html__( 'قلب', 'ziteh' ),
					'leaf'    => esc_html__( 'برگ', 'ziteh' ),
					'droplet' => esc_html__( 'قطره', 'ziteh' ),
					'cart'    => esc_html__( 'سبد خرید', 'ziteh' ),
					'phone'   => esc_html__( 'پشتیبانی', 'ziteh' ),
				),
			)
		);
		$rep->add_control(
			'title',
			array(
				'label'   => esc_html__( 'عنوان', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'مزیت', 'ziteh' ),
			)
		);
		$rep->add_control(
			'subtitle',
			array(
				'label'   => esc_html__( 'زیرعنوان', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'توضیح کوتاه', 'ziteh' ),
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => esc_html__( 'موارد', 'ziteh' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $rep->get_controls(),
				'title_field' => '{{{ title }}}',
				'default'     => array(
					array(
						'icon'     => 'shield',
						'title'    => esc_html__( 'ضمانت اصالت کالا', 'ziteh' ),
						'subtitle' => esc_html__( '۱۰۰٪ اورجینال', 'ziteh' ),
					),
					array(
						'icon'     => 'spray',
						'title'    => esc_html__( 'ارسال سریع', 'ziteh' ),
						'subtitle' => esc_html__( 'به سراسر کشور', 'ziteh' ),
					),
					array(
						'icon'     => 'cart',
						'title'    => esc_html__( 'ضمانت بازگشت', 'ziteh' ),
						'subtitle' => esc_html__( 'تا ۷ روز', 'ziteh' ),
					),
					array(
						'icon'     => 'phone',
						'title'    => esc_html__( 'پشتیبانی', 'ziteh' ),
						'subtitle' => esc_html__( 'پاسخگویی هر روز', 'ziteh' ),
					),
				),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			array(
				'label' => esc_html__( 'استایل', 'ziteh' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'boxed',
			array(
				'label'        => esc_html__( 'نمایش داخل کادر', 'ziteh' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'bg_color',
			array(
				'label'     => esc_html__( 'رنگ پس‌زمینه', 'ziteh' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .ziteh-features' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render the front-end output.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$boxed    = ( 'yes' === $settings['boxed'] ) ? ' ziteh-features--boxed' : '';
		?>
		<section class="ziteh-features<?php echo esc_attr( $boxed ); ?> ziteh-reveal">
			<div class="ziteh-container">
				<ul class="ziteh-features__row">
					<?php foreach ( $settings['items'] as $item ) : ?>
						<li class="ziteh-feature">
							<span class="ziteh-feature__icon"><?php echo $this->get_icon_svg( $item['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
							<span class="ziteh-feature__text">
								<span class="ziteh-feature__title"><?php echo esc_html( $item['title'] ); ?></span>
								<span class="ziteh-feature__sub"><?php echo esc_html( $item['subtitle'] ); ?></span>
							</span>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
		<?php
	}
}
