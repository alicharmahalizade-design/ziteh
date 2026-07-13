<?php
/**
 * Categories widget — "دسته‌بندی‌های محبوب": a centred section title and a
 * horizontal slider of rounded category cards (image + label), with prev/next
 * dot controls beneath.
 *
 * @package Ziteh_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * Class Ziteh_Categories_Widget
 */
class Ziteh_Categories_Widget extends Ziteh_Widget_Base {

	/**
	 * @return string
	 */
	public function get_name() {
		return 'ziteh-categories';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'زیته | دسته‌بندی‌ها', 'ziteh' );
	}

	/**
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-gallery-grid';
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
				'default' => esc_html__( 'دسته‌بندی‌های محبوب', 'ziteh' ),
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'cat_title',
			array(
				'label'   => esc_html__( 'عنوان دسته', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'دسته‌بندی', 'ziteh' ),
			)
		);

		$repeater->add_control(
			'cat_image',
			array(
				'label'   => esc_html__( 'تصویر', 'ziteh' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => array(
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				),
			)
		);

		$repeater->add_control(
			'cat_url',
			array(
				'label'         => esc_html__( 'لینک', 'ziteh' ),
				'type'          => Controls_Manager::URL,
				'default'       => array( 'url' => '#' ),
				'show_external' => false,
			)
		);

		$this->add_control(
			'categories',
			array(
				'label'       => esc_html__( 'دسته‌بندی‌ها', 'ziteh' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ cat_title }}}',
				'default'     => array(
					array( 'cat_title' => esc_html__( 'مراقبت پوست', 'ziteh' ) ),
					array( 'cat_title' => esc_html__( 'مراقبت مو', 'ziteh' ) ),
					array( 'cat_title' => esc_html__( 'آرایشی', 'ziteh' ) ),
					array( 'cat_title' => esc_html__( 'عطر', 'ziteh' ) ),
					array( 'cat_title' => esc_html__( 'بهداشت فردی', 'ziteh' ) ),
					array( 'cat_title' => esc_html__( 'مکمل‌ها', 'ziteh' ) ),
				),
			)
		);

		$this->end_controls_section();

		// Settings.
		$this->start_controls_section(
			'section_settings',
			array(
				'label' => esc_html__( 'تنظیمات اسلایدر', 'ziteh' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_responsive_control(
			'per_view',
			array(
				'label'          => esc_html__( 'تعداد نمایش', 'ziteh' ),
				'type'           => Controls_Manager::NUMBER,
				'min'            => 1,
				'max'            => 8,
				'default'        => 6,
				'tablet_default' => 3,
				'mobile_default' => 2,
				'selectors'      => array(
					'{{WRAPPER}} .ziteh-cats__track' => '--ziteh-per-view: {{VALUE}};',
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
		?>
		<section class="ziteh-cats">
			<div class="ziteh-container">

				<h2 class="ziteh-section-title ziteh-section-title--center">
					<?php echo $this->get_icon_svg( 'leaf', 'ziteh-section-title__leaf' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php echo esc_html( $settings['title'] ); ?>
				</h2>

				<div class="ziteh-slider" data-ziteh-slider>
					<div class="ziteh-cats__viewport">
						<ul class="ziteh-cats__track" data-ziteh-track>
							<?php foreach ( $settings['categories'] as $cat ) : ?>
								<?php $url = ! empty( $cat['cat_url']['url'] ) ? $cat['cat_url']['url'] : '#'; ?>
								<li class="ziteh-cat-card">
									<a href="<?php echo esc_url( $url ); ?>">
										<span class="ziteh-cat-card__thumb">
											<?php if ( ! empty( $cat['cat_image']['url'] ) ) : ?>
												<img src="<?php echo esc_url( $cat['cat_image']['url'] ); ?>" alt="<?php echo esc_attr( $cat['cat_title'] ); ?>">
											<?php endif; ?>
										</span>
										<span class="ziteh-cat-card__title"><?php echo esc_html( $cat['cat_title'] ); ?></span>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>

					<div class="ziteh-slider__nav">
						<button class="ziteh-slider__btn" type="button" data-ziteh-prev aria-label="<?php esc_attr_e( 'قبلی', 'ziteh' ); ?>">
							<?php echo $this->get_icon_svg( 'arrow-r' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</button>
						<button class="ziteh-slider__btn" type="button" data-ziteh-next aria-label="<?php esc_attr_e( 'بعدی', 'ziteh' ); ?>">
							<?php echo $this->get_icon_svg( 'arrow-l' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</button>
					</div>
				</div>

			</div>
		</section>
		<?php
	}
}
