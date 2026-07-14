<?php
/**
 * Central design settings.
 *
 * Adds a "زیته" top-level admin menu where the whole palette / radius / motion
 * can be tuned from one place, then prints matching CSS custom-property
 * overrides in the <head> so every widget updates at once — no per-widget
 * editing needed.
 *
 * @package Ziteh_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Ziteh_Settings
 */
class Ziteh_Settings {

	/**
	 * Singleton instance.
	 *
	 * @var Ziteh_Settings|null
	 */
	private static $instance = null;

	/**
	 * Option key.
	 */
	const OPTION = 'ziteh_settings';

	/**
	 * Get the singleton instance.
	 *
	 * @return Ziteh_Settings
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Default values (mirror the CSS defaults).
	 *
	 * @return array<string,string>
	 */
	public static function defaults() {
		return array(
			'green'      => '#7c8a5c',
			'green_dark' => '#55603a',
			'cream'      => '#f6f2ea',
			'ink'        => '#47473f',
			'sale'       => '#e0483d',
			'radius'     => '20',
			'animations' => 'on',
			'font'       => 'Vazirmatn',
		);
	}

	/**
	 * Allowed font-family choices (label => CSS stack).
	 *
	 * @return array<string,string>
	 */
	public static function fonts() {
		return array(
			'Vazirmatn' => "'Vazirmatn', 'Tahoma', sans-serif",
			'Estedad'   => "'Estedad', 'Vazirmatn', 'Tahoma', sans-serif",
			'Sahel'     => "'Sahel', 'Vazirmatn', 'Tahoma', sans-serif",
			'IRANSansX' => "'IRANSansX', 'IRANSans', 'Vazirmatn', 'Tahoma', sans-serif",
			'IRANYekan' => "'IRANYekan', 'Vazirmatn', 'Tahoma', sans-serif",
			'System'    => "'Tahoma', 'Segoe UI', sans-serif",
		);
	}

