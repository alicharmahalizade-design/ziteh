<?php
/**
 * Blog widget — "مجله زیته": section title with a "مشاهده مقالات" link and a
 * three-column grid of article cards (image, category chip, title and a
 * "مطالعه مقاله" link).
 *
 * Two sources:
 *   - wordpress: real posts pulled live via WP_Query (count, category, order).
 *   - manual: hand-authored cards (repeater).
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
				'default'       => array( 'url' => '' ),
				'show_external' => false,
				'description'   => esc_html__( 'خالی بگذارید تا به‌صورت خودکار به صفحه مطالب لینک شود.', 'ziteh' ),
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

		// Source.
		$this->start_controls_section(
			'section_source',
			array(
				'label' => esc_html__( 'منبع مقالات', 'ziteh' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'source',
			array(
				'label'   => esc_html__( 'منبع', 'ziteh' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'wordpress',
				'options' => array(
					'wordpress' => esc_html__( 'نوشته‌های وردپرس (داینامیک)', 'ziteh' ),
					'manual'    => esc_html__( 'دستی', 'ziteh' ),
				),
			)
		);

		$this->add_control(
			'wp_post_type',
			array(
				'label'     => esc_html__( 'نوع محتوا', 'ziteh' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'post',
				'options'   => $this->get_post_types(),
				'condition' => array( 'source' => 'wordpress' ),
			)
		);

		$this->add_control(
			'wp_count',
			array(
				'label'     => esc_html__( 'تعداد مقالات', 'ziteh' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 12,
				'default'   => 3,
				'condition' => array( 'source' => 'wordpress' ),
			)
		);

		$this->add_control(
			'wp_orderby',
			array(
				'label'     => esc_html__( 'مرتب‌سازی بر اساس', 'ziteh' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'date',
				'options'   => array(
					'date'          => esc_html__( 'جدیدترین', 'ziteh' ),
					'comment_count' => esc_html__( 'پربحث‌ترین', 'ziteh' ),
					'title'         => esc_html__( 'عنوان', 'ziteh' ),
					'rand'          => esc_html__( 'تصادفی', 'ziteh' ),
				),
				'condition' => array( 'source' => 'wordpress' ),
			)
		);

		$this->add_control(
			'wp_category',
			array(
				'label'       => esc_html__( 'دسته‌بندی (اسلاگ)', 'ziteh' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => $this->get_category_options(),
				'default'     => array(),
				'description' => esc_html__( 'خالی بگذارید تا از همه دسته‌ها نمایش داده شود.', 'ziteh' ),
				'condition'   => array(
					'source'       => 'wordpress',
					'wp_post_type' => 'post',
				),
			)
		);

		$this->end_controls_section();

		// Manual posts.
		$this->start_controls_section(
			'section_posts',
			array(
				'label'     => esc_html__( 'مقالات (دستی)', 'ziteh' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => array( 'source' => 'manual' ),
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

		// Layout.
		$this->start_controls_section(
			'section_layout',
			array(
				'label' => esc_html__( 'چیدمان', 'ziteh' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_responsive_control(
			'columns',
			array(
				'label'          => esc_html__( 'تعداد ستون', 'ziteh' ),
				'type'           => Controls_Manager::NUMBER,
				'min'            => 1,
				'max'            => 4,
				'default'        => 3,
				'tablet_default' => 2,
				'mobile_default' => 1,
				'selectors'      => array(
					'{{WRAPPER}} .ziteh-blog__grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Public post types available for the source selector.
	 *
	 * @return array<string,string>
	 */
	private function get_post_types() {
		$types   = get_post_types( array( 'public' => true ), 'objects' );
		$options = array();
		$exclude = array( 'attachment', 'product', 'elementor_library', 'e-landing-page' );
		foreach ( $types as $type ) {
			if ( in_array( $type->name, $exclude, true ) ) {
				continue;
			}
			$options[ $type->name ] = $type->label;
		}
		if ( empty( $options ) ) {
			$options['post'] = esc_html__( 'نوشته‌ها', 'ziteh' );
		}
		return $options;
	}

	/**
	 * Category slug => name map for the SELECT2 control.
	 *
	 * Note: intentionally NOT named get_categories() — that is a reserved,
	 * public method on Elementor\Widget_Base (returns the widget's Elementor
	 * categories); overriding it as private is a fatal error.
	 *
	 * @return array<string,string>
	 */
	private function get_category_options() {
		$options = array();
		$terms   = get_terms(
			array(
				'taxonomy'   => 'category',
				'hide_empty' => false,
				'number'     => 100,
			)
		);
		if ( is_wp_error( $terms ) ) {
			return $options;
		}
		foreach ( $terms as $term ) {
			$options[ $term->slug ] = $term->name;
		}
		return $options;
	}

	/**
	 * Render the front-end output.
	 */
	protected function render() {
		$settings     = $this->get_settings_for_display();
		$view_all_url = ! empty( $settings['view_all_url']['url'] ) ? $settings['view_all_url']['url'] : '';

		if ( '' === $view_all_url ) {
			$posts_page = get_permalink( get_option( 'page_for_posts' ) );
			$view_all_url = $posts_page ? $posts_page : home_url( '/' );
		}
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
					<?php
					if ( 'wordpress' === $settings['source'] ) {
						$this->render_wp_posts( $settings );
					} else {
						$this->render_manual_posts( $settings );
					}
					?>
				</div>

			</div>
		</section>
		<?php
	}

	/**
	 * Render manually-authored article cards.
	 *
	 * @param array $settings Widget settings.
	 */
	private function render_manual_posts( $settings ) {
		if ( empty( $settings['posts'] ) ) {
			return;
		}
		foreach ( $settings['posts'] as $post ) {
			$url = ! empty( $post['post_url']['url'] ) ? $post['post_url']['url'] : '#';
			$this->card(
				$url,
				! empty( $post['post_image']['url'] ) ? '<img src="' . esc_url( $post['post_image']['url'] ) . '" alt="' . esc_attr( wp_strip_all_tags( $post['post_title'] ) ) . '">' : '',
				$post['post_cat'],
				$post['post_title'],
				$settings['read_more_text']
			);
		}
	}

	/**
	 * Query WordPress posts and render real article cards.
	 *
	 * @param array $settings Widget settings.
	 */
	private function render_wp_posts( $settings ) {
		$args = array(
			'post_type'           => ! empty( $settings['wp_post_type'] ) ? $settings['wp_post_type'] : 'post',
			'posts_per_page'      => isset( $settings['wp_count'] ) ? (int) $settings['wp_count'] : 3,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'orderby'             => ! empty( $settings['wp_orderby'] ) ? $settings['wp_orderby'] : 'date',
			'order'               => ( 'title' === ( $settings['wp_orderby'] ?? '' ) ) ? 'ASC' : 'DESC',
			'no_found_rows'       => true,
		);

		if ( 'post' === $args['post_type'] && ! empty( $settings['wp_category'] ) ) {
			$args['category_name'] = implode( ',', (array) $settings['wp_category'] );
		}

		$query = new WP_Query( $args );

		if ( ! $query->have_posts() ) {
			echo '<p class="ziteh-blog__empty">' . esc_html__( 'مقاله‌ای برای نمایش یافت نشد.', 'ziteh' ) . '</p>';
			wp_reset_postdata();
			return;
		}

		while ( $query->have_posts() ) {
			$query->the_post();

			$thumb = '';
			if ( has_post_thumbnail() ) {
				$thumb = get_the_post_thumbnail( get_the_ID(), 'medium_large' );
			}

			$cat_name = '';
			$cats     = get_the_category();
			if ( ! empty( $cats ) && ! is_wp_error( $cats ) ) {
				$cat_name = $cats[0]->name;
			} elseif ( 'post' !== $args['post_type'] ) {
				$obj      = get_post_type_object( $args['post_type'] );
				$cat_name = $obj ? $obj->labels->singular_name : '';
			}

			$this->card(
				get_permalink(),
				$thumb,
				$cat_name,
				get_the_title(),
				$settings['read_more_text']
			);
		}
		wp_reset_postdata();
	}

	/**
	 * Output one article card (shared by both sources).
	 *
	 * @param string $url        Permalink.
	 * @param string $image_html Ready image markup (may be empty).
	 * @param string $cat        Category label (may be empty).
	 * @param string $title      Post title.
	 * @param string $read_more  Read-more label.
	 */
	private function card( $url, $image_html, $cat, $title, $read_more ) {
		?>
		<article class="ziteh-post-card">
			<a class="ziteh-post-card__thumb" href="<?php echo esc_url( $url ); ?>">
				<?php echo $image_html; // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php if ( $cat ) : ?>
					<span class="ziteh-post-card__chip"><?php echo esc_html( $cat ); ?></span>
				<?php endif; ?>
			</a>
			<div class="ziteh-post-card__body">
				<h3 class="ziteh-post-card__title">
					<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $title ); ?></a>
				</h3>
				<a class="ziteh-link" href="<?php echo esc_url( $url ); ?>">
					<?php echo esc_html( $read_more ); ?>
					<?php echo $this->get_icon_svg( 'arrow-l' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</a>
			</div>
		</article>
		<?php
	}
}
