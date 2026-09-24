<?php
/**
 * Demo content importer: the categories, products, reviews and articles of the
 * original design, so every dynamic widget shows real data immediately.
 * Idempotent — items are keyed by "_zt_demo" and never created twice.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ZT_Demo
 */
class ZT_Demo {

	/**
	 * Log lines.
	 *
	 * @var array
	 */
	private static $log = array();

	/**
	 * Run the import.
	 *
	 * @return array Log.
	 */
	public static function import() {
		self::$log = array();
		if ( ! zt_is_woo() ) {
			return array( 'ووکامرس فعال نیست.' );
		}
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$cats = self::categories();
		$main = self::main_product( $cats );
		foreach ( self::products() as $key => $p ) {
			self::product( $key, $p, $cats );
		}
		if ( $main ) {
			self::reviews( $main );
		}
		self::posts();
		update_option( 'zt_demo_imported', time() );
		self::$log[] = 'درون‌ریزی تمام شد.';
		return self::$log;
	}

	/**
	 * Price in store units (the design prices are in toman).
	 *
	 * @param float $toman Price.
	 * @return float
	 */
	private static function price( $toman ) {
		return $toman * max( 1, (float) zt_opt( 'general.price_divisor', 1 ) );
	}

	/**
	 * Attachment from a design image (reused if already imported).
	 *
	 * @param string $file   File name in assets/img.
	 * @param int    $parent Parent post.
	 * @return int
	 */
	public static function attach( $file, $parent = 0 ) {
		$found = get_posts(
			array(
				'post_type'   => 'attachment',
				'post_status' => 'inherit',
				'meta_key'    => '_zt_demo_src', // phpcs:ignore
				'meta_value'  => $file, // phpcs:ignore
				'fields'      => 'ids',
				'numberposts' => 1,
			)
		);
		if ( $found ) {
			return (int) $found[0];
		}
		$src = ZT_PATH . 'assets/img/' . $file;
		if ( ! file_exists( $src ) ) {
			return 0;
		}
		$tmp = wp_tempnam( $file );
		if ( ! $tmp || ! copy( $src, $tmp ) ) {
			return 0;
		}
		$id = media_handle_sideload(
			array(
				'name'     => 'ziteh-' . $file,
				'tmp_name' => $tmp,
			),
			$parent
		);
		if ( is_wp_error( $id ) ) {
			@unlink( $tmp ); // phpcs:ignore
			self::$log[] = 'خطا در تصویر ' . $file . ': ' . $id->get_error_message();
			return 0;
		}
		update_post_meta( $id, '_zt_demo_src', $file );
		return (int) $id;
	}

	/**
	 * Existing demo post by key.
	 *
	 * @param string $key  Key.
	 * @param string $type Post type.
	 * @return int
	 */
	private static function existing( $key, $type ) {
		$found = get_posts(
			array(
				'post_type'   => $type,
				'post_status' => 'any',
				'meta_key'    => '_zt_demo', // phpcs:ignore
				'meta_value'  => $key, // phpcs:ignore
				'fields'      => 'ids',
				'numberposts' => 1,
			)
		);
		return $found ? (int) $found[0] : 0;
	}

	/**
	 * Product categories.
	 *
	 * @return array name => term id
	 */
	private static function categories() {
		$defs = array(
			'مراقبت پوست'      => array( 'skin-care', 'cat-1.jpg', '' ),
			'مراقبت مو'        => array( 'hair-care', 'cat-2.jpg', '' ),
			'مراقبت کودک'      => array( 'baby-care', 'cat-3.jpg', '' ),
			'بهداشت فردی'      => array( 'personal-care', 'cat-4.jpg', '' ),
			'سلامت دندان'      => array( 'oral-care', 'cat-5.jpg', '' ),
			'مراقبت دور چشم'   => array( 'eye-care', 'cat-6.jpg', '' ),
			'خوشبو کننده محیط' => array( 'home-fragrance', '', 'بهداشت فردی' ),
			'ضد آفتاب'         => array( 'sunscreen', '', 'مراقبت پوست' ),
		);
		$out  = array();
		foreach ( $defs as $name => $d ) {
			$term = get_term_by( 'slug', $d[0], 'product_cat' );
			if ( ! $term ) {
				$args = array( 'slug' => $d[0] );
				if ( $d[2] && isset( $out[ $d[2] ] ) ) {
					$args['parent'] = $out[ $d[2] ];
				}
				$r = wp_insert_term( $name, 'product_cat', $args );
				if ( is_wp_error( $r ) ) {
					continue;
				}
				$tid = (int) $r['term_id'];
				if ( $d[1] ) {
					$img = self::attach( $d[1] );
					if ( $img ) {
						update_term_meta( $tid, 'thumbnail_id', $img );
					}
				}
				self::$log[] = 'دسته ساخته شد: ' . $name;
			} else {
				$tid = (int) $term->term_id;
			}
			$out[ $name ] = $tid;
		}
		return $out;
	}

