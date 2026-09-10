<?php
/**
 * Guru theme — bootstrap.
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GURU_VERSION', '1.1.0' );

/**
 * Theme supports and menus.
 */
function guru_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );

	// The About lead and the Investor intro are edited as the page's excerpt.
	add_post_type_support( 'page', 'excerpt' );

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

	// Page-specific styles, loaded only where they are needed.
	$page_css = '';
	if ( is_page_template( 'page-about.php' ) || is_page( 'about' ) ) {
		$page_css = 'about';
	} elseif ( is_post_type_archive( 'work' ) || is_singular( 'work' ) || is_tax( 'work_category' ) || is_page( 'work' ) ) {
		$page_css = 'work';
	} elseif ( ( is_home() && ! is_front_page() ) || is_singular( 'post' ) || is_archive() || is_search() || is_404() || is_page( 'blog' ) ) {
		$page_css = 'blog';
	} elseif ( is_page_template( 'page-services.php' ) || is_page( 'services' ) || is_page( 'solutions' ) || is_singular( 'solution' ) ) {
		$page_css = 'services';
	} elseif ( is_page_template( 'page-investor.php' ) || is_page( 'investor' ) ) {
		$page_css = 'investor';
	} elseif ( ! is_front_page() && is_singular() ) {
		// Any other page or post type uses the single-post layout from blog.css.
		$page_css = 'blog';
	}

	if ( $page_css ) {
		$path = get_stylesheet_directory() . '/assets/css/' . $page_css . '.css';
		if ( file_exists( $path ) ) {
			wp_enqueue_style(
				'guru-' . $page_css,
				get_stylesheet_directory_uri() . '/assets/css/' . $page_css . '.css',
				array( 'guru' ),
				filemtime( $path )
			);
		}
	}

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

require_once get_stylesheet_directory() . '/inc/template-helpers.php';
require_once get_stylesheet_directory() . '/inc/customizer.php';
require_once get_stylesheet_directory() . '/inc/post-types.php';
require_once get_stylesheet_directory() . '/inc/fields.php';
require_once get_stylesheet_directory() . '/inc/enquiries.php';
require_once get_stylesheet_directory() . '/inc/contact-form.php';
require_once get_stylesheet_directory() . '/inc/importer.php';
