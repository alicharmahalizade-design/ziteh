<?php
/**
 * Document head and the site header location.
 *
 * @package Ziteh_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="ziteh-skip-link" href="#ziteh-main"><?php esc_html_e( 'رفتن به محتوای اصلی', 'ziteh-theme' ); ?></a>

<div class="ziteh-site__wrap">
	<?php
	// An Elementor header template wins; otherwise the theme renders a working
	// header so the site is never left without navigation.
	if ( ! Ziteh_Theme::do_location( 'header' ) ) {
		ziteh_theme_fallback_header();
	}
	?>

	<main class="ziteh-site__main" id="ziteh-main">