	/**
	 * Simple product helper.
	 *
	 * @param string $key  Demo key.
	 * @param array  $p    Data: name, cat, img, price, regular, exp, card_cat, sections, gallery, meta, desc, short, featured.
	 * @param array  $cats Categories.
	 * @return int
	 */
	private static function product( $key, $p, $cats ) {
		$id = self::existing( $key, 'product' );
		if ( $id ) {
			return $id;
		}
		$prod = new WC_Product_Simple();
		$prod->set_name( $p['name'] );
		$prod->set_status( 'publish' );
		$prod->set_catalog_visibility( 'visible' );
		$reg  = ! empty( $p['regular'] ) ? $p['regular'] : $p['price'];
		$prod->set_regular_price( (string) self::price( $reg ) );
		if ( ! empty( $p['regular'] ) && $p['regular'] > $p['price'] ) {
			$prod->set_sale_price( (string) self::price( $p['price'] ) );
		}
		if ( ! empty( $p['cat'] ) && isset( $cats[ $p['cat'] ] ) ) {
			$prod->set_category_ids( array( $cats[ $p['cat'] ] ) );
		}
		$prod->set_description( isset( $p['desc'] ) ? $p['desc'] : '' );
		$prod->set_short_description( isset( $p['short'] ) ? $p['short'] : '' );
		$prod->set_featured( ! empty( $p['featured'] ) );
		$prod->set_manage_stock( true );
		$prod->set_stock_quantity( isset( $p['stock'] ) ? $p['stock'] : 40 );
		$prod->set_reviews_allowed( true );
		$id = $prod->save();
		$img = self::attach( $p['img'], $id );
		if ( $img ) {
			$prod->set_image_id( $img );
		}
		if ( ! empty( $p['gallery'] ) ) {
			$gal = array();
			foreach ( $p['gallery'] as $g ) {
				$gid = self::attach( $g, $id );
				if ( $gid ) {
					$gal[] = $gid;
				}
			}
			$prod->set_gallery_image_ids( $gal );
		}
		$prod->save();
		$meta = isset( $p['meta'] ) ? $p['meta'] : array();
		if ( ! empty( $p['exp'] ) ) {
			$meta['_zt_expiry'] = $p['exp'];
		}
		if ( ! empty( $p['card_cat'] ) ) {
			$meta['_zt_card_cat'] = $p['card_cat'];
		}
		if ( ! empty( $p['sections'] ) ) {
			$meta['_zt_sections'] = $p['sections'];
		}
		foreach ( $meta as $mk => $mv ) {
			update_post_meta( $id, $mk, $mv );
		}
		update_post_meta( $id, '_zt_demo', $key );
		self::$log[] = 'محصول ساخته شد: ' . $p['name'];
		return $id;
	}

