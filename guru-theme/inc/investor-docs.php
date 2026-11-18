<?php
/**
 * Investor documents — the old site's "Financial Information" and "Last Announcement" lists.
 *
 * The documents live in the `investor_document` post type with a `document_category`
 * taxonomy (slugs `financial-information` and `last-announcement`). On the live site
 * both are registered by the Pods plugin and edited under "Investor Doc", with two ACF
 * fields: `date_of_publish` (stored as Ymd) and `document` (the PDF's attachment ID).
 * They are registered here only when missing, so a fresh install still works.
 *
 * English and Chinese versions are separate posts published on the same date; the
 * Chinese PDF's file name ends in "_c". Each pair is shown as one row with an English
 * and a 中文 link (language, file type and file size), newest first.
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Document categories shown on the Investor page, in display order: slug => label.
 *
 * @return string[]
 */
function guru_investor_doc_categories() {
	return array(
		'financial-information' => __( 'Financial Information', 'guru' ),
		'last-announcement'     => __( 'Last Announcement', 'guru' ),
	);
}

/**
 * Register the post type and taxonomy when no plugin has (fresh installs, local tests).
 */
function guru_register_investor_docs() {
	if ( ! post_type_exists( 'investor_document' ) ) {
		register_post_type(
			'investor_document',
			array(
				'labels'       => array(
					'name'          => __( 'Investor Doc', 'guru' ),
					'singular_name' => __( 'Investor document', 'guru' ),
				),
				'public'       => true,
				'show_ui'      => true,
				'menu_icon'    => 'dashicons-media-document',
				'supports'     => array( 'title' ),
				'rewrite'      => array( 'slug' => 'investor-document' ),
				'show_in_rest' => false,
			)
		);
	}
	if ( ! taxonomy_exists( 'document_category' ) ) {
		register_taxonomy(
			'document_category',
			'investor_document',
			array(
				'label'             => __( 'Document categories', 'guru' ),
				'hierarchical'      => true,
				'show_admin_column' => true,
				'rewrite'           => array( 'slug' => 'document-category' ),
			)
		);
	}
}
add_action( 'init', 'guru_register_investor_docs', 99 );

/**
 * PDF URL of a document: the `document` field holds an attachment ID (or, rarely, a URL).
 *
 * @param int $id Post ID.
 * @return string
 */
function guru_investor_doc_url( $id ) {
	$value = get_post_meta( $id, 'document', true );
	if ( is_numeric( $value ) ) {
		return (string) wp_get_attachment_url( (int) $value );
	}
	return is_string( $value ) ? esc_url_raw( $value ) : '';
}

/**
 * File size in bytes of a document's PDF, or 0 when it cannot be read.
 *
 * Uses the attachment metadata WordPress stores on upload (`filesize`), then the file
 * on disk. A plain URL in the `document` field is looked up as an attachment first.
 *
 * @param int $id Post ID.
 * @return int
 */
function guru_investor_doc_bytes( $id ) {
	$value = get_post_meta( $id, 'document', true );
	$att   = is_numeric( $value ) ? (int) $value : ( is_string( $value ) && $value ? attachment_url_to_postid( $value ) : 0 );
	if ( ! $att ) {
		return 0;
	}
	$meta = wp_get_attachment_metadata( $att );
	if ( is_array( $meta ) && ! empty( $meta['filesize'] ) ) {
		return (int) $meta['filesize'];
	}
	$file = get_attached_file( $att );
	return ( $file && is_readable( $file ) ) ? (int) filesize( $file ) : 0;
}

/**
 * "PDF · 2.3 MB" for a document link: the file type from the URL and the size when known.
 *
 * @param string $url   File URL.
 * @param int    $bytes Size in bytes (0 = unknown, left out).
 * @return string
 */
