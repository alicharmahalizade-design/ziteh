<?php
/**
 * Single posts and pages.
 *
 * @package Ziteh_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( ! Ziteh_Theme::do_location( 'single' ) ) :
	while ( have_posts() ) :
		the_post();
		?>
		<div class="ziteh-shell">
			<article <?php post_class( 'ziteh-entry' ); ?>>
				<header class="ziteh-entry__head">
					<h1 class="ziteh-entry__title"><?php the_title(); ?></h1>
					<?php
					if ( 'post' === get_post_type() ) {
						ziteh_theme_entry_meta();
					}
					?>
				</header>

				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="ziteh-entry__thumb">
						<?php the_post_thumbnail( 'large', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?>
					</figure>
				<?php endif; ?>

				<div class="ziteh-prose">
					<?php
					the_content();

					wp_link_pages(
						array(
							'before' => '<nav class="ziteh-page-links">' . esc_html__( 'صفحه‌ها:', 'ziteh-theme' ),
							'after'  => '</nav>',
						)
					);
					?>
				</div>

				<?php
				$tags = get_the_tag_list( '', '', '' );
				if ( $tags ) :
					?>
					<div class="ziteh-entry__tags"><?php echo wp_kses_post( $tags ); ?></div>
				<?php endif; ?>
			</article>

			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			?>
		</div>
		<?php
	endwhile;
endif;

get_footer();
