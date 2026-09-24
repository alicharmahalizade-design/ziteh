<?php
/**
 * Shared markup partials (identical to the original design markup) used by
 * widgets, AJAX responses and templates.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_Parts
 */
class ZT_Parts {

	/**
	 * Normalise a WooCommerce product into a card item.
	 *
	 * @param WC_Product|int $product Product.
	 * @param string         $size    Image size.
	 * @return array|null
	 */
	public static function product_item( $product, $size = 'large' ) {
		if ( ! zt_is_woo() ) {
			return null;
		}
		$product = is_numeric( $product ) ? wc_get_product( $product ) : $product;
		if ( ! $product instanceof WC_Product ) {
			return null;
		}
		$id      = $product->get_id();
		$img_id  = $product->get_image_id();
		$img     = $img_id ? wp_get_attachment_image_url( $img_id, $size ) : wc_placeholder_img_src( $size );
		$price   = (float) $product->get_price();
		$regular = (float) $product->get_regular_price();
		if ( $product->is_type( 'variable' ) ) {
			$price   = (float) $product->get_variation_price( 'min', true );
			$regular = (float) $product->get_variation_regular_price( 'min', true );
		}
		$pct = ( $product->is_on_sale() && $regular > 0 && $regular > $price ) ? (int) round( ( $regular - $price ) / $regular * 100 ) : 0;
		$cat = get_post_meta( $id, '_zt_card_cat', true );
		if ( ! $cat ) {
			$terms = get_the_terms( $id, 'product_cat' );
			if ( $terms && ! is_wp_error( $terms ) ) {
				$pick = $terms[0];
				foreach ( $terms as $t ) {
					if ( $t->parent ) {
						$pick = $t; // prefer the deepest category.
					}
				}
				$cat = $pick->name;
			}
		}
		return array(
			'id'          => $id,
			'title'       => $product->get_name(),
			'cat'         => (string) $cat,
			'url'         => $product->get_permalink(),
			'img'         => $img,
			'price'       => $price,
			'regular'     => $regular,
			'pct'         => $pct,
			'exp'         => (string) get_post_meta( $id, '_zt_expiry', true ),
			'type'        => $product->get_type(),
			'purchasable' => $product->is_purchasable() && $product->is_in_stock(),
			'in_stock'    => $product->is_in_stock(),
			'stock'       => $product->managing_stock() ? (int) $product->get_stock_quantity() : null,
			'rating'      => (float) $product->get_average_rating(),
			'reviews'     => (int) $product->get_review_count(),
			'volume'      => (string) get_post_meta( $id, '_zt_volume', true ),
			'en'          => (string) get_post_meta( $id, '_zt_en_name', true ),
		);
	}

	/**
	 * Expiry badge.
	 *
	 * @param string $date  Date (Jalali, e.g. 1408/02).
	 * @param string $label Label.
	 * @return string
	 */
	public static function exp( $date, $label = 'انقضا' ) {
		if ( '' === trim( (string) $date ) ) {
			return '';
		}
		return '<span class="zt-exp">' . zt_icon( 'calendar' ) . '<i>' . esc_html( $label ) . '</i><b>' . esc_html( zt_fa( $date ) ) . '</b></span>';
	}

	/**
	 * Stars.
	 *
	 * @param float  $rating Rating 0-5.
	 * @param string $tag    Wrapper tag.
	 * @param string $extra  Extra classes.
	 * @return string
	 */
	public static function stars( $rating = 5, $tag = 'span', $extra = '' ) {
		$full = (int) round( (float) $rating );
		$out  = '<' . $tag . ' class="zt-stars' . ( $extra ? ' ' . esc_attr( $extra ) : '' ) . '">';
		for ( $i = 1; $i <= 5; $i++ ) {
			$out .= zt_icon( 'star', $i > $full ? array( 'class' => 'zt-off' ) : array() );
		}
		return $out . '</' . $tag . '>';
	}

