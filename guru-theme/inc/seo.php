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
 * Public pages run in English (v1.4.2).
 *
 * The WordPress site language stays Traditional Chinese so the admin is in
 * Chinese, but that leaked into the public pages: Yoast's Schema said
 * inLanguage "zh-TW", its share-card label read 「作者:」 and WordPress titles
 * such as 「找不到符合條件的頁面」 appeared on an English site. Once WordPress has
 * worked out which page is being shown (the "wp" action — it never runs for
 * the admin, the REST API, the login page or form posts), the request is
 * switched to English, so every string printed afterwards is English.
 */
function guru_frontend_switch_locale() {
	if ( is_admin() || guru_multilingual_active() || ! function_exists( 'switch_to_locale' ) ) {
		return;
	}
	switch_to_locale( 'en_US' );
}
add_action( 'wp', 'guru_frontend_switch_locale', 0 );

/**
 * Yoast Schema: every graph piece carries the same language as <html lang>.
 *
 * @param string $lang Language from get_bloginfo( 'language' ).
 * @return string
 */
function guru_schema_language( $lang ) {
	return guru_multilingual_active() ? $lang : guru_frontend_lang();
}
add_filter( 'wpseo_schema_piece_language', 'guru_schema_language' );

/**
 * Yoast's og:locale follows the site language (zh_TW); match the page instead.
 *
 * Yoast checks its first value against Facebook's locale list, which has no
 * en_HK, and turns it into en_US; the final filter below sets the page's own
 * locale after that check so all three tags agree.
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
add_filter( 'wpseo_og_locale', 'guru_wpseo_locale' );

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

/* ===================================================================
 * Article author in Yoast (v1.4.2, spec #15).
 *
 * The page no longer shows "admin", and neither does anything a search
 * engine reads: Schema, <meta name="author"> and the share card all use
 * guru_article_author(), the same record as the visible byline.
 * =================================================================== */

/**
 * True on a single Insights article.
 *
 * @return bool
 */
function guru_is_article() {
	return is_singular( 'post' );
}

/**
 * Point every reference to one Schema @id at another.
 *
 * @param mixed  $node   Graph or part of it.
 * @param string $old_id Old @id.
 * @param string $new_id New @id.
 * @return mixed
 */
function guru_schema_swap_id( $node, $old_id, $new_id ) {
	if ( ! is_array( $node ) ) {
		return $node;
	}
	if ( isset( $node['@id'] ) && $old_id === $node['@id'] && count( $node ) <= 2 ) {
		// A reference ({"@id": …} or {"name": …, "@id": …}); full nodes are handled by the caller.
		$node['@id'] = $new_id;
		return $node;
	}
	foreach ( $node as $key => $value ) {
		$node[ $key ] = guru_schema_swap_id( $value, $old_id, $new_id );
	}
	return $node;
}

/**
 * True when a Schema node is of the given type.
 *
 * @param array  $node Node.
 * @param string $type Type.
 * @return bool
 */
function guru_schema_is( $node, $type ) {
	return isset( $node['@type'] ) && in_array( $type, (array) $node['@type'], true );
}

/**
 * Yoast Schema: the article's author is the same person or team as the byline.
 *
 * @param array $graph Schema graph pieces.
 * @return array
 */
function guru_schema_article_author( $graph ) {
	if ( ! guru_is_article() || ! is_array( $graph ) ) {
		return $graph;
	}
	$author = guru_article_author( get_queried_object_id() );
	if ( '' === $author['name'] ) {
		return $graph;
	}

	// The @id Yoast gave the WordPress user behind the post.
	$old_id = '';
	foreach ( $graph as $node ) {
		if ( ( guru_schema_is( $node, 'Article' ) || guru_schema_is( $node, 'BlogPosting' ) ) && isset( $node['author']['@id'] ) ) {
			$old_id = $node['author']['@id'];
			break;
		}
	}

	if ( ! $author['is_team'] ) {
		// A real person: Yoast already uses their display name; keep the bio identical to the page.
		foreach ( $graph as $i => $node ) {
			if ( $old_id && isset( $node['@id'] ) && $old_id === $node['@id'] ) {
				$graph[ $i ]['name'] = $author['name'];
				if ( '' !== $author['bio'] ) {
					$graph[ $i ]['description'] = $author['bio'];
				} else {
					unset( $graph[ $i ]['description'] );
				}
			}
		}
		return $graph;
	}

	// Back-office account: drop its Person node and credit the editorial team.
	$team_id = guru_editorial_schema_id();
	$team    = array(
		'@type' => 'Organization',
		'@id'   => $team_id,
		'name'  => $author['name'],
	);
	if ( '' !== $author['bio'] ) {
		$team['description'] = $author['bio'];
	}
	$blog_id = (int) get_option( 'page_for_posts' );
	if ( $blog_id ) {
		$team['url'] = get_permalink( $blog_id );
	}

	$out = array();
	foreach ( $graph as $node ) {
		if ( $old_id && isset( $node['@id'] ) && $old_id === $node['@id'] && guru_schema_is( $node, 'Person' ) ) {
			continue;
		}
		if ( ( guru_schema_is( $node, 'Article' ) || guru_schema_is( $node, 'BlogPosting' ) ) ) {
			$node['author'] = array(
				'name' => $author['name'],
				'@id'  => $team_id,
			);
		}
		$out[] = $old_id ? guru_schema_swap_id( $node, $old_id, $team_id ) : $node;
	}
	$out[] = $team;
	return $out;
}
add_filter( 'wpseo_schema_graph', 'guru_schema_article_author', 20 );

/**
 * Yoast <meta name="author">: the byline name.
 *
 * @param string $name Display name of the WordPress user.
 * @return string
 */
function guru_wpseo_meta_author( $name ) {
	if ( ! guru_is_article() ) {
		return $name;
	}
	$author = guru_article_author( get_queried_object_id() );
	return '' !== $author['name'] ? $author['name'] : $name;
}
add_filter( 'wpseo_meta_author', 'guru_wpseo_meta_author' );

/**
 * Yoast share card ("Written by …"): the byline name.
 *
 * @param array $data Label => value.
 * @return array
 */
function guru_wpseo_slack_author( $data ) {
	if ( ! guru_is_article() || ! is_array( $data ) ) {
		return $data;
	}
	$author = guru_article_author( get_queried_object_id() );
	$wp     = (string) get_the_author_meta( 'display_name', $author['user_id'] );
	foreach ( $data as $label => $value ) {
		if ( '' !== $wp && $value === $wp ) {
			$data[ $label ] = $author['name'];
		}
	}
	return $data;
}
add_filter( 'wpseo_enhanced_slack_data', 'guru_wpseo_slack_author' );
