<?php
/**
 * Blog: post footer (tags, share, author, prev/next, comments).
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Post_Footer
 */
class ZT_W_Post_Footer extends ZT_Widget_Base {

	protected $zt_group = 'blog';
	protected $zt_icon  = 'eicon-comments';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-post-footer';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'پایان مقاله (برچسب، اشتراک، دیدگاه‌ها)';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'نمایش' );
		$this->ctl( 'tags', 'switch', 'برچسب‌ها', true );
		$this->ctl( 'share', 'switch', 'اشتراک‌گذاری', true );
		$this->ctl( 'share_text', 'text', 'متن اشتراک', 'اشتراک‌گذاری:' );
		$this->ctl( 'author', 'switch', 'درباره نویسنده', false );
		$this->ctl( 'nav', 'switch', 'مقاله قبلی / بعدی', true );
		$this->ctl( 'comments', 'switch', 'دیدگاه‌ها', true );
		$this->ctl( 'c_title', 'text', 'عنوان دیدگاه‌ها', 'دیدگاه‌ها' );
		$this->end_controls_section();
		$this->style_section(
			'st',
			'پایان مقاله',
			array(
				array( 'bar', 'نوار برچسب/اشتراک', '.zt-pfoot__bar', array( 'bg', 'border', 'radius', 'padding', 'margin' ) ),
				array( 'tag', 'برچسب', '.zt-pfoot__tags a', array( 'typo', 'color', 'bg' ) ),
				array( 'sh', 'دکمه اشتراک', '.zt-pfoot__share .zt-iconbtn', array( 'size', 'color', 'bg', 'radius' ) ),
				array( 'nav', 'قبلی / بعدی', '.zt-pnav a', array( 'bg', 'radius', 'padding' ) ),
				array( 'cm', 'دیدگاه', '.zt-comment', array( 'bg', 'border', 'radius', 'padding' ) ),
				array( 'btn', 'دکمه ارسال', '.zt-cform .zt-btn', self::fx( 'button' ) ),
			)
		);
	}

	/**
	 * Comment callback.
	 *
	 * @param WP_Comment $c     Comment.
	 * @param array      $args  Args.
	 * @param int        $depth Depth.
	 */
	public static function comment( $c, $args, $depth ) {
		$tag = 'div' === $args['style'] ? 'div' : 'li';
		$ini = function_exists( 'mb_substr' ) ? mb_substr( $c->comment_author, 0, 1 ) : substr( $c->comment_author, 0, 1 );
		echo '<' . $tag . ' id="comment-' . (int) $c->comment_ID . '" class="zt-comment' . ( $c->comment_parent ? ' zt-comment--reply' : '' ) . '"><div class="zt-rev__ava"><span class="zt-rev__initial">' . esc_html( $ini ) . '</span></div><div class="zt-comment__body">'; // phpcs:ignore
		echo '<div class="zt-rev__head"><b>' . esc_html( get_comment_author( $c ) ) . '</b><time>' . esc_html( zt_jdate( 'j F Y', strtotime( $c->comment_date_gmt . ' UTC' ) ) ) . '</time></div>';
		if ( '0' === (string) $c->comment_approved ) {
			echo '<span class="zt-ostatus zt-ostatus--wait">' . zt_icon( 'clock' ) . ' دیدگاه شما در انتظار تایید است</span>'; // phpcs:ignore
		}
		echo '<div class="zt-comment__text">' . wp_kses_post( wpautop( get_comment_text( $c ) ) ) . '</div>';
		comment_reply_link(
			array_merge(
				$args,
				array(
					'depth'      => $depth,
					'max_depth'  => $args['max_depth'],
					'reply_text' => 'پاسخ',
				)
			),
			$c
		);
		echo '</div>';
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$p = ZT_Context::post();
		if ( ! $p ) {
			echo '<div class="zt-editor-note">برچسب‌ها، اشتراک‌گذاری و دیدگاه‌های مقاله اینجا نمایش داده می‌شود.</div>';
			return;
		}
		$url  = get_permalink( $p );
		$tags = 'yes' === $s['tags'] ? get_the_tags( $p->ID ) : array();
		echo '<footer class="zt-pfoot">';
		if ( $tags || 'yes' === $s['share'] ) {
			echo '<div class="zt-pfoot__bar">';
			if ( $tags ) {
				echo '<div class="zt-pfoot__tags">' . zt_icon( 'tag' ); // phpcs:ignore
				foreach ( $tags as $t ) {
					echo '<a href="' . esc_url( get_tag_link( $t ) ) . '">' . esc_html( $t->name ) . '</a>';
				}
				echo '</div>';
			}
			if ( 'yes' === $s['share'] ) {
				$e = rawurlencode( $url );
				$t = rawurlencode( get_the_title( $p ) );
				echo '<div class="zt-pfoot__share"><span>' . esc_html( $s['share_text'] ) . '</span>';
				echo '<a class="zt-iconbtn" target="_blank" rel="noopener" href="https://t.me/share/url?url=' . esc_attr( $e ) . '&text=' . esc_attr( $t ) . '" aria-label="تلگرام">' . zt_icon( 'telegram' ) . '</a>'; // phpcs:ignore
				echo '<a class="zt-iconbtn" target="_blank" rel="noopener" href="https://wa.me/?text=' . esc_attr( $t . '%20' . $e ) . '" aria-label="واتساپ">' . zt_icon( 'whatsapp' ) . '</a>'; // phpcs:ignore
				echo '<button type="button" class="zt-iconbtn" data-zt-copy="' . esc_attr( wp_get_shortlink( $p->ID ) ? wp_get_shortlink( $p->ID ) : $url ) . '" aria-label="کپی لینک">' . zt_icon( 'copy' ) . '</button></div>'; // phpcs:ignore
			}
			echo '</div>';
		}
		if ( 'yes' === $s['author'] ) {
			$bio = get_the_author_meta( 'description', $p->post_author );
			echo '<div class="zt-pauthor"><div class="zt-rev__ava"><span class="zt-rev__initial">' . esc_html( mb_substr( get_the_author_meta( 'display_name', $p->post_author ), 0, 1 ) ) . '</span></div><div><b>' . esc_html( get_the_author_meta( 'display_name', $p->post_author ) ) . '</b>' . ( $bio ? '<p>' . esc_html( $bio ) . '</p>' : '' ) . '</div></div>';
		}
		if ( 'yes' === $s['nav'] ) {
			$GLOBALS['post'] = $p; // phpcs:ignore
			setup_postdata( $p );
			$prev = get_previous_post();
			$next = get_next_post();
			wp_reset_postdata();
			if ( $prev || $next ) {
				echo '<nav class="zt-pnav">';
				echo $prev ? '<a class="zt-pnav__prev" href="' . esc_url( get_permalink( $prev ) ) . '"><span>' . zt_icon( 'chev-right' ) . ' مقاله قبلی</span><b>' . esc_html( get_the_title( $prev ) ) . '</b></a>' : '<span></span>'; // phpcs:ignore
				echo $next ? '<a class="zt-pnav__next" href="' . esc_url( get_permalink( $next ) ) . '"><span>مقاله بعدی ' . zt_icon( 'chev-left' ) . '</span><b>' . esc_html( get_the_title( $next ) ) . '</b></a>' : '<span></span>'; // phpcs:ignore
				echo '</nav>';
			}
		}
		if ( 'yes' === $s['comments'] && ( comments_open( $p ) || get_comments_number( $p ) ) ) {
			$list = get_comments(
				array(
					'post_id' => $p->ID,
					'status'  => 'approve',
					'order'   => 'ASC',
				)
			);
			echo '<section class="zt-comments" id="comments"><h2 class="zt-comments__title">' . zt_icon( 'send' ) . ' ' . esc_html( $s['c_title'] ) . ' <small>(' . esc_html( zt_fa( count( $list ) ) ) . ')</small></h2>'; // phpcs:ignore
			if ( $list ) {
				echo '<ol class="zt-comments__list">';
				wp_list_comments(
					array(
						'callback'  => array( __CLASS__, 'comment' ),
						'style'     => 'ol',
						'max_depth' => (int) get_option( 'thread_comments_depth', 3 ),
					),
					$list
				);
				echo '</ol>';
			}
			if ( comments_open( $p ) ) {
				ob_start();
				comment_form(
					array(
						'class_form'           => 'zt-cform zt-form-grid',
						'title_reply'          => 'دیدگاه خود را بنویسید',
						'title_reply_before'   => '<h3 class="zt-cform__title">',
						'title_reply_after'    => '</h3>',
						'label_submit'         => 'ارسال دیدگاه',
						'class_submit'         => 'zt-btn zt-btn--primary',
						'submit_button'        => '<button name="%1$s" type="submit" id="%2$s" class="%3$s">%4$s</button>',
						'comment_notes_before' => '<p class="zt-full zt-cform__note">نشانی ایمیل شما منتشر نخواهد شد.</p>',
						'comment_field'        => '<label class="zt-field zt-full"><span class="zt-label">دیدگاه <span class="zt-req">*</span></span><textarea class="zt-textarea" name="comment" id="comment" required></textarea></label>',
						'fields'               => array(
							'author' => '<label class="zt-field"><span class="zt-label">نام <span class="zt-req">*</span></span><input class="zt-input" name="author" id="author" required></label>',
							'email'  => '<label class="zt-field"><span class="zt-label">ایمیل <span class="zt-req">*</span></span><input class="zt-input" type="email" name="email" id="email" required></label>',
						),
					),
					$p->ID
				);
				echo ob_get_clean(); // phpcs:ignore
			}
			echo '</section>';
		}
		echo '</footer>';
	}
}
