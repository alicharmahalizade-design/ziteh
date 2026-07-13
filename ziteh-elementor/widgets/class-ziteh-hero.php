<?php
/**
 * Hero widget — the large opening banner: product image on the left, headline,
 * sub-text and a CTA button on the right (RTL), on a soft cream background with
 * a decorative leaf flourish.
 *
 * @package Ziteh_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;

/**
 * Class Ziteh_Hero_Widget
 */
class Ziteh_Hero_Widget extends Ziteh_Widget_Base {

	/**
	 * @return string
	 */
	public function get_name() {
		return 'ziteh-hero';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'زیته | هیرو', 'ziteh' );
	}

	/**
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-banner';
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
			'title_line1',
			array(
				'label'   => esc_html__( 'عنوان - خط اول', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'جوانه‌ای', 'ziteh' ),
			)
		);

		$this->add_control(
			'title_line2',
			array(
				'label'   => esc_html__( 'عنوان - خط دوم', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'برای مراقبت از خودت', 'ziteh' ),
			)
		);

		$this->add_control(
			'description',
			array(
				'label'   => esc_html__( 'توضیحات', 'ziteh' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 3,
				'default' => esc_html__( 'زیته، انتخابی آگاهانه از بهترین محصولات بهداشتی و مراقبتی برای زیبایی، سلامت و آرامش تو', 'ziteh' ),
			)
		);

		$this->add_control(
			'button_text',
			array(
				'label'   => esc_html__( 'متن دکمه', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'مشاهده محصولات', 'ziteh' ),
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

		// Style.
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
				'label'     => esc_html__( 'رنگ پس‌زمینه', 'ziteh' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .ziteh-hero' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => esc_html__( 'رنگ عنوان', 'ziteh' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .ziteh-hero__title' => 'color: {{VALUE}};',
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
		<section class="ziteh-hero">
			<span class="ziteh-hero__leaf" aria-hidden="true">
				<?php echo $this->get_icon_svg( 'leaf' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</span>
			<div class="ziteh-container ziteh-hero__inner">

				<div class="ziteh-hero__content">
					<h1 class="ziteh-hero__title">
						<span class="ziteh-hero__title-1"><?php echo esc_html( $settings['title_line1'] ); ?></span>
						<span class="ziteh-hero__title-2"><?php echo esc_html( $settings['title_line2'] ); ?></span>
					</h1>
					<p class="ziteh-hero__desc"><?php echo esc_html( $settings['description'] ); ?></p>
					<?php if ( ! empty( $settings['button_text'] ) ) : ?>
						<a class="ziteh-btn ziteh-btn--primary ziteh-hero__btn" href="<?php echo esc_url( $btn_url ); ?>">
							<?php echo esc_html( $settings['button_text'] ); ?>
							<?php echo $this->get_icon_svg( 'arrow-l' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</a>
					<?php endif; ?>
				</div>

				<div class="ziteh-hero__media">
					<?php if ( ! empty( $settings['image']['url'] ) ) : ?>
						<img src="<?php echo esc_url( $settings['image']['url'] ); ?>" alt="<?php echo esc_attr( $settings['title_line1'] . ' ' . $settings['title_line2'] ); ?>">
					<?php endif; ?>
				</div>

			</div>
		</section>
		<?php
	}
}
