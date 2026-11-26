<?php
/**
 * SEO FAQ for search engines (v1.4.6, client request of 2026-11-26).
 *
 * Each solution has an "SEO FAQ" box. The questions and answers in it are not
 * shown on the page; the Solutions page only prints them as FAQPage structured
 * data (JSON-LD) for search engines and AI search, as the client asked.
 *
 * Format in the box (the client can paste straight from Word):
 *
 *   Q1. What services does Guru provide?
 *   As a listed 4A's digital marketing agency…
 *
 *   Q2. Next question?
 *   Answer…
 *
 * A line starting with "Q" plus a number or ":" starts a question; the lines after
 * it, up to the next question, are its answer (a leading "A:" is dropped).
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parse the box text into question/answer pairs.
 *
 * @param string $text Raw text.
 * @return array[] List of array( 'q' => …, 'a' => … ); questions without an answer are dropped.
 */
function guru_parse_seo_faq( $text ) {
	$pairs = array();
	$cur   = null;

	foreach ( preg_split( '/\R/u', (string) $text ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		if ( preg_match( '/^Q\s*(?:\d+\s*[.:：、)]?|[.:：])\s*(.+)$/iu', $line, $m ) ) {
			if ( $cur ) {
				$pairs[] = $cur;
			}
			$cur = array(
				'q' => trim( $m[1] ),
				'a' => '',
			);
			continue;
		}
		if ( ! $cur ) {
			continue;
		}
		$line     = preg_replace( '/^A\s*\d*\s*[.:：]\s*/u', '', $line );
		$cur['a'] = trim( $cur['a'] . ' ' . $line );
	}
	if ( $cur ) {
		$pairs[] = $cur;
	}

	return array_values(
		array_filter(
			$pairs,
			function ( $p ) {
				return '' !== $p['q'] && '' !== $p['a'];
			}
		)
	);
}

/**
 * Register the box on the solution edit screen.
 */
function guru_add_seo_faq_meta_box() {
	add_meta_box(
		'guru_seo_faq',
		__( 'SEO FAQ (search engines only — not shown on the page)', 'guru' ),
		'guru_render_seo_faq_meta_box',
		'solution',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'guru_add_seo_faq_meta_box' );

/**
 * Box markup.
 *
 * @param WP_Post $post Current post.
 */
function guru_render_seo_faq_meta_box( $post ) {
	wp_nonce_field( 'guru_save_seo_faq', 'guru_seo_faq_nonce' );
	$text  = (string) get_post_meta( $post->ID, '_guru_seo_faq', true );
	$count = count( guru_parse_seo_faq( $text ) );
	?>
	<p class="description" style="margin-bottom:8px">
		<?php esc_html_e( 'Visitors do not see these. They are added to the Solutions page as FAQ structured data for Google and AI search. One question per block: a line starting with "Q1." (or "Q:"), then the answer on the next line, then an empty line.', 'guru' ); ?>
	</p>
	<textarea name="guru_seo_faq" rows="16" style="width:100%;font-family:inherit"
		placeholder="Q1. What services does Guru provide?&#10;As a listed 4A's digital marketing agency in Hong Kong, Guru…&#10;&#10;Q2. …"><?php echo esc_textarea( $text ); ?></textarea>
	<p class="description">
		<?php
		/* translators: %d: number of questions. */
		echo esc_html( sprintf( _n( 'Search engines currently get %d question from this solution.', 'Search engines currently get %d questions from this solution.', $count, 'guru' ), $count ) );
		?>
	</p>
	<?php
}

/**
 * Save the box.
 *
 * @param int $post_id Post ID.
 */
function guru_save_seo_faq( $post_id ) {
	if ( ! isset( $_POST['guru_seo_faq_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['guru_seo_faq_nonce'] ) ), 'guru_save_seo_faq' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$text = isset( $_POST['guru_seo_faq'] ) ? sanitize_textarea_field( wp_unslash( $_POST['guru_seo_faq'] ) ) : '';
	if ( '' === trim( $text ) ) {
		delete_post_meta( $post_id, '_guru_seo_faq' );
	} else {
		update_post_meta( $post_id, '_guru_seo_faq', $text );
	}
}
add_action( 'save_post_solution', 'guru_save_seo_faq' );

/**
 * Print the FAQPage JSON-LD on the Solutions page: every solution's questions, in
 * the page's order. Nothing is printed when no solution has any.
 */
function guru_print_seo_faq_schema() {
	if ( ! is_page() || 'page-services.php' !== get_page_template_slug( get_queried_object_id() ) ) {
		return;
	}

	$entities = array();
	foreach ( get_posts( guru_solutions_query_args( array( 'fields' => 'ids' ) ) ) as $id ) {
		foreach ( guru_parse_seo_faq( get_post_meta( $id, '_guru_seo_faq', true ) ) as $p ) {
			$entities[] = array(
				'@type'          => 'Question',
				'name'           => $p['q'],
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => $p['a'],
				),
			);
		}
	}
	if ( ! $entities ) {
		return;
	}

	$url    = get_permalink( get_queried_object_id() );
	$schema = array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'@id'        => $url . '#faq',
		'url'        => $url,
		'inLanguage' => guru_frontend_lang(),
		'mainEntity' => $entities,
	);

	echo "\n<script type=\"application/ld+json\" class=\"guru-faq-schema\">" . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG ) . "</script>\n";
}
add_action( 'wp_head', 'guru_print_seo_faq_schema', 40 );

/**
 * One-time fill of the client's first FAQ set (Word file "SEO for website",
 * 2026-11-26) into solutions whose box is still empty, matched by slug. Runs once
 * in the admin after this theme version is installed; never overwrites edits.
 */
function guru_seed_seo_faq() {
	if ( get_option( 'guru_seo_faq_seeded' ) || ! current_user_can( 'edit_posts' ) ) {
		return;
	}
	$file = get_stylesheet_directory() . '/inc/seo-faq-seed.json';
	$seed = file_exists( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( ! is_array( $seed ) ) {
		return;
	}
	foreach ( get_posts( guru_solutions_query_args( array( 'post_status' => 'any' ) ) ) as $post ) {
		if ( isset( $seed[ $post->post_name ] ) && '' === (string) get_post_meta( $post->ID, '_guru_seo_faq', true ) ) {
			update_post_meta( $post->ID, '_guru_seo_faq', sanitize_textarea_field( $seed[ $post->post_name ] ) );
		}
	}
	update_option( 'guru_seo_faq_seeded', GURU_VERSION, false );
}
add_action( 'admin_init', 'guru_seed_seo_faq' );
