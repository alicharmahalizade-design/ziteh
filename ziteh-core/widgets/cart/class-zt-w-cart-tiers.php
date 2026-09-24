<?php
/**
 * Cart: tiered discount progress.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/demo.php';

/**
 * Class ZT_W_Cart_Tiers
 */
class ZT_W_Cart_Tiers extends ZT_Widget_Base {

	protected $zt_group = 'cart';
	protected $zt_icon  = 'eicon-progress-tracker';

	/** @inheritDoc */
	public function get_name() {
		return 'zt-cart-tiers';
	}

	/** @inheritDoc */
	public function get_title() {
		return 'تخفیف پلکانی';
	}

	/** @inheritDoc */
	protected function register_controls() {
		$this->section( 'c', 'محتوا' );
		$this->ctl( 'notice', 'notice', '', 'پله‌ها، مبالغ، نوع تخفیف و متن پیام‌ها در «زیته ← سبد خرید» تنظیم می‌شوند و روی محاسبه واقعی سبد اعمال می‌شوند.' );
		$this->ctl( 'icon', 'icon', 'آیکون عنوان', 'leaf' );
		$this->ctl( 'title', 'text', 'عنوان', 'تخفیف پلکانی' );
		$this->ctl( 'sub', 'text', 'زیرعنوان', 'برای دریافت تخفیف‌های بیشتر، مبلغ خرید خود را افزایش دهید' );
		$this->ctl( 'sub_tpl', 'text', 'زیرعنوان هر پله ({amount})', 'با خرید {amount} تومان' );
		$this->ctl( 'hide_empty', 'switch', 'پنهان وقتی سبد خالی است', true );
		$this->end_controls_section();
		$this->style_section(
			'st',
			'تخفیف پلکانی',
			array(
				array( 'card', 'کارت', '.zt-pcard', array( 'bg', 'border', 'radius', 'padding', 'margin' ) ),
				array( 'title', 'عنوان', '.zt-pcard__title', self::fx( 'text' ) ),
				array( 'sub', 'زیرعنوان', '.zt-pcard__sub', self::fx( 'text' ) ),
				array( 'line', 'خط', '.zt-tiers::before', array( 'bg', 'height' ) ),
				array( 'fill', 'خط تکمیل‌شده', '.zt-tiers__fill', array( 'bg', 'height' ) ),
				array( 'dot', 'دایره پله', '.zt-tier__dot', array( 'size', 'bg', 'color' ) ),
				array( 'dotd', 'دایره پله فعال', '.zt-tier.zt-is-done .zt-tier__dot', array( 'bg', 'color' ) ),
				array( 'tt', 'عنوان پله', '.zt-tier b', array( 'typo', 'color' ) ),
				array( 'ts', 'زیرعنوان پله', '.zt-tier span', array( 'typo', 'color' ) ),
				array( 'msg', 'پیام', '.zt-tier-progress p', array( 'typo', 'color' ) ),
				array( 'bar', 'نوار پیشرفت', '.zt-tier-progress .zt-bar', array( 'bg', 'height', 'radius' ) ),
				array( 'bari', 'پیشرفت', '.zt-tier-progress .zt-bar i', array( 'bg' ) ),
			)
		);
	}

	/** @inheritDoc */
	protected function zt_render( $s ) {
		$demo  = ZT_Cart_Demo::on();
		$tiers = array_reverse( class_exists( 'ZT_Tiers' ) ? ZT_Tiers::tiers() : (array) zt_opt( 'cart.tiers', array() ) );
		if ( ! $tiers ) {
			echo ZT_Parts::empty_marker(); // phpcs:ignore
			return;
		}
		$st = $demo ? ZT_Cart_Demo::state() : ZT_Woo::cart_state();
		if ( ! $demo && 'yes' === $s['hide_empty'] && empty( $st['count'] ) && ! $this->is_editor() ) {
			echo ZT_Parts::empty_marker(); // phpcs:ignore
			return;
		}
		$fill = $st['tiers']['fill'];
		$fill_style = $fill < 0 ? 'width:calc(0% - 60px)' : 'width:calc((100% - 2 * var(--zt-tin, 100px)) * ' . ( $fill / 100 ) . ')';
		echo '<section class="zt-pcard"><h2 class="zt-pcard__title">' . zt_icon( $s['icon'] ) . ' ' . esc_html( $s['title'] ) . '</h2><p class="zt-pcard__sub">' . esc_html( $s['sub'] ) . '</p>'; // phpcs:ignore
		echo '<div class="zt-tiers" data-zt-tiers><span class="zt-tiers__fill' . ( $fill < 0 ? '' : ' zt-fill-l' ) . '" data-zt-tier-fill style="' . esc_attr( $fill_style ) . '"></span>';
		foreach ( $tiers as $t ) {
			$sub  = '' !== trim( (string) $t['sub'] ) ? $t['sub'] : str_replace( '{amount}', zt_money( $t['amount'] ), $s['sub_tpl'] );
			$done = $st['subtotal_tier'] >= (float) $t['amount'];
			echo '<div class="zt-tier' . ( $done ? ' zt-is-done' : '' ) . '" data-zt-tier="' . esc_attr( (float) $t['amount'] ) . '"><div class="zt-tier__dot">' . zt_icon( $t['icon'] ? $t['icon'] : 'tag' ) . '</div><b>' . esc_html( $t['title'] ) . '</b><span>' . esc_html( zt_fa( $sub ) ) . '</span></div>'; // phpcs:ignore
		}
		echo '</div><div class="zt-tier-progress"><p data-zt-tier-msg>' . esc_html( $st['tiers']['msg'] ) . '</p><div class="zt-bar"><i data-zt-tier-bar style="width:' . esc_attr( $st['tiers']['bar'] ) . '%"></i></div></div></section>';
	}
}
