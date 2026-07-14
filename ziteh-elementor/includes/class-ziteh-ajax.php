<?php
/**
 * AJAX + WooCommerce glue for the interactive v2 features:
 *   - Quick View modal content.
 *   - Live product search.
 *   - Slide-in mini-cart (via WooCommerce add-to-cart fragments) + header count.
 *
 * Everything degrades gracefully when WooCommerce is inactive.
 *
 * @package Ziteh_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Ziteh_Ajax
 */
class Ziteh_Ajax {

	/**
	 * Singleton instance.
	 *
	 * @var Ziteh_Ajax|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Ziteh_Ajax
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Whether WooCommerce is available.
	 *
	 * @return bool
	 */
	private function wc() {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * Constructor. Registers the AJAX endpoints + cart fragment filter.
	 */
	private function __construct() {
		add_action( 'wp_ajax_ziteh_quickview', array( $this, 'quickview' ) );
		add_action( 'wp_ajax_nopriv_ziteh_quickview', array( $this, 'quickview' ) );

		add_action( 'wp_ajax_ziteh_search', array( $this, 'search' ) );
		add_action( 'wp_ajax_nopriv_ziteh_search', array( $this, 'search' ) );

		if ( $this->wc() ) {
			add_filter( 'woocommerce_add_to_cart_fragments', array( $this, 'cart_fragments' ) );
		}
	}

	/**
	 * Verify the shared nonce for a request (soft-fail: search/quickview are
	 * read-only so we accept a missing nonce but reject an invalid one).
	 */
	private function check_nonce() {
		if ( isset( $_REQUEST['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'ziteh' ) ) {
			wp_send_json_error( array( 'message' => __( 'درخواست نامعتبر است.', 'ziteh' ) ), 403 );
		}
	}

	/**
	 * Quick View: return the modal body markup for a product.
	 */
	public function quickview() {
		$this->check_nonce();

		if ( ! $this->wc() ) {
			wp_send_json_error( array( 'message' => __( 'ووکامرس فعال نیست.', 'ziteh' ) ), 400 );
		}

		$id      = isset( $_REQUEST['id'] ) ? absint( $_REQUEST['id'] ) : 0;
		$product = $id ? wc_get_product( $id ) : null;

		if ( ! $product ) {
			wp_send_json_error( array( 'message' => __( 'محصول یافت نشد.', 'ziteh' ) ), 404 );
		}

		ob_start();
		$this->render_quickview( $product );
		wp_send_json_success( array( 'html' => ob_get_clean() ) );
	}

	/**
	 * Render Quick View content for a product.
	 *
	 * @param \WC_Product $product Product.
	 */
	private function render_quickview( $product ) {
		$permalink = get_permalink( $product->get_id() );
		?>
		<div class="ziteh-qv">
			<div class="ziteh-qv__media">
				<?php echo $product->get_image( 'woocommerce_single' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
			<div class="ziteh-qv__info">
				<h3 class="ziteh-qv__title"><a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h3>
				<div class="ziteh-qv__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>

				<?php if ( $product->get_average_rating() > 0 ) : ?>
					<div class="ziteh-qv__rating"><?php echo wp_kses_post( wc_get_rating_html( $product->get_average_rating() ) ); ?></div>
				<?php endif; ?>

				<div class="ziteh-qv__excerpt"><?php echo wp_kses_post( wpautop( $product->get_short_description() ) ); ?></div>

				<div class="ziteh-qv__actions">
					<?php if ( $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock() ) : ?>
						<a class="ziteh-btn ziteh-btn--primary add_to_cart_button ajax_add_to_cart"
							href="<?php echo esc_url( $product->add_to_cart_url() ); ?>"
							data-quantity="1"
							data-product_id="<?php echo esc_attr( $product->get_id() ); ?>"
							rel="nofollow">
							<?php echo esc_html( $product->add_to_cart_text() ); ?>
						</a>
					<?php else : ?>
						<a class="ziteh-btn ziteh-btn--primary" href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $product->add_to_cart_text() ); ?></a>
					<?php endif; ?>
					<a class="ziteh-btn ziteh-btn--outline" href="<?php echo esc_url( $permalink ); ?>"><?php esc_html_e( 'مشاهده کامل', 'ziteh' ); ?></a>
				</div>

				<?php
				$cats = wc_get_product_category_list( $product->get_id() );
				if ( $cats ) :
					?>
					<p class="ziteh-qv__meta"><?php esc_html_e( 'دسته:', 'ziteh' ); ?> <?php echo wp_kses_post( $cats ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Live search: return a small list of matching products.
	 */
	public function search() {
		$this->check_nonce();

		$term = isset( $_REQUEST['q'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['q'] ) ) : '';
		$term = trim( $term );

		if ( mb_strlen( $term ) < 2 ) {
			wp_send_json_success( array( 'html' => '' ) );
		}

		if ( ! $this->wc() ) {
			// Fall back to searching posts if WooCommerce is not present.
			$this->search_posts( $term );
			return;
		}

		$products = wc_get_products(
			array(
				'status' => 'publish',
				'limit'  => 6,
				's'      => $term,
			)
		);

		ob_start();
		if ( empty( $products ) ) {
			echo '<p class="ziteh-search__empty">' . esc_html__( 'نتیجه‌ای یافت نشد.', 'ziteh' ) . '</p>';
		} else {
			echo '<ul class="ziteh-search__list">';
			foreach ( $products as $product ) {
				?>
				<li class="ziteh-search__item">
					<a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>">
						<span class="ziteh-search__thumb"><?php echo $product->get_image( 'thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
						<span class="ziteh-search__meta">
							<span class="ziteh-search__name"><?php echo esc_html( $product->get_name() ); ?></span>
							<span class="ziteh-search__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
						</span>
					</a>
				</li>
				<?php
			}
			echo '</ul>';
		}
		wp_send_json_success( array( 'html' => ob_get_clean() ) );
	}

	/**
	 * Fallback live search over regular posts (no WooCommerce).
	 *
	 * @param string $term Search term.
	 */
	private function search_posts( $term ) {
		$q = new WP_Query(
			array(
				'post_type'           => 'post',
				'posts_per_page'      => 6,
				's'                   => $term,
				'post_status'         => 'publish',
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
		);

		ob_start();
		if ( ! $q->have_posts() ) {
			echo '<p class="ziteh-search__empty">' . esc_html__( 'نتیجه‌ای یافت نشد.', 'ziteh' ) . '</p>';
		} else {
			echo '<ul class="ziteh-search__list">';
			while ( $q->have_posts() ) {
				$q->the_post();
				?>
				<li class="ziteh-search__item">
					<a href="<?php the_permalink(); ?>">
						<span class="ziteh-search__thumb"><?php echo get_the_post_thumbnail( get_the_ID(), 'thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
						<span class="ziteh-search__meta"><span class="ziteh-search__name"><?php the_title(); ?></span></span>
					</a>
				</li>
				<?php
			}
			echo '</ul>';
			wp_reset_postdata();
		}
		wp_send_json_success( array( 'html' => ob_get_clean() ) );
	}

	/**
	 * Add fragments so the header count + mini-cart drawer refresh on add-to-cart.
	 *
	 * @param array $fragments Existing fragments.
	 * @return array
	 */
	public function cart_fragments( $fragments ) {
		if ( ! $this->wc() || null === WC()->cart ) {
			return $fragments;
		}

		$count = WC()->cart->get_cart_contents_count();

		ob_start();
		echo '<span class="ziteh-cart-count" data-ziteh-cart-count>' . esc_html( $count ) . '</span>';
		$fragments['.ziteh-cart-count'] = ob_get_clean();

		ob_start();
		$this->render_mini_cart();
		$fragments['.ziteh-drawer__panel'] = ob_get_clean();

		return $fragments;
	}

	/**
	 * Render the slide-in mini-cart panel contents.
	 */
	public function render_mini_cart() {
		$cart  = ( $this->wc() && null !== WC()->cart ) ? WC()->cart : null;
		$count = $cart ? $cart->get_cart_contents_count() : 0;
		?>
		<div class="ziteh-drawer__panel">
			<div class="ziteh-drawer__head">
				<h3 class="ziteh-drawer__title"><?php esc_html_e( 'سبد خرید', 'ziteh' ); ?> <span class="ziteh-drawer__count">(<?php echo esc_html( $count ); ?>)</span></h3>
				<button class="ziteh-drawer__close" type="button" data-ziteh-drawer-close aria-label="<?php esc_attr_e( 'بستن', 'ziteh' ); ?>">&times;</button>
			</div>

			<?php if ( ! $cart || $cart->is_empty() ) : ?>
				<div class="ziteh-drawer__empty">
					<p><?php esc_html_e( 'سبد خرید شما خالی است.', 'ziteh' ); ?></p>
					<?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
						<a class="ziteh-btn ziteh-btn--outline" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'رفتن به فروشگاه', 'ziteh' ); ?></a>
					<?php endif; ?>
				</div>
			<?php else : ?>
				<ul class="ziteh-drawer__items">
					<?php
					foreach ( $cart->get_cart() as $key => $item ) :
						$product = $item['data'];
						if ( ! $product || ! $product->exists() || $item['quantity'] <= 0 ) {
							continue;
						}
						$permalink = $product->is_visible() ? $product->get_permalink( $item ) : '';
						?>
						<li class="ziteh-drawer__item">
							<span class="ziteh-drawer__thumb"><?php echo $product->get_image( 'thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
							<span class="ziteh-drawer__info">
								<a class="ziteh-drawer__name" href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
								<span class="ziteh-drawer__qty"><?php echo esc_html( $item['quantity'] ); ?> × <?php echo wp_kses_post( wc_price( $product->get_price() ) ); ?></span>
							</span>
							<a href="#" class="ziteh-drawer__remove remove_from_cart_button" data-product_id="<?php echo esc_attr( $item['product_id'] ); ?>" data-cart_item_key="<?php echo esc_attr( $key ); ?>" aria-label="<?php esc_attr_e( 'حذف', 'ziteh' ); ?>">&times;</a>
						</li>
					<?php endforeach; ?>
				</ul>

				<div class="ziteh-drawer__foot">
					<div class="ziteh-drawer__total">
						<span><?php esc_html_e( 'مجموع', 'ziteh' ); ?></span>
						<strong><?php echo wp_kses_post( $cart->get_cart_subtotal() ); ?></strong>
					</div>
					<div class="ziteh-drawer__actions">
						<a class="ziteh-btn ziteh-btn--outline" href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e( 'مشاهده سبد', 'ziteh' ); ?></a>
						<a class="ziteh-btn ziteh-btn--primary" href="<?php echo esc_url( wc_get_checkout_url() ); ?>"><?php esc_html_e( 'تسویه حساب', 'ziteh' ); ?></a>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}
