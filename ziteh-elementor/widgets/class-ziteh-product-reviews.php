<?php
/**
 * WooCommerce product reviews summary and review cards.
 *
 * @package Ziteh_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;

/** Product reviews widget. */
class Ziteh_Product_Reviews_Widget extends Ziteh_Widget_Base {

	public function get_name() { return 'ziteh-product-reviews'; }
	public function get_title() { return esc_html__( '۴. نظرات کاربران محصول', 'ziteh' ); }
	public function get_icon() { return 'eicon-review'; }
	public function get_keywords() { return array_merge( parent::get_keywords(), array( 'review', 'rating', 'woocommerce', 'نظر', 'امتیاز', 'دیدگاه محصول' ) ); }

	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => esc_html__( 'محتوا', 'ziteh' ), 'tab' => Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'title', array( 'label' => esc_html__( 'عنوان', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'نظرات کاربران', 'ziteh' ) ) );
		$this->add_control( 'show_leaf', array( 'label' => esc_html__( 'نمایش آیکن برگ', 'ziteh' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'product_id', array( 'label' => esc_html__( 'شناسه محصول برای پیش‌نمایش', 'ziteh' ), 'type' => Controls_Manager::NUMBER, 'min' => 1, 'description' => esc_html__( 'در قالب تکی محصول خالی بگذارید.', 'ziteh' ) ) );
		$this->add_control( 'reviews_limit', array( 'label' => esc_html__( 'تعداد دیدگاه‌ها', 'ziteh' ), 'type' => Controls_Manager::NUMBER, 'min' => 1, 'max' => 20, 'default' => 3 ) );
		$this->add_control( 'review_order', array( 'label' => esc_html__( 'ترتیب دیدگاه‌ها', 'ziteh' ), 'type' => Controls_Manager::SELECT, 'default' => 'DESC', 'options' => array( 'DESC' => esc_html__( 'جدیدترین', 'ziteh' ), 'ASC' => esc_html__( 'قدیمی‌ترین', 'ziteh' ) ) ) );
		$this->add_control( 'excerpt_length', array( 'label' => esc_html__( 'حداکثر تعداد واژه متن', 'ziteh' ), 'type' => Controls_Manager::NUMBER, 'min' => 8, 'max' => 100, 'default' => 28 ) );
		$this->add_control( 'empty_text', array( 'label' => esc_html__( 'پیام نبود دیدگاه', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'هنوز دیدگاهی برای این محصول ثبت نشده است.', 'ziteh' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_summary', array( 'label' => esc_html__( 'خلاصه امتیاز', 'ziteh' ), 'tab' => Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'show_summary', array( 'label' => esc_html__( 'نمایش خلاصه', 'ziteh' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'show_distribution', array( 'label' => esc_html__( 'نمایش توزیع ستاره‌ها', 'ziteh' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes', 'condition' => array( 'show_summary' => 'yes' ) ) );
		$this->add_control( 'button_text', array( 'label' => esc_html__( 'متن دکمه', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'مشاهده همه نظرات', 'ziteh' ), 'condition' => array( 'show_summary' => 'yes' ) ) );
		$this->add_control( 'button_url', array( 'label' => esc_html__( 'لینک سفارشی دکمه', 'ziteh' ), 'type' => Controls_Manager::URL, 'placeholder' => '#reviews', 'condition' => array( 'show_summary' => 'yes' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_display', array( 'label' => esc_html__( 'اجزای دیدگاه', 'ziteh' ), 'tab' => Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'show_avatar', array( 'label' => esc_html__( 'نمایش آواتار', 'ziteh' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'show_date', array( 'label' => esc_html__( 'نمایش تاریخ', 'ziteh' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'show_rating', array( 'label' => esc_html__( 'نمایش ستاره‌ها', 'ziteh' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'show_verified', array( 'label' => esc_html__( 'نمایش نشان خریدار', 'ziteh' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_seo', array( 'label' => esc_html__( 'SEO و دسترس‌پذیری', 'ziteh' ), 'tab' => Controls_Manager::TAB_CONTENT ) );
		$this->add_control( 'heading_tag', array( 'label' => esc_html__( 'تگ عنوان', 'ziteh' ), 'type' => Controls_Manager::SELECT, 'default' => 'h2', 'options' => array( 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4', 'div' => 'DIV' ) ) );
		$this->add_control( 'section_label', array( 'label' => esc_html__( 'برچسب دسترس‌پذیری سکشن', 'ziteh' ), 'type' => Controls_Manager::TEXT, 'default' => esc_html__( 'نظرات کاربران محصول', 'ziteh' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_layout', array( 'label' => esc_html__( 'چیدمان', 'ziteh' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_responsive_control( 'max_width', array( 'label' => esc_html__( 'حداکثر عرض', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'size_units' => array( 'px' ), 'range' => array( 'px' => array( 'min' => 600, 'max' => 1500 ) ), 'default' => array( 'size' => 790, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-pr' => '--ziteh-pr-max:{{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'summary_width', array( 'label' => esc_html__( 'عرض خلاصه امتیاز', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 210, 'max' => 380 ) ), 'default' => array( 'size' => 260, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-pr' => '--ziteh-pr-summary:{{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'column_gap', array( 'label' => esc_html__( 'فاصله ستون‌ها', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 4, 'max' => 60 ) ), 'default' => array( 'size' => 16, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-pr' => '--ziteh-pr-gap:{{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'card_gap', array( 'label' => esc_html__( 'فاصله کارت‌ها', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 4, 'max' => 30 ) ), 'default' => array( 'size' => 6, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-pr' => '--ziteh-pr-card-gap:{{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'avatar_size', array( 'label' => esc_html__( 'اندازه آواتار', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 32, 'max' => 90 ) ), 'default' => array( 'size' => 48, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-pr' => '--ziteh-pr-avatar:{{SIZE}}{{UNIT}};' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_colors', array( 'label' => esc_html__( 'رنگ‌ها و سطح‌ها', 'ziteh' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$colors = array(
			'background_color' => array( 'پس‌زمینه سکشن', '#fbfaf7', '--ziteh-pr-bg' ), 'surface_color' => array( 'پس‌زمینه کارت', '#ffffff', '--ziteh-pr-surface' ),
			'primary_color' => array( 'رنگ اصلی', '#7d8a70', '--ziteh-pr-primary' ), 'heading_color' => array( 'رنگ عنوان', '#465044', '--ziteh-pr-heading' ),
			'text_color' => array( 'رنگ متن', '#747872', '--ziteh-pr-text' ), 'muted_color' => array( 'رنگ متن کم‌رنگ', '#a09f99', '--ziteh-pr-muted' ),
			'border_color' => array( 'رنگ خط', '#e9e6df', '--ziteh-pr-border' ), 'star_color' => array( 'رنگ ستاره', '#f5ad00', '--ziteh-pr-star' ), 'track_color' => array( 'رنگ مسیر نمودار', '#efefed', '--ziteh-pr-track' ),
		);
		foreach ( $colors as $key => $data ) {
			$this->add_control( $key, array( 'label' => esc_html( $data[0] ), 'type' => Controls_Manager::COLOR, 'default' => $data[1], 'selectors' => array( '{{WRAPPER}} .ziteh-pr' => $data[2] . ':{{VALUE}};' ) ) );
		}
		$this->add_responsive_control( 'radius', array( 'label' => esc_html__( 'گردی کارت', 'ziteh' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 40 ) ), 'default' => array( 'size' => 14, 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .ziteh-pr' => '--ziteh-pr-radius:{{SIZE}}{{UNIT}};' ) ) );
		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array( 'name' => 'card_shadow', 'label' => esc_html__( 'سایه کارت‌ها', 'ziteh' ), 'selector' => '{{WRAPPER}} .ziteh-pr__card, {{WRAPPER}} .ziteh-pr__summary' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_typography', array( 'label' => esc_html__( 'تایپوگرافی', 'ziteh' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'heading_typography', 'label' => esc_html__( 'عنوان سکشن', 'ziteh' ), 'selector' => '{{WRAPPER}} .ziteh-pr__heading' ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'name_typography', 'label' => esc_html__( 'نام کاربر', 'ziteh' ), 'selector' => '{{WRAPPER}} .ziteh-pr__name' ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'body_typography', 'label' => esc_html__( 'متن دیدگاه', 'ziteh' ), 'selector' => '{{WRAPPER}} .ziteh-pr__comment' ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'average_typography', 'label' => esc_html__( 'امتیاز میانگین', 'ziteh' ), 'selector' => '{{WRAPPER}} .ziteh-pr__average' ) );
		$this->end_controls_section();
	}

	private function current_product( $settings ) {
		$id = ! empty( $settings['product_id'] ) ? absint( $settings['product_id'] ) : 0;
		if ( ! $id && function_exists( 'is_product' ) && is_product() ) { $id = get_queried_object_id(); }
		if ( ! $id && 'product' === get_post_type( get_the_ID() ) ) { $id = get_the_ID(); }
		if ( ! $id && class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->editor->is_edit_mode() && function_exists( 'wc_get_products' ) ) {
			$ids = wc_get_products( array( 'limit' => 1, 'status' => 'publish', 'return' => 'ids', 'orderby' => 'rating' ) );
			$id = $ids ? (int) reset( $ids ) : 0;
		}
		return $id && function_exists( 'wc_get_product' ) ? wc_get_product( $id ) : false;
	}

	private function stars( $rating, $label = '' ) {
		$rating = max( 0, min( 5, (float) $rating ) );
		?><span class="ziteh-pr__stars" role="img" aria-label="<?php echo esc_attr( $label ? $label : sprintf( __( '%s از ۵ ستاره', 'ziteh' ), wc_format_decimal( $rating, 1 ) ) ); ?>"><?php
		for ( $i = 1; $i <= 5; $i++ ) : ?><i class="<?php echo esc_attr( $i <= round( $rating ) ? 'fas' : 'far' ); ?> fa-star" aria-hidden="true"></i><?php endfor;
		?></span><?php
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$product = $this->current_product( $s );
		$tag = in_array( $s['heading_tag'], array( 'h2', 'h3', 'h4', 'div' ), true ) ? $s['heading_tag'] : 'h2';
		if ( ! $product ) { echo '<div class="ziteh-pr__notice">' . esc_html__( 'برای نمایش نظرات، یک محصول معتبر انتخاب کنید.', 'ziteh' ) . '</div>'; return; }
		$limit = max( 1, min( 20, (int) $s['reviews_limit'] ) );
		$reviews = get_comments( array( 'post_id' => $product->get_id(), 'status' => 'approve', 'type' => 'review', 'number' => $limit, 'order' => 'ASC' === $s['review_order'] ? 'ASC' : 'DESC', 'orderby' => 'comment_date_gmt' ) );
		$average = (float) $product->get_average_rating();
		$count = (int) $product->get_review_count();
		$counts = (array) $product->get_rating_counts();
		$button_url = ! empty( $s['button_url']['url'] ) ? $s['button_url']['url'] : $product->get_permalink() . '#reviews';
		?>
		<section class="ziteh-pr" dir="rtl" aria-label="<?php echo esc_attr( $s['section_label'] ); ?>">
			<div class="ziteh-pr__container">
				<div class="ziteh-pr__head"><<?php echo esc_attr( $tag ); ?> class="ziteh-pr__heading"><?php echo esc_html( $s['title'] ); ?></<?php echo esc_attr( $tag ); ?>><?php if ( 'yes' === $s['show_leaf'] ) : ?><i class="fas fa-leaf" aria-hidden="true"></i><?php endif; ?></div>
				<div class="ziteh-pr__layout<?php echo 'yes' !== $s['show_summary'] ? ' is-single' : ''; ?>">
					<div class="ziteh-pr__list">
						<?php if ( $reviews ) : foreach ( $reviews as $review ) :
							$rating = (int) get_comment_meta( $review->comment_ID, 'rating', true );
							$verified = function_exists( 'wc_review_is_from_verified_owner' ) && wc_review_is_from_verified_owner( $review->comment_ID ); ?>
							<article class="ziteh-pr__card" itemprop="review" itemscope itemtype="https://schema.org/Review">
								<?php if ( 'yes' === $s['show_avatar'] ) : ?><div class="ziteh-pr__avatar"><?php echo get_avatar( $review, 96, '', $review->comment_author, array( 'class' => 'ziteh-pr__avatar-img', 'loading' => 'lazy' ) ); ?></div><?php endif; // phpcs:ignore WordPress.Security.EscapeOutput ?>
								<div class="ziteh-pr__body"><div class="ziteh-pr__meta"><strong class="ziteh-pr__name" itemprop="author"><?php echo esc_html( $review->comment_author ); ?></strong><?php if ( $verified && 'yes' === $s['show_verified'] ) : ?><span class="ziteh-pr__verified" title="<?php esc_attr_e( 'خریدار تأییدشده', 'ziteh' ); ?>"><i class="fas fa-check-circle" aria-hidden="true"></i><span class="screen-reader-text"><?php esc_html_e( 'خریدار تأییدشده', 'ziteh' ); ?></span></span><?php endif; ?><?php if ( 'yes' === $s['show_date'] ) : ?><time datetime="<?php echo esc_attr( get_comment_date( DATE_W3C, $review ) ); ?>"><?php echo esc_html( get_comment_date( get_option( 'date_format' ), $review ) ); ?></time><?php endif; ?></div>
								<p class="ziteh-pr__comment" itemprop="reviewBody"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $review->comment_content ), max( 8, (int) $s['excerpt_length'] ), '…' ) ); ?></p>
								<?php if ( 'yes' === $s['show_rating'] && $rating ) : ?><div itemprop="reviewRating" itemscope itemtype="https://schema.org/Rating"><meta itemprop="ratingValue" content="<?php echo esc_attr( $rating ); ?>"><?php $this->stars( $rating ); ?></div><?php endif; ?></div>
							</article>
						<?php endforeach; else : ?><p class="ziteh-pr__empty"><?php echo esc_html( $s['empty_text'] ); ?></p><?php endif; ?>
					</div>
					<?php if ( 'yes' === $s['show_summary'] ) : ?><aside class="ziteh-pr__summary" aria-label="<?php esc_attr_e( 'خلاصه امتیاز کاربران', 'ziteh' ); ?>">
						<div class="ziteh-pr__average"><strong><?php echo esc_html( wc_format_decimal( $average, 1 ) ); ?></strong> <span><?php esc_html_e( 'از ۵', 'ziteh' ); ?></span></div>
						<?php $this->stars( $average, sprintf( __( 'میانگین امتیاز %s از ۵', 'ziteh' ), wc_format_decimal( $average, 1 ) ) ); ?>
						<p class="ziteh-pr__count"><?php echo esc_html( sprintf( _n( 'بر اساس %s نظر', 'بر اساس %s نظر', $count, 'ziteh' ), number_format_i18n( $count ) ) ); ?></p>
						<?php if ( 'yes' === $s['show_distribution'] ) : ?><div class="ziteh-pr__distribution"><?php for ( $rating = 5; $rating >= 1; $rating-- ) : $rating_count = isset( $counts[ $rating ] ) ? (int) $counts[ $rating ] : 0; $percent = $count ? min( 100, ( $rating_count / $count ) * 100 ) : 0; ?>
							<div class="ziteh-pr__row"><span><?php echo esc_html( sprintf( __( '%d ستاره', 'ziteh' ), $rating ) ); ?></span><span class="ziteh-pr__track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo esc_attr( round( $percent ) ); ?>" aria-label="<?php echo esc_attr( sprintf( __( '%d ستاره: %d نظر', 'ziteh' ), $rating, $rating_count ) ); ?>"><span style="width:<?php echo esc_attr( $percent ); ?>%"></span></span><b><?php echo esc_html( number_format_i18n( $rating_count ) ); ?></b></div>
						<?php endfor; ?></div><?php endif; ?>
						<?php if ( $s['button_text'] ) : ?><a class="ziteh-pr__button" href="<?php echo esc_url( $button_url ); ?>"<?php echo ! empty( $s['button_url']['is_external'] ) ? ' target="_blank"' : ''; ?><?php echo ! empty( $s['button_url']['nofollow'] ) ? ' rel="nofollow"' : ''; ?>><?php echo esc_html( $s['button_text'] ); ?></a><?php endif; ?>
					</aside><?php endif; ?>
				</div>
			</div>
		</section>
		<?php
	}
}
