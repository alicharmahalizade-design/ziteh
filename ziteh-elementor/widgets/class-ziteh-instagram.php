<?php
/**
 * Instagram widget — "ما را در اینستاگرام دنبال کنید": a title, the @handle, a
 * row/grid of square photo tiles (each with a hover instagram glyph) and a
 * "مشاهده بیشتر در اینستاگرام" button.
 *
 * @package Ziteh_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * Class Ziteh_Instagram_Widget
 */
class Ziteh_Instagram_Widget extends Ziteh_Widget_Base {

	/**
	 * @return string
	 */
	public function get_name() {
		return 'ziteh-instagram';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'زیته | اینستاگرام', 'ziteh' );
	}

	/**
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-instagram-gallery';
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
				'label'   => esc_html__( 'عنوان بخش', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'ما را در اینستاگرام دنبال کنید', 'ziteh' ),
			)
		);

		$this->add_control(
			'handle',
			array(
				'label'   => esc_html__( 'آیدی اینستاگرام', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '@ziteh',
			)
		);

		$this->add_control(
			'images',
			array(
				'label'   => esc_html__( 'تصاویر', 'ziteh' ),
				'type'    => Controls_Manager::GALLERY,
				'default' => array(),
			)
		);

		$this->add_control(
			'profile_url',
			array(
				'label'         => esc_html__( 'لینک پروفایل', 'ziteh' ),
				'type'          => Controls_Manager::URL,
				'default'       => array( 'url' => '#' ),
				'show_external' => true,
			)
		);

		$this->add_control(
			'button_text',
			array(
				'label'   => esc_html__( 'متن دکمه', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'مشاهده بیشتر در اینستاگرام', 'ziteh' ),
			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_settings',
			array(
				'label' => esc_html__( 'تنظیمات', 'ziteh' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_responsive_control(
			'columns',
			array(
				'label'          => esc_html__( 'تعداد ستون', 'ziteh' ),
				'type'           => Controls_Manager::NUMBER,
				'min'            => 2,
				'max'            => 10,
				'default'        => 6,
				'tablet_default' => 4,
				'mobile_default' => 3,
				'selectors'      => array(
					'{{WRAPPER}} .ziteh-insta__grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render the front-end output.
	 */
	protected function render() {
		$settings    = $this->get_settings_for_display();
		$profile_url = ! empty( $settings['profile_url']['url'] ) ? $settings['profile_url']['url'] : '#';
		$target      = ! empty( $settings['profile_url']['is_external'] ) ? ' target="_blank" rel="noopener"' : '';
		?>
		<section class="ziteh-insta">
			<div class="ziteh-container">

				<h2 class="ziteh-section-title ziteh-section-title--center">
					<?php echo $this->get_icon_svg( 'instagram', 'ziteh-section-title__leaf' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php echo esc_html( $settings['title'] ); ?>
				</h2>
				<?php if ( ! empty( $settings['handle'] ) ) : ?>
					<p class="ziteh-insta__handle"><?php echo esc_html( $settings['handle'] ); ?></p>
				<?php endif; ?>

				<div class="ziteh-insta__grid">
					<?php if ( ! empty( $settings['images'] ) ) : ?>
						<?php foreach ( $settings['images'] as $img ) : ?>
							<a class="ziteh-insta__tile" href="<?php echo esc_url( $profile_url ); ?>"<?php echo $target; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
								<img src="<?php echo esc_url( $img['url'] ); ?>" alt="<?php echo esc_attr( $settings['handle'] ); ?>">
								<span class="ziteh-insta__tile-icon">
									<?php echo $this->get_icon_svg( 'instagram' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
								</span>
							</a>
						<?php endforeach; ?>
					<?php else : ?>
						<?php for ( $i = 0; $i < 6; $i++ ) : ?>
							<span class="ziteh-insta__tile ziteh-insta__tile--empty">
								<?php echo $this->get_icon_svg( 'instagram' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							</span>
						<?php endfor; ?>
					<?php endif; ?>
				</div>

				<?php if ( ! empty( $settings['button_text'] ) ) : ?>
					<div class="ziteh-insta__foot">
						<a class="ziteh-btn ziteh-btn--outline" href="<?php echo esc_url( $profile_url ); ?>"<?php echo $target; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
							<?php echo $this->get_icon_svg( 'instagram' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<?php echo esc_html( $settings['button_text'] ); ?>
						</a>
					</div>
				<?php endif; ?>

			</div>
		</section>
		<?php
	}
}