	/**
	 * The product of the design's product page.
	 *
	 * @param array $cats Categories.
	 * @return int
	 */
	private static function main_product( $cats ) {
		$id = self::existing( 'main', 'product' );
		if ( $id ) {
			return $id;
		}
		$id = self::product(
			'main',
			array(
				'name'     => 'شامپو تقویت‌کننده و ضد ریزش موی زیته',
				'cat'      => 'مراقبت مو',
				'img'      => 'p-main.jpg',
				'gallery'  => array( 'p-thumb-2.jpg', 'p-thumb-3.jpg', 'p-thumb-4.jpg', 'p-thumb-5.jpg' ),
				'price'    => 385000,
				'regular'  => 450000,
				'exp'      => '1408/05',
				'featured' => true,
				'stock'    => 25,
				'short'    => 'شامپویی تخصصی برای کاهش ریزش مو و تقویت ریشه و فولیکول‌مو پی‌مو. مناسب استفاده روزانه، برای خانم ها و آقایان.',
				'desc'     => "<p>شامپو تقویت‌کننده زیته با ترکیبی از عصاره‌های گیاهی و مواد مؤثر طبیعی، کاهش ریزش مو کمک کرده و باعث تقویت ریشه و افزایش استحکام تارهای مو می‌شود.</p>\n<p>استفاده منظم از این شامپو، موهایی سالم‌تر، پرپشت‌تر و درخشان‌تر برای شما به ارمغان می‌آورد.</p>",
				'meta'     => array(
					'_zt_en_name'     => 'Anti Hair Fall Shock Shampoo',
					'_zt_crumb_title' => 'شامپو تقویت کننده و ضد ریزش مو',
					'_zt_short_title' => 'شامپو ضد ریزش زیته',
					'_zt_volume'      => '۵۰۰ میلی‌لیتر',
					'_zt_flag'        => 'پیشنهاد ویژه',
					'_zt_desc_title'  => 'قدرت طبیعت برای مویی سالم‌تر',
					'_zt_features'    => "ban|کاهش محسوس ریزش مو از ریشه\ndroplet|تقویت فولیکول‌ها و افزایش رشد مو\ncheck-circle|ترکیبات ملایم، مناسب پوست سر حساس\nwaves|مناسب انواع مو و استفاده روزانه",
					'_zt_props'       => "waves|مناسب انواع مو\nflask|بدون پارابن\nleaf|فرمول گیاهی\natom|بدون سولفات",
					'_zt_ingredients' => "عصاره رزماری — تحریک گردش خون پوست سر و تقویت ریشه مو\nروغن آرگان — تغذیه و نرم‌کنندگی تارهای مو\nبیوتین و پانتنول — افزایش استحکام و درخشندگی مو\nعصاره آلوئه‌ورا — آبرسانی و تسکین پوست سر حساس\nبدون سولفات، بدون پارابن و بدون رنگ مصنوعی",
					'_zt_usage'       => "موها را کاملاً مرطوب کنید و مقدار مناسبی از شامپو را روی پوست سر بمالید.\nبا نوک انگشتان به مدت ۲ تا ۳ دقیقه ماساژ دهید تا کف کند.\nبا آب ولرم کاملاً بشویید و در صورت نیاز مرحله را تکرار کنید.\nبرای بهترین نتیجه، ۳ تا ۴ بار در هفته استفاده شود.",
				),
			),
			$cats
		);
		if ( $id ) {
			$desc = self::attach( 'p-desc.jpg', $id );
			if ( $desc ) {
				update_post_meta( $id, '_zt_desc_image', $desc );
			}
			// The first gallery thumbnail of the design is the main image itself.
		}
		return $id;
	}

