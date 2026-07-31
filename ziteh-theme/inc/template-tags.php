<?php
/**
 * Template helpers, including the PHP fallbacks used before any Elementor
 * Theme Builder template exists.
 *
 * The fallbacks matter more than they look: switching themes is the moment a
 * site is most likely to break, and a header location that renders nothing
 * would leave the shop with no navigation and no cart until someone finishes
 * building templates. These keep the site usable from the first second.
 *
 * @package Ziteh_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fallback site header.
 */
function ziteh_theme_fallback_header() {
	?>
	<header class="ziteh-site-header" role="banner">
		<div class="ziteh-site-header__inner">
			<div class="ziteh-site-header__brand">
				<?php
				if ( has_custom_logo() ) {
					the_custom_logo();
				} else {
					printf(
						'<a class="ziteh-site-header__title" href="%1$s" rel="home">%2$s</a>',
						esc_url( home_url( '/' ) ),
						esc_html( get_bloginfo( 'name' ) )
					);
					$tagline = get_bloginfo( 'description', 'display' );
					if ( $tagline ) {
						printf( '<span class="ziteh-site-header__tagline">%s</span>', esc_html( $tagline ) );
					}
				}
				?>
			</div>

			<?php if ( has_nav_menu( 'primary' ) ) : ?>
				<button class="ziteh-site-header__burger" type="button" data-ziteh-site-nav-toggle aria-expanded="false" aria-controls="ziteh-site-nav" aria-label="<?php esc_attr_e( 'باز و بسته کردن منو', 'ziteh-theme' ); ?>">
					<span></span><span></span><span></span>
				</button>

				<nav class="ziteh-site-nav" id="ziteh-site-nav" aria-label="<?php esc_attr_e( 'منوی اصلی', 'ziteh-theme' ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'primary',
							'container'      => false,
							'menu_class'     => 'ziteh-site-nav__list',
							'depth'          => 2,
						)
					);
					?>
				</nav>
			<?php endif; ?>

			<div class="ziteh-site-header__actions">
				<?php if ( function_exists( 'wc_get_cart_url' ) ) : ?>
					<a class="ziteh-site-header__cart" href="<?php echo esc_url( wc_get_cart_url() ); ?>">
						<span><?php esc_html_e( 'سبد خرید', 'ziteh-theme' ); ?></span>
						<?php if ( function_exists( 'WC' ) && WC()->cart ) : ?>
							<b><?php echo esc_html( number_format_i18n( WC()->cart->get_cart_contents_count() ) ); ?></b>
						<?php endif; ?>
					</a>
				<?php endif; ?>
			</div>
		</div>

		<?php if ( ! function_exists( 'elementor_theme_do_location' ) ) : ?>
			<p class="ziteh-site-header__hint">
				<?php esc_html_e( 'این هدر پیش‌فرض قالب است. برای هدر اختصاصی، در Elementor Theme Builder یک قالب Header بسازید و ویجت «زیته | هدر و منو» را در آن بگذارید.', 'ziteh-theme' ); ?>
			</p>
		<?php endif; ?>
	</header>
	<?php
}

/**
 * Fallback site footer.
 */
function ziteh_theme_fallback_footer() {
	$has_widgets = is_active_sidebar( 'ziteh-footer-1' ) || is_active_sidebar( 'ziteh-footer-2' ) || is_active_sidebar( 'ziteh-footer-3' );
	?>
	<footer class="ziteh-site-footer" role="contentinfo">
		<?php if ( $has_widgets ) : ?>
			<div class="ziteh-site-footer__widgets">
				<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
					<?php if ( is_active_sidebar( 'ziteh-footer-' . $i ) ) : ?>
						<div class="ziteh-site-footer__column"><?php dynamic_sidebar( 'ziteh-footer-' . $i ); ?></div>
					<?php endif; ?>
				<?php endfor; ?>
			</div>
		<?php endif; ?>

		<div class="ziteh-site-footer__bar">
			<p class="ziteh-site-footer__copy">
				<?php
				printf(
					/* translators: 1: current year, 2: site name */
					esc_html__( '© %1$s %2$s — همه حقوق محفوظ است.', 'ziteh-theme' ),
					esc_html( wp_date( 'Y' ) ),
					esc_html( get_bloginfo( 'name' ) )
				);
				?>
			</p>

			<?php if ( has_nav_menu( 'footer' ) ) : ?>
				<nav class="ziteh-site-footer__nav" aria-label="<?php esc_attr_e( 'منوی فوتر', 'ziteh-theme' ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer',
							'container'      => false,
							'menu_class'     => 'ziteh-site-footer__list',
							'depth'          => 1,
						)
					);
					?>
				</nav>
			<?php endif; ?>
		</div>
	</footer>
	<?php
}

/**
 * Post meta line for the fallback templates.
 */
function ziteh_theme_entry_meta() {
	?>
	<div class="ziteh-entry__meta">
		<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
		<span><?php echo esc_html( get_the_author() ); ?></span>
		<?php
		$categories = get_the_category();
		if ( $categories ) :
			?>
			<a href="<?php echo esc_url( get_category_link( $categories[0] ) ); ?>"><?php echo esc_html( $categories[0]->name ); ?></a>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Archive pagination.
 */
function ziteh_theme_pagination() {
	the_posts_pagination(
		array(
			'mid_size'           => 1,
			'prev_text'          => esc_html__( 'قبلی', 'ziteh-theme' ),
			'next_text'          => esc_html__( 'بعدی', 'ziteh-theme' ),
			'screen_reader_text' => esc_html__( 'صفحه‌بندی نوشته‌ها', 'ziteh-theme' ),
			'class'              => 'ziteh-pagination',
		)
	);
}

/**
 * One archive card for the fallback templates.
 */
function ziteh_theme_entry_card() {
	?>
	<article <?php post_class( 'ziteh-card' ); ?>>
		<?php if ( has_post_thumbnail() ) : ?>
			<a class="ziteh-card__thumb" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
				<?php the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy', 'decoding' => 'async', 'alt' => '' ) ); ?>
			</a>
		<?php endif; ?>

		<div class="ziteh-card__body">
			<?php ziteh_theme_entry_meta(); ?>
			<h2 class="ziteh-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
			<p class="ziteh-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24, '…' ) ); ?></p>
			<a class="ziteh-card__more" href="<?php the_permalink(); ?>"><?php esc_html_e( 'ادامه مطلب', 'ziteh-theme' ); ?></a>
		</div>
	</article>
	<?php
}
