<?php
/**
 * Hero widget — a full-width image slider (banner carousel). Each slide is an
 * image with an optional link; the slider fades between slides with autoplay,
 * prev/next arrows and dot navigation.
 *
 * @package Ziteh_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Repeater;

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
		return esc_html__( 'زیته | هیرو (اسلایدر تصویری)', 'ziteh' );
	}

	/**
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-slider-push';
	}

	/**
	 * Register the editable controls.
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'section_slides',
			array(
				'label' => esc_html__( 'اسلایدها', 'ziteh' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'image',
			array(
				'label'       => esc_html__( 'تصویر دسکتاپ', 'ziteh' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => array(
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				),
				'description' => esc_html__( 'اندازه پیشنهادی: ۱۹۲۰ × ۴۶۰ پیکسل', 'ziteh' ),
			)
		);

		$repeater->add_control(
			'mobile_image',
			array(
				'label'       => esc_html__( 'تصویر موبایل (اختیاری)', 'ziteh' ),
				'type'        => Controls_Manager::MEDIA,
				'default'     => array( 'url' => '' ),
				'description' => esc_html__( 'اندازه پیشنهادی: ۷۵۰ × ۴۴۰ پیکسل؛ در صورت خالی‌بودن، تصویر دسکتاپ استفاده می‌شود.', 'ziteh' ),
			)
		);

		$repeater->add_control(
			'link',
			array(
				'label'         => esc_html__( 'لینک اسلاید (اختیاری)', 'ziteh' ),
				'type'          => Controls_Manager::URL,
				'default'       => array( 'url' => '' ),
				'show_external' => true,
			)
		);

		$repeater->add_control(
			'alt',
			array(
				'label'   => esc_html__( 'متن جایگزین تصویر', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '',
			)
		);

		$this->add_control(
			'slides',
			array(
				'label'       => esc_html__( 'اسلایدها', 'ziteh' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => esc_html__( 'اسلاید', 'ziteh' ) . ' {{{ alt }}}',
				'default'     => array(
					array( 'image' => array( 'url' => \Elementor\Utils::get_placeholder_image_src() ) ),
					array( 'image' => array( 'url' => \Elementor\Utils::get_placeholder_image_src() ) ),
					array( 'image' => array( 'url' => \Elementor\Utils::get_placeholder_image_src() ) ),
				),
			)
		);

		$this->end_controls_section();

		// Slider behaviour.
		$this->start_controls_section(
			'section_settings',
			array(
				'label' => esc_html__( 'تنظیمات اسلایدر', 'ziteh' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'autoplay',
			array(
				'label'        => esc_html__( 'پخش خودکار', 'ziteh' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'autoplay_speed',
			array(
				'label'     => esc_html__( 'مدت هر اسلاید (میلی‌ثانیه)', 'ziteh' ),
				'type'      => Controls_Manager::NUMBER,
				'default'   => 5000,
				'min'       => 1500,
				'max'       => 15000,
				'step'      => 500,
				'condition' => array( 'autoplay' => 'yes' ),
			)
		);

		$this->add_control(
			'show_arrows',
			array(
				'label'        => esc_html__( 'نمایش فلش‌ها', 'ziteh' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'show_dots',
			array(
				'label'        => esc_html__( 'نمایش نقطه‌ها', 'ziteh' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_responsive_control(
			'height',
			array(
				'label'      => esc_html__( 'ارتفاع اسلایدر', 'ziteh' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'vh' ),
				'range'      => array(
					'px' => array(
						'min' => 200,
						'max' => 900,
					),
					'vh' => array(
						'min' => 20,
						'max' => 100,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 460,
				),
				'tablet_default' => array(
					'unit' => 'px',
					'size' => 360,
				),
				'mobile_default' => array(
					'unit' => 'px',
					'size' => 220,
				),
				'selectors'  => array(
					'{{WRAPPER}} .ziteh-hero-slider' => '--ziteh-hero-h: {{SIZE}}{{UNIT}};',
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
			'radius',
			array(
				'label'      => esc_html__( 'گردی گوشه‌ها', 'ziteh' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
				'default'    => array(
					'unit' => 'px',
					'size' => 0,
				),
				'selectors'  => array(
					'{{WRAPPER}} .ziteh-hero-slider' => '--ziteh-hero-radius: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'fit',
			array(
				'label'     => esc_html__( 'نحوه نمایش تصویر', 'ziteh' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'cover',
				'options'   => array(
					'cover'   => esc_html__( 'پرکردن (Cover)', 'ziteh' ),
					'contain' => esc_html__( 'جا‌شدن کامل (Contain)', 'ziteh' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .ziteh-hero-slide img' => 'object-fit: {{VALUE}};',
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
		$slides   = ! empty( $settings['slides'] ) ? $settings['slides'] : array();

		if ( empty( $slides ) ) {
			return;
		}

		$autoplay = ( 'yes' === $settings['autoplay'] ) ? (int) $settings['autoplay_speed'] : 0;
		?>
		<section class="ziteh-hero-slider" data-ziteh-hero data-autoplay="<?php echo esc_attr( $autoplay ); ?>">
			<div class="ziteh-hero-slider__slides">
				<?php
				$i = 0;
				foreach ( $slides as $slide ) :
					$active  = ( 0 === $i ) ? ' is-active' : '';
					$url     = ! empty( $slide['link']['url'] ) ? $slide['link']['url'] : '';
					$target  = ! empty( $slide['link']['is_external'] ) ? ' target="_blank"' : '';
					$nofollow = ! empty( $slide['link']['nofollow'] ) ? ' rel="nofollow"' : '';
					$img_url = ! empty( $slide['image']['url'] ) ? $slide['image']['url'] : '';
					$mobile_img_url = ! empty( $slide['mobile_image']['url'] ) ? $slide['mobile_image']['url'] : '';
					$alt     = ! empty( $slide['alt'] ) ? $slide['alt'] : '';

					if ( ! $img_url ) {
						$i++;
						continue;
					}

					$tag   = $url ? 'a' : 'div';
					$attrs = $url ? ' href="' . esc_url( $url ) . '"' . $target . $nofollow : '';
					?>
					<<?php echo esc_html( $tag ); ?> class="ziteh-hero-slide<?php echo esc_attr( $active ); ?>"<?php echo $attrs; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
						<picture>
							<?php if ( $mobile_img_url ) : ?>
								<source media="(max-width: 767px)" srcset="<?php echo esc_url( $mobile_img_url ); ?>">
							<?php endif; ?>
							<img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $alt ); ?>" loading="<?php echo 0 === $i ? 'eager' : 'lazy'; ?>" decoding="async">
						</picture>
					</<?php echo esc_html( $tag ); ?>>
					<?php
					$i++;
				endforeach;
				?>
			</div>

			<?php if ( 'yes' === $settings['show_arrows'] && count( $slides ) > 1 ) : ?>
				<button class="ziteh-hero-slider__arrow ziteh-hero-slider__arrow--prev" type="button" data-ziteh-hero-prev aria-label="<?php esc_attr_e( 'قبلی', 'ziteh' ); ?>">
					<?php echo $this->get_icon_svg( 'arrow-r' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</button>
				<button class="ziteh-hero-slider__arrow ziteh-hero-slider__arrow--next" type="button" data-ziteh-hero-next aria-label="<?php esc_attr_e( 'بعدی', 'ziteh' ); ?>">
					<?php echo $this->get_icon_svg( 'arrow-l' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</button>
			<?php endif; ?>

			<?php if ( 'yes' === $settings['show_dots'] && count( $slides ) > 1 ) : ?>
				<div class="ziteh-hero-slider__dots" data-ziteh-hero-dots>
					<?php for ( $d = 0; $d < count( $slides ); $d++ ) : ?>
						<button class="ziteh-hero-slider__dot<?php echo 0 === $d ? ' is-active' : ''; ?>" type="button" data-ziteh-hero-dot="<?php echo esc_attr( $d ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %d slide number */ __( 'اسلاید %d', 'ziteh' ), $d + 1 ) ); ?>"></button>
					<?php endfor; ?>
				</div>
			<?php endif; ?>
		</section>
		<?php
	}
}
