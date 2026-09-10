<?php
/**
 * 301 redirects from the previous site's URLs to their new homes.
 *
 * The old site's pages, "service" and "portfolio-item" entries are still in the
 * database (Pods registers those post types), so without this they would render
 * as unstyled leftovers. Investor documents keep their URLs and are not touched.
 * Legal pages (privacy-policy, fanalytics-privacy) are kept as they are.
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Old service slug → slug of the Solution it maps to (the hash on the Solutions page).
 *
 * @return array
 */
function guru_legacy_service_map() {
	return array(
		'ar-production'                 => 'video-production',
		'multimedia-creation'           => 'video-production',
		'social-media-marketing'        => 'social-media-marketing',
		'digital-performance-marketing' => 'social-media-marketing',
		'chatbot'                       => 'ai-development',
		'whatsapp-marketing'            => 'ai-development',
		'digital-activation-technology' => 'ai-development',
		'search-strategy-integration'   => 'geo-search-strategy',
		'china-marketing'               => 'china-digital-expansion',
		'e-commerce-management'         => 'china-digital-expansion',
		'influencer-marketing'          => 'influencer-marketing',
		'global-marketing'              => 'go-global',
	);
}

/**
 * Redirect legacy paths before any template loads.
 */
function guru_legacy_redirects() {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
		return;
	}

	$request = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$path    = trim( (string) wp_parse_url( $request, PHP_URL_PATH ), '/' );

	// Sub-directory installs (a /staging/ copy): compare paths relative to the site root.
	$base = trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );
	if ( $base && 0 === strpos( $path, $base ) ) {
		$path = trim( substr( $path, strlen( $base ) ), '/' );
	}
	$path = strtolower( urldecode( $path ) );

	if ( '' === $path ) {
		return;
	}

	$home      = home_url( '/' );
	$about     = guru_page_url( 'about', '/about/', 'page-about.php' );
	$solutions = guru_page_url( array( 'solutions', 'services' ), '/solutions/', 'page-services.php' );
	$investor  = guru_page_url( 'investor', '/investor/', 'page-investor.php' );
	$work      = get_post_type_archive_link( 'work' );
	$blog_id   = (int) get_option( 'page_for_posts' );
	$blog      = $blog_id ? get_permalink( $blog_id ) : home_url( '/blog/' );

	$exact = array(
		'about-us'                                => $about,
		'our-work'                                => $work,
		'contact-us'                              => $home . '#contact',
		'home-new'                                => $home,
		'test-chatbot'                            => $home,
		'd-bizsolution'                           => $solutions,
		'global-marketing-cn'                     => $solutions . '#go-global',
		'blog/digital-marketing-solution'         => $blog,
		'last-announcement'                       => $investor,
		'financial-information'                   => $investor,
		'document-category/last-announcement'     => $investor,
		'document-category/financial-information' => $investor,
		'service'                                 => $solutions,
		'portfolio-item'                          => $work,
	);

	$target = '';

	if ( isset( $exact[ $path ] ) ) {
		$target = $exact[ $path ];
	} elseif ( preg_match( '#^service/([^/]+)$#', $path, $m ) ) {
		$map    = guru_legacy_service_map();
		$target = $solutions . ( isset( $map[ $m[1] ] ) ? '#' . $map[ $m[1] ] : '' );
	} elseif ( preg_match( '#^portfolio-item/#', $path ) ) {
		$target = $work;
	} elseif ( is_page() ) {
		// A page the importer moved aside (now a draft) still resolves for editors — send them on.
		$replaced_by = (string) get_post_meta( get_queried_object_id(), '_guru_replaced_by', true );
		if ( $replaced_by ) {
			$target = guru_page_url( $replaced_by, '/' );
		}
	}

	if ( $target ) {
		wp_safe_redirect( $target, 301 );
		exit;
	}
}
add_action( 'template_redirect', 'guru_legacy_redirects', 1 );
