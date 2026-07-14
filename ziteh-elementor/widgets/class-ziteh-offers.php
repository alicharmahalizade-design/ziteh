<?php
/**
 * Special offers widget — "پیشنهادهای ویژه": a highlighted section with a
 * live countdown timer and a slider of discounted product cards (dynamic from
 * WooCommerce on-sale products, or manual). Reuses the shared product-card
 * styles so it matches the "محصولات منتخب" section.
 *
 * @package Ziteh_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Repeater;

/**
 * Class Ziteh_Offers_Widget
 */
class Ziteh_Offers_Widget extends Ziteh_Widget_Base {

	/**
	 * @return string
	 */
	public function get_name() {
		return 'ziteh-offers';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'زیته | پیشنهادهای ویژه', 'ziteh' );
	}

	/**
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-countdown';
	}

	/**
	 * WooCommerce availability check.
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
				'label'   => esc_html__( 'عنوان', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'پیشنهادهای ویژه', 'ziteh' ),
			)
		);

		$this->add_control(
			'subtitle',
			array(
				'label'   => esc_html__( 'زیرعنوان', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'تخفیف‌های شگفت‌انگیز، تا پایان شمارش معکوس!', 'ziteh' ),
			)
		);

		$this->add_control(
			'end_time',
			array(
				'label'       => esc_html__( 'پایان شمارش معکوس', 'ziteh' ),
				'type'        => Controls_Manager::DATE_TIME,
				'default'     => '',
				'description' => esc_html__( 'اگر خالی بماند، شمارش تا پایان امشب انجام می‌شود.', 'ziteh' ),
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
					'woocommerce' => esc_html__( 'ووکامرس - محصولات تخفیف‌دار', 'ziteh' ),
					'manual'      => esc_html__( 'دستی', 'ziteh' ),
				),
			)
		);

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
				'default' => array( 'url' => \Elementor\Utils::get_placeholder_image_src() ),
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
			'p_regular',
			array(
				'label'   => esc_html__( 'قیمت اصلی', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( '۵۰۰٬۰۰۰', 'ziteh' ),
			)
		);

		$repeater->add_control(
			'p_sale',
			array(
				'label'   => esc_html__( 'قیمت با تخفیف', 'ziteh' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( '۳۵۰٬۰۰۰', 'ziteh' ),
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
						'p_brand'   => 'CeraVe',
						'p_name'    => esc_html__( 'کرم مرطوب‌کننده صورت', 'ziteh' ),
						'p_regular' => esc_html__( '۱٬۵۵۰٬۰۰۰', 'ziteh' ),
						'p_sale'    => esc_html__( '۱٬۱۰۰٬۰۰۰', 'ziteh' ),
					),
					array(
						'p_brand'   => 'The Ordinary',
						'p_name'    => esc_html__( 'سرم نیاسیناماید ۱۰٪', 'ziteh' ),
						'p_regular' => esc_html__( '۹۸۰٬۰۰۰', 'ziteh' ),
						'p_sale'    => esc_html__( '۶۹۰٬۰۰۰', 'ziteh' ),
					),
					array(
						'p_brand'   => 'La Roche-Posay',
						'p_name'    => esc_html__( 'ژل شست‌وشوی صورت', 'ziteh' ),
						'p_regular' => esc_html__( '۹۵۰٬۰۰۰', 'ziteh' ),
						'p_sale'    => esc_html__( '۷۱۰٬۰۰۰', 'ziteh' ),
					),
					array(
						'p_brand'   => 'COSRX',
						'p_name'    => esc_html__( 'ماسک صورت آبرسان', 'ziteh' ),
						'p_regular' => esc_html__( '۴۹۵٬۰۰۰', 'ziteh' ),
						'p_sale'    => esc_html__( '۳۴۰٬۰۰۰', 'ziteh' ),
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
	 * Render the front-end output.
	 */
	protected function render() {
		$settings     = $this->get_settings_for_display();
		$view_all_url = ! empty( $settings['view_all_url']['url'] ) ? $settings['view_all_url']['url'] : '#';

		if ( ( '#' === $view_all_url || '' === $view_all_url ) && function_exists( 'wc_get_page_permalink' ) ) {
			$shop = wc_get_page_permalink( 'shop' );
			if ( $shop ) {
				$view_all_url = $shop;
			}
		}

		$use_wc = ( 'woocommerce' === $settings['source'] ) && $this->has_woocommerce();

		// End timestamp (ms). Empty → end of today (local server time).
		$end = $settings['end_time'];
		if ( ! empty( $end ) ) {
			$end_ts = strtotime( $end );
		} else {
			$end_ts = strtotime( 'tomorrow 00:00:00' );
		}
		$end_iso = $end_ts ? gmdate( 'Y-m-d\TH:i:s', $end_ts - (int) ( get_option( 'gmt_offset' ) * 3600 ) ) : '';
		?>
		<section class="ziteh-offers">
			<div class="ziteh-container">

				<div class="ziteh-offers__head">
					<div class="ziteh-offers__intro">
						<h2 class="ziteh-section-title ziteh-section-title--start ziteh-offers__title">
							<?php echo $this->get_icon_svg( 'leaf', 'ziteh-section-title__leaf' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<?php echo esc_html( $settings['title'] ); ?>
						</h2>
						<?php if ( ! empty( $settings['subtitle'] ) ) : ?>
							<p class="ziteh-offers__subtitle"><?php echo esc_html( $settings['subtitle'] ); ?></p>
						<?php endif; ?>
					</div>

					<div class="ziteh-offers__timer" data-ziteh-countdown data-end="<?php echo esc_attr( $end_iso ); ?>">
						<?php
						$units = array(
							'd' => __( 'روز', 'ziteh' ),
							'h' => __( 'ساعت', 'ziteh' ),
							'm' => __( 'دقیقه', 'ziteh' ),
							's' => __( 'ثانیه', 'ziteh' ),
						);
						$first = true;
						foreach ( $units as $key => $label ) :
							if ( ! $first ) :
								?>
								<span class="ziteh-cd__sep">:</span>
								<?php
							endif;
							$first = false;
							?>
							<span class="ziteh-cd">
								<span class="ziteh-cd__num" data-cd="<?php echo esc_attr( $key ); ?>">۰۰</span>
								<span class="ziteh-cd__label"><?php echo esc_html( $label ); ?></span>
							</span>
						<?php endforeach; ?>
					</div>

					<a class="ziteh-link ziteh-link--muted ziteh-offers__all" href="<?php echo esc_url( $view_all_url ); ?>">
						<?php echo esc_html( $settings['view_all_text'] ); ?>
						<?php echo $this->get_icon_svg( 'arrow-l' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</a>
				</div>

				<div class="ziteh-slider ziteh-slider--sided" data-ziteh-slider>
					<button class="ziteh-slider__side ziteh-slider__side--prev" type="button" data-ziteh-prev aria-label="<?php esc_attr_e( 'قبلی', 'ziteh' ); ?>">
						<?php echo $this->get_icon_svg( 'arrow-r' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</button>

					<div class="ziteh-products__viewport">
						<ul class="ziteh-products__track" data-ziteh-track>
							<?php
							if ( $use_wc ) {
								$this->render_wc_offers( $settings );
							} else {
								$this->render_manual_offers( $settings );
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
	 * Shared product-card renderer (same markup/classes as the products widget).
	 *
	 * @param array $data {
	 *     url, image_html, brand, name, percent, price_html,
	 *     add_url, add_label, add_attrs (raw string), show_cart (bool)
	 * }
	 */
	private function render_card( $data ) {
		?>
		<li class="ziteh-product-card"<?php echo ! empty( $data['id'] ) ? ' data-ziteh-product="' . esc_attr( $data['id'] ) . '"' : ''; ?>>
			<div class="ziteh-product-card__media">
				<button class="ziteh-product-card__wish" type="button"<?php echo ! empty( $data['id'] ) ? ' data-ziteh-wish="' . esc_attr( $data['id'] ) . '"' : ''; ?> aria-label="<?php esc_attr_e( 'افزودن به علاقه‌مندی‌ها', 'ziteh' ); ?>">
					<?php echo $this->get_icon_svg( 'heart' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</button>
				<?php if ( ! empty( $data['percent'] ) && $data['percent'] > 0 ) : ?>
					<span class="ziteh-product-card__badge ziteh-product-card__badge--off"><?php echo esc_html( sprintf( /* translators: %d percent */ __( '%d٪', 'ziteh' ), $data['percent'] ) ); ?></span>
				<?php endif; ?>
				<a class="ziteh-product-card__thumb" href="<?php echo esc_url( $data['url'] ); ?>">
					<?php echo $data['image_html']; // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</a>
				<?php if ( ! empty( $data['id'] ) ) : ?>
					<button class="ziteh-product-card__quick" type="button" data-ziteh-quickview="<?php echo esc_attr( $data['id'] ); ?>">
						<?php echo $this->get_icon_svg( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<span><?php esc_html_e( 'مشاهده سریع', 'ziteh' ); ?></span>
					</button>
				<?php endif; ?>
			</div>
			<div class="ziteh-product-card__body">
				<?php if ( ! empty( $data['brand'] ) ) : ?>
					<span class="ziteh-product-card__brand"><?php echo esc_html( $data['brand'] ); ?></span>
				<?php endif; ?>
				<a class="ziteh-product-card__name" href="<?php echo esc_url( $data['url'] ); ?>"><?php echo esc_html( $data['name'] ); ?></a>
				<?php if ( isset( $data['progress'] ) && $data['progress'] >= 0 ) : ?>
					<div class="ziteh-sold" title="<?php echo esc_attr( sprintf( /* translators: %d percent */ __( '%d درصد فروخته شده', 'ziteh' ), $data['progress'] ) ); ?>">
						<div class="ziteh-sold__bar"><span style="width:<?php echo esc_attr( max( 6, $data['progress'] ) ); ?>%"></span></div>
						<span class="ziteh-sold__label"><?php echo esc_html( sprintf( /* translators: %d percent sold */ __( '%d٪ فروخته شد', 'ziteh' ), $data['progress'] ) ); ?></span>
					</div>
				<?php endif; ?>
				<div class="ziteh-product-card__foot">
					<?php echo $data['price_html']; // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php if ( ! empty( $data['show_cart'] ) ) : ?>
						<a class="ziteh-product-card__add<?php echo isset( $data['add_class'] ) ? esc_attr( $data['add_class'] ) : ''; ?>" href="<?php echo esc_url( $data['add_url'] ); ?>" aria-label="<?php echo esc_attr( $data['add_label'] ); ?>" title="<?php echo esc_attr( $data['add_label'] ); ?>" <?php echo isset( $data['add_attrs'] ) ? $data['add_attrs'] : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
							<?php echo $this->get_icon_svg( 'cart' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</li>
		<?php
	}

	/**
	 * Build a clean sale price block (new price + struck old price).
	 *
	 * @param string $new_html wc_price() / formatted new price HTML.
	 * @param string $old_html wc_price() / formatted old price HTML (may be empty).
	 * @return string
	 */
	private function price_block( $new_html, $old_html = '' ) {
		$out = '<span class="ziteh-product-card__price ziteh-product-card__price--wc is-sale">';
		$out .= '<ins>' . $new_html . '</ins>';
		if ( $old_html ) {
			$out .= '<del>' . $old_html . '</del>';
		}
		$out .= '</span>';
		return $out;
	}

	/**
	 * Render manual offer cards.
	 *
	 * @param array $settings Widget settings.
	 */
	private function render_manual_offers( $settings ) {
		if ( empty( $settings['products'] ) ) {
			return;
		}
		foreach ( $settings['products'] as $p ) {
			$url      = ! empty( $p['p_url']['url'] ) ? $p['p_url']['url'] : '#';
			$currency = isset( $p['p_currency'] ) ? $p['p_currency'] : '';
			$regular  = isset( $p['p_regular'] ) ? $p['p_regular'] : '';
			$sale     = isset( $p['p_sale'] ) ? $p['p_sale'] : '';

			$new_html = '<span class="ziteh-amount">' . esc_html( $sale ? $sale : $regular ) . ' <em>' . esc_html( $currency ) . '</em></span>';
			$old_html = ( $sale && $regular ) ? '<span class="ziteh-amount">' . esc_html( $regular ) . ' <em>' . esc_html( $currency ) . '</em></span>' : '';

			$image_html = ! empty( $p['p_image']['url'] )
				? '<img src="' . esc_url( $p['p_image']['url'] ) . '" alt="' . esc_attr( $p['p_name'] ) . '">'
				: '';

			$this->render_card(
				array(
					'url'        => $url,
					'image_html' => $image_html,
					'brand'      => isset( $p['p_brand'] ) ? $p['p_brand'] : '',
					'name'       => isset( $p['p_name'] ) ? $p['p_name'] : '',
					'percent'    => $this->manual_percent( $regular, $sale ),
					'price_html' => $this->price_block( $new_html, $old_html ),
					'show_cart'  => true,
					'add_url'    => $url,
					'add_label'  => __( 'افزودن به سبد', 'ziteh' ),
				)
			);
		}
	}

	/**
	 * Compute a discount percent from two formatted price strings (manual mode).
	 * Digits may be Persian; we normalise before comparing.
	 *
	 * @param string $regular Regular price text.
	 * @param string $sale    Sale price text.
	 * @return int
	 */
	private function manual_percent( $regular, $sale ) {
		$r = (float) $this->digits_to_en( $regular );
		$s = (float) $this->digits_to_en( $sale );
		if ( $r <= 0 || $s <= 0 || $s >= $r ) {
			return 0;
		}
		return (int) round( ( ( $r - $s ) / $r ) * 100 );
	}

	/**
	 * Normalise Persian/Arabic digits and strip separators for numeric parsing.
	 *
	 * @param string $str Input.
	 * @return string ASCII digits only.
	 */
	private function digits_to_en( $str ) {
		$fa = array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩' );
		$en = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );
		$str = str_replace( $fa, $en, (string) $str );
		return preg_replace( '/[^0-9.]/', '', $str );
	}

	/**
	 * Query WooCommerce on-sale products and render offer cards.
	 *
	 * @param array $settings Widget settings.
	 */
	private function render_wc_offers( $settings ) {
		$on_sale_ids = wc_get_product_ids_on_sale();

		if ( empty( $on_sale_ids ) ) {
			echo '<li class="ziteh-products__empty">' . esc_html__( 'در حال حاضر پیشنهاد تخفیفی موجود نیست.', 'ziteh' ) . '</li>';
			return;
		}

		$products = wc_get_products(
			array(
				'status'  => 'publish',
				'limit'   => isset( $settings['wc_count'] ) ? (int) $settings['wc_count'] : 10,
				'include' => $on_sale_ids,
				'orderby' => 'date',
				'order'   => 'DESC',
			)
		);

		if ( empty( $products ) ) {
			echo '<li class="ziteh-products__empty">' . esc_html__( 'در حال حاضر پیشنهاد تخفیفی موجود نیست.', 'ziteh' ) . '</li>';
			return;
		}

		$show_cart = ! isset( $settings['wc_show_cart'] ) || 'yes' === $settings['wc_show_cart'];

		foreach ( $products as $product ) {
			$regular = (float) $product->get_regular_price();
			$active  = '' !== $product->get_price() ? (float) $product->get_price() : 0.0;
			$percent = ( $regular > 0 && $active > 0 && $active < $regular ) ? (int) round( ( ( $regular - $active ) / $regular ) * 100 ) : 0;

			$new_html = wc_price( $active > 0 ? $active : $regular );
			$old_html = ( $regular > 0 && $active > 0 && $active < $regular ) ? wc_price( $regular ) : '';

			$add_class = '';
			$add_attrs = '';
			if ( $show_cart ) {
				$ajax      = $product->supports( 'ajax_add_to_cart' ) && $product->is_purchasable() && $product->is_in_stock();
				$add_class = $ajax ? ' add_to_cart_button ajax_add_to_cart' : '';
				$add_attrs = 'data-quantity="1" data-product_id="' . esc_attr( $product->get_id() ) . '" rel="nofollow"';
			}

			$this->render_card(
				array(
					'id'         => $product->get_id(),
					'url'        => get_permalink( $product->get_id() ),
					'image_html' => $product->get_image( 'woocommerce_thumbnail' ),
					'brand'      => $this->get_offer_brand( $product ),
					'name'       => $product->get_name(),
					'percent'    => $percent,
					'progress'   => $this->get_sold_progress( $product ),
					'price_html' => $this->price_block( $new_html, $old_html ),
					'show_cart'  => $show_cart,
					'add_url'    => $product->add_to_cart_url(),
					'add_label'  => $product->add_to_cart_text(),
					'add_class'  => $add_class,
					'add_attrs'  => $add_attrs,
				)
			);
		}
	}

	/**
	 * A "sold" progress percentage for the urgency bar.
	 *
	 * Uses total_sales vs. remaining stock when stock is managed; otherwise
	 * derives a stable pseudo value from sales so the bar always shows movement.
	 *
	 * @param \WC_Product $product Product object.
	 * @return int 0-100
	 */
	private function get_sold_progress( $product ) {
		$sold = (int) $product->get_total_sales();

		if ( $product->managing_stock() ) {
			$stock = max( 0, (int) $product->get_stock_quantity() );
			$total = $sold + $stock;
			if ( $total > 0 ) {
				return (int) min( 99, max( 5, round( ( $sold / $total ) * 100 ) ) );
			}
		}

		// No managed stock: map sales onto a friendly 40-90% band.
		if ( $sold <= 0 ) {
			return 45;
		}
		return (int) min( 90, 45 + ( $sold % 46 ) );
	}

	/**
	 * First brand/category term for a product, used as the card eyebrow.
	 *
	 * @param \WC_Product $product Product object.
	 * @return string
	 */
	private function get_offer_brand( $product ) {
		$id = $product->get_id();
		foreach ( array( 'product_brand', 'pwb-brand', 'yith_product_brand' ) as $tax ) {
			if ( taxonomy_exists( $tax ) ) {
				$terms = get_the_terms( $id, $tax );
				if ( $terms && ! is_wp_error( $terms ) ) {
					return $terms[0]->name;
				}
			}
		}
		$cats = get_the_terms( $id, 'product_cat' );
		if ( $cats && ! is_wp_error( $cats ) ) {
			return $cats[0]->name;
		}
		return '';
	}
}
