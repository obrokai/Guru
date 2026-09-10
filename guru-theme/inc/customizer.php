<?php
/**
 * Customizer settings — the site-wide text the client edits most.
 *
 * Appearance → Customize → Guru site settings.
 *
 * Every get_theme_mod() the templates read is registered here, so the client
 * can change any of it without touching code. Multi-line fields use one item
 * per line, with " | " between the parts, matching how the templates parse them.
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default value of a setting, shared by the Customizer and the templates.
 *
 * @param string $id Setting ID.
 * @return string
 */
function guru_default( $id ) {
	static $defaults = null;

	if ( null === $defaults ) {
		$defaults = array(
			// Contact.
			'guru_email'             => 'hello@guruonline.com.hk',
			'guru_phone'             => '+852 3952 1100',
			'guru_city'              => 'Hong Kong',
			'guru_whatsapp'          => 'https://wa.me/85200000000',
			'guru_contact_heading'   => 'Talk to Us and We Build Something Great Together.',
			'guru_contact_cta_heading' => "Let's Build Something Great Together.",
			'guru_contact_cta_button'  => 'Get In Touch',
			// Homepage.
			'guru_solutions_lead'    => 'We craft integrated digital marketing solutions for ambitious brands — blending strategy, creativity, media, data, technology and AI into measurable growth.',
			'guru_home_about_title'  => 'Your Holistic Digital Partner',
			'guru_home_about_stats'  => "200 |  | Digital Specialists\n500 | + | International Clients\n6 |  | Offices\n100 | + | Awards & Recognitions",
			'guru_home_about_copy'   => "For nearly two decades, Guru has evolved from its founding in 2007 into a listed 4A's digital marketing agency and a true digital marketing powerhouse, driven by more than 300 experienced, creative and future-ready minds.\n\nBuilt for the next era of marketing, Guru is a digital marketing enabler for ambitious brands — connecting strategy, creativity, media, data, technology and AI to unlock growth, accelerate relevance and create measurable business impact.",
			'guru_home_about_cta'    => 'Learn More About Us',
			// About page.
			'guru_founded'           => 'Est. 2007',
			'guru_credential'        => "Listed 4A's Agency",
			'guru_story_heading'     => "Today's\nBreakthrough Is\nTomorrow's Legend",
			'guru_clients_heading'   => 'A Roster of Brands That Trust Guru.',
			'guru_offices_heading'   => "Our Offices,\nOne Vision.",
			'guru_offices'           => "Hong Kong | Unit 1201 & 1216, Two Harbour Square, 180 Wai Yip Street, Kwun Tong, Kowloon | Head Office\nGuangzhou | Mainland China\nNanjing | Mainland China\nTaipei | Taiwan\nSingapore | Southeast Asia\nMacao | Macao SAR",
			// Blog.
			'guru_blog_intro'        => 'Perspectives on digital marketing, brand strategy, influencer culture and the future of creativity.',
			// Investor.
			'guru_stock_code'        => '08121',
			'guru_exchange'          => 'HKEX GEM',
			'guru_board_lot'         => '2,000 shares',
			'guru_fiscal_year'       => '31 March',
			'guru_investor_docs'     => "2025.11.13 | Interim Results Announcement | Six Months Ended 30 September 2025 | https://www.hkexnews.hk | https://www.hkexnews.hk\n2025.11.13 | Interim Report 2025 | | https://www.hkexnews.hk | https://www.hkexnews.hk\n2025.06.30 | Annual Report 2025 | | https://www.hkexnews.hk | https://www.hkexnews.hk\n2024.11.14 | Interim Results Announcement | Six Months Ended 30 September 2024 | https://www.hkexnews.hk | https://www.hkexnews.hk\n2024.11.14 | Interim Report 2024 | | https://www.hkexnews.hk | https://www.hkexnews.hk\n2024.06.28 | Annual Report 2024 | | https://www.hkexnews.hk | https://www.hkexnews.hk",
			'guru_investor_entity'   => "Guoen\n(Holdings) Limited",
			'guru_investor_intro'    => 'Guoen (Holdings) Limited is a leading internet company that has driven sustained and consistent growth across the region. Our strong management team has facilitated continuous enhancement in innovation, creativity and technological insights, which have been proven successful in our extensive strategic alliances with global clientele.',
			'guru_investor_more_url' => 'https://www.hkexnews.hk/listedco/listconews/GEM/listco_news_gem.aspx?stock=8121',
			'guru_investor_quote_url' => 'https://www.hkex.com.hk/Market-Data/Securities-Prices/Equities?sym=8121&sc_lang=en',
			'guru_investor_prev_url' => 'https://www.guruonline.com.hk/investor/',
			'guru_investor_governance' => "Corporate | Articles of Association | https://www.hkexnews.hk | EN\nCorporate | Board Committees & Charters | https://www.hkexnews.hk | View",
			'guru_investor_contacts' => "Hong Kong (Head Office) | +852 3952 1100 | Unit 1201 & 16, 12/F, Two Harbour Square, No. 180 Wai Yip Street, Kwun Tong, Kowloon\nGuangzhou | +852 3952 1100 | Unit 2405, 24/F, Building A, Fengxing Plaza\nNanjing | +852 3952 1100 | Unit 1010, Building C, Shimao 52+\nTaipei | +886 2-2370-8433 | Rm. 1201, 11F.-1, No. 142, Sec. 3, Minquan E. Rd.\nBeijing | | Jing'an Center, No. 8 East Section of the North 3rd Ring",
			'guru_investor_disclaimer' => 'The information on this page is for reference only and does not constitute investment advice.',
		);
	}

	return isset( $defaults[ $id ] ) ? $defaults[ $id ] : '';
}

