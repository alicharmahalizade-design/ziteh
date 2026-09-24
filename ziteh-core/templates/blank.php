<?php
/**
 * Page template "زیته — خالی": page content only.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;
require ZT_PATH . 'templates/header-open.php';
while ( have_posts() ) :
	the_post();
	the_content();
endwhile;
wp_footer();
?>
</body>
</html>
