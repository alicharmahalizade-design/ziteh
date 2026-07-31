<?php
/**
 * Site footer location and document close.
 *
 * @package Ziteh_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
	</main>

	<?php
	if ( ! Ziteh_Theme::do_location( 'footer' ) ) {
		ziteh_theme_fallback_footer();
	}
	?>
</div>

<?php wp_footer(); ?>
</body>
</html>