/**
 * Theme mod with the shared default applied.
 *
 * @param string $id Setting ID.
 * @return string
 */
function guru_mod( $id ) {
	return (string) get_theme_mod( $id, guru_default( $id ) );
}

/**
 * Register a batch of plain settings + controls in one section.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 * @param string               $section      Section ID.
 * @param array                $fields       id => [ label, type, description ].
 */
function guru_customize_fields( $wp_customize, $section, $fields ) {
	foreach ( $fields as $id => $config ) {
		list( $label, $type ) = $config;
		$description          = isset( $config[2] ) ? $config[2] : '';

		switch ( $type ) {
			case 'email':
				$sanitize = 'sanitize_email';
				break;
			case 'url':
				$sanitize = 'esc_url_raw';
				break;
			case 'textarea':
				$sanitize = 'guru_sanitize_textarea';
				break;
			default:
				$sanitize = 'sanitize_text_field';
		}

		$wp_customize->add_setting(
			$id,
			array(
				'default'           => guru_default( $id ),
				'sanitize_callback' => $sanitize,
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$id,
			array(
				'label'       => $label,
				'section'     => $section,
				'type'        => $type,
				'description' => $description,
			)
		);
	}
}

/**
 * Register a video picker that falls back to a file bundled with the theme.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 * @param string               $id           Setting ID.
 * @param string               $section      Section ID.
 * @param string               $label        Control label.
 * @param string               $description  Help text.
 */
function guru_customize_video( $wp_customize, $id, $section, $label, $description ) {
	// A Media control stores the attachment ID, so sanitise as an integer (0 = none).
	$wp_customize->add_setting(
		$id,
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			$id,
			array(
				'label'       => $label,
				'section'     => $section,
				'mime_type'   => 'video',
				'description' => $description,
			)
		)
	);
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
			'description' => __( 'Contact details, social links, homepage and page copy, investor information.', 'guru' ),
			'priority'    => 20,
		)
	);

	$pipe = __( 'One per line. Separate the parts with " | ".', 'guru' );

	/* ---------------------------------------------------------- Contact */
	$wp_customize->add_section( 'guru_contact', array( 'title' => __( 'Contact details', 'guru' ), 'panel' => 'guru_panel', 'priority' => 10 ) );
	guru_customize_fields(
		$wp_customize,
		'guru_contact',
		array(
			'guru_email'           => array( __( 'Enquiry email', 'guru' ), 'email', __( 'Contact-form submissions are sent here.', 'guru' ) ),
			'guru_phone'           => array( __( 'Phone', 'guru' ), 'text' ),
			'guru_whatsapp'        => array( __( 'WhatsApp link', 'guru' ), 'url', __( 'Format: https://wa.me/852XXXXXXXX (country code, no spaces).', 'guru' ) ),
			'guru_city'            => array( __( 'City', 'guru' ), 'text' ),
			'guru_contact_heading' => array( __( 'Contact section heading', 'guru' ), 'text', __( 'Above the contact form in the footer.', 'guru' ) ),
			'guru_contact_cta_heading' => array( __( 'Compact footer heading', 'guru' ), 'text', __( 'The Work and Blog pages end with a short call-to-action instead of the form.', 'guru' ) ),
			'guru_contact_cta_button'  => array( __( 'Compact footer button', 'guru' ), 'text' ),
		)
	);

	/* ----------------------------------------------------------- Social */
	$wp_customize->add_section( 'guru_social', array( 'title' => __( 'Social links', 'guru' ), 'panel' => 'guru_panel', 'priority' => 20 ) );
	guru_customize_fields(
		$wp_customize,
		'guru_social',
		array(
			'guru_instagram' => array( __( 'Instagram URL', 'guru' ), 'url', __( 'Leave empty to hide this link.', 'guru' ) ),
			'guru_linkedin'  => array( __( 'LinkedIn URL', 'guru' ), 'url', __( 'Leave empty to hide this link.', 'guru' ) ),
			'guru_facebook'  => array( __( 'Facebook URL', 'guru' ), 'url', __( 'Leave empty to hide this link.', 'guru' ) ),
		)
	);

	/* --------------------------------------------------------- Homepage */
	$wp_customize->add_section(
		'guru_home',
		array(
			'title'       => __( 'Homepage', 'guru' ),
			'panel'       => 'guru_panel',
			'priority'    => 30,
			'description' => __( 'The Solutions and Showcase blocks come from the Solutions and Showcase menus; this section covers the remaining homepage copy.', 'guru' ),
		)
	);
	guru_customize_fields(
		$wp_customize,
		'guru_home',
		array(
			'guru_solutions_lead'   => array( __( '"Our Solutions" intro', 'guru' ), 'textarea' ),
			'guru_home_about_title' => array( __( 'About block — heading', 'guru' ), 'text' ),
			'guru_home_about_stats' => array( __( 'About block — statistics', 'guru' ), 'textarea', $pipe . ' ' . __( 'Format: number | suffix | label (e.g. "500 | + | International Clients").', 'guru' ) ),
			'guru_home_about_copy'  => array( __( 'About block — paragraphs', 'guru' ), 'textarea', __( 'Leave an empty line between paragraphs.', 'guru' ) ),
			'guru_home_about_cta'   => array( __( 'About block — button text', 'guru' ), 'text' ),
		)
	);
	guru_customize_video( $wp_customize, 'guru_hero_video', 'guru_home', __( 'Hero video (desktop)', 'guru' ), __( 'Leave empty to use the video bundled with the theme.', 'guru' ) );
	guru_customize_video( $wp_customize, 'guru_hero_video_mobile', 'guru_home', __( 'Hero video (mobile)', 'guru' ), __( 'Smaller file served under 900px wide.', 'guru' ) );

	/* ------------------------------------------------------- About page */
	$wp_customize->add_section(
		'guru_about',
		array(
			'title'       => __( 'About page', 'guru' ),
			'panel'       => 'guru_panel',
			'priority'    => 40,
			'description' => __( 'The About page title, intro (excerpt) and story text are edited on the page itself under Pages → About.', 'guru' ),
		)
	);
	guru_customize_fields(
		$wp_customize,
		'guru_about',
		array(
			'guru_founded'         => array( __( 'Founded label', 'guru' ), 'text' ),
			'guru_credential'      => array( __( 'Credential line', 'guru' ), 'text' ),
			'guru_story_heading'   => array( __( '"Our Story" heading', 'guru' ), 'textarea', __( 'Line breaks are kept.', 'guru' ) ),
			'guru_clients_heading' => array( __( 'Client wall heading', 'guru' ), 'text' ),
			'guru_offices_heading' => array( __( 'Offices heading', 'guru' ), 'textarea', __( 'Line breaks are kept.', 'guru' ) ),
			'guru_offices'         => array( __( 'Offices', 'guru' ), 'textarea', $pipe . ' ' . __( 'Format: City | Region or address | Badge (optional — a badge makes the card featured).', 'guru' ) ),
		)
	);
	guru_customize_video( $wp_customize, 'guru_world_map_video', 'guru_about', __( 'World map video', 'guru' ), __( 'Shown above the offices. Leave empty to use the video bundled with the theme.', 'guru' ) );

	/* ------------------------------------------------------------- Blog */
	$wp_customize->add_section( 'guru_blog', array( 'title' => __( 'Blog', 'guru' ), 'panel' => 'guru_panel', 'priority' => 50 ) );
	guru_customize_fields(
		$wp_customize,
		'guru_blog',
		array(
			'guru_blog_intro' => array( __( 'Blog intro', 'guru' ), 'textarea' ),
		)
	);

	/* --------------------------------------------------------- Investor */
	$wp_customize->add_section(
		'guru_investor',
		array(
			'title'       => __( 'Investor page', 'guru' ),
			'panel'       => 'guru_panel',
			'priority'    => 60,
			'description' => __( 'Listing information shown in the sidebar, and the document list.', 'guru' ),
		)
	);
	guru_customize_fields(
		$wp_customize,
		'guru_investor',
		array(
			'guru_investor_entity'     => array( __( 'Listed company name (heading)', 'guru' ), 'textarea', __( 'Line breaks are kept.', 'guru' ) ),
			'guru_investor_intro'      => array( __( 'Intro paragraph', 'guru' ), 'textarea', __( 'Used when the Investor page has no excerpt of its own.', 'guru' ) ),
			'guru_stock_code'          => array( __( 'Stock code', 'guru' ), 'text' ),
			'guru_exchange'            => array( __( 'Exchange', 'guru' ), 'text' ),
			'guru_board_lot'           => array( __( 'Board lot', 'guru' ), 'text' ),
			'guru_fiscal_year'         => array( __( 'Fiscal year end', 'guru' ), 'text' ),
			'guru_investor_docs'       => array( __( 'Financial results & reports', 'guru' ), 'textarea', $pipe . ' ' . __( 'Format: YYYY.MM.DD | Title | Subtitle (optional) | English PDF URL | Chinese PDF URL', 'guru' ) ),
			'guru_investor_more_url'   => array( __( '"View all announcements" link', 'guru' ), 'url' ),
			'guru_investor_governance' => array( __( 'Corporate governance documents', 'guru' ), 'textarea', $pipe . ' ' . __( 'Format: Label | Title | URL | Link text', 'guru' ) ),
			'guru_investor_quote_url'  => array( __( 'Stock quote link', 'guru' ), 'url' ),
			'guru_investor_prev_url'   => array( __( 'Previous IR website link', 'guru' ), 'url', __( 'Leave empty to hide.', 'guru' ) ),
			'guru_investor_contacts'   => array( __( 'IR & office contacts', 'guru' ), 'textarea', $pipe . ' ' . __( 'Format: City | Telephone (optional) | Address', 'guru' ) ),
			'guru_investor_disclaimer' => array( __( 'Disclaimer', 'guru' ), 'textarea' ),
		)
	);
}
add_action( 'customize_register', 'guru_customize_register' );

