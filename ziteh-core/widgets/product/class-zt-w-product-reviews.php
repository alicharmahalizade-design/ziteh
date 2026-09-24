<?php
/**
 * Product: reviews list + rating summary (+ review form helper).
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_W_Product_Reviews
 */
class ZT_W_Product_Reviews extends ZT_Widget_Base {

	protected $zt_group = 'product';
	protected $zt_icon  = 'eicon-review';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-product-reviews';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'نظرات محصول';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'نظرات' );
		$this->ctl( 'visible', 'number', 'تعداد نظرات قابل مشاهده در ابتدا', 3 );
		$this->ctl( 'more', 'text', 'متن دکمه', 'مشاهده همه نظرات' );
		$this->ctl( 'empty', 'text', 'متن نبود نظر', 'هنوز نظری برای این محصول ثبت نشده است؛ اولین نفر باشید!' );
		$this->ctl( 'form', 'switch', 'فرم ثبت نظر زیر فهرست', false );
		$this->ctl( 'score_small', 'text', 'متن کنار امتیاز', 'از ۵' );
		$this->ctl( 'count_tpl', 'text', 'متن تعداد', 'بر اساس {count} نظر' );
		$this->ctl( 'star_tpl', 'text', 'برچسب ردیف ستاره', '{n} ستاره' );
		$this->end_controls_section();
		$this->style_section(
			'st',
			'نظرات',
			array(
				array( 'grid', 'چیدمان', '.zt-reviews', array( 'gap' ) ),
				array( 'rev', 'کارت نظر', '.zt-rev', array( 'bg', 'border', 'radius', 'padding', 'margin' ) ),
				array( 'ava', 'آواتار', '.zt-rev__ava', array( 'size', 'radius', 'bg' ) ),
				array( 'name', 'نام', '.zt-rev__head b', array( 'typo', 'color' ) ),
				array( 'date', 'تاریخ', '.zt-rev__head time', array( 'typo', 'color' ) ),
				array( 'text', 'متن', '.zt-rev p', self::fx( 'text' ) ),
				array( 'sum', 'کادر خلاصه', '.zt-revsum', array( 'bg', 'radius', 'padding', 'shadow' ) ),
				array( 'score', 'امتیاز', '.zt-revsum__score', array( 'typo', 'color' ) ),
				array( 'bar', 'نوار', '.zt-revsum__row .zt-bar i', array( 'bg' ) ),
				array( 'btn', 'دکمه', '.zt-revsum .zt-btn', self::fx( 'button' ) ),
			)
		);
	}

	/**
	 * Review form markup (also used in the reviews tab).
	 *
	 * @param int $pid Product id.
	 * @return string
	 */
	public static function form( $pid ) {
		$user = wp_get_current_user();
		$out  = '<form class="zt-revform" action="' . esc_url( site_url( '/wp-comments-post.php' ) ) . '" method="post">';
		$out .= '<p class="zt-revform__title">دیدگاه خود را ثبت کنید</p>';
		$out .= '<div class="zt-revform__rate"><span>امتیاز شما:</span><span class="zt-revform__stars" data-zt-rate>';
		for ( $i = 1; $i <= 5; $i++ ) {
			$out .= '<button type="button" aria-label="' . esc_attr( zt_fa( $i ) . ' ستاره' ) . '">' . zt_icon( 'star' ) . '</button>';
		}
		$out .= '</span><input type="hidden" name="rating" value="" required></div>';
		if ( ! $user->exists() ) {
			$out .= '<div class="zt-form-grid"><label class="zt-field"><span class="zt-label">نام <span class="zt-req">*</span></span><input class="zt-input" name="author" required></label>';
			$out .= '<label class="zt-field"><span class="zt-label">ایمیل <span class="zt-req">*</span></span><input class="zt-input" type="email" name="email" required></label></div>';
		}
		$out .= '<label class="zt-field zt-full"><span class="zt-label">متن دیدگاه <span class="zt-req">*</span></span><textarea class="zt-textarea" name="comment" required></textarea></label>';
		$out .= '<input type="hidden" name="comment_post_ID" value="' . esc_attr( $pid ) . '"><input type="hidden" name="comment_parent" value="0">';
		$out .= wp_nonce_field( 'unfiltered-html-comment_' . $pid, '_wp_unfiltered_html_comment_disabled', false, false );
		$out .= '<button type="submit" class="zt-btn zt-btn--primary zt-btn--sm">ثبت دیدگاه</button></form>';
		return $out;
	}

	/**
	 * Reviews data.
	 *
	 * @return array [ reviews[[name, date, text, rating, avatar]], avg, count, dist[5..1] ]
	 */
	private function data() {
		$p = ZT_Context::product();
		if ( ! $p ) {
			return array(
				array(
					array( 'مریم محمدی', '۱۴ اردیبهشت ۱۴۰۳', 'بعد از دو هفته استفاده، ریزش موهام خیلی کمتر شده و احساس می‌کنم موهام قوی‌تر شده.', 5, zt_asset_img( 'avatar-1.jpg' ) ),
					array( 'سارا احمدی', '۱۸ اردیبهشت ۱۴۰۳', 'محصول فوق‌العاده‌ای‌ه، پوست سرم حساسه و هیچ‌گونه خارش یا خشکی ایجاد نکرد.', 5, zt_asset_img( 'avatar-2.jpg' ) ),
					array( 'علی رضا', '۱۰ اردیبهشت ۱۴۰۳', 'کیفیت خیلی بالا و رایحه طبیعی و دلنشینی داره. پیشنهاد می‌کنم امتحان کنید.', 5, zt_asset_img( 'avatar-3.jpg' ) ),
				),
				4.8,
				25,
				array( 5 => 20, 4 => 4, 3 => 1, 2 => 0, 1 => 0 ),
			);
		}
		$reviews  = array();
		$comments = get_comments(
			array(
				'post_id' => $p->get_id(),
				'status'  => 'approve',
				'type'    => 'review',
				'orderby' => 'comment_date_gmt',
				'order'   => 'DESC',
			)
		);
		foreach ( $comments as $c ) {
			$av        = get_comment_meta( $c->comment_ID, 'zt_avatar', true );
			$reviews[] = array( $c->comment_author, zt_jdate( 'j F Y', strtotime( $c->comment_date_gmt . ' UTC' ) ), $c->comment_content, (int) get_comment_meta( $c->comment_ID, 'rating', true ), $av );
		}
		$dist = array();
		for ( $i = 5; $i >= 1; $i-- ) {
			$dist[ $i ] = (int) $p->get_rating_count( $i );
		}
		return array( $reviews, (float) $p->get_average_rating(), (int) $p->get_review_count(), $dist );
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		list( $reviews, $avg, $count, $dist ) = $this->data();
		$total   = max( 1, array_sum( $dist ) );
		$visible = max( 1, (int) $s['visible'] );
		echo '<div class="zt-reviews" id="zt-reviews"><div>';
		if ( ! $reviews ) {
			echo '<p class="zt-rev zt-rev--empty">' . esc_html( $s['empty'] ) . '</p>';
		}
		foreach ( $reviews as $i => $r ) {
			$ava = $r[4] ? '<img src="' . esc_url( $r[4] ) . '" alt="">' : '<span class="zt-rev__initial">' . esc_html( function_exists( 'mb_substr' ) ? mb_substr( $r[0], 0, 1 ) : substr( $r[0], 0, 1 ) ) . '</span>';
			echo '<article class="zt-rev"' . ( $i >= $visible ? ' hidden' : '' ) . '><div class="zt-rev__ava">' . $ava . '</div><div>'; // phpcs:ignore
			echo '<div class="zt-rev__head"><b>' . esc_html( $r[0] ) . '</b><time>' . esc_html( $r[1] ) . '</time></div>';
			echo '<p>' . esc_html( $r[2] ) . '</p>' . ZT_Parts::stars( $r[3] ? $r[3] : 5 ) . '</div></article>'; // phpcs:ignore
		}
		$p = ZT_Context::product();
		if ( 'yes' === $s['form'] && $p && comments_open( $p->get_id() ) ) {
			echo self::form( $p->get_id() ); // phpcs:ignore
		}
		echo '</div><aside class="zt-revsum">';
		$avg_s = rtrim( rtrim( number_format( $avg, 1, '.', '' ), '0' ), '.' );
		echo '<div class="zt-revsum__score">' . esc_html( zt_fa( $avg_s ? $avg_s : '0' ) ) . ' <small>' . esc_html( $s['score_small'] ) . '</small></div>';
		echo ZT_Parts::stars( $avg, 'div' ); // phpcs:ignore
		echo '<div class="zt-revsum__count">' . esc_html( zt_fa( str_replace( '{count}', $count, $s['count_tpl'] ) ) ) . '</div>';
		foreach ( $dist as $n => $c ) {
			$w = $c ? round( $c / $total * 100 ) . '%' : '0';
			echo '<div class="zt-revsum__row"><span>' . esc_html( zt_fa( str_replace( '{n}', $n, $s['star_tpl'] ) ) ) . '</span><span class="zt-bar"><i style="width:' . esc_attr( $w ) . '"></i></span><span>' . esc_html( zt_fa( $c ) ) . '</span></div>';
		}
		$more = count( $reviews ) > $visible ? ' data-zt-reviews-more' : '';
		echo '<a href="#zt-reviews" class="zt-btn zt-btn--ghost zt-btn--sm zt-btn--block"' . $more . '>' . esc_html( $s['more'] ) . '</a>'; // phpcs:ignore
		echo '</aside></div>';
	}
}
