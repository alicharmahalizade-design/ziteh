<?php
/**
 * Single article: hero, contents, body, author, related.
 *
 * @package Ziteh_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

/**
 * Class Ziteh_Post_Widget.
 */
class Ziteh_Post_Widget extends Ziteh_Widget_Base {

	public function get_name() {
		return 'ziteh-post';
	}

	public function get_title() {
		return esc_html__( 'زیته | تک مقاله', 'ziteh' );
	}

	public function get_icon() {
		return 'eicon-post-content';
	}

	public function get_keywords() {
		return array_merge( parent::get_keywords(), array( 'single', 'post', 'article', 'مقاله', 'نوشته', 'تک بلاگ' ) );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_source', array( 'label' => esc_html__( 'نوشته', 'ziteh' ), 'tab' => Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'post_id', array( 'label' => esc_html__( 'شناسه نوشته برای پیش‌نمایش', 'ziteh' ), 'type' => Controls_Manager::NUMBER, 'min' => 1, 'description' => esc_html__( 'در قالب تک‌نوشته خالی بگذارید تا نوشته جاری استفاده شود.', 'ziteh' ) ) );
		$this->add_control( 'heading_tag', array( 'label' => esc_html__( 'تگ عنوان', 'ziteh' ), 'type' => Controls_Manager::SELECT, 'default' => 'h1', 'options' => array( 'h1' => 'H1', 'h2' => 'H2', 'div' => 'DIV' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_parts', array( 'label' => esc_html__( 'بخش‌ها', 'ziteh' ), 'tab' => Controls_Manager::TAB_CONTENT ) );
		foreach ( array(
			'show_breadcrumbs' => array( 'مسیر راهنما', 'yes' ),
			'show_hero'        => array( 'تصویر شاخص', 'yes' ),
			'show_meta'        => array( 'نویسنده، تاریخ و زمان مطالعه', 'yes' ),
			'show_toc'         => array( 'فهرست مطالب خودکار', 'yes' ),
			'show_share'       => array( 'دکمه‌های اشتراک‌گذاری', 'yes' ),
			'show_tags'        => array( 'برچسب‌ها', 'yes' ),
			'show_author'      => array( 'کارت نویسنده', 'yes' ),
			'show_nav'         => array( 'نوشته قبلی و بعدی', 'yes' ),
			'show_related'     => array( 'نوشته‌های مرتبط', 'yes' ),
		) as $key => $data ) {
			$this->add_control( $key, array( 'label' => esc_html( $data[0] ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => $data[1] ) );
		}
		$this->add_control( 'toc_title', array( 'label' => esc_html__( 'عنوان فهرست', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'در این مقاله می‌خوانید', 'ziteh' ), 'condition' => array( 'show_toc' => 'yes' ) ) );
		$this->add_control( 'related_title', array( 'label' => esc_html__( 'عنوان مرتبط‌ها', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'مطالب مرتبط', 'ziteh' ), 'condition' => array( 'show_related' => 'yes' ) ) );
		$this->add_control( 'related_count', array( 'label' => esc_html__( 'تعداد مرتبط‌ها', 'ziteh' ), 'type' => Controls_Manager::NUMBER, 'min' => 2, 'max' => 6, 'default' => 3, 'condition' => array( 'show_related' => 'yes' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_layout', array( 'label' => esc_html__( 'چیدمان', 'ziteh' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_responsive_control( 'max_width', array( 'label' => esc_html__( 'حداکثر عرض', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'size_units' => array( 'px' ), 'range' => array( 'px' => array( 'min' => 640, 'max' => 1400 ) ), 'default' => array( 'size' => 1120, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-po' => '--ziteh-po-max: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'content_width', array( 'label' => esc_html__( 'عرض ستون متن', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 460, 'max' => 900 ) ), 'default' => array( 'size' => 720, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-po' => '--ziteh-po-content: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'aside_width', array( 'label' => esc_html__( 'عرض ستون کناری', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 200, 'max' => 400 ) ), 'default' => array( 'size' => 300, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-po' => '--ziteh-po-aside: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_control( 'hero_ratio', array( 'label' => esc_html__( 'نسبت تصویر شاخص', 'ziteh' ), 'type' => Controls_Manager::SELECT, 'default' => '21 / 9', 'options' => array( '21 / 9' => '۲۱:۹', '16 / 9' => '۱۶:۹', '3 / 2' => '۳:۲', '2 / 1' => '۲:۱' ), 'selectors' => array( '{{WRAPPER}} .ziteh-po' => '--ziteh-po-hero: {{VALUE}};' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_colors', array( 'label' => esc_html__( 'رنگ‌ها', 'ziteh' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$colors = array(
			'background_color' => array( 'پس‌زمینه', '#faf9f6', '--ziteh-po-bg' ),
			'surface_color'    => array( 'سطح کارت', '#ffffff', '--ziteh-po-surface' ),
			'primary_color'    => array( 'رنگ اصلی', '#6f7d5f', '--ziteh-po-primary' ),
			'heading_color'    => array( 'رنگ عنوان', '#333830', '--ziteh-po-heading' ),
			'text_color'       => array( 'رنگ متن', '#5f625b', '--ziteh-po-text' ),
			'border_color'     => array( 'رنگ خطوط', '#e9e6df', '--ziteh-po-border' ),
		);
		foreach ( $colors as $key => $data ) {
			$this->add_control( $key, array( 'label' => esc_html( $data[0] ), 'type' => Controls_Manager::COLOR, 'default' => $data[1], 'selectors' => array( '{{WRAPPER}} .ziteh-po' => $data[2] . ': {{VALUE}};' ) ) );
		}
		$this->add_responsive_control( 'radius', array( 'label' => esc_html__( 'گردی گوشه‌ها', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 40 ) ), 'default' => array( 'size' => 18, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-po' => '--ziteh-po-radius: {{SIZE}}{{UNIT}};' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_typography', array( 'label' => esc_html__( 'تایپوگرافی', 'ziteh' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'title_typography', 'label' => esc_html__( 'عنوان مقاله', 'ziteh' ), 'selector' => '{{WRAPPER}} .ziteh-po__title' ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'body_typography', 'label' => esc_html__( 'متن مقاله', 'ziteh' ), 'selector' => '{{WRAPPER}} .ziteh-po__content' ) );
		$this->end_controls_section();
	}

	/**
	 * Resolve the post to render.
	 *
	 * @param array $settings Widget settings.
	 * @return WP_Post|null
	 */
	private function resolve_post( $settings ) {
		if ( ! empty( $settings['post_id'] ) ) {
			$post = get_post( absint( $settings['post_id'] ) );
			if ( $post ) {
				return $post;
			}
		}

		if ( is_singular() ) {
			return get_queried_object();
		}

		if ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			$posts = get_posts( array( 'numberposts' => 1, 'post_status' => 'publish' ) );
			return $posts ? $posts[0] : null;
		}

		return null;
	}

	/**
	 * Rough reading time in minutes.
	 *
	 * @param string $content Post content.
	 * @return int
	 */
	private function reading_minutes( $content ) {
		$words = preg_split( '/\s+/u', wp_strip_all_tags( (string) $content ), -1, PREG_SPLIT_NO_EMPTY );
		return max( 1, (int) ceil( count( $words ) / 160 ) );
	}

	/**
	 * Give every h2/h3 in the rendered content a stable id and collect them.
	 *
	 * Done on the rendered HTML rather than the raw content so shortcodes and
	 * blocks that generate headings are included too. Existing ids are kept, so
	 * links people have already shared keep working.
	 *
	 * @param string $html Rendered post content.
	 * @return array{html: string, items: array<int, array{id: string, text: string, level: int}>}
	 */
	private function collect_headings( $html ) {
		$items = array();
		$used  = array();

		$html = preg_replace_callback(
			'/<h([23])([^>]*)>(.*?)<\/h\1>/is',
			static function ( $match ) use ( &$items, &$used ) {
				$level = (int) $match[1];
				$attrs = $match[2];
				$inner = $match[3];
				$text  = trim( wp_strip_all_tags( $inner ) );

				if ( '' === $text ) {
					return $match[0];
				}

				if ( preg_match( '/\sid=["\']([^"\']+)["\']/i', $attrs, $found ) ) {
					$id = $found[1];
				} else {
					$id = sanitize_title( $text );
					if ( '' === $id ) {
						$id = 'bakhsh';
					}
					$base = $id;
					$n    = 2;
					while ( isset( $used[ $id ] ) ) {
						$id = $base . '-' . $n;
						$n++;
					}
					$attrs .= ' id="' . esc_attr( $id ) . '"';
				}

				$used[ $id ] = true;
				$items[]     = array( 'id' => $id, 'text' => $text, 'level' => $level );

				return '<h' . $level . $attrs . '>' . $inner . '</h' . $level . '>';
			},
			$html
		);

		return array( 'html' => $html, 'items' => $items );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$post     = $this->resolve_post( $settings );

		if ( ! $post ) {
			echo '<p class="ziteh-po__notice">' . esc_html__( 'برای نمایش، این ویجت را در قالب تک‌نوشته بگذارید یا یک شناسه نوشته وارد کنید.', 'ziteh' ) . '</p>';
			return;
		}

		$tag        = in_array( $settings['heading_tag'], array( 'h1', 'h2', 'div' ), true ) ? $settings['heading_tag'] : 'h1';
		$categories = get_the_category( $post->ID );
		$rendered   = apply_filters( 'the_content', $post->post_content );
		$collected  = 'yes' === $settings['show_toc'] ? $this->collect_headings( $rendered ) : array( 'html' => $rendered, 'items' => array() );
		$has_aside  = ! empty( $collected['items'] ) || 'yes' === $settings['show_share'];
		?>
		<article class="ziteh-po" dir="rtl" data-ziteh-post>
			<div class="ziteh-po__container">
				<header class="ziteh-po__head">
					<?php if ( 'yes' === $settings['show_breadcrumbs'] ) : ?>
						<nav class="ziteh-po__breadcrumbs" aria-label="<?php esc_attr_e( 'مسیر راهنما', 'ziteh' ); ?>">
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'خانه', 'ziteh' ); ?></a><span>/</span>
							<?php if ( $categories ) : ?>
								<a href="<?php echo esc_url( get_category_link( $categories[0] ) ); ?>"><?php echo esc_html( $categories[0]->name ); ?></a><span>/</span>
							<?php endif; ?>
							<span aria-current="page"><?php echo esc_html( wp_trim_words( get_the_title( $post ), 6 ) ); ?></span>
						</nav>
					<?php endif; ?>

					<?php if ( $categories ) : ?>
						<a class="ziteh-po__category" href="<?php echo esc_url( get_category_link( $categories[0] ) ); ?>">
							<?php Ziteh_Icons::render( 'leaf' ); ?><span><?php echo esc_html( $categories[0]->name ); ?></span>
						</a>
					<?php endif; ?>

					<<?php echo esc_attr( $tag ); ?> class="ziteh-po__title"><?php echo esc_html( get_the_title( $post ) ); ?></<?php echo esc_attr( $tag ); ?>>

					<?php if ( 'yes' === $settings['show_meta'] ) : ?>
						<div class="ziteh-po__meta">
							<span class="ziteh-po__author">
								<?php echo get_avatar( $post->post_author, 40, '', '', array( 'class' => 'ziteh-po__avatar', 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
								<span><?php echo esc_html( get_the_author_meta( 'display_name', $post->post_author ) ); ?></span>
							</span>
							<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $post ) ); ?>"><?php echo esc_html( get_the_date( '', $post ) ); ?></time>
							<span class="ziteh-po__read">
								<?php
								echo esc_html(
									sprintf(
										/* translators: %s: number of minutes */
										__( '%s دقیقه مطالعه', 'ziteh' ),
										number_format_i18n( $this->reading_minutes( $post->post_content ) )
									)
								);
								?>
							</span>
						</div>
					<?php endif; ?>
				</header>

				<?php if ( 'yes' === $settings['show_hero'] && has_post_thumbnail( $post ) ) : ?>
					<figure class="ziteh-po__hero">
						<?php echo get_the_post_thumbnail( $post, 'full', array( 'loading' => 'eager', 'fetchpriority' => 'high', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<?php if ( get_the_post_thumbnail_caption( $post ) ) : ?>
							<figcaption><?php echo esc_html( get_the_post_thumbnail_caption( $post ) ); ?></figcaption>
						<?php endif; ?>
					</figure>
				<?php endif; ?>

				<div class="ziteh-po__layout<?php echo $has_aside ? '' : ' is-single'; ?>">
					<div class="ziteh-po__content">
						<?php echo wp_kses_post( $collected['html'] ); ?>

						<?php if ( 'yes' === $settings['show_tags'] ) : ?>
							<?php $tags = get_the_tags( $post->ID ); ?>
							<?php if ( $tags && ! is_wp_error( $tags ) ) : ?>
								<div class="ziteh-po__tags">
									<?php foreach ( $tags as $term ) : ?>
										<a href="<?php echo esc_url( get_tag_link( $term ) ); ?>">#<?php echo esc_html( $term->name ); ?></a>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						<?php endif; ?>
					</div>

					<?php if ( $has_aside ) : ?>
						<aside class="ziteh-po__aside">
							<?php if ( ! empty( $collected['items'] ) ) : ?>
								<nav class="ziteh-po__toc" aria-label="<?php echo esc_attr( $settings['toc_title'] ); ?>">
									<h2 class="ziteh-po__toc-title"><?php echo esc_html( $settings['toc_title'] ); ?></h2>
									<ol>
										<?php foreach ( $collected['items'] as $item ) : ?>
											<li class="is-level-<?php echo esc_attr( $item['level'] ); ?>">
												<a href="#<?php echo esc_attr( $item['id'] ); ?>"><?php echo esc_html( $item['text'] ); ?></a>
											</li>
										<?php endforeach; ?>
									</ol>
								</nav>
							<?php endif; ?>

							<?php if ( 'yes' === $settings['show_share'] ) : ?>
								<?php $this->render_share( $post ); ?>
							<?php endif; ?>
						</aside>
					<?php endif; ?>
				</div>

				<?php if ( 'yes' === $settings['show_author'] ) : ?>
					<?php $this->render_author( $post ); ?>
				<?php endif; ?>

				<?php if ( 'yes' === $settings['show_nav'] ) : ?>
					<?php $this->render_nav( $post ); ?>
				<?php endif; ?>

				<?php if ( 'yes' === $settings['show_related'] ) : ?>
					<?php $this->render_related( $post, (int) $settings['related_count'], $settings['related_title'] ); ?>
				<?php endif; ?>
			</div>
		</article>
		<?php
	}

	/**
	 * Share links.
	 *
	 * Plain links with no third-party script: nothing here phones home or drops
	 * a cookie before the reader chooses to share.
	 *
	 * @param WP_Post $post Post.
	 */
	private function render_share( $post ) {
		$url   = rawurlencode( get_permalink( $post ) );
		$title = rawurlencode( get_the_title( $post ) );
		$links = array(
			'telegram' => array( 'https://t.me/share/url?url=' . $url . '&text=' . $title, __( 'تلگرام', 'ziteh' ), 'chat' ),
			'whatsapp' => array( 'https://api.whatsapp.com/send?text=' . $title . '%20' . $url, __( 'واتساپ', 'ziteh' ), 'chat' ),
			'x'        => array( 'https://twitter.com/intent/tweet?url=' . $url . '&text=' . $title, __( 'ایکس', 'ziteh' ), 'chat' ),
			'email'    => array( 'mailto:?subject=' . $title . '&body=' . $url, __( 'ایمیل', 'ziteh' ), 'chat' ),
		);
		?>
		<div class="ziteh-po__share">
			<h2 class="ziteh-po__share-title"><?php esc_html_e( 'اشتراک‌گذاری', 'ziteh' ); ?></h2>
			<div class="ziteh-po__share-links">
				<?php foreach ( $links as $key => $data ) : ?>
					<a class="ziteh-po__share-link is-<?php echo esc_attr( $key ); ?>"
						href="<?php echo esc_url( $data[0] ); ?>"
						target="_blank"
						rel="noopener nofollow"
						aria-label="<?php echo esc_attr( sprintf( /* translators: %s: network name */ __( 'اشتراک در %s', 'ziteh' ), $data[1] ) ); ?>">
						<?php Ziteh_Icons::render( $data[2] ); ?>
						<span><?php echo esc_html( $data[1] ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Author card.
	 *
	 * @param WP_Post $post Post.
	 */
	private function render_author( $post ) {
		$bio = get_the_author_meta( 'description', $post->post_author );
		?>
		<section class="ziteh-po__author-card">
			<?php echo get_avatar( $post->post_author, 96, '', '', array( 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div>
				<span class="ziteh-po__author-label"><?php esc_html_e( 'نویسنده', 'ziteh' ); ?></span>
				<strong><?php echo esc_html( get_the_author_meta( 'display_name', $post->post_author ) ); ?></strong>
				<?php if ( $bio ) : ?><p><?php echo esc_html( $bio ); ?></p><?php endif; ?>
			</div>
		</section>
		<?php
	}

	/**
	 * Previous / next links.
	 *
	 * @param WP_Post $post Post.
	 */
	private function render_nav( $post ) {
		$previous = get_previous_post();
		$next     = get_next_post();

		// Outside a single-post loop those helpers have nothing to work from.
		if ( ! is_singular() ) {
			return;
		}

		if ( ! $previous && ! $next ) {
			return;
		}
		?>
		<nav class="ziteh-po__nav" aria-label="<?php esc_attr_e( 'نوشته‌های قبلی و بعدی', 'ziteh' ); ?>">
			<?php if ( $previous ) : ?>
				<a class="ziteh-po__nav-link is-prev" href="<?php echo esc_url( get_permalink( $previous ) ); ?>">
					<?php Ziteh_Icons::render( 'chevron-right' ); ?>
					<span><small><?php esc_html_e( 'نوشته قبلی', 'ziteh' ); ?></small><strong><?php echo esc_html( get_the_title( $previous ) ); ?></strong></span>
				</a>
			<?php endif; ?>
			<?php if ( $next ) : ?>
				<a class="ziteh-po__nav-link is-next" href="<?php echo esc_url( get_permalink( $next ) ); ?>">
					<span><small><?php esc_html_e( 'نوشته بعدی', 'ziteh' ); ?></small><strong><?php echo esc_html( get_the_title( $next ) ); ?></strong></span>
					<?php Ziteh_Icons::render( 'chevron-left' ); ?>
				</a>
			<?php endif; ?>
		</nav>
		<?php
	}

	/**
	 * Related posts from the same categories.
	 *
	 * @param WP_Post $post  Post.
	 * @param int     $count How many.
	 * @param string  $title Section heading.
	 */
	private function render_related( $post, $count, $title ) {
		$terms = wp_get_post_categories( $post->ID );
		$query = new WP_Query(
			array(
				'post_type'           => 'post',
				'post_status'         => 'publish',
				'posts_per_page'      => max( 2, $count ),
				'post__not_in'        => array( $post->ID ),
				'category__in'        => $terms ? $terms : array(),
				'ignore_sticky_posts' => true,
				'orderby'             => 'date',
			)
		);

		if ( ! $query->have_posts() ) {
			wp_reset_postdata();
			return;
		}
		?>
		<section class="ziteh-po__related">
			<h2 class="ziteh-po__related-title"><?php Ziteh_Icons::render( 'leaf' ); ?><span><?php echo esc_html( $title ); ?></span></h2>
			<div class="ziteh-po__related-grid">
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();
					?>
					<a class="ziteh-po__related-card" href="<?php the_permalink(); ?>">
						<span class="ziteh-po__related-thumb">
							<?php
							if ( has_post_thumbnail() ) {
								the_post_thumbnail( 'medium', array( 'loading' => 'lazy', 'decoding' => 'async', 'alt' => '' ) );
							} else {
								echo '<span class="ziteh-po__related-fallback">' . Ziteh_Icons::get( 'leaf' ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
							}
							?>
						</span>
						<strong><?php the_title(); ?></strong>
						<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
					</a>
					<?php
				endwhile;
				?>
			</div>
		</section>
		<?php
		wp_reset_postdata();
	}
}
