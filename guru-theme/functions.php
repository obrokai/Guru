<?php
/**
 * Guru theme — bootstrap.
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GURU_VERSION', '1.0.0' );

/**
 * Theme supports and menus.
 */
function guru_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );

	register_nav_menus(
		array(
			'primary' => __( 'Primary navigation', 'guru' ),
			'footer'  => __( 'Footer navigation', 'guru' ),
		)
	);
}
add_action( 'after_setup_theme', 'guru_setup' );

/**
 * Front-end assets.
 */
function guru_assets() {
	$css = get_stylesheet_directory() . '/style.css';
	wp_enqueue_style(
		'guru',
		get_stylesheet_uri(),
		array(),
		file_exists( $css ) ? filemtime( $css ) : GURU_VERSION
	);

	wp_enqueue_style(
		'guru-fonts',
		'https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400..900&display=swap',
		array(),
		null
	);

	$js = get_stylesheet_directory() . '/assets/js/main.js';
	wp_enqueue_script(
		'guru',
		get_stylesheet_directory_uri() . '/assets/js/main.js',
		array(),
		file_exists( $js ) ? filemtime( $js ) : GURU_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'guru_assets' );

/**
 * Preconnect to the Google Fonts hosts.
 */
function guru_resource_hints( $hints, $relation ) {
	if ( 'preconnect' === $relation ) {
		$hints[] = array( 'href' => 'https://fonts.googleapis.com' );
		$hints[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => '',
		);
	}
	return $hints;
}
add_filter( 'wp_resource_hints', 'guru_resource_hints', 10, 2 );

require_once get_stylesheet_directory() . '/inc/post-types.php';
require_once get_stylesheet_directory() . '/inc/fields.php';
require_once get_stylesheet_directory() . '/inc/template-helpers.php';
require_once get_stylesheet_directory() . '/inc/customizer.php';
require_once get_stylesheet_directory() . '/inc/contact-form.php';
