<?php
/**
 * Customize → Guru site settings → Typography: site-wide font-size scaling.
 *
 * Four percentages (body text, section headings, page titles, navigation),
 * default 100%. They set CSS variables that the generated stylesheets in
 * assets/css/type/ multiply into every original font-size, so at 100% the site
 * renders exactly as designed and the responsive breakpoints are preserved.
 * The generated files come from tools/build-typography.py — re-run it after
 * editing any theme CSS.
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The four scale settings: id => [ label, description ].
 *
 * @return array
 */
function guru_typography_settings() {
	return array(
		'guru_fs_body'    => array(
			__( 'Body text', 'guru' ),
			__( 'Paragraphs, descriptions, card details, footer links.', 'guru' ),
		),
		'guru_fs_heading' => array(
			__( 'Section headings', 'guru' ),
			__( '"Our Solutions", "Our Work", card titles, the big statistics, the footer heading.', 'guru' ),
		),
		'guru_fs_hero'    => array(
			__( 'Page titles', 'guru' ),
			__( 'The large title at the top of About, Solutions, Work, Blog and Investor.', 'guru' ),
		),
		'guru_fs_nav'     => array(
			__( 'Navigation links', 'guru' ),
			__( 'The menu links in the header (the two buttons keep their size).', 'guru' ),
		),
	);
}

/**
 * Sanitize a scale percentage: whole number between 70 and 150.
 *
 * @param mixed $value Raw value.
 * @return int
 */
function guru_sanitize_scale( $value ) {
	$value = (int) $value;
	if ( $value < 70 || $value > 150 ) {
		return 100;
	}
	return $value;
}

/**
 * Register the Typography section.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function guru_typography_customize( $wp_customize ) {
	$wp_customize->add_section(
		'guru_typography',
		array(
			'title'       => __( 'Typography', 'guru' ),
			'panel'       => 'guru_panel',
			'priority'    => 70,
			'description' => __( 'Scale the site’s text sizes. 100% is the original design; changes keep the responsive layout and apply everywhere the group appears.', 'guru' ),
		)
	);

	foreach ( guru_typography_settings() as $id => $config ) {
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => 100,
				'sanitize_callback' => 'guru_sanitize_scale',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$id,
			array(
				'label'       => $config[0],
				'description' => $config[1] . ' ' . __( '(70–150%, default 100%)', 'guru' ),
				'section'     => 'guru_typography',
				'type'        => 'number',
				'input_attrs' => array(
					'min'  => 70,
					'max'  => 150,
					'step' => 5,
				),
			)
		);
	}
}
add_action( 'customize_register', 'guru_typography_customize', 20 );

/**
 * The CSS variables for the current scales, or '' when everything is at 100%.
 *
 * @return string
 */
function guru_typography_css() {
	$vars = array();
	foreach ( array_keys( guru_typography_settings() ) as $id ) {
		$pct = guru_sanitize_scale( get_theme_mod( $id, 100 ) );
		if ( 100 !== $pct ) {
			$vars[] = sprintf( '--%s: %s;', str_replace( '_', '-', $id ), rtrim( rtrim( number_format( $pct / 100, 2, '.', '' ), '0' ), '.' ) );
		}
	}
	return $vars ? ':root{' . implode( '', $vars ) . '}' : '';
}

/**
 * Enqueue the override stylesheet derived from a theme stylesheet, right after it.
 *
 * @param string $handle Handle of the stylesheet it derives from.
 * @param string $name   File name under assets/css/type/ (e.g. "style.css").
 */
function guru_enqueue_typography_css( $handle, $name ) {
	$path = get_stylesheet_directory() . '/assets/css/type/' . $name;
	if ( ! file_exists( $path ) ) {
		return;
	}
	wp_enqueue_style(
		$handle . '-type',
		get_stylesheet_directory_uri() . '/assets/css/type/' . $name,
		array( $handle ),
		filemtime( $path )
	);
}

/**
 * Output the variables once the base stylesheet is enqueued.
 */
function guru_typography_inline() {
	$css = guru_typography_css();
	if ( $css ) {
		wp_add_inline_style( 'guru', $css );
	}
}
add_action( 'wp_enqueue_scripts', 'guru_typography_inline', 20 );
