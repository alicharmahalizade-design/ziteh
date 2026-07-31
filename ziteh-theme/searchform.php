<?php
/**
 * Search form.
 *
 * @package Ziteh_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ziteh_search_id = wp_unique_id( 'ziteh-search-' );
?>
<form class="ziteh-searchform" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $ziteh_search_id ); ?>"><?php esc_html_e( 'جست‌وجو', 'ziteh-theme' ); ?></label>
	<input class="ziteh-searchform__input" type="search" id="<?php echo esc_attr( $ziteh_search_id ); ?>" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'دنبال چه می‌گردید؟', 'ziteh-theme' ); ?>">
	<button class="ziteh-searchform__submit" type="submit"><?php esc_html_e( 'جست‌وجو', 'ziteh-theme' ); ?></button>
</form>
