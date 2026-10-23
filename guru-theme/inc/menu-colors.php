<?php
/**
 * Menu colours — Customize → Guru site settings → Menu colours.
 *
 * Covers the top navigation (logo pill and link pill), the mobile menu panel and
 * the two buttons in both. Only colours changed from the original design are
 * output, so an untouched site renders exactly as before.
 *
 * Also lets Editors open the Customizer: the client's day-to-day accounts are
 * Editors, and every site setting lives there.
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Menu colour settings: id => [ label, default ].
 *
 * @return array[]
 */
function guru_menu_color_settings() {
	return array(
		'guru_menu_bg'       => array( __( 'Menu background', 'guru' ), '#f7f4e8' ),
		'guru_menu_text'     => array( __( 'Menu text', 'guru' ), '#0e0e10' ),
		'guru_menu_cta'      => array( __( '"Contact Us" button', 'guru' ), '#1b23e0' ),
		'guru_menu_whatsapp' => array( __( '"WhatsApp" button', 'guru' ), '#25d366' ),
	);
}

/**
 * Register the Menu colours section.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function guru_menu_colors_customize( $wp_customize ) {
	$wp_customize->add_section(
		'guru_menu_colors',
		array(
			'title'       => __( 'Menu colours', 'guru' ),
			'panel'       => 'guru_panel',
			'priority'    => 25,
			'description' => __( 'The top menu, the mobile menu and their two buttons. "Default" restores the original colour.', 'guru' ),
		)
	);

	foreach ( guru_menu_color_settings() as $id => $config ) {
		list( $label, $default ) = $config;
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => $default,
				'sanitize_callback' => 'sanitize_hex_color',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				$id,
				array(
					'label'   => $label,
					'section' => 'guru_menu_colors',
				)
			)
		);
	}
}
add_action( 'customize_register', 'guru_menu_colors_customize', 20 );

/**
 * CSS for the colours that differ from the defaults, or '' when none do.
 *
 * @return string
 */
function guru_menu_colors_css() {
	$css = '';

	foreach ( guru_menu_color_settings() as $id => $config ) {
		$color = sanitize_hex_color( get_theme_mod( $id, $config[1] ) );
		if ( ! $color || strtolower( $color ) === $config[1] ) {
			continue;
		}

		switch ( $id ) {
			case 'guru_menu_bg':
				// Keep the slight see-through of the original so the blur still shows.
				$hex = ltrim( $color, '#' );
				if ( 3 === strlen( $hex ) ) {
					$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
				}
				list( $r, $g, $b ) = array_map( 'hexdec', str_split( $hex, 2 ) );
				$css .= sprintf( '.gn-logo,.gn-pill,.gn-mobile-menu{background:rgba(%d,%d,%d,0.95)}', $r, $g, $b );
				break;
			case 'guru_menu_text':
				$css .= sprintf( '.gn-link,.gn-mobile-link{color:%1$s}.gn-hamburger span{background:%1$s}', $color );
				break;
			case 'guru_menu_cta':
				$css .= sprintf( '.gn-link-cta{background:%s}', $color );
				break;
			case 'guru_menu_whatsapp':
				$css .= sprintf( '.gn-link-whatsapp,.gn-link-whatsapp:hover{background:%s}', $color );
				break;
		}
	}

	return $css;
}

/**
 * Output the colours after the base stylesheet.
 */
function guru_menu_colors_inline() {
	$css = guru_menu_colors_css();
	if ( $css ) {
		wp_add_inline_style( 'guru', $css );
	}
}
add_action( 'wp_enqueue_scripts', 'guru_menu_colors_inline', 20 );

/**
 * Give Editors the "Appearance" menu (Customize, Menus).
 *
 * Granted at run time rather than saved to the role, so switching themes takes
 * it away again. Theme tools that change data (the importer) still need an
 * Administrator.
 *
 * @param bool[] $allcaps The user's capabilities.
 * @return bool[]
 */
function guru_editors_can_customize( $allcaps ) {
	if ( ! empty( $allcaps['edit_others_pages'] ) && ! empty( $allcaps['publish_pages'] ) ) {
		$allcaps['edit_theme_options'] = true;
	}
	return $allcaps;
}
add_filter( 'user_has_cap', 'guru_editors_can_customize' );