/**
 * Sanitise a multi-line setting, keeping line breaks and leaving URLs intact.
 *
 * sanitize_textarea_field() strips percent-encoded sequences, which mangles
 * PDF links in the "one per line, parts separated by |" fields; URL-shaped
 * parts go through esc_url_raw() instead.
 *
 * @param string $text Raw value.
 * @return string
 */
function guru_sanitize_textarea( $text ) {
	$lines = array();

	foreach ( preg_split( '/\r\n|\r|\n/', (string) $text ) as $line ) {
		if ( false === strpos( $line, '|' ) && ! preg_match( '#^\s*https?://#i', $line ) ) {
			$lines[] = sanitize_textarea_field( $line );
			continue;
		}
		$parts = array();
		foreach ( explode( '|', $line ) as $part ) {
			$part    = trim( $part );
			$parts[] = preg_match( '#^https?://#i', $part ) ? esc_url_raw( $part ) : sanitize_text_field( $part );
		}
		$lines[] = implode( ' | ', $parts );
	}

	return implode( "\n", $lines );
}

/**
 * Resolve a video URL chosen in the Customizer, falling back to a bundled file.
 *
 * @param string $mod  Theme mod holding an attachment ID (or, from older saves, a URL).
 * @param string $file File under assets/media/, used when the mod is empty.
 * @return string URL, or '' if neither is available.
 */
function guru_video_url( $mod, $file ) {
	$value = get_theme_mod( $mod, 0 );

	if ( $value ) {
		$url = is_numeric( $value ) ? wp_get_attachment_url( (int) $value ) : $value;
		if ( $url ) {
			return esc_url( $url );
		}
	}

	return guru_theme_media_url( $file );
}

/**
 * Hero video URL.
 *
 * @param string $which 'desktop' or 'mobile'.
 * @return string
 */
function guru_hero_video_url( $which = 'desktop' ) {
	return 'mobile' === $which
		? guru_video_url( 'guru_hero_video_mobile', 'hero-bg-mobile.mp4' )
		: guru_video_url( 'guru_hero_video', 'hero-bg.mp4' );
}

/**
 * Split a multi-line " | " field into rows of trimmed parts.
 *
 * @param string $text Raw setting value.
 * @return array[] One array of parts per non-empty line.
 */
function guru_pipe_rows( $text ) {
	$rows = array();

	foreach ( preg_split( '/\r\n|\r|\n/', (string) $text ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		$rows[] = array_map( 'trim', explode( '|', $line ) );
	}

	return $rows;
}