function guru_investor_doc_meta( $url, $bytes = 0 ) {
	$parts = array();
	$ext   = strtolower( pathinfo( (string) wp_parse_url( $url, PHP_URL_PATH ), PATHINFO_EXTENSION ) );
	if ( in_array( $ext, array( 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip' ), true ) ) {
		$parts[] = strtoupper( $ext );
	}
	if ( $bytes > 0 ) {
		// Under 1 MB in whole KB ("412 KB"), above with one decimal ("2.3 MB").
		$parts[] = $bytes < MB_IN_BYTES
			? max( 1, (int) round( $bytes / KB_IN_BYTES ) ) . ' KB'
			: number_format_i18n( $bytes / MB_IN_BYTES, 1 ) . ' MB';
	}
	return implode( ' · ', $parts );
}

/**
 * All rows of one category, newest first, English and Chinese paired.
 *
 * Cached for a day; any change to an investor document clears the cache.
 *
 * @param string $slug Category slug.
 * @return array[] Each row: date (Y.m.d), title, sub, links ([ url, label, class, bytes, document name, lang ]).
 */
function guru_investor_doc_rows( $slug ) {
	$key  = 'guru_inv_docs_' . md5( $slug ) . '_v2';
	$rows = get_transient( $key );
	if ( is_array( $rows ) ) {
		return $rows;
	}

	$rows = array();
	if ( post_type_exists( 'investor_document' ) && taxonomy_exists( 'document_category' ) ) {
		$q = new WP_Query(
			array(
				'post_type'              => 'investor_document',
				'post_status'            => 'publish',
				'posts_per_page'         => -1,
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
				'tax_query'              => array( // phpcs:ignore WordPress.DB.SlowDBQuery
					array(
						'taxonomy' => 'document_category',
						'field'    => 'slug',
						'terms'    => $slug,
					),
				),
			)
		);

		// Group by publish date.
		$by_day = array();
		foreach ( $q->posts as $post ) {
			$raw = (string) get_post_meta( $post->ID, 'date_of_publish', true );
			$ymd = preg_match( '/^\d{8}$/', $raw ) ? $raw : get_the_date( 'Ymd', $post );
			$url = guru_investor_doc_url( $post->ID );
			$zh  = preg_match( '/_c(-\d+)?\.pdf$/i', $url ) || preg_match( '/\p{Han}/u', $post->post_title );

			$by_day[ $ymd ][] = array(
				'id'    => $post->ID,
				'title' => $post->post_title,
				'url'   => $url,
				'bytes' => $url ? guru_investor_doc_bytes( $post->ID ) : 0,
				'zh'    => (bool) $zh,
			);
		}
		krsort( $by_day, SORT_STRING );

		foreach ( $by_day as $ymd => $docs ) {
			// Each pair was uploaded one after the other, so neighbours by ID belong together.
			usort(
				$docs,
				static function ( $a, $b ) {
					return $a['id'] - $b['id'];
				}
			);
			$day_rows = array();
			for ( $i = 0, $n = count( $docs ); $i < $n; $i++ ) {
				$a = $docs[ $i ];
				$b = ( $i + 1 < $n && $docs[ $i + 1 ]['zh'] !== $a['zh'] ) ? $docs[ ++$i ] : null;
				$en = $a['zh'] ? $b : $a;
				$zh = $a['zh'] ? $a : $b;

				$links = array();
				if ( $en && $en['url'] ) {
					$links[] = array( $en['url'], 'English', '', $en['bytes'], $en['title'], 'en' );
				}
				if ( $zh && $zh['url'] ) {
					$links[] = array( $zh['url'], '中文', 'inv-doc-link-zh', $zh['bytes'], $zh['title'], 'zh-HK' );
				}
				$day_rows[] = array(
					'order' => max( $a['id'], $b ? $b['id'] : 0 ),
					'date'  => substr( $ymd, 0, 4 ) . '.' . substr( $ymd, 4, 2 ) . '.' . substr( $ymd, 6, 2 ),
					'title' => $en ? $en['title'] : $zh['title'],
					'sub'   => ( $en && $zh ) ? $zh['title'] : '',
					'links' => $links,
				);
			}
			// Within a day, the most recently added document first.
			usort(
				$day_rows,
				static function ( $a, $b ) {
					return $b['order'] - $a['order'];
				}
			);
			foreach ( $day_rows as $row ) {
				unset( $row['order'] );
				$rows[] = $row;
			}
		}
	}

	set_transient( $key, $rows, DAY_IN_SECONDS );
	return $rows;
}

/**
 * Clear the cached lists.
 */
function guru_investor_docs_flush() {
	foreach ( array_keys( guru_investor_doc_categories() ) as $slug ) {
		delete_transient( 'guru_inv_docs_' . md5( $slug ) . '_v1' );
		delete_transient( 'guru_inv_docs_' . md5( $slug ) . '_v2' );
	}
}

/**
 * Clear the cache whenever an investor document is saved, trashed, deleted or re-categorised.
 *
 * @param int $post_id Post ID.
 */
function guru_investor_docs_maybe_flush( $post_id ) {
	if ( 'investor_document' === get_post_type( $post_id ) ) {
		guru_investor_docs_flush();
	}
}
add_action( 'save_post', 'guru_investor_docs_maybe_flush' );
add_action( 'deleted_post', 'guru_investor_docs_maybe_flush' );
add_action( 'trashed_post', 'guru_investor_docs_maybe_flush' );
add_action( 'set_object_terms', 'guru_investor_docs_maybe_flush' );
add_action( 'updated_post_meta', static function ( $meta_id, $post_id ) { guru_investor_docs_maybe_flush( $post_id ); }, 10, 2 );
add_action( 'added_post_meta', static function ( $meta_id, $post_id ) { guru_investor_docs_maybe_flush( $post_id ); }, 10, 2 );

/**
 * URL of the full list for a category on the Investor page (optionally a given page).
 *
 * @param string $slug Category slug.
 * @param int    $page Page number.
 * @return string
 */
function guru_investor_list_url( $slug, $page = 1 ) {
	$base = guru_page_url( 'investor', '/investor/', 'page-investor.php' );
	$args = array( 'docs' => $slug );
	if ( $page > 1 ) {
		$args['pg'] = (int) $page;
	}
	return add_query_arg( $args, $base );
}
