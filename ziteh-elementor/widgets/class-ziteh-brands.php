<?php
/**
 * Brands widget — "برندهای معتبر": a centred title with a "مشاهده همه" link and
 * a single row of muted brand logos separated by thin dividers.
 *
 * @package Ziteh_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * Class Ziteh_Brands_Widget
 */
class Ziteh_Brands_Widget extends Ziteh_Widget_Base {

	/**
	 * @return string
	 */
	public function get_name() {
		return 'ziteh-brands';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'زیته | برندها', 'ziteh' );
	}

	/**
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-logo';
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
				'default' => esc_html__( 'برندهای معتبر', 'ziteh' ),
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'brand_logo',
			array(
				'label' => esc_html__( 'لوگو', 'ziteh' ),
				'type'  => Controls_Manager::MEDIA,
			)
		);

		$repeater->add_control(
			'brand_name',
			array(
				'label'   => esc_html__( 'نام برند (جایگزین لوگو)', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'برند', 'ziteh' ),
			)
		);

		$repeater->add_control(
			'brand_url',
			array(
				'label'         => esc_html__( 'لینک', 'ziteh' ),
				'type'          => Controls_Manager::URL,
				'default'       => array( 'url' => '#' ),
				'show_external' => false,
			)
		);

		$this->add_control(
			'brands',
			array(
				'label'       => esc_html__( 'برندها', 'ziteh' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ brand_name }}}',
				'default'     => array(
					array( 'brand_name' => 'LA ROCHE-POSAY' ),
					array( 'brand_name' => 'The Ordinary' ),
					array( 'brand_name' => 'CeraVe' ),
					array( 'brand_name' => 'VICHY' ),
					array( 'brand_name' => 'COSRX' ),
					array( 'brand_name' => 'BIODERMA' ),
					array( 'brand_name' => "L'OREAL" ),
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
		<section class="ziteh-brands">
			<div class="ziteh-container">

				<h2 class="ziteh-section-title ziteh-section-title--center">
					<?php echo $this->get_icon_svg( 'leaf', 'ziteh-section-title__leaf' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php echo esc_html( $settings['title'] ); ?>
				</h2>

				<ul class="ziteh-brands__row">
					<?php foreach ( $settings['brands'] as $brand ) : ?>
						<?php $url = ! empty( $brand['brand_url']['url'] ) ? $brand['brand_url']['url'] : '#'; ?>
						<li class="ziteh-brands__item">
							<a href="<?php echo esc_url( $url ); ?>">
								<?php if ( ! empty( $brand['brand_logo']['url'] ) ) : ?>
									<img src="<?php echo esc_url( $brand['brand_logo']['url'] ); ?>" alt="<?php echo esc_attr( $brand['brand_name'] ); ?>">
								<?php else : ?>
									<span><?php echo esc_html( $brand['brand_name'] ); ?></span>
								<?php endif; ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>

			</div>
		</section>
		<?php
	}
}
