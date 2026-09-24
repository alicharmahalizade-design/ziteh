<?php
/**
 * Preview / editor canvas for a Ziteh template (zt_template post).
 * Header and footer templates show only themselves; every other type is
 * shown between the site header and footer, like on the live site.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;
require ZT_PATH . 'templates/header-open.php';
$zt_type  = get_post_meta( get_the_ID(), '_zt_tpl_type', true );
$zt_frame = ! in_array( $zt_type, array( 'header', 'footer', 'section' ), true );
if ( $zt_frame ) {
	ZT_Templates::render_header();
}
if ( 'header' === $zt_type ) {
	echo '<div class="zt-site-header">';
}
while ( have_posts() ) :
	the_post();
	if ( 'single_product' === $zt_type && class_exists( 'ZT_Context' ) ) {
		$zt_p = ZT_Context::product();
		if ( $zt_p ) {
			$GLOBALS['product']  = $zt_p;
			ZT_Context::$product = $zt_p;
		}
	}
	the_content();
endwhile;
if ( 'header' === $zt_type ) {
	echo '</div>';
}
if ( $zt_frame ) {
	ZT_Templates::render_footer();
}
wp_footer();
?>
</body>
</html>
