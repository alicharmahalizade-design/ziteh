<?php
/**
 * Shared document head for Ziteh full-page templates.
 *
 * @package ZitehCore
 */

defined( 'ABSPATH' ) || exit;
ZT_Templates::$canvas = true;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="<?php echo esc_attr( zt_opt( 'general.c_paper', '#FAF8F5' ) ); ?>">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php
wp_body_open();