	/**
	 * Other products of the design.
	 *
	 * @return array
	 */
	private static function products() {
		$out    = array();
		$offers = array(
			array( 'عطر خانه ارکید اواسیس ویت یو', 'offer-1.jpg', '1408/02' ),
			array( 'عطر خانه پلیس گالا ویت یو', 'offer-2.jpg', '1408/04' ),
			array( 'عطر خانه اربیتال سوک ویت یو', 'offer-3.jpg', '1408/01' ),
			array( 'عطر خانه فارست کاتیج ویت یو', 'offer-4.jpg', '1407/11' ),
		);
		foreach ( $offers as $i => $o ) {
			$out[ 'offer-' . ( $i + 1 ) ] = array(
				'name'     => $o[0],
				'cat'      => 'خوشبو کننده محیط',
				'card_cat' => 'خوشبو کننده محیط',
				'img'      => $o[1],
				'price'    => 900000,
				'regular'  => 980000,
				'exp'      => $o[2],
				'short'    => 'رایحه‌ای ماندگار و دلنشین برای فضای خانه.',
			);
		}
		$sel = array(
			array( 'شامپو ضد شوره ملایم D1 پرایم', 'مراقبت مو', 'شامپو', 'sel-1.jpg', 690000, '1407/09' ),
			array( 'ماسک لب آبرسان و حجم دهنده آردن اکسپرتیج', 'مراقبت پوست', 'مراقبت از لب', 'sel-2.jpg', 495000, '1407/06' ),
			array( 'استیک ضد آفتاب سولار شیلد SPF50 بی رنگ پوست خشک سان...', 'ضد آفتاب', 'ضد آفتاب', 'sel-3.jpg', 1099000, '1408/03' ),
			array( 'کرم ژل مرطوب کننده پوست خشک، حساس و مستعد قرمزی...', 'مراقبت پوست', 'کرم مرطوب کننده پوست حساس', 'sel-4.jpg', 1548000, '1407/12' ),
		);
		foreach ( $sel as $i => $o ) {
			$out[ 'sel-' . ( $i + 1 ) ] = array(
				'name'     => $o[0],
				'cat'      => $o[1],
				'card_cat' => $o[2],
				'img'      => $o[3],
				'price'    => $o[4],
				'exp'      => $o[5],
				'sections' => 'selected',
				'featured' => true,
			);
		}
		$rel = array(
			array( 'روغن تقویت ریشه مو زیته', 'rel-1.jpg', 395000, '1408/03' ),
			array( 'شامپو روزانه ملایم زیته', 'rel-2.jpg', 285000, '1407/10' ),
			array( 'لوسیون تقویت مو زیته', 'rel-3.jpg', 375000, '1408/01' ),
			array( 'ماسک مو تقویت‌کننده زیته', 'rel-4.jpg', 475000, '1407/08' ),
			array( 'سرم ضد ریزش مو زیته', 'rel-5.jpg', 395000, '1408/04' ),
		);
		foreach ( $rel as $i => $o ) {
			$out[ 'rel-' . ( $i + 1 ) ] = array(
				'name'  => $o[0],
				'cat'   => 'مراقبت مو',
				'img'   => $o[1],
				'price' => $o[2],
				'exp'   => $o[3],
				'meta'  => array( '_zt_brand' => 'زیته' ),
			);
		}
		return $out;
	}

	/**
	 * Reviews of the main product.
	 *
	 * @param int $pid Product.
	 */
	private static function reviews( $pid ) {
		if ( get_comments( array( 'post_id' => $pid, 'count' => true, 'meta_key' => '_zt_demo' ) ) ) { // phpcs:ignore
			return;
		}
		$list = array(
			array( 'مریم محمدی', 'بعد از دو هفته استفاده، ریزش موهام خیلی کمتر شده و احساس می‌کنم موهام قوی‌تر شده.', 'avatar-1.jpg', '-10 days' ),
			array( 'سارا احمدی', 'محصول فوق‌العاده‌ای‌ه، پوست سرم حساسه و هیچ‌گونه خارش یا خشکی ایجاد نکرد.', 'avatar-2.jpg', '-6 days' ),
			array( 'علی رضا', 'کیفیت خیلی بالا و رایحه طبیعی و دلنشینی داره. پیشنهاد می‌کنم امتحان کنید.', 'avatar-3.jpg', '-14 days' ),
		);
		foreach ( $list as $r ) {
			$time = strtotime( $r[3] );
			$cid  = wp_insert_comment(
				array(
					'comment_post_ID'  => $pid,
					'comment_author'   => $r[0],
					'comment_content'  => $r[1],
					'comment_type'     => 'review',
					'comment_approved' => 1,
					'comment_date'     => wp_date( 'Y-m-d H:i:s', $time ),
					'comment_date_gmt' => gmdate( 'Y-m-d H:i:s', $time ),
				)
			);
			if ( $cid ) {
				update_comment_meta( $cid, 'rating', 5 );
				update_comment_meta( $cid, 'verified', 1 );
				update_comment_meta( $cid, '_zt_demo', 1 );
				$av = self::attach( $r[2] );
				if ( $av ) {
					update_comment_meta( $cid, 'zt_avatar', wp_get_attachment_image_url( $av, 'thumbnail' ) );
				}
			}
		}
		if ( class_exists( 'WC_Comments' ) ) {
			WC_Comments::clear_transients( $pid );
		}
		self::$log[] = 'نظرات نمونه ثبت شد.';
	}

