<?php
/**
 * Blog: single post header (category, title, meta, featured image).
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Post_Header
 */
class ZT_W_Post_Header extends ZT_Widget_Base {

	protected $zt_group = 'blog';
	protected $zt_icon  = 'eicon-post-title';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-post-header';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'سربرگ مقاله';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'نمایش' );
		$this->ctl( 'cat', 'switch', 'دسته‌بندی', true );
		$this->ctl( 'author', 'switch', 'نویسنده', true );
		$this->ctl( 'date', 'switch', 'تاریخ', true );
		$this->ctl( 'read', 'switch', 'زمان مطالعه', true );
		$this->ctl( 'comments', 'switch', 'تعداد دیدگاه', true );
		$this->ctl( 'excerpt', 'switch', 'خلاصه (لید)', true );
		$this->ctl( 'image', 'switch', 'تصویر شاخص', true );
		$this->end_controls_section();
		$this->style_section(
			'st',
			'سربرگ',
			array(
				array( 'box', 'کادر', '.zt-phead', array( 'align', 'padding', 'margin' ) ),
				array( 'cat', 'دسته', '.zt-phead .zt-chip', array( 'typo', 'color', 'bg' ) ),
				array( 'title', 'عنوان', '.zt-phead h1', array( 'typo', 'color' ) ),
				array( 'meta', 'اطلاعات', '.zt-phead__meta', array( 'typo', 'color', 'justify' ) ),
				array( 'lead', 'لید', '.zt-phead__lead', array( 'typo', 'color' ) ),
				array( 'img', 'تصویر', '.zt-phead__img', array( 'radius', 'ratio', 'shadow', 'margin' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$p = ZT_Context::post();
		if ( ! $p ) {
			echo '<div class="zt-editor-note">سربرگ مقاله — عنوان، اطلاعات و تصویر مقاله اینجا نمایش داده می‌شود.</div>';
			return;
		}
		echo '<header class="zt-phead">';
		if ( 'yes' === $s['cat'] ) {
			$cats = get_the_category( $p->ID );
			if ( $cats ) {
				echo '<a class="zt-chip" href="' . esc_url( get_category_link( $cats[0] ) ) . '">' . esc_html( $cats[0]->name ) . '</a>';
			}
		}
		echo '<h1>' . esc_html( get_the_title( $p ) ) . '</h1><div class="zt-phead__meta">';
		if ( 'yes' === $s['author'] ) {
			echo '<span>' . zt_icon( 'user' ) . ' ' . esc_html( get_the_author_meta( 'display_name', $p->post_author ) ) . '</span>'; // phpcs:ignore
		}
		if ( 'yes' === $s['date'] ) {
			echo '<span>' . zt_icon( 'calendar' ) . ' <time datetime="' . esc_attr( get_post_time( 'c', true, $p ) ) . '">' . esc_html( zt_jdate( 'j F Y', get_post_time( 'U', true, $p ) ) ) . '</time></span>'; // phpcs:ignore
		}
		if ( 'yes' === $s['read'] ) {
			echo '<span>' . zt_icon( 'clock' ) . ' ' . esc_html( zt_fa( ZT_W_Post_Grid::reading_time( $p ) ) ) . ' دقیقه مطالعه</span>'; // phpcs:ignore
		}
		if ( 'yes' === $s['comments'] && comments_open( $p ) ) {
			echo '<a href="#comments">' . zt_icon( 'send' ) . ' ' . esc_html( zt_fa( get_comments_number( $p ) ) ) . ' دیدگاه</a>'; // phpcs:ignore
		}
		echo '</div>';
		if ( 'yes' === $s['excerpt'] && has_excerpt( $p ) ) {
			echo '<p class="zt-phead__lead">' . esc_html( $p->post_excerpt ) . '</p>';
		}
		if ( 'yes' === $s['image'] && has_post_thumbnail( $p ) ) {
			echo '<figure class="zt-phead__img">' . get_the_post_thumbnail( $p, 'full', array( 'loading' => 'eager' ) ) . '</figure>'; // phpcs:ignore
		}
		echo '</header>';
	}
}
