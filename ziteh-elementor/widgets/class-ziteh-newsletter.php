<?php
/**
 * Newsletter widget — "خبرنامه زیته": a bordered rounded box with the heading
 * and helper text on the right and an email input + subscribe button on the
 * left (RTL).
 *
 * @package Ziteh_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;

/**
 * Class Ziteh_Newsletter_Widget
 */
class Ziteh_Newsletter_Widget extends Ziteh_Widget_Base {

	/**
	 * @return string
	 */
	public function get_name() {
		return 'ziteh-newsletter';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'زیته | خبرنامه', 'ziteh' );
	}

	/**
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-email-field';
	}

	/**
	 * Register the editable controls.
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'section_content',
			array(
				'label' => esc_html__( 'محتوا', 'ziteh' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'title',
			array(
				'label'   => esc_html__( 'عنوان', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'خبرنامه زیته', 'ziteh' ),
			)
		);

		$this->add_control(
			'text',
			array(
				'label'   => esc_html__( 'توضیحات', 'ziteh' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 2,
				'default' => esc_html__( 'جدیدترین محصولات، مقالات و تخفیف‌ها را مستقیماً در ایمیل خود دریافت کنید.', 'ziteh' ),
			)
		);

		$this->add_control(
			'placeholder',
			array(
				'label'   => esc_html__( 'متن فیلد ایمیل', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'ایمیل خود را وارد کنید...', 'ziteh' ),
			)
		);

		$this->add_control(
			'button_text',
			array(
				'label'   => esc_html__( 'متن دکمه', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'عضویت', 'ziteh' ),
			)
		);

		$this->add_control(
			'action_url',
			array(
				'label'       => esc_html__( 'آدرس اکشن فرم', 'ziteh' ),
				'type'        => Controls_Manager::TEXT,
				'description' => esc_html__( 'در صورت اتصال به سرویس خبرنامه، آدرس مقصد فرم را وارد کنید.', 'ziteh' ),
				'default'     => '',
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
			'bg_color',
			array(
				'label'     => esc_html__( 'رنگ پس‌زمینه باکس', 'ziteh' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .ziteh-newsletter__box' => 'background-color: {{VALUE}};',
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
		$action   = ! empty( $settings['action_url'] ) ? $settings['action_url'] : '';
		$onsubmit = $action ? '' : ' onsubmit="return false;"';
		?>
		<section class="ziteh-newsletter">
			<div class="ziteh-container">
				<div class="ziteh-newsletter__box">

					<form class="ziteh-newsletter__form" action="<?php echo esc_url( $action ); ?>" method="post"<?php echo $onsubmit; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
						<input type="email" name="ziteh_email" required placeholder="<?php echo esc_attr( $settings['placeholder'] ); ?>" aria-label="<?php echo esc_attr( $settings['placeholder'] ); ?>">
						<button class="ziteh-btn ziteh-btn--primary" type="submit"><?php echo esc_html( $settings['button_text'] ); ?></button>
					</form>

					<div class="ziteh-newsletter__intro">
						<h2 class="ziteh-newsletter__title">
							<?php echo $this->get_icon_svg( 'leaf', 'ziteh-section-title__leaf' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<?php echo esc_html( $settings['title'] ); ?>
						</h2>
						<p class="ziteh-newsletter__text"><?php echo esc_html( $settings['text'] ); ?></p>
					</div>

				</div>
			</div>
		</section>
		<?php
	}
}
