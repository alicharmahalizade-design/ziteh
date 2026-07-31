<?php
/**
 * Category, tag, author and date archives.
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
		<header class="ziteh-shell__head">
			<h1 class="ziteh-shell__title"><?php the_archive_title(); ?></h1>
			<?php if ( get_the_archive_description() ) : ?>
				<div class="ziteh-shell__description"><?php the_archive_description(); ?></div>
			<?php endif; ?>
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
			<p class="ziteh-empty"><?php esc_html_e( 'چیزی در این بخش پیدا نشد.', 'ziteh-theme' ); ?></p>
		<?php endif; ?>
	</div>
	<?php
endif;

get_footer();
