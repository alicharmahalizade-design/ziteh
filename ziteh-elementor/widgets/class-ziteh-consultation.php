<?php
/**
 * Consultation CTA widget — "از اینجا شروع کن": a wide rounded banner with a
 * soft green wash, a product photo on the left and a heading, helper text and a
 * primary button on the right.
 *
 * @package Ziteh_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;

/**
 * Class Ziteh_Consultation_Widget
 */
class Ziteh_Consultation_Widget extends Ziteh_Widget_Base {

	/**
	 * @return string
	 */
	public function get_name() {
		return 'ziteh-consultation';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'زیته | بنر مشاوره', 'ziteh' );
	}

	/**
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-call-to-action';
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
				'default' => esc_html__( 'از اینجا شروع کن', 'ziteh' ),
			)
		);

		$this->add_control(
			'text',
			array(
				'label'   => esc_html__( 'توضیحات', 'ziteh' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 3,
				'default' => esc_html__( 'نمی‌دانی چه محصولی برای پوستت مناسبه؟ با چند سؤال کوتاه، روتین مناسب پوست خودت را پیدا کن.', 'ziteh' ),
			)
		);

		$this->add_control(
			'button_text',
			array(
				'label'   => esc_html__( 'متن دکمه', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'شروع مشاوره پوستی', 'ziteh' ),
			)
		);

		$this->add_control(
			'button_url',
			array(
				'label'         => esc_html__( 'لینک دکمه', 'ziteh' ),
				'type'          => Controls_Manager::URL,
				'default'       => array( 'url' => '#' ),
				'show_external' => false,
			)
		);

		$this->add_control(
			'image',
			array(
				'label'   => esc_html__( 'تصویر محصولات', 'ziteh' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => array(
					'url' => \Elementor\Utils::get_placeholder_image_src(),
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
			'bg_color',
			array(
				'label'     => esc_html__( 'رنگ پس‌زمینه بنر', 'ziteh' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .ziteh-cta__box' => 'background-color: {{VALUE}};',
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
		$btn_url  = ! empty( $settings['button_url']['url'] ) ? $settings['button_url']['url'] : '#';
		?>
		<section class="ziteh-cta">
			<div class="ziteh-container">
				<div class="ziteh-cta__box">

					<div class="ziteh-cta__media">
						<?php if ( ! empty( $settings['image']['url'] ) ) : ?>
							<img src="<?php echo esc_url( $settings['image']['url'] ); ?>" alt="<?php echo esc_attr( $settings['title'] ); ?>">
						<?php endif; ?>
					</div>

					<div class="ziteh-cta__content">
						<h2 class="ziteh-cta__title">
							<?php echo $this->get_icon_svg( 'leaf', 'ziteh-section-title__leaf' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<?php echo esc_html( $settings['title'] ); ?>
						</h2>
						<p class="ziteh-cta__text"><?php echo esc_html( $settings['text'] ); ?></p>
						<?php if ( ! empty( $settings['button_text'] ) ) : ?>
							<a class="ziteh-btn ziteh-btn--primary" href="<?php echo esc_url( $btn_url ); ?>">
								<?php echo esc_html( $settings['button_text'] ); ?>
								<?php echo $this->get_icon_svg( 'arrow-l' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							</a>
						<?php endif; ?>
					</div>

				</div>
			</div>
		</section>
		<?php
	}
}
