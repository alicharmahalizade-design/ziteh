<?php
/**
 * Account: profile card + statistics.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/common.php';

/**
 * Class ZT_W_Account_Profile
 */
class ZT_W_Account_Profile extends ZT_Widget_Base {

	protected $zt_group = 'account';
	protected $zt_icon  = 'eicon-person';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-account-profile';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'پروفایل و آمار کاربر';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'پروفایل' );
		$this->ctl( 'show_on', 'select', 'نمایش در', 'dashboard', array( 'options' => array( 'dashboard' => 'فقط پیشخوان', 'all' => 'همه بخش‌های پنل' ) ) );
		$this->ctl( 'avatar', 'select', 'آواتار', 'icon', array( 'options' => array( 'icon' => 'آیکون', 'gravatar' => 'تصویر کاربر (در صورت وجود)' ) ) );
		$this->ctl( 'ava_icon', 'icon', 'آیکون آواتار', 'user' );
		$this->ctl( 'edit', 'switch', 'دکمه ویرایش', true );
		$this->ctl( 'badge', 'text', 'نشان (خالی = تنظیمات)', '' );
		$this->ctl( 'badge_icon', 'icon', 'آیکون نشان', 'verified' );
		$this->ctl( 'since', 'text', 'متن تاریخ عضویت', 'عضویت از {date}' );
		$this->ctl( 'orn', 'media', 'تصویر تزئینی', 'branch-soft.png' );
		$this->end_controls_section();
		$this->section( 'c2', 'آمار' );
		$this->repeater(
			'stats',
			'آمار',
			array(
				array( 'type', 'select', 'مقدار', 'points', array( 'options' => array( 'points' => 'امتیاز باشگاه', 'spent' => 'مجموع خریدها', 'completed' => 'سفارش تکمیل شده', 'shipping' => 'سفارش در حال ارسال', 'orders' => 'کل سفارش‌ها', 'wishlist' => 'تعداد علاقه‌مندی', 'reviews' => 'تعداد نظرات' ) ) ),
				array( 'icon', 'icon', 'آیکون', 'star-o' ),
				array( 'label', 'textarea', 'برچسب ({currency} = واحد پول)', '' ),
			),
			array(
				array( 'type' => 'points', 'icon' => 'star-o', 'label' => 'امتیاز باشگاه' ),
				array( 'type' => 'spent', 'icon' => 'wallet', 'label' => "{currency}\nمجموع خریدها" ),
				array( 'type' => 'completed', 'icon' => 'clipboard', 'label' => 'سفارش تکمیل شده' ),
				array( 'type' => 'shipping', 'icon' => 'truck-fast', 'label' => 'سفارش در حال ارسال' ),
			),
			'label'
		);
		$this->end_controls_section();
		$this->style_section(
			'st',
			'پروفایل',
			array(
				array( 'box', 'کادر', '.zt-profile', array( 'bg', 'border', 'radius', 'padding', 'margin' ) ),
				array( 'ava', 'آواتار', '.zt-profile__ava', array( 'size', 'bg', 'color' ) ),
				array( 'name', 'نام', '.zt-profile__id h2', array( 'typo', 'color' ) ),
				array( 'badge', 'نشان', '.zt-profile__badge', array( 'typo', 'color' ) ),
				array( 'since', 'تاریخ عضویت', '.zt-profile__id p', array( 'typo', 'color' ) ),
				array( 'sic', 'آیکون آمار', '.zt-pstat svg', array( 'size', 'color' ) ),
				array( 'sb', 'عدد آمار', '.zt-pstat b', array( 'typo', 'color' ) ),
				array( 'ss', 'برچسب آمار', '.zt-pstat span', array( 'typo', 'color' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$mode = ZT_Acc::mode();
		if ( 'guest' === $mode || ( 'dashboard' === $s['show_on'] && ! ZT_Acc::dashboard() ) ) {
			echo ZT_Parts::empty_marker(); // phpcs:ignore
			return;
		}
		if ( 'sample' === $mode ) {
			$name  = 'نرگس محمدی';
			$date  = 'فروردین ۱۴۰۳';
			$m     = max( 1, (float) zt_opt( 'general.price_divisor', 1 ) );
			$stats = array( 'points' => 2450, 'spent' => 1485000 * $m, 'completed' => 12, 'shipping' => 4, 'orders' => 16, 'wishlist' => 4, 'reviews' => 3 );
			$ava   = '';
		} else {
			$u     = wp_get_current_user();
			$name  = trim( $u->first_name . ' ' . $u->last_name );
			$name  = $name ? $name : $u->display_name;
			$date  = zt_jdate( 'F Y', strtotime( $u->user_registered . ' UTC' ) );
			$stats = ZT_Account::stats( $u->ID );
			$ava   = 'gravatar' === $s['avatar'] ? get_avatar_url( $u->ID, array( 'size' => 164, 'default' => '404' ) ) : '';
			if ( $ava && ! get_user_meta( $u->ID, 'zt_avatar', true ) && ! get_option( 'show_avatars' ) ) {
				$ava = '';
			}
		}
		$badge = '' !== $s['badge'] ? $s['badge'] : zt_opt( 'tracking.badge' );
		echo '<section class="zt-profile">';
		if ( ! empty( $s['orn']['url'] ) ) {
			echo '<img class="zt-orn" src="' . esc_url( zt_img_url( $s['orn'] ) ) . '" alt="">';
		}
		echo '<div class="zt-profile__id"><div class="zt-profile__ava">';
		echo $ava ? '<img src="' . esc_url( $ava ) . '" alt="" class="zt-profile__img" onerror="this.remove()">' : zt_icon( $s['ava_icon'] ); // phpcs:ignore
		if ( 'yes' === $s['edit'] ) {
			echo '<a class="zt-edit" href="' . esc_url( 'sample' === $mode ? '#' : zt_page_url( 'edit-account' ) ) . '" aria-label="ویرایش پروفایل">' . zt_icon( 'pen' ) . '</a>'; // phpcs:ignore
		}
		echo '</div><div><h2>' . esc_html( $name ) . '</h2>';
		if ( $badge ) {
			echo '<div class="zt-profile__badge">' . zt_icon( $s['badge_icon'] ) . ' ' . esc_html( $badge ) . '</div>'; // phpcs:ignore
		}
		if ( $s['since'] ) {
			echo '<p>' . esc_html( str_replace( '{date}', $date, $s['since'] ) ) . '</p>';
		}
		echo '</div></div>';
		if ( $s['stats'] ) {
			echo '<div class="zt-profile__stats">';
			foreach ( $s['stats'] as $st ) {
				$v     = isset( $stats[ $st['type'] ] ) ? $stats[ $st['type'] ] : 0;
				$v     = 'spent' === $st['type'] ? zt_money( $v, true ) : zt_money( $v );
				$label = str_replace( "\n", '<br>', esc_html( str_replace( '{currency}', zt_currency(), $st['label'] ) ) );
				echo '<div class="zt-pstat">' . zt_icon( $st['icon'] ) . '<div><b>' . esc_html( $v ) . '</b><span>' . $label . '</span></div></div>'; // phpcs:ignore
			}
			echo '</div>';
		}
		echo '</section>';
	}
}
