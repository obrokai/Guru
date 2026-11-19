<?php
/**
 * SEO groundwork (v1.3.0, client spec of 2026-11-17).
 *
 * Yoast SEO owns the title, description, canonical, robots, sitemap and schema
 * tags — this file never prints a second copy of them. It only:
 *   - marks the English front end as en-HK (the admin stays in Chinese);
 *   - gives Yoast a description to fall back on when a page has none of its own;
 *   - keeps the Investor full-list views (?docs=…) out of search results;
 *   - prints a plain description only if no SEO plugin is active at all.
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * True while a multilingual plugin decides each page's language itself.
 *
 * @return bool
 */
function guru_multilingual_active() {
	return function_exists( 'pll_current_language' ) || defined( 'ICL_SITEPRESS_VERSION' ) || class_exists( 'TRP_Translate_Press' );
}

/**
 * True when an SEO plugin prints the meta tags.
 *
 * @return bool
 */
function guru_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' );
}

/**
 * Language of the public pages. The WordPress site language is Traditional
 * Chinese so the admin is in Chinese, but every public page is English.
 *
 * @return string BCP 47 tag, e.g. "en-HK".
 */
function guru_frontend_lang() {
	return (string) apply_filters( 'guru_frontend_lang', 'en-HK' );
}

/**
 * <html lang="…"> on the front end (spec #3).
 *
 * @param string $output Attributes.
 * @return string
 */
function guru_language_attributes( $output ) {
	if ( is_admin() || guru_multilingual_active() ) {
		return $output;
	}
	$lang = guru_frontend_lang();
	if ( preg_match( '/lang="[^"]*"/', $output ) ) {
		return preg_replace( '/lang="[^"]*"/', 'lang="' . esc_attr( $lang ) . '"', $output );
	}
	return trim( $output . ' lang="' . esc_attr( $lang ) . '"' );
}
add_filter( 'language_attributes', 'guru_language_attributes', 20 );

/**
 * Yoast's og:locale follows the site language (zh_TW); match the page instead.
 *
 * @param string $locale Locale.
 * @return string
 */
function guru_wpseo_locale( $locale ) {
	if ( guru_multilingual_active() ) {
		return $locale;
	}
	return str_replace( '-', '_', guru_frontend_lang() );
}
add_filter( 'wpseo_locale', 'guru_wpseo_locale' );

/**
 * Automatic description for the current page: the excerpt, or the start of
 * the text, or — for a showcase item without text — its title and client.
 * Used only when nobody has written one in Yoast (spec #12 "dynamic fallback").
 *
 * @return string
 */
function guru_auto_description() {
	$text = '';

	if ( is_front_page() ) {
		$text = guru_mod( 'guru_hero_intro' );
	} elseif ( is_page_template( 'page-services.php' ) ) {
		$text = guru_mod( 'guru_solutions_lead' );
	} elseif ( is_page_template( 'page-investor.php' ) ) {
		$text = has_excerpt() ? get_the_excerpt() : guru_mod( 'guru_investor_intro' );
	} elseif ( is_post_type_archive( 'work' ) || is_tax( 'work_category' ) ) {
		$text = __( 'Selected campaigns, films and digital projects by GURU Online for brands in Hong Kong, Mainland China and international markets.', 'guru' );
	} elseif ( is_home() ) {
		$text = guru_mod( 'guru_blog_intro' );
	} elseif ( is_singular() ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post ) {
			$text = has_excerpt( $post ) ? $post->post_excerpt : wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
			if ( '' === trim( $text ) && 'work' === $post->post_type ) {
				$client = (string) get_post_meta( $post->ID, '_guru_client', true );
				/* translators: 1: project title, 2: client name. */
				$text = $client ? sprintf( __( '%1$s — a project for %2$s by GURU Online.', 'guru' ), get_the_title( $post ), $client )
					/* translators: %s: project title. */
					: sprintf( __( '%s — a project by GURU Online.', 'guru' ), get_the_title( $post ) );
			}
		}
	}

	$text = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( (string) $text ) ) );
	return $text ? wp_html_excerpt( $text, 155, '…' ) : '';
}

/**
 * Yoast: fill an empty description with the automatic one.
 *
 * @param string $desc Description from Yoast (empty when none is set).
 * @return string
 */
function guru_wpseo_metadesc( $desc ) {
	return ( is_string( $desc ) && '' !== trim( $desc ) ) ? $desc : guru_auto_description();
}
add_filter( 'wpseo_metadesc', 'guru_wpseo_metadesc' );

/**
 * True on the Investor page's full-list views (?docs=…&pg=…), which repeat
 * the same documents in different slices.
 *
 * @return bool
 */
function guru_is_filtered_list() {
	return isset( $_GET['docs'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
}

/**
 * Keep those list views out of the index but let crawlers follow their links
 * to the documents (spec 4.1: filter combinations noindex by default).
 *
 * @param string $robots Yoast robots value.
 * @return string
 */
function guru_wpseo_robots( $robots ) {
	return guru_is_filtered_list() ? 'noindex, follow' : $robots;
}
add_filter( 'wpseo_robots', 'guru_wpseo_robots' );

/**
 * Same rule through core's robots tag (used when Yoast is not active).
 *
 * @param array $robots Directives.
 * @return array
 */
function guru_wp_robots( $robots ) {
	if ( ! guru_seo_plugin_active() && guru_is_filtered_list() ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
	}
	return $robots;
}
add_filter( 'wp_robots', 'guru_wp_robots' );

/**
 * No SEO plugin at all: print the automatic description so the page is not bare.
 */
function guru_fallback_meta_description() {
	if ( is_admin() || guru_seo_plugin_active() ) {
		return;
	}
	$desc = guru_auto_description();
	if ( $desc ) {
		echo '<meta name="description" content="' . esc_attr( $desc ) . '" />' . "\n";
	}
}
add_action( 'wp_head', 'guru_fallback_meta_description', 1 );