	/**
	 * Quantity stepper.
	 *
	 * @param int    $value Value.
	 * @param array  $attrs Extra attributes for the wrapper.
	 * @param string $name  Optional input name (adds a hidden input).
	 * @param int    $max   Max.
	 * @return string
	 */
	public static function qty( $value = 1, $attrs = array(), $name = '', $max = 99 ) {
		$a = '';
		foreach ( $attrs as $k => $v ) {
			$a .= ' ' . esc_attr( $k ) . '="' . esc_attr( $v ) . '"';
		}
		$out  = '<div class="zt-qty" data-zt-qty data-value="' . esc_attr( $value ) . '" data-max="' . esc_attr( $max ) . '"' . $a . '>';
		$out .= '<button type="button" data-zt-step="-1" aria-label="کاهش">' . zt_icon( 'minus' ) . '</button>';
		$out .= '<span>' . esc_html( zt_fa( $value ) ) . '</span>';
		$out .= '<button type="button" data-zt-step="1" aria-label="افزایش">' . zt_icon( 'plus' ) . '</button>';
		if ( $name ) {
			$out .= '<input type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '">';
		}
		return $out . '</div>';
	}

	/**
	 * JSON for quick add.
	 *
	 * @param array $it Item.
	 * @return string
	 */
	public static function qa_json( $it ) {
		return wp_json_encode(
			array(
				'id'      => isset( $it['id'] ) ? (int) $it['id'] : 0,
				'title'   => isset( $it['title'] ) ? $it['title'] : '',
				'cat'     => isset( $it['cat'] ) ? $it['cat'] : '',
				'img'     => isset( $it['img'] ) ? $it['img'] : '',
				'price'   => isset( $it['price'] ) ? round( zt_amount( $it['price'] ) ) : 0,
				'url'     => isset( $it['url'] ) ? $it['url'] : '',
				'variable' => isset( $it['type'] ) && in_array( $it['type'], array( 'variable', 'grouped', 'external' ), true ),
				'buy'     => ! isset( $it['purchasable'] ) || $it['purchasable'],
			)
		);
	}

	/**
	 * Product card (.prod) — home carousels, archives.
	 *
	 * @param array $it   Item.
	 * @param array $opts badge (bool), badge_left (bool), exp (bool), exp_label, add_label.
	 * @return string
	 */
	public static function product_card( $it, $opts = array() ) {
		$o   = wp_parse_args(
			$opts,
			array(
				'badge'      => true,
				'badge_left' => true,
				'badge_text' => '{pct}٪',
				'exp'        => true,
				'exp_label'  => 'انقضا',
				'del'        => true,
				'add_icon'   => 'cart',
				'stock'      => false,
				'stock_text' => 'فقط {n} عدد باقی مانده',
				'stock_max'  => 3,
			)
		);
		$url = esc_url( $it['url'] );
		$out = '<article class="zt-prod" data-zt-product="' . esc_attr( self::qa_json( $it ) ) . '">';
		if ( $o['badge'] && ! empty( $it['pct'] ) ) {
			$out .= '<span class="zt-badge-off' . ( $o['badge_left'] ? ' zt-badge-off--left' : '' ) . '">' . esc_html( zt_fa( str_replace( '{pct}', $it['pct'], $o['badge_text'] ) ) ) . '</span>';
		}
		$out .= '<a href="' . $url . '" class="zt-prod__img">' . ( $it['img'] ? '<img src="' . esc_url( $it['img'] ) . '" alt="' . esc_attr( $it['title'] ) . '" loading="lazy">' : '' );
		if ( $o['exp'] && ! empty( $it['exp'] ) ) {
			$out .= self::exp( $it['exp'], $o['exp_label'] );
		}
		$out .= '</a><div class="zt-prod__body">';
		if ( $o['stock'] && isset( $it['stock'] ) && null !== $it['stock'] && $it['stock'] > 0 && $it['stock'] <= (int) $o['stock_max'] ) {
			$out .= '<span class="zt-prod__stock"><i></i>' . esc_html( zt_fa( str_replace( '{n}', $it['stock'], $o['stock_text'] ) ) ) . '</span>';
		}
		if ( '' !== (string) $it['cat'] ) {
			$out .= '<span class="zt-prod__cat">' . esc_html( $it['cat'] ) . '</span>';
		}
		$out .= '<a href="' . $url . '"><h3 class="zt-prod__title">' . esc_html( $it['title'] ) . '</h3></a>';
		$out .= '<div class="zt-prod__foot">';
		$out .= '<button class="zt-prod__add" aria-label="افزودن به سبد"' . ( empty( $it['purchasable'] ) && isset( $it['purchasable'] ) && ! empty( $it['id'] ) ? ' data-zt-oos' : '' ) . '>' . zt_icon( $o['add_icon'] ) . '</button>';
		$out .= '<div class="zt-prod__price"><b>' . zt_price( $it['price'] ) . '</b>';
		if ( $o['del'] && ! empty( $it['pct'] ) && $it['regular'] > $it['price'] ) {
			$out .= '<del>' . esc_html( zt_money( $it['regular'], true ) ) . '</del>';
		}
		$out .= '</div></div></div></article>';
		return $out;
	}

