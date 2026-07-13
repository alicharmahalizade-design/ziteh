<?php
/**
 * Blog widget — "مجله زیته": section title with a "مشاهده مقالات" link and a
 * three-column grid of article cards (image, category chip, title and a
 * "مطالعه مقاله" link).
 *
 * @package Ziteh_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * Class Ziteh_Blog_Widget
 */
class Ziteh_Blog_Widget extends Ziteh_Widget_Base {

	/**
	 * @return string
	 */
	public function get_name() {
		return 'ziteh-blog';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'زیته | مجله (بلاگ)', 'ziteh' );
	}

	/**
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-post-list';
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
				'default' => esc_html__( 'مجله زیته', 'ziteh' ),
			)
		);

		$this->add_control(
			'view_all_text',
			array(
				'label'   => esc_html__( 'متن مشاهده مقالات', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'مشاهده مقالات', 'ziteh' ),
			)
		);

		$this->add_control(
			'view_all_url',
			array(
				'label'         => esc_html__( 'لینک', 'ziteh' ),
				'type'          => Controls_Manager::URL,
				'default'       => array( 'url' => '#' ),
				'show_external' => false,
			)
		);

		$this->add_control(
			'read_more_text',
			array(
				'label'   => esc_html__( 'متن دکمه مقاله', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'مطالعه مقاله', 'ziteh' ),
			)
		);

		$this->end_controls_section();

		// Posts.
		$this->start_controls_section(
			'section_posts',
			array(
				'label' => esc_html__( 'مقالات', 'ziteh' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'post_image',
			array(
				'label'   => esc_html__( 'تصویر', 'ziteh' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => array(
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				),
			)
		);

		$repeater->add_control(
			'post_cat',
			array(
				'label'   => esc_html__( 'دسته', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'مراقبت پوست', 'ziteh' ),
			)
		);

		$repeater->add_control(
			'post_title',
			array(
				'label'   => esc_html__( 'عنوان مقاله', 'ziteh' ),
				'type'    => Controls_Manager::TEXTAREA,
				'rows'    => 2,
				'default' => esc_html__( 'عنوان مقاله را اینجا بنویسید', 'ziteh' ),
			)
		);

		$repeater->add_control(
			'post_url',
			array(
				'label'         => esc_html__( 'لینک مقاله', 'ziteh' ),
				'type'          => Controls_Manager::URL,
				'default'       => array( 'url' => '#' ),
				'show_external' => false,
			)
		);

		$this->add_control(
			'posts',
			array(
				'label'       => esc_html__( 'مقالات', 'ziteh' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ post_title }}}',
				'default'     => array(
					array(
						'post_cat'   => esc_html__( 'مراقبت پوست', 'ziteh' ),
						'post_title' => esc_html__( '۵ نکته برای داشتن پوستی سالم در فصل تابستان', 'ziteh' ),
					),
					array(
						'post_cat'   => esc_html__( 'مراقبت مو', 'ziteh' ),
						'post_title' => esc_html__( 'چگونه شامپو مناسب موهای خود را انتخاب کنیم؟', 'ziteh' ),
					),
					array(
						'post_cat'   => esc_html__( 'سبک زندگی', 'ziteh' ),
						'post_title' => esc_html__( 'جوانِ کالو، کلید زیبایی و سلامت', 'ziteh' ),
					),
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
		<section class="ziteh-blog">
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

				<div class="ziteh-blog__grid">
					<?php foreach ( $settings['posts'] as $post ) : ?>
						<?php $url = ! empty( $post['post_url']['url'] ) ? $post['post_url']['url'] : '#'; ?>
						<article class="ziteh-post-card">
							<a class="ziteh-post-card__thumb" href="<?php echo esc_url( $url ); ?>">
								<?php if ( ! empty( $post['post_image']['url'] ) ) : ?>
									<img src="<?php echo esc_url( $post['post_image']['url'] ); ?>" alt="<?php echo esc_attr( wp_strip_all_tags( $post['post_title'] ) ); ?>">
								<?php endif; ?>
								<span class="ziteh-post-card__chip"><?php echo esc_html( $post['post_cat'] ); ?></span>
							</a>
							<div class="ziteh-post-card__body">
								<h3 class="ziteh-post-card__title">
									<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $post['post_title'] ); ?></a>
								</h3>
								<a class="ziteh-link" href="<?php echo esc_url( $url ); ?>">
									<?php echo esc_html( $settings['read_more_text'] ); ?>
									<?php echo $this->get_icon_svg( 'arrow-l' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
								</a>
							</div>
						</article>
					<?php endforeach; ?>
				</div>

			</div>
		</section>
		<?php
	}
}
