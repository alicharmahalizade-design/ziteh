<?php
/**
 * Dynamic template (single product, archives, posts, 404, search): renders
 * the Ziteh template chosen in settings in the context of the current query.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;
require ZT_PATH . 'templates/header-open.php';
ZT_Templates::render_header();
if ( is_singular() ) {
	the_post();
	if ( function_exists( 'wc_get_product' ) && 'product' === get_post_type() ) {
		$GLOBALS['product'] = wc_get_product( get_the_ID() );
		ZT_Context::$product = $GLOBALS['product'];
		do_action( 'woocommerce_before_single_product' );
	}
}
ZT_Templates::render( ZT_Templates::$current );
ZT_Templates::render_footer();
wp_footer();
?>
</body>
</html>
