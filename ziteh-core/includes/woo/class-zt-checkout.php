<?php
/**
 * Checkout: settings-driven fields, Iranian validation, custom markup for
 * shipping / payment / summary kept in sync through WooCommerce's own
 * checkout script (update_order_review fragments), thank-you → tracking.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_Checkout
 */
class ZT_Checkout {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_filter( 'woocommerce_checkout_fields', array( __CLASS__, 'fields' ), 50 );
		add_filter( 'woocommerce_billing_fields', array( __CLASS__, 'billing_fields' ), 50 );
		add_filter( 'woocommerce_default_address_fields', array( __CLASS__, 'address_fields' ), 50 );
		add_filter( 'woocommerce_process_checkout_field_billing_phone', array( __CLASS__, 'normalize_digits' ) );
		add_filter( 'woocommerce_process_checkout_field_billing_postcode', array( __CLASS__, 'normalize_digits' ) );
		add_action( 'woocommerce_after_checkout_validation', array( __CLASS__, 'validate' ), 10, 2 );
		add_action( 'woocommerce_checkout_create_order', array( __CLASS__, 'save_custom' ), 10, 2 );
		add_filter( 'woocommerce_update_order_review_fragments', array( __CLASS__, 'fragments' ) );
		add_filter( 'woocommerce_shipping_chosen_method', array( __CLASS__, 'default_method' ), 20, 3 );
		add_action( 'template_redirect', array( __CLASS__, 'thankyou_redirect' ), 5 );
		add_filter( 'woocommerce_ship_to_different_address_checked', '__return_false' );
		add_filter( 'default_checkout_billing_state', '__return_empty_string' );
		add_filter( 'default_checkout_billing_country', array( __CLASS__, 'country' ) );
	}

	/**
	 * Default country.
	 *
	 * @return string
	 */
	public static function country() {
		return 'IR';
	}

	/**
	 * Enabled field definitions from settings.
	 *
	 * @return array
	 */
	public static function defs() {
		$out = array();
		foreach ( (array) zt_opt( 'checkout.fields', array() ) as $f ) {
			if ( empty( $f['enabled'] ) || empty( $f['key'] ) ) {
				continue;
			}
			$key         = sanitize_key( $f['key'] );
			$out[ $key ] = $f;
		}
		return $out;
	}

	/**
	 * Provinces (WooCommerce code => Persian name) and cities.
	 *
	 * @return array [ code => [name, cities[]] ]
	 */
	public static function iran() {
		static $data = null;
		if ( null === $data ) {
			$data   = include ZT_PATH . 'includes/data/iran.php';
			$custom = json_decode( (string) zt_opt( 'checkout.cities_json', '' ), true );
			if ( is_array( $custom ) ) {
				foreach ( $custom as $k => $cities ) {
					$code = isset( $data[ $k ] ) ? $k : self::code_for( $k, $data );
					if ( $code && is_array( $cities ) ) {
						$data[ $code ][1] = array_values( array_map( 'sanitize_text_field', $cities ) );
					}
				}
			}
		}
		return $data;
	}

	/**
	 * Code for a province name.
	 *
	 * @param string $name Name.
	 * @param array  $data Data.
	 * @return string
	 */
	private static function code_for( $name, $data ) {
		foreach ( $data as $code => $p ) {
			if ( $p[0] === $name ) {
				return $code;
			}
		}
		return '';
	}

	/**
	 * Map a settings field to a WooCommerce field.
	 *
	 * @param array $f        Def.
	 * @param int   $priority Priority.
	 * @return array
	 */
	private static function wc_field( $f, $priority ) {
		$type = $f['type'];
		$map  = array(
			'tel'      => 'tel',
			'email'    => 'email',
			'number'   => 'text',
			'postcode' => 'text',
			'state'    => 'state',
			'city'     => 'text',
			'select'   => 'select',
			'textarea' => 'textarea',
		);
		$args = array(
			'label'       => $f['label'],
			'placeholder' => $f['placeholder'],
			'required'    => ! empty( $f['required'] ),
			'type'        => isset( $map[ $type ] ) ? $map[ $type ] : 'text',
			'priority'    => $priority,
			'class'       => array( 'full' === $f['width'] ? 'form-row-wide' : 'form-row-first' ),
		);
		if ( 'select' === $type ) {
			$args['options'] = array( '' => $f['placeholder'] );
			foreach ( zt_lines( $f['options'] ) as $o ) {
				$args['options'][ $o ] = $o;
			}
		}
		if ( 'email' === $type ) {
			$args['validate'] = array( 'email' );
		}
		if ( 'tel' === $type ) {
			$args['validate'] = array( 'phone' );
		}
		if ( 'state' === $type ) {
			$args['country']  = 'IR';
			$args['validate'] = array( 'state' );
		}
		return $args;
	}

	/**
	 * Billing fields = settings.
	 *
	 * @param array $fields Fields.
	 * @return array
	 */
	public static function billing_fields( $fields ) {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return $fields;
		}
		$out = array();
		$p   = 10;
		foreach ( self::defs() as $key => $f ) {
			$out[ $key ] = self::wc_field( $f, $p );
			$p          += 10;
		}
		$out['billing_country'] = array(
			'type'     => 'hidden',
			'default'  => 'IR',
			'required' => false,
			'priority' => 1,
			'label'    => '',
		);
		return $out;
	}

	/**
	 * Checkout fields: billing from settings; shipping follows billing.
	 *
	 * @param array $fields Fields.
	 * @return array
	 */
	public static function fields( $fields ) {
		$fields['billing'] = self::billing_fields( isset( $fields['billing'] ) ? $fields['billing'] : array() );
		$fields['shipping'] = array();
		if ( ! zt_opt( 'checkout.note_enabled', 1 ) ) {
			unset( $fields['order']['order_comments'] );
		}
		return $fields;
	}

	/**
	 * Default address fields tweaks (my-account edit address).
	 *
	 * @param array $f Fields.
	 * @return array
	 */
	public static function address_fields( $f ) {
		if ( isset( $f['last_name'] ) ) {
			$f['last_name']['required'] = false;
		}
		if ( isset( $f['address_2'] ) ) {
			$f['address_2']['required'] = false;
		}
		return $f;
	}

	/**
	 * Persian → Latin digits, strip spaces.
	 *
	 * @param string $v Value.
	 * @return string
	 */
	public static function normalize_digits( $v ) {
		return preg_replace( '/[\s\-]+/', '', zt_en( (string) $v ) );
	}

	/**
	 * Extra validation (mobile, postcode, city).
	 *
	 * @param array    $data   Posted data.
	 * @param WP_Error $errors Errors.
	 */
	public static function validate( $data, $errors ) {
		$defs = self::defs();
		if ( isset( $defs['billing_phone'] ) && zt_opt( 'checkout.validate_mobile', 1 ) && ! empty( $data['billing_phone'] ) ) {
			$m = self::normalize_digits( $data['billing_phone'] );
			$m = preg_replace( '/^(\+98|0098|98)/', '0', $m );
			if ( ! preg_match( '/^09\d{9}$/', $m ) ) {
				$errors->add( 'validation', 'شماره موبایل وارد شده معتبر نیست (مثال: ۰۹۱۲۱۲۳۴۵۶۷).' );
			}
		}
		if ( isset( $defs['billing_postcode'] ) && zt_opt( 'checkout.validate_postcode', 1 ) && ! empty( $data['billing_postcode'] ) ) {
			if ( ! preg_match( '/^\d{10}$/', self::normalize_digits( $data['billing_postcode'] ) ) ) {
				$errors->add( 'validation', 'کد پستی باید ۱۰ رقم باشد.' );
			}
		}
	}

	/**
	 * Save non-billing custom fields on the order.
	 *
	 * @param WC_Order $order Order.
	 * @param array    $data  Data.
	 */
	public static function save_custom( $order, $data ) {
		foreach ( self::defs() as $key => $f ) {
			if ( 0 === strpos( $key, 'billing_' ) ) {
				continue;
			}
			if ( isset( $_POST[ $key ] ) ) { // phpcs:ignore
				$order->update_meta_data( '_' . $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) ); // phpcs:ignore
				$order->update_meta_data( '_zt_label_' . $key, $f['label'] );
			}
		}
		if ( zt_opt( 'checkout.points_enabled', 1 ) ) {
			$order->update_meta_data( '_zt_points', self::points_for( (float) $order->get_total() ) );
		}
	}

	/**
	 * Club points for an amount (store units).
	 *
	 * @param float $total Total.
	 * @return int
	 */
	public static function points_for( $total ) {
		$per = (float) zt_opt( 'checkout.points_per', 10000 );
		return $per > 0 ? (int) floor( zt_amount( $total ) / $per ) : 0;
	}

	/**
	 * Default shipping choice (settings) when none is chosen yet.
	 *
	 * @param string $default         Default.
	 * @param array  $rates           Rates.
	 * @param string $chosen_method   Chosen.
	 * @return string
	 */
	public static function default_method( $default, $rates, $chosen_method ) {
		if ( $chosen_method && isset( $rates[ $chosen_method ] ) ) {
			return $chosen_method;
		}
		$want = 'zt_shipping:' . sanitize_key( zt_opt( 'checkout.shipping_default', 'post' ) );
		return isset( $rates[ $want ] ) ? $want : $default;
	}

	/**
	 * Order received → tracking page.
	 */
	public static function thankyou_redirect() {
		if ( ! zt_opt( 'checkout.thankyou_tracking', 1 ) || ! function_exists( 'is_order_received_page' ) || ! is_order_received_page() ) {
			return;
		}
		$tracking = (int) zt_opt( 'pages.tracking' );
		if ( ! $tracking || ! get_post( $tracking ) ) {
			return;
		}
		global $wp;
		$id    = absint( isset( $wp->query_vars['order-received'] ) ? $wp->query_vars['order-received'] : 0 );
		$key   = isset( $_GET['key'] ) ? sanitize_text_field( wp_unslash( $_GET['key'] ) ) : ''; // phpcs:ignore
		$order = $id ? wc_get_order( $id ) : false;
		if ( ! $order || ! hash_equals( $order->get_order_key(), $key ) ) {
			return;
		}
		wp_safe_redirect(
			add_query_arg(
				array(
					'zt_order' => $id,
					'zt_key'   => $key,
					'zt_new'   => 1,
				),
				get_permalink( $tracking )
			)
		);
		exit;
	}

	/* ------------------------------------------------------------ markup */

	/**
	 * Field grid.
	 *
	 * @param array $o Options: save_label.
	 * @return string
	 */
	public static function render_fields( $o = array() ) {
		$checkout = WC()->checkout();
		$iran     = self::iran();
		$out      = '';
		foreach ( self::defs() as $key => $f ) {
			$val   = $checkout->get_value( $key );
			$req   = ! empty( $f['required'] );
			$label = '<span class="zt-label">' . esc_html( $f['label'] ) . ( $req ? ' <span class="zt-req">*</span>' : '' ) . '</span>';
			$cls   = 'zt-field' . ( 'full' === $f['width'] ? ' zt-full' : '' ) . ' form-row';
			$id    = esc_attr( $key );
			$ph    = esc_attr( $f['placeholder'] );
			switch ( $f['type'] ) {
				case 'state':
					$cls .= ' address-field update_totals_on_change';
					$in   = '<select class="zt-select state_select" name="' . $id . '" id="' . $id . '" data-zt-province' . ( $req ? ' required' : '' ) . '><option value=""' . ( $val ? '' : ' selected' ) . ' disabled>' . $ph . '</option>';
					foreach ( $iran as $code => $p ) {
						$in .= '<option value="' . esc_attr( $code ) . '"' . selected( $val, $code, false ) . '>' . esc_html( $p[0] ) . '</option>';
					}
					$in .= '</select>';
					break;
				case 'city':
					$cls .= ' address-field update_totals_on_change';
					$in   = '<select class="zt-select" name="' . $id . '" id="' . $id . '" data-zt-city data-value="' . esc_attr( $val ) . '" data-empty="' . $ph . '"' . ( $req ? ' required' : '' ) . '><option value="" disabled selected>' . $ph . '</option>';
					if ( $val ) {
						$in .= '<option selected>' . esc_html( $val ) . '</option>';
					}
					$in .= '</select>';
					break;
				case 'select':
					$in = '<select class="zt-select" name="' . $id . '" id="' . $id . '"><option value="" disabled' . ( $val ? '' : ' selected' ) . '>' . $ph . '</option>';
					foreach ( zt_lines( $f['options'] ) as $op ) {
						$in .= '<option' . selected( $val, $op, false ) . '>' . esc_html( $op ) . '</option>';
					}
					$in .= '</select>';
					break;
				case 'textarea':
					$in = '<textarea class="zt-textarea" name="' . $id . '" id="' . $id . '" placeholder="' . $ph . '">' . esc_textarea( $val ) . '</textarea>';
					break;
				default:
					$attrs = array(
						'tel'      => ' type="tel" inputmode="tel" autocomplete="tel"',
						'email'    => ' type="email" inputmode="email" autocomplete="email"',
						'number'   => ' type="text" inputmode="numeric" pattern="[0-9]*"',
						'postcode' => ' type="text" inputmode="numeric" pattern="[0-9۰-۹]*" autocomplete="postal-code"',
					);
					$auto  = array(
						'billing_first_name' => ' autocomplete="name"',
						'billing_address_1'  => ' autocomplete="street-address"',
					);
					$a     = isset( $attrs[ $f['type'] ] ) ? $attrs[ $f['type'] ] : ' type="text"' . ( isset( $auto[ $key ] ) ? $auto[ $key ] : '' );
					if ( 'billing_postcode' === $key || 'billing_address_1' === $key || 'billing_city' === $key ) {
						$cls .= ' address-field';
					}
					$in = '<input class="zt-input input-text" name="' . $id . '" id="' . $id . '"' . $a . ' enterkeyhint="next" placeholder="' . $ph . '" value="' . esc_attr( $val ) . '"' . ( $req ? ' required' : '' ) . '>';
			}
			$out .= '<label class="' . esc_attr( $cls ) . '" for="' . $id . '">' . $label . $in . '</label>';
		}
		$out .= '<input type="hidden" name="billing_country" id="billing_country" value="IR">';
		if ( zt_opt( 'checkout.save_info', 1 ) ) {
			$out .= '<label class="zt-check zt-full"><input type="checkbox" name="zt_save_info" value="1" checked><span class="zt-box">' . zt_icon( 'check' ) . '</span>' . esc_html( zt_opt( 'checkout.save_info_label' ) ) . '</label>';
		}
		return $out;
	}

	/**
	 * Shipping option cards.
	 *
	 * @return string
	 */
	public static function render_shipping() {
		$out = '<div class="zt-opts zt-co-ship" data-zt-radio-group data-zt-ship-group>';
		if ( ! WC()->cart || ! WC()->cart->needs_shipping() ) {
			return $out . '</div>';
		}
		$packages = WC()->shipping()->get_packages();
		$chosen   = WC()->session ? (array) WC()->session->get( 'chosen_shipping_methods', array() ) : array();
		$opts     = array();
		foreach ( (array) zt_opt( 'checkout.shipping', array() ) as $o ) {
			$opts[ sanitize_key( $o['id'] ) ] = $o;
		}
		foreach ( $packages as $i => $pkg ) {
			$rates = isset( $pkg['rates'] ) ? $pkg['rates'] : array();
			$sel   = isset( $chosen[ $i ] ) && isset( $rates[ $chosen[ $i ] ] ) ? $chosen[ $i ] : ( $rates ? key( $rates ) : '' );
			foreach ( $rates as $rid => $rate ) {
				$meta  = $rate->get_meta_data();
				$o     = isset( $meta['zt_opt'], $opts[ $meta['zt_opt'] ] ) ? $opts[ $meta['zt_opt'] ] : null;
				$icon  = $o && $o['icon'] ? $o['icon'] : 'truck';
				$desc  = $o ? $o['desc'] : '';
				$cost  = (float) $rate->get_cost() + ( WC()->cart->display_prices_including_tax() ? array_sum( $rate->get_taxes() ) : 0 );
				$price = $cost > 0 ? zt_money( $cost, true ) . ' ' . zt_currency() : zt_opt( 'checkout.shipping_free_text', 'رایگان' );
				$auto  = '';
				if ( $o && ! empty( $o['auto_select'] ) && '' !== trim( $o['cities'] ) ) {
					$auto = ' data-zt-ship-auto="' . esc_attr( implode( '|', zt_lines( $o['cities'] ) ) ) . '"';
				}
				$is   = $rid === $sel;
				$out .= '<label class="zt-opt' . ( $is ? ' zt-is-selected' : '' ) . '" data-zt-radio' . $auto . '>';
				$out .= '<div class="zt-opt__top">' . zt_icon( $icon ) . '<div><b>' . esc_html( $rate->get_label() ) . '</b>' . ( $desc ? '<small>' . esc_html( $desc ) . '</small>' : '' ) . '</div></div>';
				$out .= '<div class="zt-opt__foot"><span>' . esc_html( $price ) . '</span><input type="radio" name="shipping_method[' . esc_attr( $i ) . ']" data-index="' . esc_attr( $i ) . '" value="' . esc_attr( $rid ) . '" class="shipping_method"' . checked( $is, true, false ) . ' hidden><span class="zt-radio"></span></div></label>';
			}
			if ( ! $rates ) {
				$out .= '<p class="zt-co-empty">' . esc_html__( 'برای آدرس وارد شده روش ارسالی موجود نیست.', 'ziteh-core' ) . '</p>';
			}
		}
		return $out . '</div>';
	}

	/**
	 * Payment option cards + payment boxes.
	 *
	 * @return string
	 */
	public static function render_payment() {
		$gateways = WC()->payment_gateways() ? WC()->payment_gateways()->get_available_payment_gateways() : array();
		$chosen   = WC()->session ? WC()->session->get( 'chosen_payment_method' ) : '';
		if ( ! $chosen || ! isset( $gateways[ $chosen ] ) ) {
			$chosen = $gateways ? key( $gateways ) : '';
			// prefer an online gateway (not card2card / cod) as in the design
			foreach ( $gateways as $gid => $g ) {
				if ( ! in_array( $gid, array( 'zt_card2card', 'cod', 'bacs', 'cheque' ), true ) ) {
					$chosen = $gid;
					break;
				}
			}
		}
		$map = array();
		foreach ( (array) zt_opt( 'checkout.gateways', array() ) as $g ) {
			$map[ $g['id'] ] = $g;
		}
		$out   = '<div class="zt-co-pay"><div class="zt-opts payment_methods wc_payment_methods" data-zt-radio-group>';
		$boxes = '';
		foreach ( $gateways as $gid => $g ) {
			$m     = isset( $map[ $gid ] ) ? $map[ $gid ] : ( isset( $map['*'] ) && 'zt_card2card' !== $gid ? $map['*'] : array() );
			$title = ! empty( $map[ $gid ]['title'] ) ? $map[ $gid ]['title'] : $g->get_title();
			$sub   = ! empty( $m['sub'] ) ? $m['sub'] : wp_strip_all_tags( $g->get_description() );
			$icon  = ! empty( $m['icon'] ) ? $m['icon'] : 'card';
			$is    = $gid === $chosen;
			$out  .= '<label class="zt-opt wc_payment_method payment_method_' . esc_attr( $gid ) . ( $is ? ' zt-is-selected' : '' ) . '" data-zt-radio>';
			$out  .= '<div class="zt-opt__top">' . zt_icon( $icon ) . '<div><b>' . esc_html( $title ) . '</b>' . ( $sub ? '<small>' . esc_html( $sub ) . '</small>' : '' ) . '</div></div>';
			$out  .= '<div class="zt-opt__foot"><span></span><input id="payment_method_' . esc_attr( $gid ) . '" type="radio" class="input-radio" name="payment_method" value="' . esc_attr( $gid ) . '"' . checked( $is, true, false ) . ' data-order_button_text="' . esc_attr( $g->order_button_text ) . '" hidden><span class="zt-radio"></span></div></label>';
			if ( $g->has_fields() ) {
				ob_start();
				$g->payment_fields();
				$fields = ob_get_clean();
				$boxes .= '<div class="payment_box zt-paybox payment_method_' . esc_attr( $gid ) . '"' . ( $is ? '' : ' style="display:none"' ) . '>' . $fields . '</div>';
			}
		}
		if ( ! $gateways ) {
			$out .= '<p class="zt-co-empty">' . esc_html__( 'هیچ روش پرداختی فعال نیست. از ووکامرس ← تنظیمات ← پرداخت‌ها یک درگاه را فعال کنید.', 'ziteh-core' ) . '</p>';
		}
		return $out . '</div>' . $boxes . '</div>';
	}

	/**
	 * Order summary items + totals (inside .zt-osum).
	 *
	 * @param array $o Options: coupon (bool), points (bool), labels.
	 * @return string
	 */
	public static function render_summary( $o = array() ) {
		$o    = wp_parse_args(
			$o,
			array(
				'coupon'        => true,
				'coupon_title'  => 'کد تخفیف دارید؟',
				'coupon_ph'     => 'کد تخفیف را وارد کنید',
				'coupon_btn'    => 'اعمال',
				'l_subtotal'    => 'جمع جزئی',
				'l_discount'    => 'تخفیف محصولات',
				'l_ship'        => 'هزینه ارسال',
				'l_total'       => 'مبلغ قابل پرداخت',
				'points'        => true,
			)
		);
		$cart = WC()->cart;
		$out  = '<div class="zt-osum-body">';
		$out .= '<div class="zt-osum__items"><div>';
		foreach ( $cart->get_cart() as $key => $item ) {
			$p = $item['data'];
			if ( ! $p ) {
				continue;
			}
			$img   = $p->get_image_id() ? wp_get_attachment_image_url( $p->get_image_id(), 'thumbnail' ) : wc_placeholder_img_src( 'thumbnail' );
			$vol   = get_post_meta( $p->get_parent_id() ? $p->get_parent_id() : $p->get_id(), '_zt_volume', true );
			$attrs = $p->is_type( 'variation' ) ? wc_get_formatted_variation( $p, true, false, true ) : '';
			$out  .= '<div class="zt-oitem"><img src="' . esc_url( $img ) . '" alt=""><div><b>' . esc_html( $p->get_name() ) . '</b>';
			if ( $vol || $attrs ) {
				$out .= '<span>' . esc_html( zt_fa( $vol ? $vol : $attrs ) ) . '</span>';
			}
			$out .= '<span>' . esc_html( zt_fa( $item['quantity'] ) ) . ' عدد</span>';
			$out .= '<span class="zt-price">' . esc_html( zt_money( $item['line_subtotal'] + ( $cart->display_prices_including_tax() ? $item['line_subtotal_tax'] : 0 ), true ) . ' ' . zt_currency() ) . '</span></div>';
			$out .= '<button type="button" class="zt-rm" aria-label="حذف" data-zt-co-remove="' . esc_attr( $key ) . '">' . zt_icon( 'x' ) . '</button></div>';
		}
		$sample = ZT_Samples::selected();
		$all    = ZT_Samples::all();
		if ( $sample >= 0 && isset( $all[ $sample ] ) && ZT_Samples::unlocked() ) {
			$s    = $all[ $sample ];
			$out .= '<div class="zt-oitem zt-oitem--sample"><img src="' . esc_url( zt_img_url( $s['image'] ) ) . '" alt=""><div><b>' . esc_html( zt_opt( 'cart.samples_line_name' ) ) . '</b><span>' . esc_html( $s['title'] . ' — ' . $s['size'] ) . '</span><span>' . esc_html( zt_fa( 1 ) ) . ' عدد</span><span class="zt-price">' . esc_html( zt_opt( 'checkout.shipping_free_text', 'رایگان' ) ) . '</span></div></div>';
		}
		$out .= '</div></div>';
		if ( $o['coupon'] && wc_coupons_enabled() ) {
			$out .= '<div class="zt-coupon" data-zt-coupon><p>' . esc_html( $o['coupon_title'] ) . '</p><div><input type="text" autocomplete="off" autocapitalize="characters" enterkeyhint="go" placeholder="' . esc_attr( $o['coupon_ph'] ) . '"><button type="button">' . esc_html( $o['coupon_btn'] ) . '</button></div>';
			foreach ( $cart->get_applied_coupons() as $code ) {
				$out .= '<span class="zt-coupon__tag">' . esc_html( $code ) . '</span>';
			}
			$out .= '</div>';
		}
		$subtotal = (float) $cart->get_subtotal() + ( $cart->display_prices_including_tax() ? (float) $cart->get_subtotal_tax() : 0 );
		$discount = ZT_Woo::discount_total();
		$out     .= '<div class="zt-osum__row"><span>' . esc_html( $o['l_subtotal'] ) . '</span><b>' . esc_html( zt_money( $subtotal, true ) . ' ' . zt_currency() ) . '</b></div>';
		if ( $discount > 0 ) {
			$out .= '<div class="zt-osum__row"><span>' . esc_html( $o['l_discount'] ) . '</span><b>− ' . esc_html( zt_money( $discount, true ) . ' ' . zt_currency() ) . '</b></div>';
		}
		if ( $cart->needs_shipping() ) {
			$ship = (float) $cart->get_shipping_total() + ( $cart->display_prices_including_tax() ? (float) $cart->get_shipping_tax() : 0 );
			$out .= '<div class="zt-osum__row"><span>' . esc_html( $o['l_ship'] ) . '</span><b>' . esc_html( $ship > 0 ? zt_money( $ship, true ) . ' ' . zt_currency() : zt_opt( 'checkout.shipping_free_text', 'رایگان' ) ) . '</b></div>';
		}
		$total = (float) $cart->get_total( 'edit' );
		$out  .= '<div class="zt-osum__total"><span>' . esc_html( $o['l_total'] ) . '</span><b>' . zt_price( $total ) . '</b></div>'; // phpcs:ignore
		if ( $o['points'] && zt_opt( 'checkout.points_enabled', 1 ) ) {
			$out .= '<p class="zt-osum__pts">' . esc_html( str_replace( '{points}', zt_fa( self::points_for( $total ) ), zt_opt( 'checkout.points_text' ) ) ) . '</p>';
		}
		return $out . '</div>';
	}

	/**
	 * update_order_review fragments.
	 *
	 * @param array $f Fragments.
	 * @return array
	 */
	public static function fragments( $f ) {
		$f['.zt-co-ship']   = self::render_shipping();
		$f['.zt-co-pay']    = self::render_payment();
		$f['.zt-osum-body'] = self::render_summary( self::summary_opts() );
		$f['[data-zt-ab-total]'] = '<span data-zt-ab-total>' . esc_html( zt_money( (float) WC()->cart->get_total( 'edit' ), true ) ) . '</span>';
		return $f;
	}

	/**
	 * Summary widget options persisted for AJAX renders.
	 *
	 * @return array
	 */
	public static function summary_opts() {
		$o = get_option( 'zt_osum_opts', array() );
		return is_array( $o ) ? $o : array();
	}
}
