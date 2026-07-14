<?php
/**
 * Products widget — "محصولات منتخب": section title with a "مشاهده همه" link,
 * side arrows and a slider of product cards (wishlist heart, image, brand,
 * name, price and an add-to-cart button).
 *
 * Two sources:
 *   - manual: hand-authored cards (repeater).
 *   - woocommerce: real products pulled live from the store, with options for
 *     ordering, count and category. Falls back to manual if WooCommerce is not
 *     active.
 *
 * @package Ziteh_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * Class Ziteh_Products_Widget
 */
class Ziteh_Products_Widget extends Ziteh_Widget_Base {

	/**
	 * @return string
	 */
	public function get_name() {
		return 'ziteh-products';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'زیته | محصولات منتخب', 'ziteh' );
	}

	/**
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-products';
	}

	/**
	 * Whether WooCommerce is available for dynamic queries.
	 *
	 * @return bool
	 */
	private function has_woocommerce() {
		return class_exists( 'WooCommerce' ) && function_exists( 'wc_get_products' );
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
				'default' => esc_html__( 'محصولات منتخب', 'ziteh' ),
			)
		);

		$this->add_control(
			'view_all_text',
			array(
				'label'   => esc_html__( 'متن مشاهده همه', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'مشاهده همه', 'ziteh' ),
			)
		);

		$this->add_control(
			'view_all_url',
			array(
				'label'         => esc_html__( 'لینک مشاهده همه', 'ziteh' ),
				'type'          => Controls_Manager::URL,
				'default'       => array( 'url' => '#' ),
				'show_external' => false,
			)
		);

		$this->end_controls_section();

		// Source.
		$this->start_controls_section(
			'section_source',
			array(
				'label' => esc_html__( 'منبع محصولات', 'ziteh' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'source',
			array(
				'label'   => esc_html__( 'منبع', 'ziteh' ),
				'type'    => Controls_Manager::SELECT,
				'default' => $this->has_woocommerce() ? 'woocommerce' : 'manual',
				'options' => array(
					'woocommerce' => esc_html__( 'ووکامرس (داینامیک)', 'ziteh' ),
					'manual'      => esc_html__( 'دستی', 'ziteh' ),
				),
			)
		);

		if ( ! $this->has_woocommerce() ) {
			$this->add_control(
				'wc_missing_notice',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => esc_html__( 'ووکامرس فعال نیست؛ در صورت انتخاب حالت داینامیک، محتوای دستی نمایش داده می‌شود.', 'ziteh' ),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
					'condition'       => array( 'source' => 'woocommerce' ),
				)
			);
		}

		$this->add_control(
			'wc_count',
			array(
				'label'     => esc_html__( 'تعداد محصولات', 'ziteh' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 24,
				'default'   => 10,
				'condition' => array( 'source' => 'woocommerce' ),
			)
		);

		$this->add_control(
			'wc_orderby',
			array(
				'label'     => esc_html__( 'مرتب‌سازی بر اساس', 'ziteh' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'date',
				'options'   => array(
					'date'       => esc_html__( 'جدیدترین', 'ziteh' ),
					'popularity' => esc_html__( 'پرفروش‌ترین', 'ziteh' ),
					'rating'     => esc_html__( 'بیشترین امتیاز', 'ziteh' ),
					'price'      => esc_html__( 'قیمت (صعودی)', 'ziteh' ),
					'price-desc' => esc_html__( 'قیمت (نزولی)', 'ziteh' ),
					'title'      => esc_html__( 'عنوان', 'ziteh' ),
					'rand'       => esc_html__( 'تصادفی', 'ziteh' ),
				),
				'condition' => array( 'source' => 'woocommerce' ),
			)
		);

		$this->add_control(
			'wc_filter',
			array(
				'label'     => esc_html__( 'فیلتر ویژه', 'ziteh' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'none',
				'options'   => array(
					'none'      => esc_html__( 'همه محصولات', 'ziteh' ),
					'featured'  => esc_html__( 'فقط محصولات ویژه', 'ziteh' ),
					'on_sale'   => esc_html__( 'فقط تخفیف‌دار', 'ziteh' ),
					'in_stock'  => esc_html__( 'فقط موجود', 'ziteh' ),
				),
				'condition' => array( 'source' => 'woocommerce' ),
			)
		);

		$this->add_control(
			'wc_category',
			array(
				'label'       => esc_html__( 'دسته‌بندی محصول (اسلاگ)', 'ziteh' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => $this->get_product_categories(),
				'default'     => array(),
				'description' => esc_html__( 'خالی بگذارید تا از همه دسته‌ها نمایش داده شود.', 'ziteh' ),
				'condition'   => array( 'source' => 'woocommerce' ),
			)
		);

		$this->add_control(
			'wc_show_cart',
			array(
				'label'        => esc_html__( 'نمایش دکمه افزودن به سبد', 'ziteh' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => array( 'source' => 'woocommerce' ),
			)
		);

		$this->end_controls_section();

		// Manual products.
		$this->start_controls_section(
			'section_products',
			array(
				'label'     => esc_html__( 'محصولات (دستی)', 'ziteh' ),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'condition' => array( 'source' => 'manual' ),
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'p_image',
			array(
				'label'   => esc_html__( 'تصویر', 'ziteh' ),
				'type'    => Controls_Manager::MEDIA,
				'default' => array(
					'url' => \Elementor\Utils::get_placeholder_image_src(),
				),
			)
		);

		$repeater->add_control(
			'p_brand',
			array(
				'label'   => esc_html__( 'برند', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'برند', 'ziteh' ),
			)
		);

		$repeater->add_control(
			'p_name',
			array(
				'label'   => esc_html__( 'نام محصول', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'نام محصول', 'ziteh' ),
			)
		);

		$repeater->add_control(
			'p_price',
			array(
				'label'   => esc_html__( 'قیمت', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( '۱۹۰,۰۰۰', 'ziteh' ),
			)
		);

		$repeater->add_control(
			'p_currency',
			array(
				'label'   => esc_html__( 'واحد پول', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'تومان', 'ziteh' ),
			)
		);

		$repeater->add_control(
			'p_button',
			array(
				'label'   => esc_html__( 'متن دکمه', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'افزودن به سبد', 'ziteh' ),
			)
		);

		$repeater->add_control(
			'p_url',
			array(
				'label'         => esc_html__( 'لینک محصول', 'ziteh' ),
				'type'          => Controls_Manager::URL,
				'default'       => array( 'url' => '#' ),
				'show_external' => false,
			)
		);

		$this->add_control(
			'products',
			array(
				'label'       => esc_html__( 'محصولات', 'ziteh' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ p_name }}}',
				'default'     => array(
					array(
						'p_brand' => 'The Ordinary',
						'p_name'  => esc_html__( 'سرم هیالورونیک اسید', 'ziteh' ),
						'p_price' => esc_html__( '۱٬۲۹۰٬۰۰۰', 'ziteh' ),
					),
					array(
						'p_brand' => 'CeraVe',
						'p_name'  => esc_html__( 'کرم مرطوب‌کننده', 'ziteh' ),
						'p_price' => esc_html__( '۱٬۵۵۰٬۰۰۰', 'ziteh' ),
					),
					array(
						'p_brand' => 'La Roche-Posay',
						'p_name'  => esc_html__( 'ژل شست‌وشوی صورت', 'ziteh' ),
						'p_price' => esc_html__( '۹۵۰٬۰۰۰', 'ziteh' ),
					),
					array(
						'p_brand' => 'COSRX',
						'p_name'  => esc_html__( 'آبرسان مغذی خنزون', 'ziteh' ),
						'p_price' => esc_html__( '۱٬۱۸۰٬۰۰۰', 'ziteh' ),
					),
					array(
						'p_brand' => 'ZARR',
						'p_name'  => esc_html__( 'عطر زنانه', 'ziteh' ),
						'p_price' => esc_html__( '۱٬۹۵۰٬۰۰۰', 'ziteh' ),
					),
				),
			)
		);

		$this->end_controls_section();

		// Slider settings.
		$this->start_controls_section(
			'section_settings',
			array(
				'label' => esc_html__( 'تنظیمات اسلایدر', 'ziteh' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_responsive_control(
			'per_view',
			array(
				'label'          => esc_html__( 'تعداد نمایش', 'ziteh' ),
				'type'           => Controls_Manager::NUMBER,
				'min'            => 1,
				'max'            => 6,
				'default'        => 5,
				'tablet_default' => 3,
				'mobile_default' => 2,
				'selectors'      => array(
					'{{WRAPPER}} .ziteh-products__track' => '--ziteh-per-view: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Build a slug => name map of product categories for the SELECT2 control.
	 *
	 * @return array<string,string>
	 */
	private function get_product_categories() {
		$options = array();
		if ( ! taxonomy_exists( 'product_cat' ) ) {
			return $options;
		}
		$terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
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
		$view_all_url = ! empty( $settings['view_all_url']['url'] ) ? $settings['view_all_url']['url'] : '#';

		// If WooCommerce shop link is unset, point "view all" at the shop.
		if ( ( '#' === $view_all_url || '' === $view_all_url ) && function_exists( 'wc_get_page_permalink' ) ) {
			$shop = wc_get_page_permalink( 'shop' );
			if ( $shop ) {
				$view_all_url = $shop;
			}
		}

		$use_wc = ( 'woocommerce' === $settings['source'] ) && $this->has_woocommerce();
		?>
		<section class="ziteh-products">
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

				<div class="ziteh-slider ziteh-slider--sided" data-ziteh-slider>
					<button class="ziteh-slider__side ziteh-slider__side--prev" type="button" data-ziteh-prev aria-label="<?php esc_attr_e( 'قبلی', 'ziteh' ); ?>">
						<?php echo $this->get_icon_svg( 'arrow-r' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</button>

					<div class="ziteh-products__viewport">
						<ul class="ziteh-products__track" data-ziteh-track>
							<?php
							if ( $use_wc ) {
								$this->render_woocommerce_cards( $settings );
							} else {
								$this->render_manual_cards( $settings );
							}
							?>
						</ul>
					</div>

					<button class="ziteh-slider__side ziteh-slider__side--next" type="button" data-ziteh-next aria-label="<?php esc_attr_e( 'بعدی', 'ziteh' ); ?>">
						<?php echo $this->get_icon_svg( 'arrow-l' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</button>
				</div>

			</div>
		</section>
		<?php
	}

	/**
	 * Render manually-authored product cards.
	 *
	 * @param array $settings Widget settings.
	 */
	private function render_manual_cards( $settings ) {
		if ( empty( $settings['products'] ) ) {
			return;
		}
		foreach ( $settings['products'] as $p ) {
			$url = ! empty( $p['p_url']['url'] ) ? $p['p_url']['url'] : '#';
			?>
			<li class="ziteh-product-card">
				<div class="ziteh-product-card__media">
					<button class="ziteh-product-card__wish" type="button" aria-label="<?php esc_attr_e( 'افزودن به علاقه‌مندی‌ها', 'ziteh' ); ?>">
						<?php echo $this->get_icon_svg( 'heart' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</button>
					<a class="ziteh-product-card__thumb" href="<?php echo esc_url( $url ); ?>">
						<?php if ( ! empty( $p['p_image']['url'] ) ) : ?>
							<img src="<?php echo esc_url( $p['p_image']['url'] ); ?>" alt="<?php echo esc_attr( $p['p_name'] ); ?>">
						<?php endif; ?>
					</a>
				</div>
				<div class="ziteh-product-card__body">
					<span class="ziteh-product-card__brand"><?php echo esc_html( $p['p_brand'] ); ?></span>
					<a class="ziteh-product-card__name" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $p['p_name'] ); ?></a>
					<div class="ziteh-product-card__foot">
						<span class="ziteh-product-card__price">
							<strong><?php echo esc_html( $p['p_price'] ); ?></strong>
							<em><?php echo esc_html( $p['p_currency'] ); ?></em>
						</span>
						<a class="ziteh-product-card__add" href="<?php echo esc_url( $url ); ?>" aria-label="<?php echo esc_attr( $p['p_button'] ); ?>" title="<?php echo esc_attr( $p['p_button'] ); ?>">
							<?php echo $this->get_icon_svg( 'cart' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</a>
					</div>
				</div>
			</li>
			<?php
		}
	}

	/**
	 * Query WooCommerce and render real product cards.
	 *
	 * @param array $settings Widget settings.
	 */
	private function render_woocommerce_cards( $settings ) {
		$orderby = isset( $settings['wc_orderby'] ) ? $settings['wc_orderby'] : 'date';
		$order   = 'ASC';

		// Map friendly orderby values onto wc_get_products arguments.
		switch ( $orderby ) {
			case 'price':
				$orderby = 'price';
				$order   = 'ASC';
				break;
			case 'price-desc':
				$orderby = 'price';
				$order   = 'DESC';
				break;
			case 'title':
				$orderby = 'title';
				$order   = 'ASC';
				break;
			case 'popularity':
				$orderby = 'popularity';
				$order   = 'DESC';
				break;
			case 'rating':
				$orderby = 'rating';
				$order   = 'DESC';
				break;
			case 'rand':
				$orderby = 'rand';
				break;
			case 'date':
			default:
				$orderby = 'date';
				$order   = 'DESC';
				break;
		}

		$args = array(
			'status'   => 'publish',
			'limit'    => isset( $settings['wc_count'] ) ? (int) $settings['wc_count'] : 10,
			'orderby'  => $orderby,
			'order'    => $order,
			'paginate' => false,
		);

		$filter = isset( $settings['wc_filter'] ) ? $settings['wc_filter'] : 'none';
		if ( 'featured' === $filter ) {
			$args['featured'] = true;
		} elseif ( 'on_sale' === $filter ) {
			$args['include'] = wc_get_product_ids_on_sale();
			if ( empty( $args['include'] ) ) {
				$args['include'] = array( 0 ); // Force empty result set.
			}
		} elseif ( 'in_stock' === $filter ) {
			$args['stock_status'] = 'instock';
		}

		if ( ! empty( $settings['wc_category'] ) ) {
			$args['category'] = (array) $settings['wc_category']; // slugs.
		}

		$products = wc_get_products( $args );

		if ( empty( $products ) ) {
			echo '<li class="ziteh-products__empty">' . esc_html__( 'محصولی برای نمایش یافت نشد.', 'ziteh' ) . '</li>';
			return;
		}

		$show_cart = ! isset( $settings['wc_show_cart'] ) || 'yes' === $settings['wc_show_cart'];

		foreach ( $products as $product ) {
			$this->render_wc_card( $product, $show_cart );
		}
	}

	/**
	 * Render a single WooCommerce product card.
	 *
	 * @param \WC_Product $product   Product object.
	 * @param bool        $show_cart Whether to show the add-to-cart button.
	 */
	private function render_wc_card( $product, $show_cart ) {
		$permalink = get_permalink( $product->get_id() );
		$name      = $product->get_name();
		$brand     = $this->get_product_brand( $product );
		$image     = $product->get_image( 'woocommerce_thumbnail' );
		$percent   = $this->get_discount_percent( $product );
		?>
		<li class="ziteh-product-card">
			<div class="ziteh-product-card__media">
				<button class="ziteh-product-card__wish" type="button" aria-label="<?php esc_attr_e( 'افزودن به علاقه‌مندی‌ها', 'ziteh' ); ?>">
					<?php echo $this->get_icon_svg( 'heart' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</button>

				<?php if ( $percent > 0 ) : ?>
					<span class="ziteh-product-card__badge ziteh-product-card__badge--off"><?php echo esc_html( sprintf( /* translators: %d discount percent */ __( '%d٪', 'ziteh' ), $percent ) ); ?></span>
				<?php elseif ( $product->is_on_sale() ) : ?>
					<span class="ziteh-product-card__badge ziteh-product-card__badge--off"><?php esc_html_e( 'تخفیف', 'ziteh' ); ?></span>
				<?php endif; ?>

				<a class="ziteh-product-card__thumb" href="<?php echo esc_url( $permalink ); ?>">
					<?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</a>
			</div>
			<div class="ziteh-product-card__body">
				<?php if ( $brand ) : ?>
					<span class="ziteh-product-card__brand"><?php echo esc_html( $brand ); ?></span>
				<?php endif; ?>
				<a class="ziteh-product-card__name" href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $name ); ?></a>
				<div class="ziteh-product-card__foot">
					<?php $this->render_wc_price( $product ); ?>
					<?php if ( $show_cart ) : ?>
						<?php
						$cart_url  = $product->add_to_cart_url();
						$cart_text = $product->add_to_cart_text();
						$ajax_cls  = $product->supports( 'ajax_add_to_cart' ) && $product->is_purchasable() && $product->is_in_stock() ? ' add_to_cart_button ajax_add_to_cart' : '';
						?>
						<a class="ziteh-product-card__add<?php echo esc_attr( $ajax_cls ); ?>"
							href="<?php echo esc_url( $cart_url ); ?>"
							data-quantity="1"
							data-product_id="<?php echo esc_attr( $product->get_id() ); ?>"
							aria-label="<?php echo esc_attr( $cart_text ); ?>"
							title="<?php echo esc_attr( $cart_text ); ?>"
							rel="nofollow">
							<?php echo $this->get_icon_svg( 'cart' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</li>
		<?php
	}

	/**
	 * Discount percentage for a simple product on sale (0 when not applicable).
	 *
	 * @param \WC_Product $product Product object.
	 * @return int
	 */
	private function get_discount_percent( $product ) {
		if ( $product->is_type( 'variable' ) || $product->is_type( 'grouped' ) ) {
			return 0;
		}
		$regular = (float) $product->get_regular_price();
		$active  = '' !== $product->get_price() ? (float) $product->get_price() : 0.0;
		if ( ! $product->is_on_sale() || $regular <= 0 || $active <= 0 || $active >= $regular ) {
			return 0;
		}
		return (int) round( ( ( $regular - $active ) / $regular ) * 100 );
	}

	/**
	 * Render a clean, controlled price block for a product.
	 *
	 * We deliberately build the sale markup ourselves (rather than echoing
	 * WooCommerce's get_price_html()) so theme-injected discount badges and the
	 * inconsistent del/ins ordering can't leak into the card. For variable /
	 * grouped products (price ranges) we fall back to the native price HTML.
	 *
	 * @param \WC_Product $product Product object.
	 */
	private function render_wc_price( $product ) {
		// Price ranges & grouped products: keep WooCommerce's native output.
		if ( $product->is_type( 'variable' ) || $product->is_type( 'grouped' ) ) {
			$html = $product->get_price_html();
			if ( $html ) {
				echo '<span class="ziteh-product-card__price ziteh-product-card__price--wc">' . wp_kses_post( $html ) . '</span>';
			}
			return;
		}

		$regular = (float) $product->get_regular_price();
		$active  = '' !== $product->get_price() ? (float) $product->get_price() : 0.0;
		$on_sale = $product->is_on_sale() && $regular > 0 && $active > 0 && $active < $regular;

		if ( $on_sale ) {
			// The discount percentage is shown as a red badge on the image corner
			// (see render_wc_card); here we only show new price + struck old price.
			echo '<span class="ziteh-product-card__price ziteh-product-card__price--wc is-sale">';
			echo '<ins>' . wp_kses_post( wc_price( $active ) ) . '</ins>';
			echo '<del>' . wp_kses_post( wc_price( $regular ) ) . '</del>';
			echo '</span>';
			return;
		}

		// Not on sale: native price HTML is clean here (handles "free", etc.).
		$html = $product->get_price_html();
		if ( $html ) {
			echo '<span class="ziteh-product-card__price ziteh-product-card__price--wc">' . wp_kses_post( $html ) . '</span>';
		}
	}

	/**
	 * Best-effort "brand" line for a product: a brand taxonomy term if present,
	 * otherwise the first product category name.
	 *
	 * @param \WC_Product $product Product object.
	 * @return string
	 */
	private function get_product_brand( $product ) {
		$id = $product->get_id();

		// Common brand taxonomies used by popular plugins.
		foreach ( array( 'product_brand', 'pwb-brand', 'yith_product_brand' ) as $tax ) {
			if ( taxonomy_exists( $tax ) ) {
				$terms = get_the_terms( $id, $tax );
				if ( $terms && ! is_wp_error( $terms ) ) {
					return $terms[0]->name;
				}
			}
		}

		// Fallback: first product category.
		$cats = get_the_terms( $id, 'product_cat' );
		if ( $cats && ! is_wp_error( $cats ) ) {
			return $cats[0]->name;
		}

		return '';
	}
}
