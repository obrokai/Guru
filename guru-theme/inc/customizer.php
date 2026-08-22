<?php
/**
 * Customizer settings — the site-wide text the client edits most.
 *
 * Appearance → Customize → Guru site settings.
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register settings.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 */
function guru_customize_register( $wp_customize ) {

	$wp_customize->add_panel(
		'guru_panel',
		array(
			'title'       => __( 'Guru site settings', 'guru' ),
			'description' => __( 'Contact details, social links and the homepage hero.', 'guru' ),
			'priority'    => 20,
		)
	);

	/* ---------------------------------------------------------- Contact */
	$wp_customize->add_section(
		'guru_contact',
		array(
			'title' => __( 'Contact details', 'guru' ),
			'panel' => 'guru_panel',
		)
	);

	$fields = array(
		'guru_email'           => array( __( 'Enquiry email', 'guru' ), 'hello@guruonline.com.hk', 'sanitize_email', 'email' ),
		'guru_phone'           => array( __( 'Phone', 'guru' ), '+852 3952 1100', 'sanitize_text_field', 'text' ),
		'guru_city'            => array( __( 'City', 'guru' ), 'Hong Kong', 'sanitize_text_field', 'text' ),
		'guru_whatsapp'        => array( __( 'WhatsApp link', 'guru' ), 'https://wa.me/85200000000', 'esc_url_raw', 'url' ),
		'guru_contact_heading' => array( __( 'Contact section heading', 'guru' ), 'Talk to Us and We Build Something Great Together.', 'sanitize_text_field', 'text' ),
	);

	foreach ( $fields as $id => $config ) {
		list( $label, $default, $sanitize, $type ) = $config;

		$wp_customize->add_setting(
			$id,
			array(
				'default'           => $default,
				'sanitize_callback' => $sanitize,
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$id,
			array(
				'label'   => $label,
				'section' => 'guru_contact',
				'type'    => $type,
			)
		);
	}

	/* ----------------------------------------------------------- Social */
	$wp_customize->add_section(
		'guru_social',
		array(
			'title' => __( 'Social links', 'guru' ),
			'panel' => 'guru_panel',
		)
	);

	foreach ( array(
		'guru_instagram' => __( 'Instagram URL', 'guru' ),
		'guru_linkedin'  => __( 'LinkedIn URL', 'guru' ),
		'guru_facebook'  => __( 'Facebook URL', 'guru' ),
	) as $id => $label ) {
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
			)
		);
		$wp_customize->add_control(
			$id,
			array(
				'label'       => $label,
				'section'     => 'guru_social',
				'type'        => 'url',
				'description' => __( 'Leave empty to hide this link.', 'guru' ),
			)
		);
	}

	/* ------------------------------------------------------------- Hero */
	$wp_customize->add_section(
		'guru_hero',
		array(
			'title'       => __( 'Homepage hero', 'guru' ),
			'panel'       => 'guru_panel',
			'description' => __( 'The background video shown at the top of the homepage.', 'guru' ),
		)
	);

	$wp_customize->add_setting(
		'guru_hero_video',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'guru_hero_video',
			array(
				'label'       => __( 'Hero video (desktop)', 'guru' ),
				'section'     => 'guru_hero',
				'mime_type'   => 'video',
				'description' => __( 'Leave empty to use the video bundled with the theme.', 'guru' ),
			)
		)
	);

	$wp_customize->add_setting(
		'guru_hero_video_mobile',
		array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'guru_hero_video_mobile',
			array(
				'label'       => __( 'Hero video (mobile)', 'guru' ),
				'section'     => 'guru_hero',
				'mime_type'   => 'video',
				'description' => __( 'Smaller file served under 900px wide.', 'guru' ),
			)
		)
	);
}
add_action( 'customize_register', 'guru_customize_register' );

/**
 * Resolve a hero video URL, falling back to the bundled file.
 *
 * @param string $which 'desktop' or 'mobile'.
 * @return string
 */
function guru_hero_video_url( $which = 'desktop' ) {
	$mod = 'mobile' === $which ? 'guru_hero_video_mobile' : 'guru_hero_video';
	$url = get_theme_mod( $mod, '' );

	if ( $url ) {
		// Media controls may store an attachment ID.
		if ( is_numeric( $url ) ) {
			$url = wp_get_attachment_url( (int) $url );
		}
		if ( $url ) {
			return esc_url( $url );
		}
	}

	$file = 'mobile' === $which ? 'media/hero-bg-mobile.mp4' : 'media/hero-bg.mp4';
	return guru_asset( $file );
}