	/**
	 * Related product card (.relcard).
	 *
	 * @param array $it   Item.
	 * @param array $opts exp, exp_label, add_icon.
	 * @return string
	 */
	public static function rel_card( $it, $opts = array() ) {
		$o   = wp_parse_args(
			$opts,
			array(
				'exp'       => true,
				'exp_label' => 'انقضا',
				'add_icon'  => 'basket',
			)
		);
		$url = esc_url( $it['url'] );
		$out = '<article class="zt-relcard" data-zt-product="' . esc_attr( self::qa_json( $it ) ) . '">';
		$out .= '<a href="' . $url . '" class="zt-relcard__img">' . ( $it['img'] ? '<img src="' . esc_url( $it['img'] ) . '" alt="' . esc_attr( $it['title'] ) . '" loading="lazy">' : '' );
		if ( $o['exp'] && ! empty( $it['exp'] ) ) {
			$out .= self::exp( $it['exp'], $o['exp_label'] );
		}
		$out .= '</a><div class="zt-relcard__body"><h4><a href="' . $url . '">' . esc_html( $it['title'] ) . '</a></h4>';
		$out .= '<div class="zt-relcard__foot"><b>' . zt_price( $it['price'] ) . '</b>';
		$out .= '<button aria-label="افزودن به سبد">' . zt_icon( $o['add_icon'] ) . '</button></div></div></article>';
		return $out;
	}

	/**
	 * Blog post card (.post).
	 *
	 * @param array $it   Item: title, url, img, tag, more.
	 * @param array $opts more_label, tag.
	 * @return string
	 */
	public static function post_card( $it, $opts = array() ) {
		$o   = wp_parse_args(
			$opts,
			array(
				'more' => 'مطالعه مقاله',
				'tag'  => true,
			)
		);
		$url = esc_url( $it['url'] );
		$out = '<article class="zt-post"><a href="' . $url . '" class="zt-post__img">' . ( $it['img'] ? '<img src="' . esc_url( $it['img'] ) . '" alt="' . esc_attr( $it['title'] ) . '" loading="lazy">' : '' );
		if ( $o['tag'] && ! empty( $it['tag'] ) ) {
			$out .= '<span class="zt-post__tag">' . esc_html( $it['tag'] ) . '</span>';
		}
		$out .= '</a><div class="zt-post__body"><a href="' . $url . '"><h3>' . esc_html( $it['title'] ) . '</h3></a>';
		if ( '' !== $o['more'] ) {
			$out .= '<a href="' . $url . '" class="zt-link-more">' . esc_html( $o['more'] ) . ' ' . zt_icon( 'chev-left' ) . '</a>';
		}
		return $out . '</div></article>';
	}

	/**
	 * Normalise a WP post into a post card item.
	 *
	 * @param WP_Post|int $post Post.
	 * @return array
	 */
	public static function post_item( $post ) {
		$post = get_post( $post );
		$cats = get_the_category( $post->ID );
		$tag  = $cats ? $cats[0]->name : '';
		$img  = get_the_post_thumbnail_url( $post, 'large' );
		return array(
			'id'    => $post->ID,
			'title' => get_the_title( $post ),
			'url'   => get_permalink( $post ),
			'img'   => $img ? $img : '',
			'tag'   => $tag,
		);
	}

	/**
	 * Search result row (.sresult) for the mobile search pane.
	 *
	 * @param array $it Item.
	 * @return string
	 */
	public static function search_row( $it ) {
		return '<a class="zt-sresult" href="' . esc_url( $it['url'] ) . '"><img src="' . esc_url( $it['img'] ) . '" alt=""><div><b>' . esc_html( $it['title'] ) . '</b><span>' . zt_money( $it['price'], true ) . ' ' . esc_html( zt_currency() ) . '</span></div><span class="zt-go">' . zt_icon( 'chev-left' ) . '</span></a>';
	}

	/**
	 * Marker used by widgets that intentionally render nothing on the front
	 * end (the Elementor wrapper is hidden by CSS).
	 *
	 * @return string
	 */
	public static function empty_marker() {
		return '<span class="zt-empty" hidden></span>';
	}
}
