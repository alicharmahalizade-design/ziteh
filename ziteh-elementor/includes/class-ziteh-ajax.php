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

		add_action( 'wp_ajax_ziteh_quiz_products', array( $this, 'quiz_products' ) );
		add_action( 'wp_ajax_nopriv_ziteh_quiz_products', array( $this, 'quiz_products' ) );

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
	 * Stop a disabled public feature before it performs any query work.
	 *
	 * @param string $feature Settings key.
	 */
	private function require_feature( $feature ) {
		$shell_features = array( 'quickview', 'live_search', 'cart_drawer' );
		$disabled       = class_exists( 'Ziteh_Settings' ) && ! Ziteh_Settings::feature_enabled( $feature );
		if ( class_exists( 'Ziteh_Settings' ) && in_array( $feature, $shell_features, true ) && ! Ziteh_Settings::shell_enabled() ) {
			$disabled = true;
		}
		if ( $disabled ) {
			wp_send_json_error( array( 'message' => __( 'این قابلیت در هسته زیته غیرفعال است.', 'ziteh' ) ), 403 );
		}
	}

	/**
	 * Quick View: return the modal body markup for a product.
	 */
	public function quickview() {
		$this->require_feature( 'quickview' );
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
		$can_buy   = $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock();

		// Build the gallery: featured image + gallery images.
		$image_ids = array();
		if ( $product->get_image_id() ) {
			$image_ids[] = $product->get_image_id();
		}
		$image_ids = array_merge( $image_ids, $product->get_gallery_image_ids() );
		$image_ids = array_values( array_unique( array_filter( $image_ids ) ) );
		?>
		<div class="ziteh-qv">
			<div class="ziteh-qv__media">
				<div class="ziteh-qv__gallery" data-ziteh-qv-gallery>
					<div class="ziteh-qv__stage">
						<?php
						if ( ! empty( $image_ids ) ) {
							$i = 0;
							foreach ( $image_ids as $img_id ) {
								printf(
									'<div class="ziteh-qv__slide%s" data-qv-slide="%d">%s</div>',
									0 === $i ? ' is-active' : '',
									(int) $i,
									wp_get_attachment_image( $img_id, 'woocommerce_single' ) // phpcs:ignore WordPress.Security.EscapeOutput
								);
								$i++;
							}
						} else {
							echo $product->get_image( 'woocommerce_single' ); // phpcs:ignore WordPress.Security.EscapeOutput
						}
						?>
					</div>
					<?php if ( count( $image_ids ) > 1 ) : ?>
						<div class="ziteh-qv__thumbs">
							<?php
							$i = 0;
							foreach ( $image_ids as $img_id ) {
								printf(
									'<button type="button" class="ziteh-qv__thumb%s" data-qv-thumb="%d">%s</button>',
									0 === $i ? ' is-active' : '',
									(int) $i,
									wp_get_attachment_image( $img_id, 'thumbnail' ) // phpcs:ignore WordPress.Security.EscapeOutput
								);
								$i++;
							}
							?>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<div class="ziteh-qv__info">
				<h3 class="ziteh-qv__title"><a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h3>
				<div class="ziteh-qv__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>

				<?php if ( $product->get_average_rating() > 0 ) : ?>
					<div class="ziteh-qv__rating"><?php echo wp_kses_post( wc_get_rating_html( $product->get_average_rating() ) ); ?></div>
				<?php endif; ?>

				<div class="ziteh-qv__excerpt"><?php echo wp_kses_post( wpautop( $product->get_short_description() ) ); ?></div>

				<div class="ziteh-qv__actions">
					<?php if ( $can_buy ) : ?>
						<div class="ziteh-qty" data-ziteh-qty>
							<button type="button" class="ziteh-qty__btn" data-qty-minus aria-label="<?php esc_attr_e( 'کاهش', 'ziteh' ); ?>">−</button>
							<input type="number" class="ziteh-qty__input" data-qty-input value="1" min="1" step="1" inputmode="numeric" aria-label="<?php esc_attr_e( 'تعداد', 'ziteh' ); ?>">
							<button type="button" class="ziteh-qty__btn" data-qty-plus aria-label="<?php esc_attr_e( 'افزایش', 'ziteh' ); ?>">+</button>
						</div>
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
		$this->require_feature( 'live_search' );
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
				'name'   => $term,
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
	 * Quiz recommendations: return product cards for the chosen categories.
	 */
	public function quiz_products() {
		$this->require_feature( 'quiz_products' );
		$this->check_nonce();

		if ( ! $this->wc() || ! function_exists( 'wc_get_products' ) ) {
			wp_send_json_success( array( 'html' => '' ) );
		}

		$raw_cats = isset( $_REQUEST['cats'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['cats'] ) ) : '';
		$fallback = isset( $_REQUEST['fallback'] ) ? sanitize_title( wp_unslash( $_REQUEST['fallback'] ) ) : '';
		$count    = isset( $_REQUEST['count'] ) ? absint( $_REQUEST['count'] ) : 4;
		$count    = max( 1, min( 12, $count ) );

		$cats = array_filter( array_map( 'sanitize_title', explode( ',', $raw_cats ) ) );
		$cats = array_values( array_unique( $cats ) );

		$args = array(
			'status'   => 'publish',
			'limit'    => $count,
			'orderby'  => 'popularity',
			'order'    => 'DESC',
			'paginate' => false,
		);
		if ( ! empty( $cats ) ) {
			$args['category'] = $cats;
		} elseif ( $fallback ) {
			$args['category'] = array( $fallback );
		}

		$products = wc_get_products( $args );

		// If a category filter returned nothing, fall back to best-sellers.
		if ( empty( $products ) && ( ! empty( $cats ) || $fallback ) ) {
			$products = wc_get_products(
				array(
					'status'  => 'publish',
					'limit'   => $count,
					'orderby' => 'popularity',
					'order'   => 'DESC',
				)
			);
		}

		ob_start();
		foreach ( $products as $product ) {
			$this->render_mini_product_card( $product );
		}
		wp_send_json_success( array( 'html' => ob_get_clean() ) );
	}

	/**
	 * Compact product card (same classes as the products widget) for AJAX use.
	 *
	 * @param \WC_Product $product Product.
	 */
	private function render_mini_product_card( $product ) {
		$id        = $product->get_id();
		$permalink = get_permalink( $id );
		$ajax      = $product->supports( 'ajax_add_to_cart' ) && $product->is_purchasable() && $product->is_in_stock();
		?>
		<div class="ziteh-product-card" data-ziteh-product="<?php echo esc_attr( $id ); ?>">
			<div class="ziteh-product-card__media">
				<button class="ziteh-product-card__wish" type="button" data-ziteh-wish="<?php echo esc_attr( $id ); ?>" aria-label="<?php esc_attr_e( 'علاقه‌مندی', 'ziteh' ); ?>">
					<?php echo $this->heart_svg(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</button>
				<a class="ziteh-product-card__thumb" href="<?php echo esc_url( $permalink ); ?>"><?php echo $product->get_image( 'woocommerce_thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
			</div>
			<div class="ziteh-product-card__body">
				<a class="ziteh-product-card__name" href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
				<div class="ziteh-product-card__foot">
					<span class="ziteh-product-card__price ziteh-product-card__price--wc"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
					<a class="ziteh-product-card__add<?php echo $ajax ? ' add_to_cart_button ajax_add_to_cart' : ''; ?>"
						href="<?php echo esc_url( $product->add_to_cart_url() ); ?>"
						data-quantity="1" data-product_id="<?php echo esc_attr( $id ); ?>"
						aria-label="<?php echo esc_attr( $product->add_to_cart_text() ); ?>" rel="nofollow">
						<?php echo $this->cart_svg(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</a>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Minimal heart icon (independent of the widget base).
	 *
	 * @return string
	 */
	private function heart_svg() {
		return '<svg class="ziteh-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-4.6-9.3-9C1 9 2.5 5.5 6 5.5c2 0 3.2 1.2 4 2.4.8-1.2 2-2.4 4-2.4 3.5 0 5 3.5 3.3 6.5C19 16.4 12 21 12 21z"/></svg>';
	}

	/**
	 * Minimal cart icon.
	 *
	 * @return string
	 */
	private function cart_svg() {
		return '<svg class="ziteh-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 6h15l-1.5 9h-12z"/><circle cx="9" cy="20" r="1.6"/><circle cx="18" cy="20" r="1.6"/><path d="M6 6 5 3H2"/></svg>';
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