	/**
	 * Blog posts.
	 */
	private static function posts() {
		$cat = get_term_by( 'slug', 'blog', 'category' );
		$cid = $cat ? (int) $cat->term_id : 0;
		if ( ! $cid ) {
			$r   = wp_insert_term( 'وبلاگ', 'category', array( 'slug' => 'blog' ) );
			$cid = is_wp_error( $r ) ? 0 : (int) $r['term_id'];
		}
		$posts = array(
			array( 'جوش‌های زیر پوستی چیست؟ دلایل و روش های درمان', 'blog-1.jpg', 'جوش‌های زیرپوستی از رایج‌ترین مشکلات پوستی هستند که معمولاً دردناک‌اند و دیرتر از جوش‌های معمولی بهبود پیدا می‌کنند.' ),
			array( 'از بین بردن لک صورت با خمیردندان', 'blog-2.jpg', 'استفاده از خمیردندان برای درمان لک صورت یکی از توصیه‌های رایج خانگی است؛ اما آیا واقعاً مؤثر و بی‌خطر است؟' ),
			array( '۴ ویژگی که پماد حساسیت پوستی باید داشته باشد! + معرفی انواع و علائم حساسیت پوستی', 'blog-3.jpg', 'حساسیت پوستی می‌تواند دلایل مختلفی داشته باشد. انتخاب پماد مناسب به نوع و شدت حساسیت بستگی دارد.' ),
		);
		foreach ( $posts as $i => $p ) {
			$key = 'post-' . ( $i + 1 );
			if ( self::existing( $key, 'post' ) ) {
				continue;
			}
			$content  = '<p>' . $p[2] . '</p>';
			$content .= '<h2>چرا این موضوع اهمیت دارد؟</h2><p>مراقبت درست و به‌موقع از پوست، از بروز بسیاری از مشکلات جلوگیری می‌کند. شناخت نوع پوست و انتخاب محصولات مناسب، اولین قدم برای داشتن پوستی سالم است.</p>';
			$content .= '<h2>چه کارهایی انجام دهیم؟</h2><ul><li>پوست را روزانه با شوینده ملایم بشویید.</li><li>از مرطوب‌کننده مناسب نوع پوست استفاده کنید.</li><li>در طول روز از ضدآفتاب غافل نشوید.</li></ul>';
			$content .= '<blockquote>این مطلب جنبه آموزشی دارد و جایگزین مشاوره با پزشک متخصص پوست نیست.</blockquote>';
			$content .= '<h2>جمع‌بندی</h2><p>با یک روتین ساده و منظم، می‌توانید سلامت پوست خود را حفظ کنید. برای مشاوره رایگان با کارشناسان زیته در تماس باشید.</p>';
			$pid = wp_insert_post(
				array(
					'post_type'     => 'post',
					'post_status'   => 'publish',
					'post_title'    => $p[0],
					'post_content'  => $content,
					'post_excerpt'  => $p[2],
					'post_category' => $cid ? array( $cid ) : array(),
					'post_date'     => wp_date( 'Y-m-d H:i:s', strtotime( '-' . ( 3 * ( $i + 1 ) ) . ' days' ) ),
				)
			);
			if ( ! $pid || is_wp_error( $pid ) ) {
				continue;
			}
			$img = self::attach( $p[1], $pid );
			if ( $img ) {
				set_post_thumbnail( $pid, $img );
			}
			update_post_meta( $pid, '_zt_demo', $key );
			self::$log[] = 'مقاله ساخته شد: ' . $p[0];
		}
	}
}
