<?php
/**
 * Not found.
 *
 * @package Ziteh_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="ziteh-shell ziteh-shell--narrow">
	<div class="ziteh-404">
		<p class="ziteh-404__code">۴۰۴</p>
		<h1 class="ziteh-404__title"><?php esc_html_e( 'این صفحه پیدا نشد', 'ziteh-theme' ); ?></h1>
		<p class="ziteh-404__text"><?php esc_html_e( 'ممکن است نشانی تغییر کرده باشد یا صفحه حذف شده باشد. از جست‌وجو استفاده کنید یا به صفحه اصلی برگردید.', 'ziteh-theme' ); ?></p>
		<?php get_search_form(); ?>
		<a class="ziteh-404__home" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'بازگشت به صفحه اصلی', 'ziteh-theme' ); ?></a>
	</div>
</div>
<?php
get_footer();
