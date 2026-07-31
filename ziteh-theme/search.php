<?php
/**
 * Search results.
 *
 * @package Ziteh_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="ziteh-shell">
	<header class="ziteh-shell__head">
		<h1 class="ziteh-shell__title">
			<?php
			printf(
				/* translators: %s: search term */
				esc_html__( 'نتایج جست‌وجو برای «%s»', 'ziteh-theme' ),
				esc_html( get_search_query() )
			);
			?>
		</h1>
		<?php get_search_form(); ?>
	</header>

	<?php if ( have_posts() ) : ?>
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
		<p class="ziteh-empty"><?php esc_html_e( 'چیزی با این عبارت پیدا نشد. عبارت دیگری را امتحان کنید.', 'ziteh-theme' ); ?></p>
	<?php endif; ?>
</div>
<?php
get_footer();
