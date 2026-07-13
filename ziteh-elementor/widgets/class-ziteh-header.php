<?php
/**
 * Header widget — the centred logo with the primary navigation menu on the
 * right and left (RTL), matching the design's main header row.
 *
 * @package Ziteh_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * Class Ziteh_Header_Widget
 */
class Ziteh_Header_Widget extends Ziteh_Widget_Base {

	/**
	 * @return string
	 */
	public function get_name() {
		return 'ziteh-header';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'زیته | هدر و منو', 'ziteh' );
	}

	/**
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-nav-menu';
	}

	/**
	 * Register the editable controls.
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'section_logo',
			array(
				'label' => esc_html__( 'لوگو', 'ziteh' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'logo_type',
			array(
				'label'   => esc_html__( 'نوع لوگو', 'ziteh' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => array(
					'text'  => array(
						'title' => esc_html__( 'متن', 'ziteh' ),
						'icon'  => 'eicon-t-letter',
					),
					'image' => array(
						'title' => esc_html__( 'تصویر', 'ziteh' ),
						'icon'  => 'eicon-image',
					),
				),
				'default' => 'text',
			)
		);

		$this->add_control(
			'logo_text',
			array(
				'label'     => esc_html__( 'متن لوگو', 'ziteh' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'زیته', 'ziteh' ),
				'condition' => array( 'logo_type' => 'text' ),
			)
		);

		$this->add_control(
			'logo_image',
			array(
				'label'     => esc_html__( 'تصویر لوگو', 'ziteh' ),
				'type'      => Controls_Manager::MEDIA,
				'condition' => array( 'logo_type' => 'image' ),
			)
		);

		$this->add_control(
			'logo_url',
			array(
				'label'         => esc_html__( 'لینک لوگو', 'ziteh' ),
				'type'          => Controls_Manager::URL,
				'default'       => array( 'url' => '#' ),
				'show_external' => false,
			)
		);

		$this->end_controls_section();

		// Menu section.
		$this->start_controls_section(
			'section_menu',
			array(
				'label' => esc_html__( 'منو', 'ziteh' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'item_text',
			array(
				'label'   => esc_html__( 'عنوان', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'آیتم منو', 'ziteh' ),
			)
		);

		$repeater->add_control(
			'item_url',
			array(
				'label'         => esc_html__( 'لینک', 'ziteh' ),
				'type'          => Controls_Manager::URL,
				'default'       => array( 'url' => '#' ),
				'show_external' => false,
			)
		);

		$repeater->add_control(
			'item_active',
			array(
				'label'        => esc_html__( 'صفحه فعال', 'ziteh' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			)
		);

		$this->add_control(
			'menu_items',
			array(
				'label'       => esc_html__( 'آیتم‌های منو', 'ziteh' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ item_text }}}',
				'default'     => array(
					array(
						'item_text'   => esc_html__( 'خانه', 'ziteh' ),
						'item_active' => 'yes',
					),
					array( 'item_text' => esc_html__( 'دسته‌بندی‌ها', 'ziteh' ) ),
					array( 'item_text' => esc_html__( 'فروشگاه', 'ziteh' ) ),
					array( 'item_text' => esc_html__( 'بلاگ', 'ziteh' ) ),
					array( 'item_text' => esc_html__( 'درباره ما', 'ziteh' ) ),
					array( 'item_text' => esc_html__( 'تماس با ما', 'ziteh' ) ),
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
					'{{WRAPPER}} .ziteh-header' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'menu_color',
			array(
				'label'     => esc_html__( 'رنگ منو', 'ziteh' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .ziteh-header__menu a' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'active_color',
			array(
				'label'     => esc_html__( 'رنگ آیتم فعال', 'ziteh' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .ziteh-header__menu a.is-active' => 'color: {{VALUE}};',
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
		$logo_url = ! empty( $settings['logo_url']['url'] ) ? $settings['logo_url']['url'] : '#';
		?>
		<header class="ziteh-header">
			<div class="ziteh-container ziteh-header__inner">

				<nav class="ziteh-header__menu" aria-label="<?php esc_attr_e( 'منوی اصلی', 'ziteh' ); ?>">
					<ul>
						<?php foreach ( $settings['menu_items'] as $item ) : ?>
							<?php
							$url    = ! empty( $item['item_url']['url'] ) ? $item['item_url']['url'] : '#';
							$active = ( 'yes' === $item['item_active'] ) ? ' is-active' : '';
							?>
							<li>
								<a class="<?php echo esc_attr( trim( $active ) ); ?>" href="<?php echo esc_url( $url ); ?>">
									<?php echo esc_html( $item['item_text'] ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</nav>

				<a class="ziteh-header__logo" href="<?php echo esc_url( $logo_url ); ?>">
					<?php if ( 'image' === $settings['logo_type'] && ! empty( $settings['logo_image']['url'] ) ) : ?>
						<img src="<?php echo esc_url( $settings['logo_image']['url'] ); ?>" alt="<?php echo esc_attr( $settings['logo_text'] ); ?>">
					<?php else : ?>
						<span class="ziteh-header__logo-text"><?php echo esc_html( $settings['logo_text'] ); ?></span>
					<?php endif; ?>
				</a>

				<button class="ziteh-header__burger" type="button" aria-label="<?php esc_attr_e( 'منو', 'ziteh' ); ?>" data-ziteh-burger>
					<span></span><span></span><span></span>
				</button>

			</div>
		</header>
		<?php
	}
}
