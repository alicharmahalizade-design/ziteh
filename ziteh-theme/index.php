<?php
/**
 * Fallback template for every request the hierarchy does not resolve elsewhere.
 *
 * @package Ziteh_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( ! Ziteh_Theme::do_location( 'archive' ) ) :
	?>
	<div class="ziteh-shell">
		<?php if ( have_posts() ) : ?>
			<?php
			// get_the_title( 0 ) falls back to the global post, which is not what
			// we want here, so the posts page is resolved explicitly.
			$ziteh_posts_page = (int) get_option( 'page_for_posts' );
			$ziteh_heading    = $ziteh_posts_page ? get_the_title( $ziteh_posts_page ) : '';
			if ( '' === $ziteh_heading ) {
				$ziteh_heading = __( 'تازه‌ترین نوشته‌ها', 'ziteh-theme' );
			}
			?>
			<header class="ziteh-shell__head">
				<h1 class="ziteh-shell__title"><?php echo esc_html( $ziteh_heading ); ?></h1>
			</header>

			<div class="ziteh-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					ziteh_theme_entry_card();
				endwhile;
				?>
			</div>

			<?php ziteh_theme_pagination(); ?>
		<?php else : ?>
			<p class="ziteh-empty"><?php esc_html_e( 'هنوز نوشته‌ای منتشر نشده است.', 'ziteh-theme' ); ?></p>
		<?php endif; ?>
	</div>
	<?php
endif;

get_footer();
