<?php
/**
 * Article archive: filter chips, card grid and pagination.
 *
 * @package Ziteh_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;

/**
 * Class Ziteh_Articles_Widget.
 */
class Ziteh_Articles_Widget extends Ziteh_Widget_Base {

	public function get_name() {
		return 'ziteh-articles';
	}

	public function get_title() {
		return esc_html__( 'زیته | آرشیو مقالات', 'ziteh' );
	}

	public function get_icon() {
		return 'eicon-posts-grid';
	}

	public function get_keywords() {
		return array_merge( parent::get_keywords(), array( 'blog', 'archive', 'posts', 'مقالات', 'آرشیو', 'مجله' ) );
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_head', array( 'label' => esc_html__( 'سرتیتر', 'ziteh' ), 'tab' => Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'eyebrow', array( 'label' => esc_html__( 'پیش‌عنوان', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'مجله زیته', 'ziteh' ) ) );
		$this->add_control( 'title', array( 'label' => esc_html__( 'عنوان', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'دانستنی‌های مراقبت از پوست و مو', 'ziteh' ), 'label_block' => true ) );
		$this->add_control( 'description', array( 'label' => esc_html__( 'توضیح', 'ziteh' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 2, 'default' => esc_html__( 'راهنماهای کاربردی، بررسی ترکیبات و پاسخ به پرسش‌های پرتکرار، نوشته کارشناسان زیته.', 'ziteh' ) ) );
		$this->add_control( 'heading_tag', array( 'label' => esc_html__( 'تگ عنوان', 'ziteh' ), 'type' => Controls_Manager::SELECT, 'default' => 'h1', 'options' => array( 'h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3', 'div' => 'DIV' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_query', array( 'label' => esc_html__( 'محتوا', 'ziteh' ), 'tab' => Controls_Manager::TAB_CONTENT ) );
		$this->add_control(
			'source',
			array(
				'label'       => esc_html__( 'منبع', 'ziteh' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'auto',
				'options'     => array(
					'auto'   => esc_html__( 'خودکار — در آرشیو از کوئری اصلی', 'ziteh' ),
					'custom' => esc_html__( 'کوئری اختصاصی', 'ziteh' ),
				),
				'description' => esc_html__( 'حالت خودکار در صفحه آرشیو و دسته‌بندی از همان نوشته‌هایی استفاده می‌کند که وردپرس انتخاب کرده تا صفحه‌بندی و سئو درست بماند.', 'ziteh' ),
			)
		);
		$this->add_control( 'count', array( 'label' => esc_html__( 'تعداد در هر صفحه', 'ziteh' ), 'type' => Controls_Manager::NUMBER, 'min' => 1, 'max' => 24, 'default' => 9 ) );
		$this->add_control( 'categories', array( 'label' => esc_html__( 'محدود به دسته‌ها', 'ziteh' ), 'type' => Controls_Manager::SELECT2, 'multiple' => true, 'options' => $this->category_options(), 'label_block' => true ) );
		$this->add_control( 'orderby', array( 'label' => esc_html__( 'ترتیب بر اساس', 'ziteh' ), 'type' => Controls_Manager::SELECT, 'default' => 'date', 'options' => array( 'date' => esc_html__( 'تاریخ', 'ziteh' ), 'title' => esc_html__( 'عنوان', 'ziteh' ), 'comment_count' => esc_html__( 'تعداد دیدگاه', 'ziteh' ), 'rand' => esc_html__( 'تصادفی', 'ziteh' ) ) ) );
		$this->add_control( 'order', array( 'label' => esc_html__( 'جهت', 'ziteh' ), 'type' => Controls_Manager::SELECT, 'default' => 'DESC', 'options' => array( 'DESC' => esc_html__( 'نزولی', 'ziteh' ), 'ASC' => esc_html__( 'صعودی', 'ziteh' ) ) ) );
		$this->add_control( 'feature_first', array( 'label' => esc_html__( 'نوشته اول بزرگ', 'ziteh' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'show_filters', array( 'label' => esc_html__( 'نمایش فیلتر دسته‌ها', 'ziteh' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'show_pagination', array( 'label' => esc_html__( 'نمایش صفحه‌بندی', 'ziteh' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'empty_text', array( 'label' => esc_html__( 'متن حالت خالی', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'هنوز نوشته‌ای در این بخش منتشر نشده است.', 'ziteh' ), 'label_block' => true ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_card', array( 'label' => esc_html__( 'کارت نوشته', 'ziteh' ), 'tab' => Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'show_category', array( 'label' => esc_html__( 'نمایش دسته', 'ziteh' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'show_date', array( 'label' => esc_html__( 'نمایش تاریخ', 'ziteh' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'show_reading_time', array( 'label' => esc_html__( 'نمایش زمان مطالعه', 'ziteh' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'show_excerpt', array( 'label' => esc_html__( 'نمایش خلاصه', 'ziteh' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'excerpt_words', array( 'label' => esc_html__( 'تعداد واژه خلاصه', 'ziteh' ), 'type' => Controls_Manager::NUMBER, 'min' => 8, 'max' => 60, 'default' => 22, 'condition' => array( 'show_excerpt' => 'yes' ) ) );
		$this->add_control( 'read_more', array( 'label' => esc_html__( 'متن ادامه مطلب', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'ادامه مطلب', 'ziteh' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_layout', array( 'label' => esc_html__( 'چیدمان', 'ziteh' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_responsive_control( 'max_width', array( 'label' => esc_html__( 'حداکثر عرض', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'size_units' => array( 'px' ), 'range' => array( 'px' => array( 'min' => 760, 'max' => 1680 ) ), 'default' => array( 'size' => 1240, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-ar' => '--ziteh-ar-max: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'columns', array( 'label' => esc_html__( 'تعداد ستون', 'ziteh' ), 'type' => Controls_Manager::SELECT, 'default' => '3', 'tablet_default' => '2', 'mobile_default' => '1', 'options' => array( '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴' ), 'selectors' => array( '{{WRAPPER}} .ziteh-ar' => '--ziteh-ar-cols: {{VALUE}};' ) ) );
		$this->add_responsive_control( 'gap', array( 'label' => esc_html__( 'فاصله کارت‌ها', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 8, 'max' => 60 ) ), 'default' => array( 'size' => 22, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-ar' => '--ziteh-ar-gap: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_control( 'image_ratio', array( 'label' => esc_html__( 'نسبت تصویر', 'ziteh' ), 'type' => Controls_Manager::SELECT, 'default' => '3 / 2', 'options' => array( '3 / 2' => '۳:۲', '16 / 9' => '۱۶:۹', '4 / 3' => '۴:۳', '1 / 1' => esc_html__( 'مربع', 'ziteh' ) ), 'selectors' => array( '{{WRAPPER}} .ziteh-ar' => '--ziteh-ar-ratio: {{VALUE}};' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_colors', array( 'label' => esc_html__( 'رنگ‌ها', 'ziteh' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$colors = array(
			'background_color' => array( 'پس‌زمینه', '#faf9f6', '--ziteh-ar-bg' ),
			'surface_color'    => array( 'سطح کارت', '#ffffff', '--ziteh-ar-surface' ),
			'primary_color'    => array( 'رنگ اصلی', '#6f7d5f', '--ziteh-ar-primary' ),
			'heading_color'    => array( 'رنگ عنوان', '#333830', '--ziteh-ar-heading' ),
			'text_color'       => array( 'رنگ متن', '#787b74', '--ziteh-ar-text' ),
			'border_color'     => array( 'رنگ خطوط', '#e9e6df', '--ziteh-ar-border' ),
		);
		foreach ( $colors as $key => $data ) {
			$this->add_control( $key, array( 'label' => esc_html( $data[0] ), 'type' => Controls_Manager::COLOR, 'default' => $data[1], 'selectors' => array( '{{WRAPPER}} .ziteh-ar' => $data[2] . ': {{VALUE}};' ) ) );
		}
		$this->add_responsive_control( 'radius', array( 'label' => esc_html__( 'گردی کارت', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 36 ) ), 'default' => array( 'size' => 16, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-ar' => '--ziteh-ar-radius: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array( 'name' => 'card_shadow', 'label' => esc_html__( 'سایه کارت', 'ziteh' ), 'selector' => '{{WRAPPER}} .ziteh-ar__card' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_typography', array( 'label' => esc_html__( 'تایپوگرافی', 'ziteh' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'title_typography', 'label' => esc_html__( 'عنوان سکشن', 'ziteh' ), 'selector' => '{{WRAPPER}} .ziteh-ar__title' ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'card_typography', 'label' => esc_html__( 'عنوان کارت', 'ziteh' ), 'selector' => '{{WRAPPER}} .ziteh-ar__card-title' ) );
		$this->end_controls_section();
	}

	/**
	 * Categories for the SELECT2 control.
	 *
	 * @return array<int, string>
	 */
	private function category_options() {
		$options = array();
		$terms   = get_terms( array( 'taxonomy' => 'category', 'hide_empty' => false, 'number' => 100 ) );
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$options[ $term->term_id ] = $term->name;
			}
		}
		return $options;
	}

	/**
	 * Rough reading time in minutes.
	 *
	 * @param string $content Post content.
	 * @return int
	 */
	private function reading_minutes( $content ) {
		$words = preg_split( '/\s+/u', wp_strip_all_tags( (string) $content ), -1, PREG_SPLIT_NO_EMPTY );
		// 200 wpm is the usual English figure; Persian prose reads a little
		// slower, so 160 gives a less flattering and more honest estimate.
		return max( 1, (int) ceil( count( $words ) / 160 ) );
	}

	/**
	 * Build the loop.
	 *
	 * @param array $settings Widget settings.
	 * @return array{query: WP_Query, paged: int, uses_main: bool}
	 */
	private function build_query( $settings ) {
		$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );

		// On a real archive the main query already knows what to show, and
		// WordPress has already worked out pagination and canonical URLs for it.
		// Re-querying would fight that, so borrow it instead.
		if ( 'auto' === $settings['source'] && ( is_home() || is_archive() || is_search() ) && ! \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			global $wp_query;
			return array( 'query' => $wp_query, 'paged' => $paged, 'uses_main' => true );
		}

		$args = array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => max( 1, (int) $settings['count'] ),
			'paged'               => $paged,
			'orderby'             => in_array( $settings['orderby'], array( 'date', 'title', 'comment_count', 'rand' ), true ) ? $settings['orderby'] : 'date',
			'order'               => 'ASC' === $settings['order'] ? 'ASC' : 'DESC',
			'ignore_sticky_posts' => true,
		);

		// A chip click narrows the list; it is validated against the terms the
		// widget is configured for, so the query string cannot widen the scope.
		$allowed  = array_map( 'absint', (array) $settings['categories'] );
		$selected = isset( $_GET['ziteh_cat'] ) ? absint( $_GET['ziteh_cat'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( $selected && ( ! $allowed || in_array( $selected, $allowed, true ) ) ) {
			$args['cat'] = $selected;
		} elseif ( $allowed ) {
			$args['category__in'] = $allowed;
		}

		return array( 'query' => new WP_Query( $args ), 'paged' => $paged, 'uses_main' => false );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$tag      = in_array( $settings['heading_tag'], array( 'h1', 'h2', 'h3', 'div' ), true ) ? $settings['heading_tag'] : 'h1';
		$built    = $this->build_query( $settings );
		$query    = $built['query'];
		$selected = isset( $_GET['ziteh_cat'] ) ? absint( $_GET['ziteh_cat'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$base_url = remove_query_arg( array( 'ziteh_cat', 'paged' ) );
		?>
		<section class="ziteh-ar" dir="rtl" data-ziteh-articles>
			<div class="ziteh-ar__container">
				<header class="ziteh-ar__head">
					<?php if ( ! empty( $settings['eyebrow'] ) ) : ?>
						<span class="ziteh-ar__eyebrow"><?php Ziteh_Icons::render( 'leaf' ); ?><span><?php echo esc_html( $settings['eyebrow'] ); ?></span></span>
					<?php endif; ?>
					<<?php echo esc_attr( $tag ); ?> class="ziteh-ar__title"><?php echo esc_html( $settings['title'] ); ?></<?php echo esc_attr( $tag ); ?>>
					<?php if ( ! empty( $settings['description'] ) ) : ?>
						<p class="ziteh-ar__description"><?php echo esc_html( $settings['description'] ); ?></p>
					<?php endif; ?>
				</header>

				<?php
				if ( 'yes' === $settings['show_filters'] ) {
					$this->render_filters( $settings, $selected, $base_url );
				}
				?>

				<?php if ( $query->have_posts() ) : ?>
					<div class="ziteh-ar__grid<?php echo 'yes' === $settings['feature_first'] ? ' has-feature' : ''; ?>">
						<?php
						$index = 0;
						while ( $query->have_posts() ) :
							$query->the_post();
							$this->render_card( $settings, 'yes' === $settings['feature_first'] && 0 === $index );
							$index++;
						endwhile;
						?>
					</div>

					<?php
					if ( 'yes' === $settings['show_pagination'] ) {
						$this->render_pagination( $query, $built['paged'] );
					}
					?>
				<?php else : ?>
					<p class="ziteh-ar__empty"><?php echo esc_html( $settings['empty_text'] ); ?></p>
				<?php endif; ?>
			</div>
		</section>
		<?php
		// Only a query we created may be reset; resetting the main one would
		// break everything rendered after this widget.
		if ( ! $built['uses_main'] ) {
			wp_reset_postdata();
		}
	}

	/**
	 * Category chips.
	 *
	 * @param array  $settings Widget settings.
	 * @param int    $selected Currently selected term id.
	 * @param string $base_url URL without the filter arguments.
	 */
	private function render_filters( $settings, $selected, $base_url ) {
		$allowed = array_map( 'absint', (array) $settings['categories'] );
		$terms   = get_terms(
			array(
				'taxonomy'   => 'category',
				'hide_empty' => true,
				'include'    => $allowed ? $allowed : array(),
				'number'     => 12,
			)
		);

		if ( is_wp_error( $terms ) || ! $terms ) {
			return;
		}
		?>
		<nav class="ziteh-ar__filters" aria-label="<?php esc_attr_e( 'فیلتر دسته‌بندی مقالات', 'ziteh' ); ?>">
			<a class="ziteh-ar__chip<?php echo $selected ? '' : ' is-active'; ?>" href="<?php echo esc_url( $base_url ); ?>"<?php echo $selected ? '' : ' aria-current="true"'; ?>><?php esc_html_e( 'همه', 'ziteh' ); ?></a>
			<?php foreach ( $terms as $term ) : ?>
				<a class="ziteh-ar__chip<?php echo $selected === $term->term_id ? ' is-active' : ''; ?>"
					href="<?php echo esc_url( add_query_arg( 'ziteh_cat', $term->term_id, $base_url ) ); ?>"
					<?php echo $selected === $term->term_id ? ' aria-current="true"' : ''; ?>>
					<?php echo esc_html( $term->name ); ?>
				</a>
			<?php endforeach; ?>
		</nav>
		<?php
	}

	/**
	 * One article card. Must be called inside the loop.
	 *
	 * @param array $settings Widget settings.
	 * @param bool  $feature  Whether this is the highlighted first card.
	 */
	private function render_card( $settings, $feature ) {
		$categories = get_the_category();
		?>
		<article class="ziteh-ar__card<?php echo $feature ? ' is-feature' : ''; ?>">
			<a class="ziteh-ar__thumb" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
				<?php
				if ( has_post_thumbnail() ) {
					the_post_thumbnail( $feature ? 'large' : 'medium_large', array( 'loading' => 'lazy', 'decoding' => 'async', 'alt' => '' ) );
				} else {
					echo '<span class="ziteh-ar__thumb-fallback">' . Ziteh_Icons::get( 'leaf' ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
				}
				?>
			</a>

			<div class="ziteh-ar__body">
				<div class="ziteh-ar__meta">
					<?php if ( 'yes' === $settings['show_category'] && $categories ) : ?>
						<a class="ziteh-ar__category" href="<?php echo esc_url( get_category_link( $categories[0] ) ); ?>"><?php echo esc_html( $categories[0]->name ); ?></a>
					<?php endif; ?>
					<?php if ( 'yes' === $settings['show_date'] ) : ?>
						<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
					<?php endif; ?>
					<?php if ( 'yes' === $settings['show_reading_time'] ) : ?>
						<span class="ziteh-ar__read">
							<?php
							echo esc_html(
								sprintf(
									/* translators: %s: number of minutes */
									__( '%s دقیقه مطالعه', 'ziteh' ),
									number_format_i18n( $this->reading_minutes( get_the_content() ) )
								)
							);
							?>
						</span>
					<?php endif; ?>
				</div>

				<h3 class="ziteh-ar__card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>

				<?php if ( 'yes' === $settings['show_excerpt'] ) : ?>
					<p class="ziteh-ar__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), max( 8, (int) $settings['excerpt_words'] ), '…' ) ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $settings['read_more'] ) ) : ?>
					<a class="ziteh-ar__more" href="<?php the_permalink(); ?>">
						<span><?php echo esc_html( $settings['read_more'] ); ?></span>
						<?php Ziteh_Icons::render( 'chevron-left' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</article>
		<?php
	}

	/**
	 * Numbered pagination.
	 *
	 * @param WP_Query $query Loop query.
	 * @param int      $paged Current page.
	 */
	private function render_pagination( $query, $paged ) {
		$total = (int) $query->max_num_pages;
		if ( $total < 2 ) {
			return;
		}

		$links = paginate_links(
			array(
				'total'     => $total,
				'current'   => $paged,
				'type'      => 'array',
				'mid_size'  => 1,
				'prev_text' => Ziteh_Icons::get( 'chevron-right' ),
				'next_text' => Ziteh_Icons::get( 'chevron-left' ),
			)
		);

		if ( ! $links ) {
			return;
		}
		?>
		<nav class="ziteh-ar__pagination" aria-label="<?php esc_attr_e( 'صفحه‌بندی مقالات', 'ziteh' ); ?>">
			<?php foreach ( $links as $link ) : ?>
				<?php echo wp_kses( $link, $this->pagination_allowed_html() ); ?>
			<?php endforeach; ?>
		</nav>
		<?php
	}

	/**
	 * Allowed HTML for pagination links, including our inline SVG arrows.
	 *
	 * @return array<string, array<string, bool>>
	 */
	private function pagination_allowed_html() {
		return array(
			'a'    => array( 'href' => true, 'class' => true, 'aria-current' => true, 'aria-label' => true ),
			'span' => array( 'class' => true, 'aria-current' => true ),
			'svg'  => array( 'class' => true, 'viewbox' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true, 'aria-hidden' => true, 'focusable' => true, 'xmlns' => true ),
			'path' => array( 'd' => true ),
		);
	}
}
