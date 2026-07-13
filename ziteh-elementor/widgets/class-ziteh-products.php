<?php
/**
 * Products widget — "محصولات منتخب": section title with a "مشاهده همه" link,
 * side arrows and a slider of product cards (wishlist heart, image, brand,
 * name, price and an add-to-cart button).
 *
 * @package Ziteh_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * Class Ziteh_Products_Widget
 */
class Ziteh_Products_Widget extends Ziteh_Widget_Base {

	/**
	 * @return string
	 */
	public function get_name() {
		return 'ziteh-products';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'زیته | محصولات منتخب', 'ziteh' );
	}

	/**
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-products';
	}

	/**
	 * Register the editable controls.
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'section_head',
			array(
				'label' => esc_html__( 'سربخش', 'ziteh' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'title',
			array(
				'label'   => esc_html__( 'عنوان بخش', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'محصولات منتخب', 'ziteh' ),
			)
		);

		$this->add_control(
			'view_all_text',
			array(
				'label'   => esc_html__( 'متن مشاهده همه', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'مشاهده همه', 'ziteh' ),
			)
		);

		$this->add_control(
			'view_all_url',
			array(
				'label'         => esc_html__( 'لینک مشاهده همه', 'ziteh' ),
				'type'          => Controls_Manager::URL,
				'default'       => array( 'url' => '#' ),
				'show_external' => false,
			)
		);

		$this->end_controls_section();

		// Products.
		$this->start_controls_section(
			'section_products',
			array(
				'label' => esc_html__( 'محصولات', 'ziteh' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'p_image',
			array(
				'label'   => esc_html__( 'تصویر', 'ziteh' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => array(
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				),
			)
		);

		$repeater->add_control(
			'p_brand',
			array(
				'label'   => esc_html__( 'برند', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'برند', 'ziteh' ),
			)
		);

		$repeater->add_control(
			'p_name',
			array(
				'label'   => esc_html__( 'نام محصول', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'نام محصول', 'ziteh' ),
			)
		);

		$repeater->add_control(
			'p_price',
			array(
				'label'   => esc_html__( 'قیمت', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( '۱۹۰,۰۰۰', 'ziteh' ),
			)
		);

		$repeater->add_control(
			'p_currency',
			array(
				'label'   => esc_html__( 'واحد پول', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'تومان', 'ziteh' ),
			)
		);

		$repeater->add_control(
			'p_button',
			array(
				'label'   => esc_html__( 'متن دکمه', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'افزودن به سبد', 'ziteh' ),
			)
		);

		$repeater->add_control(
			'p_url',
			array(
				'label'         => esc_html__( 'لینک محصول', 'ziteh' ),
				'type'          => Controls_Manager::URL,
				'default'       => array( 'url' => '#' ),
				'show_external' => false,
			)
		);

		$this->add_control(
			'products',
			array(
				'label'       => esc_html__( 'محصولات', 'ziteh' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ p_name }}}',
				'default'     => array(
					array(
						'p_brand' => 'The Ordinary',
						'p_name'  => esc_html__( 'سرم هیالورونیک اسید', 'ziteh' ),
						'p_price' => esc_html__( '۱٬۲۹۰٬۰۰۰', 'ziteh' ),
					),
					array(
						'p_brand' => 'CeraVe',
						'p_name'  => esc_html__( 'کرم مرطوب‌کننده', 'ziteh' ),
						'p_price' => esc_html__( '۱٬۵۵۰٬۰۰۰', 'ziteh' ),
					),
					array(
						'p_brand' => 'La Roche-Posay',
						'p_name'  => esc_html__( 'ژل شست‌وشوی صورت', 'ziteh' ),
						'p_price' => esc_html__( '۹۵۰٬۰۰۰', 'ziteh' ),
					),
					array(
						'p_brand' => 'COSRX',
						'p_name'  => esc_html__( 'آبرسان مغذی خنزون', 'ziteh' ),
						'p_price' => esc_html__( '۱٬۱۸۰٬۰۰۰', 'ziteh' ),
					),
					array(
						'p_brand' => 'ZARR',
						'p_name'  => esc_html__( 'عطر زنانه', 'ziteh' ),
						'p_price' => esc_html__( '۱٬۹۵۰٬۰۰۰', 'ziteh' ),
					),
				),
			)
		);

		$this->end_controls_section();

		// Slider settings.
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
				'max'            => 6,
				'default'        => 5,
				'tablet_default' => 3,
				'mobile_default' => 2,
				'selectors'      => array(
					'{{WRAPPER}} .ziteh-products__track' => '--ziteh-per-view: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render the front-end output.
	 */
	protected function render() {
		$settings     = $this->get_settings_for_display();
		$view_all_url = ! empty( $settings['view_all_url']['url'] ) ? $settings['view_all_url']['url'] : '#';
		?>
		<section class="ziteh-products">
			<div class="ziteh-container">

				<div class="ziteh-section-head">
					<a class="ziteh-link ziteh-link--muted" href="<?php echo esc_url( $view_all_url ); ?>">
						<?php echo esc_html( $settings['view_all_text'] ); ?>
						<?php echo $this->get_icon_svg( 'arrow-l' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</a>
					<h2 class="ziteh-section-title ziteh-section-title--center">
						<?php echo $this->get_icon_svg( 'leaf', 'ziteh-section-title__leaf' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<?php echo esc_html( $settings['title'] ); ?>
					</h2>
				</div>

				<div class="ziteh-slider ziteh-slider--sided" data-ziteh-slider>
					<button class="ziteh-slider__side ziteh-slider__side--prev" type="button" data-ziteh-prev aria-label="<?php esc_attr_e( 'قبلی', 'ziteh' ); ?>">
						<?php echo $this->get_icon_svg( 'arrow-r' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</button>

					<div class="ziteh-products__viewport">
						<ul class="ziteh-products__track" data-ziteh-track>
							<?php foreach ( $settings['products'] as $p ) : ?>
								<?php $url = ! empty( $p['p_url']['url'] ) ? $p['p_url']['url'] : '#'; ?>
								<li class="ziteh-product-card">
									<button class="ziteh-product-card__wish" type="button" aria-label="<?php esc_attr_e( 'افزودن به علاقه‌مندی‌ها', 'ziteh' ); ?>">
										<?php echo $this->get_icon_svg( 'heart' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
									</button>
									<a class="ziteh-product-card__thumb" href="<?php echo esc_url( $url ); ?>">
										<?php if ( ! empty( $p['p_image']['url'] ) ) : ?>
											<img src="<?php echo esc_url( $p['p_image']['url'] ); ?>" alt="<?php echo esc_attr( $p['p_name'] ); ?>">
										<?php endif; ?>
									</a>
									<div class="ziteh-product-card__body">
										<span class="ziteh-product-card__brand"><?php echo esc_html( $p['p_brand'] ); ?></span>
										<a class="ziteh-product-card__name" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $p['p_name'] ); ?></a>
										<span class="ziteh-product-card__price">
											<strong><?php echo esc_html( $p['p_price'] ); ?></strong>
											<em><?php echo esc_html( $p['p_currency'] ); ?></em>
										</span>
										<a class="ziteh-btn ziteh-btn--outline ziteh-product-card__btn" href="<?php echo esc_url( $url ); ?>">
											<?php echo $this->get_icon_svg( 'cart' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
											<?php echo esc_html( $p['p_button'] ); ?>
										</a>
									</div>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>

					<button class="ziteh-slider__side ziteh-slider__side--next" type="button" data-ziteh-next aria-label="<?php esc_attr_e( 'بعدی', 'ziteh' ); ?>">
						<?php echo $this->get_icon_svg( 'arrow-l' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</button>
				</div>

			</div>
		</section>
		<?php
	}
}
