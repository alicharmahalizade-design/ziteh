<?php
/**
 * Top bar widget — the thin utility strip above the main header:
 * cart on the right, centred search field, login / register on the left.
 *
 * @package Ziteh_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;

/**
 * Class Ziteh_Topbar_Widget
 */
class Ziteh_Topbar_Widget extends Ziteh_Widget_Base {

	/**
	 * Widget slug.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'ziteh-topbar';
	}

	/**
	 * Widget label in the panel.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'زیته | تاپ‌بار', 'ziteh' );
	}

	/**
	 * Panel icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-cart-medium';
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
			'cart_label',
			array(
				'label'   => esc_html__( 'متن سبد خرید', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'سبد خرید', 'ziteh' ),
			)
		);

		$this->add_control(
			'cart_url',
			array(
				'label'         => esc_html__( 'لینک سبد خرید', 'ziteh' ),
				'type'          => Controls_Manager::URL,
				'default'       => array( 'url' => '#' ),
				'show_external' => false,
			)
		);

		$this->add_control(
			'search_placeholder',
			array(
				'label'   => esc_html__( 'متن جستجو', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'جستجو در فروشگاه...', 'ziteh' ),
			)
		);

		$this->add_control(
			'account_label',
			array(
				'label'   => esc_html__( 'متن حساب کاربری', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'ورود / ثبت‌نام', 'ziteh' ),
			)
		);

		$this->add_control(
			'account_url',
			array(
				'label'         => esc_html__( 'لینک حساب کاربری', 'ziteh' ),
				'type'          => Controls_Manager::URL,
				'default'       => array( 'url' => '#' ),
				'show_external' => false,
			)
		);

		$this->end_controls_section();

		// Style: colors.
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
					'{{WRAPPER}} .ziteh-topbar' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'text_color',
			array(
				'label'     => esc_html__( 'رنگ متن', 'ziteh' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .ziteh-topbar' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render the front-end output.
	 */
	/**
	 * Current WooCommerce cart item count (0 when WooCommerce is inactive).
	 *
	 * @return int
	 */
	private function get_cart_count() {
		if ( class_exists( 'WooCommerce' ) && function_exists( 'WC' ) && WC()->cart ) {
			return (int) WC()->cart->get_cart_contents_count();
		}
		return 0;
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$cart_url    = ! empty( $settings['cart_url']['url'] ) ? $settings['cart_url']['url'] : '#';
		$account_url = ! empty( $settings['account_url']['url'] ) ? $settings['account_url']['url'] : '#';
		?>
		<div class="ziteh-topbar">
			<div class="ziteh-container ziteh-topbar__inner">

				<a class="ziteh-topbar__cart" href="<?php echo esc_url( $cart_url ); ?>" data-ziteh-cart-open>
					<span class="ziteh-topbar__cart-icon">
						<?php echo $this->get_icon_svg( 'cart' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<span class="ziteh-cart-count" data-ziteh-cart-count><?php echo esc_html( $this->get_cart_count() ); ?></span>
					</span>
					<span><?php echo esc_html( $settings['cart_label'] ); ?></span>
				</a>

				<button class="ziteh-topbar__search" type="button" data-ziteh-search-open aria-label="<?php echo esc_attr( $settings['search_placeholder'] ); ?>">
					<?php echo $this->get_icon_svg( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<span class="ziteh-topbar__search-text"><?php echo esc_html( $settings['search_placeholder'] ); ?></span>
				</button>

				<a class="ziteh-topbar__account" href="<?php echo esc_url( $account_url ); ?>">
					<?php echo $this->get_icon_svg( 'user' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<span><?php echo esc_html( $settings['account_label'] ); ?></span>
				</a>

			</div>
		</div>
		<?php
	}
}
