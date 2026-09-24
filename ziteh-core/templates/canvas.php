<?php
/**
 * Page template "زیته — کامل": Ziteh header + page content + Ziteh footer.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;
require ZT_PATH . 'templates/header-open.php';
ZT_Templates::render_header();
while ( have_posts() ) :
	the_post();
	the_content();
endwhile;
ZT_Templates::render_footer();
wp_footer();
?>
</body>
</html>
