<?php
/**
 * Google Tag Manager and Google Analytics 4 (v1.4.4).
 *
 * Customize → Guru site settings → Tracking. The live site already uses the GTM
 * container GTM-K5XVQ9V (it holds the Google Ads conversion tags), so that is the
 * default: switching themes keeps tracking running with nothing to fill in.
 *
 * Codes load only on the live site address, so the staging copy (same database
 * settings, different address) never sends test visits or test enquiries into the
 * client's Analytics and Ads reports. Not loaded in the Customizer preview either.
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * "GTM-XXXXXXX" or empty.
 *
 * @param string $value Raw input.
 * @return string
 */
function guru_sanitize_gtm_id( $value ) {
	$value = strtoupper( trim( (string) $value ) );
	return preg_match( '/^GTM-[A-Z0-9]{4,12}$/', $value ) ? $value : '';
}

/**
 * "G-XXXXXXXXXX" or empty.
 *
 * @param string $value Raw input.
 * @return string
 */
function guru_sanitize_ga4_id( $value ) {
	$value = strtoupper( trim( (string) $value ) );
	return preg_match( '/^G-[A-Z0-9]{4,14}$/', $value ) ? $value : '';
}

/**
 * Host and path of an address, lower case, without scheme or trailing slash —
 * so http/https and a trailing slash do not matter when comparing.
 *
 * @param string $url Address.
 * @return string
 */
function guru_tracking_address( $url ) {
	$parts = wp_parse_url( strtolower( trim( (string) $url ) ) );
	if ( empty( $parts['host'] ) ) {
		return '';
	}
	return $parts['host'] . untrailingslashit( isset( $parts['path'] ) ? $parts['path'] : '' );
}

/**
 * Whether tracking codes load on this site: only at the live address (empty = everywhere).
 *
 * @return bool
 */
function guru_tracking_enabled() {
	if ( is_admin() || is_customize_preview() ) {
		return false;
	}
	$live = guru_tracking_address( guru_mod( 'guru_tracking_host' ) );
	return '' === $live || guru_tracking_address( home_url( '/' ) ) === $live;
}

/**
 * Customizer section.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function guru_tracking_customize( $wp_customize ) {
	$wp_customize->add_section(
		'guru_tracking',
		array(
			'title'       => __( 'Tracking (Google)', 'guru' ),
			'panel'       => 'guru_panel',
			'priority'    => 70,
			'description' => __( 'Google Tag Manager and Google Analytics 4. The codes load only on the live site address below, so test visits on the staging copy are not counted. Leave a box empty to turn that code off.', 'guru' ),
		)
	);

	$fields = array(
		'guru_gtm_id'        => array(
			__( 'Google Tag Manager container ID', 'guru' ),
			__( 'Format: GTM-XXXXXXX. GTM-K5XVQ9V is the container the old website used; it holds the Google Ads conversion tags.', 'guru' ),
			'guru_sanitize_gtm_id',
		),
		'guru_ga4_id'        => array(
			__( 'Google Analytics 4 measurement ID', 'guru' ),
			__( 'Format: G-XXXXXXXXXX. Leave empty if GA4 is already added another way: by a tag inside the GTM container, or by the Site Kit plugin (Site Kit → Settings). Adding it twice counts every visit twice.', 'guru' ),
			'guru_sanitize_ga4_id',
		),
		'guru_tracking_host' => array(
			__( 'Live site address', 'guru' ),
			__( 'The codes load only when the site runs at this address. Leave empty to load them everywhere (not recommended while a staging copy exists).', 'guru' ),
			'esc_url_raw',
		),
	);
	foreach ( $fields as $id => $config ) {
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => guru_default( $id ),
				'sanitize_callback' => $config[2],
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$id,
			array(
				'label'       => $config[0],
				'description' => $config[1],
				'section'     => 'guru_tracking',
				'type'        => 'guru_tracking_host' === $id ? 'url' : 'text',
			)
		);
	}
}
add_action( 'customize_register', 'guru_tracking_customize', 20 );

/**
 * GTM container script, as high in <head> as possible (Google's instructions).
 */
function guru_tracking_head_gtm() {
	$gtm = guru_sanitize_gtm_id( guru_mod( 'guru_gtm_id' ) );
	if ( ! $gtm ) {
		return;
	}
	if ( ! guru_tracking_enabled() ) {
		// A marker, so a check of the staging copy can see the code is held back on purpose.
		echo "<!-- Guru: Google Tag Manager is off at this address (Customize → Tracking → Live site address). -->\n";
		return;
	}
	?>
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','<?php echo esc_js( $gtm ); ?>');</script>
<!-- End Google Tag Manager -->
	<?php
}
add_action( 'wp_head', 'guru_tracking_head_gtm', 1 );

/**
 * GA4 tag, after the enqueued scripts so a Site Kit tag for the same ID can be spotted.
 */
function guru_tracking_head_ga4() {
	$ga4 = guru_sanitize_ga4_id( guru_mod( 'guru_ga4_id' ) );
	if ( ! $ga4 || ! guru_tracking_enabled() ) {
		return;
	}
	// Site Kit prints its own gtag.js ("google_gtagjs"); a second copy would double-count.
	if ( wp_script_is( 'google_gtagjs', 'done' ) ) {
		echo "<!-- Guru: GA4 is already added by Site Kit, so the theme does not add it again. -->\n";
		return;
	}
	?>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $ga4 ); ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?php echo esc_js( $ga4 ); ?>');</script>
	<?php
}
add_action( 'wp_head', 'guru_tracking_head_ga4', 20 );

/**
 * GTM fallback for visitors without JavaScript, right after <body>.
 */
function guru_tracking_body_gtm() {
	$gtm = guru_sanitize_gtm_id( guru_mod( 'guru_gtm_id' ) );
	if ( ! $gtm || ! guru_tracking_enabled() ) {
		return;
	}
	printf(
		'<noscript><iframe src="%s" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>' . "\n",
		esc_url( 'https://www.googletagmanager.com/ns.html?id=' . $gtm )
	);
}
add_action( 'wp_body_open', 'guru_tracking_body_gtm', 1 );
