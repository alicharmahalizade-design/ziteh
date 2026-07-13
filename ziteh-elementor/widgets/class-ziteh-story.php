<?php
/**
 * Story widget — "داستان زیته": a small script-style eyebrow, heading and body
 * copy with a "بیشتر بخوانید" link on the right, and a rounded image card
 * (leaf held in a hand) on the left.
 *
 * @package Ziteh_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;

/**
 * Class Ziteh_Story_Widget
 */
class Ziteh_Story_Widget extends Ziteh_Widget_Base {

	/**
	 * @return string
	 */
	public function get_name() {
		return 'ziteh-story';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'زیته | داستان زیته', 'ziteh' );
	}

	/**
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-info-box';
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
				'default' => esc_html__( 'داستان زیته', 'ziteh' ),
			)
		);

		$this->add_control(
			'text',
			array(
				'label'   => esc_html__( 'متن', 'ziteh' ),
				'type'    => Controls_Manager::WYSIWYG,
				'default' => '<p>' . esc_html__( 'زیته در زبان گیلکی یعنی روییدن و جوانه زدن. ما این نام را انتخاب کردیم چون باور داریم مراقبت از خود، مانند جوانه‌زدن است؛ با قدم‌های کوچک آغاز می‌شود و با عشق و استمرار، بیشتر می‌کند.', 'ziteh' ) . '</p>',
			)
		);

		$this->add_control(
			'button_text',
			array(
				'label'   => esc_html__( 'متن لینک', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'بیشتر درباره ما', 'ziteh' ),
			)
		);

		$this->add_control(
			'button_url',
			array(
				'label'         => esc_html__( 'لینک', 'ziteh' ),
				'type'          => Controls_Manager::URL,
				'default'       => array( 'url' => '#' ),
				'show_external' => false,
			)
		);

		$this->add_control(
			'image',
			array(
				'label'   => esc_html__( 'تصویر', 'ziteh' ),
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
				'label'     => esc_html__( 'رنگ پس‌زمینه', 'ziteh' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .ziteh-story' => 'background-color: {{VALUE}};',
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
		<section class="ziteh-story">
			<div class="ziteh-container ziteh-story__inner">

				<div class="ziteh-story__content">
					<h2 class="ziteh-section-title ziteh-section-title--start">
						<?php echo $this->get_icon_svg( 'leaf', 'ziteh-section-title__leaf' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<?php echo esc_html( $settings['title'] ); ?>
					</h2>
					<div class="ziteh-story__text"><?php echo wp_kses_post( $settings['text'] ); ?></div>
					<?php if ( ! empty( $settings['button_text'] ) ) : ?>
						<a class="ziteh-link" href="<?php echo esc_url( $btn_url ); ?>">
							<?php echo esc_html( $settings['button_text'] ); ?>
							<?php echo $this->get_icon_svg( 'arrow-l' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</a>
					<?php endif; ?>
				</div>

				<div class="ziteh-story__media">
					<?php if ( ! empty( $settings['image']['url'] ) ) : ?>
						<img src="<?php echo esc_url( $settings['image']['url'] ); ?>" alt="<?php echo esc_attr( $settings['title'] ); ?>">
					<?php endif; ?>
				</div>

			</div>
		</section>
		<?php
	}
}
