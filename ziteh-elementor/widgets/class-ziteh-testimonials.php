<?php
/**
 * Testimonials widget — "نظر شما، برای ما ارزشمند است": a centred title and a
 * three-column row of quote cards, each with a star rating, quote text and a
 * round avatar with the customer's name beneath.
 *
 * @package Ziteh_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * Class Ziteh_Testimonials_Widget
 */
class Ziteh_Testimonials_Widget extends Ziteh_Widget_Base {

	/**
	 * @return string
	 */
	public function get_name() {
		return 'ziteh-testimonials';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'زیته | نظرات مشتریان', 'ziteh' );
	}

	/**
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-testimonial';
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
				'default' => esc_html__( 'نظر شما، برای ما ارزشمند است', 'ziteh' ),
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'quote',
			array(
				'label'   => esc_html__( 'متن نظر', 'ziteh' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 3,
				'default' => esc_html__( 'محصولات اورجینال و ارسال سریع. تجربه خرید بسیار خوبی داشتم.', 'ziteh' ),
			)
		);

		$repeater->add_control(
			'name',
			array(
				'label'   => esc_html__( 'نام مشتری', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'نام مشتری', 'ziteh' ),
			)
		);

		$repeater->add_control(
			'rating',
			array(
				'label'   => esc_html__( 'امتیاز (۱ تا ۵)', 'ziteh' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 5,
				'default' => 5,
			)
		);

		$repeater->add_control(
			'avatar',
			array(
				'label' => esc_html__( 'آواتار', 'ziteh' ),
				'type'  => Controls_Manager::MEDIA,
			)
		);

		$this->add_control(
			'testimonials',
			array(
				'label'       => esc_html__( 'نظرات', 'ziteh' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ name }}}',
				'default'     => array(
					array(
						'name'   => esc_html__( 'نهال محمدی', 'ziteh' ),
						'quote'  => esc_html__( 'محصولات اورجینال و ارسال سریع. تجربه خرید برام عالی کرد.', 'ziteh' ),
						'rating' => 5,
					),
					array(
						'name'   => esc_html__( 'سارا احمدی', 'ziteh' ),
						'quote'  => esc_html__( 'تنوع محصولات و توضیحات کامل باعث شد انتخاب راحتی داشته باشم.', 'ziteh' ),
						'rating' => 5,
					),
					array(
						'name'   => esc_html__( 'مریم کریمی', 'ziteh' ),
						'quote'  => esc_html__( 'پشتیبانی عالی و بسته‌بندی شیک. حس خاصی به من داد. ممنون از زیته!', 'ziteh' ),
						'rating' => 5,
					),
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
					'{{WRAPPER}} .ziteh-testimonials' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Output a star row for a rating value.
	 *
	 * @param int $rating Number of filled stars (1-5).
	 */
	private function render_stars( $rating ) {
		$rating = max( 1, min( 5, (int) $rating ) );
		echo '<span class="ziteh-stars" aria-label="' . esc_attr( sprintf( /* translators: %d rating */ __( 'امتیاز %d از ۵', 'ziteh' ), $rating ) ) . '">';
		for ( $i = 1; $i <= 5; $i++ ) {
			$cls = $i <= $rating ? ' is-filled' : '';
			echo '<span class="ziteh-stars__star' . esc_attr( $cls ) . '">' . $this->get_icon_svg( 'star' ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		echo '</span>';
	}

	/**
	 * Render the front-end output.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		?>
		<section class="ziteh-testimonials">
			<div class="ziteh-container">

				<h2 class="ziteh-section-title ziteh-section-title--center">
					<?php echo $this->get_icon_svg( 'leaf', 'ziteh-section-title__leaf' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php echo esc_html( $settings['title'] ); ?>
				</h2>

				<div class="ziteh-testimonials__grid">
					<?php foreach ( $settings['testimonials'] as $t ) : ?>
						<figure class="ziteh-testimonial-card">
							<?php $this->render_stars( $t['rating'] ); ?>
							<blockquote class="ziteh-testimonial-card__quote"><?php echo esc_html( $t['quote'] ); ?></blockquote>
							<figcaption class="ziteh-testimonial-card__author">
								<span class="ziteh-testimonial-card__avatar">
									<?php if ( ! empty( $t['avatar']['url'] ) ) : ?>
										<img src="<?php echo esc_url( $t['avatar']['url'] ); ?>" alt="<?php echo esc_attr( $t['name'] ); ?>">
									<?php else : ?>
										<span class="ziteh-testimonial-card__initial"><?php echo esc_html( mb_substr( $t['name'], 0, 1 ) ); ?></span>
									<?php endif; ?>
								</span>
								<span class="ziteh-testimonial-card__name"><?php echo esc_html( $t['name'] ); ?></span>
							</figcaption>
						</figure>
					<?php endforeach; ?>
				</div>

			</div>
		</section>
		<?php
	}
}