	/**
	 * Get merged settings (defaults + saved).
	 *
	 * @return array<string,string>
	 */
	public static function get() {
		$saved = get_option( self::OPTION, array() );
		if ( ! is_array( $saved ) ) {
			$saved = array();
		}
		return wp_parse_args( $saved, self::defaults() );
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		// Print the variable overrides on the front-end and the Elementor editor.
		add_action( 'wp_head', array( $this, 'print_vars' ), 20 );
		add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'print_vars' ), 20 );
	}

	/**
	 * Top-level "زیته" menu with the settings screen.
	 */
	public function register_menu() {
		add_menu_page(
			esc_html__( 'تنظیمات زیته', 'ziteh' ),
			esc_html__( 'زیته', 'ziteh' ),
			'manage_options',
			'ziteh-settings',
			array( $this, 'render_page' ),
			'dashicons-leaf',
			58
		);
	}

	/**
	 * Register the setting + sanitiser.
	 */
	public function register_settings() {
		register_setting(
			'ziteh_settings_group',
			self::OPTION,
			array( $this, 'sanitize' )
		);
	}

	/**
	 * Sanitise the submitted settings.
	 *
	 * @param mixed $input Raw input.
	 * @return array<string,string>
	 */
	public function sanitize( $input ) {
		$out      = array();
		$defaults = self::defaults();
		foreach ( array( 'green', 'green_dark', 'cream', 'ink', 'sale' ) as $color ) {
			$val           = isset( $input[ $color ] ) ? sanitize_hex_color( $input[ $color ] ) : '';
			$out[ $color ] = $val ? $val : $defaults[ $color ];
		}
		$out['radius']     = isset( $input['radius'] ) ? (string) max( 0, min( 40, (int) $input['radius'] ) ) : $defaults['radius'];
		$out['animations'] = ( isset( $input['animations'] ) && 'on' === $input['animations'] ) ? 'on' : 'off';
		$fonts             = self::fonts();
		$out['font']       = ( isset( $input['font'] ) && isset( $fonts[ $input['font'] ] ) ) ? $input['font'] : $defaults['font'];
		return $out;
	}

	/**
	 * Render the settings screen.
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$s      = self::get();
		$fields = array(
			'green'      => esc_html__( 'رنگ اصلی (سبز)', 'ziteh' ),
			'green_dark' => esc_html__( 'رنگ تیره (عنوان‌ها)', 'ziteh' ),
			'cream'      => esc_html__( 'رنگ پس‌زمینه (کرم)', 'ziteh' ),
			'ink'        => esc_html__( 'رنگ متن', 'ziteh' ),
			'sale'       => esc_html__( 'رنگ تخفیف (قرمز)', 'ziteh' ),
		);
		?>
		<div class="wrap" dir="rtl" style="max-width:720px">
			<h1><?php esc_html_e( 'تنظیمات ظاهری زیته', 'ziteh' ); ?></h1>
			<p><?php esc_html_e( 'رنگ‌ها و ظاهر کلی همه‌ی ویجت‌های زیته را از همین‌جا کنترل کنید. با ذخیره، کل صفحه به‌روز می‌شود.', 'ziteh' ); ?></p>
			<form method="post" action="options.php">
				<?php settings_fields( 'ziteh_settings_group' ); ?>
				<table class="form-table" role="presentation">
					<?php foreach ( $fields as $key => $label ) : ?>
						<tr>
							<th scope="row"><label for="ziteh-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
							<td>
								<input type="color" id="ziteh-<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( self::OPTION ); ?>[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $s[ $key ] ); ?>">
								<code><?php echo esc_html( $s[ $key ] ); ?></code>
							</td>
						</tr>
					<?php endforeach; ?>
					<tr>
						<th scope="row"><label for="ziteh-font"><?php esc_html_e( 'فونت قالب', 'ziteh' ); ?></label></th>
						<td>
							<select id="ziteh-font" name="<?php echo esc_attr( self::OPTION ); ?>[font]">
								<?php foreach ( array_keys( self::fonts() ) as $font ) : ?>
									<option value="<?php echo esc_attr( $font ); ?>" <?php selected( $font, $s['font'] ); ?>><?php echo esc_html( $font ); ?></option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php esc_html_e( 'فقط «Vazirmatn» همراه افزونه لود می‌شود؛ برای بقیه فونت‌ها باید فایل فونت توسط قالب سایت لود شده باشد.', 'ziteh' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="ziteh-radius"><?php esc_html_e( 'گردی گوشه کارت‌ها (px)', 'ziteh' ); ?></label></th>
						<td><input type="number" id="ziteh-radius" min="0" max="40" name="<?php echo esc_attr( self::OPTION ); ?>[radius]" value="<?php echo esc_attr( $s['radius'] ); ?>"></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'انیمیشن‌های اسکرول', 'ziteh' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( self::OPTION ); ?>[animations]" value="on" <?php checked( 'on', $s['animations'] ); ?>>
								<?php esc_html_e( 'فعال باشد', 'ziteh' ); ?>
							</label>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Print CSS custom-property overrides so saved colours win over the defaults.
	 */
	public function print_vars() {
		$s = self::get();

		$css  = ':root{';
		$css .= '--ziteh-green:' . $s['green'] . ';';
		$css .= '--ziteh-green-dark:' . $s['green_dark'] . ';';
		$css .= '--ziteh-green-hover:' . $this->shade( $s['green'], -12 ) . ';';
		$css .= '--ziteh-green-soft:' . $this->tint( $s['green'], 82 ) . ';';
		$css .= '--ziteh-cream:' . $s['cream'] . ';';
		$css .= '--ziteh-ink:' . $s['ink'] . ';';
		$css .= '--ziteh-sale:' . $s['sale'] . ';';
		$css .= '--ziteh-radius:' . (int) $s['radius'] . 'px;';
		$fonts = self::fonts();
		if ( isset( $fonts[ $s['font'] ] ) ) {
			$css .= '--ziteh-font:' . $fonts[ $s['font'] ] . ';';
		}
		$css .= '}';

		if ( 'off' === $s['animations'] ) {
			$css .= '.ziteh-reveal{opacity:1!important;transform:none!important;}';
		}

		printf( "<style id='ziteh-vars'>%s</style>\n", $css ); // phpcs:ignore WordPress.Security.EscapeOutput
	}

	/**
	 * Darken/lighten a hex colour by a percentage (-100..100).
	 *
	 * @param string $hex     Hex colour.
	 * @param int    $percent Negative = darker, positive = lighter.
	 * @return string
	 */
	private function shade( $hex, $percent ) {
		$rgb = $this->hex_to_rgb( $hex );
		if ( ! $rgb ) {
			return $hex;
		}
		foreach ( $rgb as &$c ) {
			$c = (int) max( 0, min( 255, $c + ( $c * $percent / 100 ) ) );
		}
		return sprintf( '#%02x%02x%02x', $rgb[0], $rgb[1], $rgb[2] );
	}

	/**
	 * Mix a colour towards white by a percentage (0..100 = amount of white).
	 *
	 * @param string $hex     Hex colour.
	 * @param int    $percent Amount of white mixed in.
	 * @return string
	 */
	private function tint( $hex, $percent ) {
		$rgb = $this->hex_to_rgb( $hex );
		if ( ! $rgb ) {
			return $hex;
		}
		foreach ( $rgb as &$c ) {
			$c = (int) round( $c + ( 255 - $c ) * ( $percent / 100 ) );
		}
		return sprintf( '#%02x%02x%02x', $rgb[0], $rgb[1], $rgb[2] );
	}

	/**
	 * Parse a #rrggbb string to [r,g,b].
	 *
	 * @param string $hex Hex colour.
	 * @return int[]|null
	 */
	private function hex_to_rgb( $hex ) {
		$hex = ltrim( (string) $hex, '#' );
		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		if ( 6 !== strlen( $hex ) ) {
			return null;
		}
		return array(
			hexdec( substr( $hex, 0, 2 ) ),
			hexdec( substr( $hex, 2, 2 ) ),
			hexdec( substr( $hex, 4, 2 ) ),
		);
	}
}
