<?php
/**
 * One-click content importer.
 *
 * Fills a fresh install with the real Guru content so the admin is never empty:
 * Solutions, Showcase items, work categories, the site pages, the primary menu,
 * and — optionally — the client logos and showcase videos as Media Library items.
 *
 * Every step is idempotent and non-destructive: running it again only adds what
 * is missing (posts are matched by the seed key stamped on them) and never
 * overwrites anything an editor has changed.
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bundled seed content, decoded once per request.
 *
 * @return array
 */
function guru_seed_data() {
	static $data = null;

	if ( null === $data ) {
		$file = get_stylesheet_directory() . '/inc/seed-data.json';
		$data = array();

		if ( file_exists( $file ) ) {
			$decoded = function_exists( 'wp_json_file_decode' )
				? wp_json_file_decode( $file, array( 'associative' => true ) )
				: json_decode( file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

			if ( is_array( $decoded ) ) {
				$data = $decoded;
			}
		}
	}

	return $data;
}

/**
 * Existing post of a type with this exact title, if there is one.
 *
 * @param string $post_type Post type.
 * @param string $title     Post title.
 * @return int Post ID, or 0.
 */
function guru_find_by_title( $post_type, $title ) {
	$found = get_posts(
		array(
			'post_type'        => $post_type,
			'title'            => $title,
			'post_status'      => 'any',
			'numberposts'      => 1,
			'fields'           => 'ids',
			'suppress_filters' => false,
		)
	);

	return $found ? (int) $found[0] : 0;
}

/**
 * Existing post created by the importer for this seed entry.
 *
 * Matched by the seed key stamped on import, so a post the editor renamed still
 * maps back to its seed entry; falls back to an exact title match for posts
 * imported before the key existed (and stamps them).
 *
 * @param string $post_type Post type.
 * @param string $key       Seed key.
 * @param string $title     Seed title.
 * @return int Post ID, or 0.
 */
function guru_find_seeded( $post_type, $key, $title ) {
	$found = get_posts(
		array(
			'post_type'   => $post_type,
			'post_status' => 'any',
			'numberposts' => 1,
			'fields'      => 'ids',
			'meta_key'    => '_guru_seed_key', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'  => $key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	);

	if ( $found ) {
		return (int) $found[0];
	}

	$id = guru_find_by_title( $post_type, $title );
	if ( $id ) {
		update_post_meta( $id, '_guru_seed_key', $key );
	}

	return $id;
}

/**
 * The page the importer created for a seed entry.
 *
 * Like guru_find_seeded(), but the title fallback only adopts a page that is
 * empty or already uses our template — never a content page inherited from the
 * previous theme, whose title may happen to match.
 *
 * @param string $key      Seed key (page-about …).
 * @param string $title    Seed title.
 * @param string $template Our template file for this page, or ''.
 * @return int Page ID, or 0.
 */
function guru_find_seeded_page( $key, $title, $template ) {
	$found = get_posts(
		array(
			'post_type'   => 'page',
			'post_status' => 'any',
			'numberposts' => 1,
			'fields'      => 'ids',
			'meta_key'    => '_guru_seed_key', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'  => $key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	);
	if ( $found ) {
		return (int) $found[0];
	}

	$id = guru_find_by_title( 'page', $title );
	if ( ! $id ) {
		return 0;
	}

	$ours = '' === trim( (string) get_post_field( 'post_content', $id ) )
		|| ( $template && get_post_meta( $id, '_wp_page_template', true ) === $template );
	if ( ! $ours ) {
		return 0;
	}

	update_post_meta( $id, '_guru_seed_key', $key );
	return $id;
}

/**
 * True the first time the importer handles $key for this post, false after.
 *
 * Lets a re-run fill in fields that a newer seed file introduced, without ever
 * re-seeding a field the editor has since cleared on purpose.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Meta key or marker name.
 * @return bool
 */
function guru_seed_once( $post_id, $key ) {
	$done = get_post_meta( $post_id, '_guru_seeded_keys', true );
	$done = is_array( $done ) ? $done : array();

	if ( in_array( $key, $done, true ) ) {
		return false;
	}

	$done[] = $key;
	update_post_meta( $post_id, '_guru_seeded_keys', $done );

	return true;
}

/**
 * Write a meta value only when it has never been seeded and is currently empty.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Meta key.
 * @param mixed  $value   Value.
 */
function guru_seed_meta( $post_id, $key, $value ) {
	if ( ! guru_seed_once( $post_id, $key ) ) {
		return;
	}
	$current = get_post_meta( $post_id, $key, true );
	if ( '' === $current || array() === $current ) {
		update_post_meta( $post_id, $key, $value );
	}
}

/**
 * Where a seed media file lives: a bundled image → theme URL; a video that a
 * Showcase item already hosts → that URL; otherwise the old site.
 *
 * @param string $file   File name from the seed data.
 * @param string $source Base URL of the old site.
 * @return string
 */
function guru_seed_media_url( $file, $source ) {
	$file = ltrim( $file, '/' );
	$name = basename( $file );

	if ( preg_match( '/\.(png|jpe?g|webp|svg|gif)$/i', $name ) ) {
		$local = guru_theme_media_url( 'solutions/' . $name );
		if ( $local ) {
			return $local;
		}
	}

	$hosted = guru_find_work_video_url( $name );
	if ( $hosted ) {
		return $hosted;
	}

	return esc_url_raw( $source . '/' . $file );
}

/**
 * The URL a Showcase item already uses for a video of this file name, if any.
 *
 * @param string $basename File name, e.g. "influencer-1.mp4".
 * @return string
 */
function guru_find_work_video_url( $basename ) {
	$works = get_posts(
		array(
			'post_type'   => 'work',
			'post_status' => 'any',
			'numberposts' => -1,
			'fields'      => 'ids',
			'meta_key'    => '_guru_video_url', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		)
	);

	foreach ( $works as $id ) {
		$url = (string) get_post_meta( $id, '_guru_video_url', true );
		if ( $url && basename( (string) wp_parse_url( $url, PHP_URL_PATH ) ) === $basename ) {
			return $url;
		}
	}

	return '';
}

/**
 * Seed a solution's media block, caption, short tag and highlight cards.
 *
 * Every field is written at most once and only while it is empty, so a re-run
 * never undoes an editor's change (or the local copies step 4 makes).
 *
 * @param int    $post_id  Solution ID.
 * @param array  $solution Seed entry.
 * @param string $source   Base URL of the old site.
 */
function guru_import_solution_media( $post_id, $solution, $source ) {
	$type = isset( $solution['media_type'] ) ? sanitize_key( $solution['media_type'] ) : '';
	if ( ! array_key_exists( $type, guru_solution_media_types() ) ) {
		$type = '';
	}

	if ( $type && ! empty( $solution['media'] ) && guru_seed_once( $post_id, '_guru_media_items' ) && ! get_post_meta( $post_id, '_guru_media_items', true ) ) {
		$items = array();
		foreach ( (array) $solution['media'] as $item ) {
			if ( empty( $item['file'] ) ) {
				continue;
			}
			$items[] = array(
				'src'     => guru_seed_media_url( $item['file'], $source ),
				'caption' => isset( $item['caption'] ) ? sanitize_text_field( $item['caption'] ) : '',
				'alt'     => isset( $item['alt'] ) ? sanitize_text_field( $item['alt'] ) : '',
			);
		}
		if ( $items ) {
			update_post_meta( $post_id, '_guru_media_items', $items );
			update_post_meta( $post_id, '_guru_media_type', $type );
		}
	}

	if ( ! empty( $solution['media_label'] ) ) {
		guru_seed_meta( $post_id, '_guru_media_label', sanitize_text_field( $solution['media_label'] ) );
	}
	if ( ! empty( $solution['panel_tag'] ) ) {
		guru_seed_meta( $post_id, '_guru_panel_tag', sanitize_text_field( $solution['panel_tag'] ) );
	}

	$highlights = array();
	foreach ( (array) ( isset( $solution['highlights'] ) ? $solution['highlights'] : array() ) as $h ) {
		if ( empty( $h['title'] ) ) {
			continue;
		}
		$highlights[] = array(
			'icon'  => isset( $h['icon'] ) ? sanitize_text_field( $h['icon'] ) : '',
			'title' => sanitize_text_field( $h['title'] ),
			'desc'  => isset( $h['desc'] ) ? sanitize_text_field( $h['desc'] ) : '',
		);
	}
	if ( $highlights ) {
		guru_seed_meta( $post_id, '_guru_highlights', $highlights );
	}
}

/**
 * After a video lands in the Media Library, point every solution media item
 * that used the same file at the local copy.
 *
 * @param string $basename File name that was downloaded.
 * @param string $new_url  Its Media Library URL.
 */
function guru_repoint_solution_media( $basename, $new_url ) {
	$solutions = get_posts(
		array(
			'post_type'   => 'solution',
			'post_status' => 'any',
			'numberposts' => -1,
			'fields'      => 'ids',
		)
	);

	foreach ( $solutions as $id ) {
		$items = get_post_meta( $id, '_guru_media_items', true );
		if ( ! is_array( $items ) ) {
			continue;
		}
		$changed = false;
		foreach ( $items as $i => $item ) {
			if ( empty( $item['src'] ) || $item['src'] === $new_url ) {
				continue;
			}
			if ( basename( (string) wp_parse_url( $item['src'], PHP_URL_PATH ) ) === $basename ) {
				$items[ $i ]['src'] = $new_url;
				$changed            = true;
			}
		}
		if ( $changed ) {
			update_post_meta( $id, '_guru_media_items', $items );
		}
	}
}

/* -------------------------------------------------------------------------
 * Admin screen
 * ---------------------------------------------------------------------- */

/**
 * Top-level "Guru" menu holding the setup screen.
 */
function guru_importer_menu() {
	add_menu_page(
		__( 'Guru setup', 'guru' ),
		__( 'Guru', 'guru' ),
		'manage_options',
		'guru-setup',
		'guru_importer_screen',
		'dashicons-superhero-alt',
		3
	);
}
add_action( 'admin_menu', 'guru_importer_menu' );

/**
 * How much content is already in place, for the status table.
 *
 * @return array
 */
function guru_importer_status() {
	$logos = get_posts(
		array(
			'post_type'   => 'client_logo',
			'post_status' => 'any',
			'numberposts' => -1,
			'fields'      => 'ids',
			'meta_key'    => '_thumbnail_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		)
	);

	$videos = get_posts(
		array(
			'post_type'   => 'work',
			'post_status' => 'any',
			'numberposts' => -1,
			'fields'      => 'ids',
			'meta_key'    => '_guru_video_url', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		)
	);

	$local = 0;
	foreach ( $videos as $video_id ) {
		if ( false !== strpos( (string) get_post_meta( $video_id, '_guru_video_url', true ), '/wp-content/uploads/' ) ) {
			++$local;
		}
	}

	return array(
		'solutions'  => (int) wp_count_posts( 'solution' )->publish,
		'works'      => (int) wp_count_posts( 'work' )->publish,
		'categories' => (int) wp_count_terms( array( 'taxonomy' => 'work_category', 'hide_empty' => false ) ),
		'logos'      => count( $logos ),
		'videos'     => $local . ' / ' . count( $videos ),
		'enquiries'  => guru_enquiries_count(),
	);
}

/**
 * The Guru setup screen.
 */
function guru_importer_screen() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$status = guru_importer_status();
	$labels = array(
		'solutions'  => __( 'Solutions', 'guru' ),
		'works'      => __( 'Showcase items', 'guru' ),
		'categories' => __( 'Showcase categories', 'guru' ),
		'logos'      => __( 'Client logos in Media Library', 'guru' ),
		'videos'     => __( 'Showcase videos hosted here', 'guru' ),
		'enquiries'  => __( 'Enquiries received', 'guru' ),
	);

	$steps = array(
		'content' => array(
			'title' => __( '1. Import content', 'guru' ),
			'desc'  => __( 'Creates the Solutions, Showcase items and categories. Re-running only adds what is missing — anything you have edited is left alone.', 'guru' ),
			'button' => __( 'Import content', 'guru' ),
		),
		'pages'   => array(
			'title' => __( '2. Create pages and menu', 'guru' ),
			'desc'  => __( 'Creates Home, About, Solutions, Investor and Blog with the right templates, sets the front page and builds the primary menu. Existing pages and menu items are kept.', 'guru' ),
			'button' => __( 'Create pages and menu', 'guru' ),
		),
		'logos'   => array(
			'title' => __( '3. Import client logos', 'guru' ),
			'desc'  => __( 'Copies the bundled client logos into the Media Library so they can be edited or replaced. Optional — the About page already shows them without this step.', 'guru' ),
			'button' => __( 'Import client logos', 'guru' ),
		),
		'videos'  => array(
			'title' => __( '4. Import showcase videos', 'guru' ),
			'desc'  => __( 'Downloads the showcase videos onto this site so they no longer load from the old site. Large files — run this last, and run it again if it stops partway.', 'guru' ),
			'button' => __( 'Import videos', 'guru' ),
		),
	);

	$notice = get_transient( 'guru_import_notice_' . get_current_user_id() );
	if ( $notice ) {
		delete_transient( 'guru_import_notice_' . get_current_user_id() );
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Guru setup', 'guru' ); ?></h1>

		<?php if ( $notice ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $notice ); ?></p></div>
		<?php endif; ?>

		<p class="description" style="max-width:46em">
			<?php esc_html_e( 'Run the steps in order on a new site. Everything imported here stays fully editable — Solutions and Showcase items are ordinary posts, and the rest lives in Appearance → Customize. Re-running a step is safe: it only adds what is missing.', 'guru' ); ?>
		</p>

		<h2><?php esc_html_e( 'Current content', 'guru' ); ?></h2>
		<table class="widefat striped" style="max-width:34em">
			<tbody>
			<?php foreach ( $labels as $key => $label ) : ?>
				<tr>
					<td><?php echo esc_html( $label ); ?></td>
					<td style="text-align:right"><strong><?php echo esc_html( $status[ $key ] ); ?></strong></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<h2><?php esc_html_e( 'Import steps', 'guru' ); ?></h2>
		<?php foreach ( $steps as $key => $step ) : ?>
			<div class="card" style="max-width:46em;margin-bottom:12px">
				<h3 style="margin-top:0"><?php echo esc_html( $step['title'] ); ?></h3>
				<p><?php echo esc_html( $step['desc'] ); ?></p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="guru_import" />
					<input type="hidden" name="step" value="<?php echo esc_attr( $key ); ?>" />
					<?php wp_nonce_field( 'guru_import_' . $key ); ?>
					<?php if ( 'videos' === $key ) : ?>
						<p>
							<label for="guru-src"><?php esc_html_e( 'Download from:', 'guru' ); ?></label><br />
							<input type="url" id="guru-src" name="source" class="regular-text"
								value="<?php echo esc_attr( get_option( 'guru_import_source', 'https://guru-ebon-eight.vercel.app' ) ); ?>" />
						</p>
					<?php endif; ?>
					<p><button type="submit" class="button button-primary"><?php echo esc_html( $step['button'] ); ?></button></p>
				</form>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
}

/* -------------------------------------------------------------------------
 * Handler
 * ---------------------------------------------------------------------- */

/**
 * Runs the requested import step and returns to the setup screen.
 */
function guru_handle_import() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to do this.', 'guru' ), 403 );
	}

	$step = isset( $_POST['step'] ) ? sanitize_key( wp_unslash( $_POST['step'] ) ) : '';

	if ( ! in_array( $step, array( 'content', 'pages', 'logos', 'videos' ), true ) ) {
		wp_die( esc_html__( 'Unknown import step.', 'guru' ), 400 );
	}

	check_admin_referer( 'guru_import_' . $step );

	switch ( $step ) {
		case 'content':
			$message = guru_import_content();
			break;
		case 'pages':
			$message = guru_import_pages();
			break;
		case 'logos':
			$message = guru_import_logos();
			break;
		default:
			$source = isset( $_POST['source'] ) ? esc_url_raw( wp_unslash( $_POST['source'] ) ) : '';
			if ( $source ) {
				update_option( 'guru_import_source', untrailingslashit( $source ) );
			}
			$message = guru_import_videos( untrailingslashit( $source ) );
	}

	set_transient( 'guru_import_notice_' . get_current_user_id(), $message, MINUTE_IN_SECONDS );

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'      => 'guru-setup',
				'guru_done' => 1,
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}
add_action( 'admin_post_guru_import', 'guru_handle_import' );

/* -------------------------------------------------------------------------
 * Step 1 — Solutions, Showcase, categories
 * ---------------------------------------------------------------------- */

/**
 * Imports the Solutions, Showcase items and their categories.
 *
 * Items imported before (matched by the seed key stamped on them, or by title
 * for older imports) are left exactly as the editor has them; only missing
 * items and never-seeded empty fields are filled in. Re-running is always safe.
 *
 * @return string Result message.
 */
function guru_import_content() {
	$data  = guru_seed_data();
	$added = array(
		'terms'     => 0,
		'solutions' => 0,
		'works'     => 0,
	);

	foreach ( (array) ( isset( $data['categories'] ) ? $data['categories'] : array() ) as $slug => $name ) {
		if ( ! get_term_by( 'slug', $slug, 'work_category' ) ) {
			wp_insert_term( $name, 'work_category', array( 'slug' => $slug ) );
			++$added['terms'];
		}
	}

	// Blog categories, so article cards pick up the colour classes the stylesheet defines.
	foreach ( (array) ( isset( $data['blog_categories'] ) ? $data['blog_categories'] : array() ) as $slug => $name ) {
		if ( ! term_exists( $slug, 'category' ) ) {
			wp_insert_term( $name, 'category', array( 'slug' => $slug ) );
		}
	}

	$source = untrailingslashit( get_option( 'guru_import_source', 'https://guru-ebon-eight.vercel.app' ) );

	foreach ( (array) ( isset( $data['solutions'] ) ? $data['solutions'] : array() ) as $index => $solution ) {
		if ( empty( $solution['title'] ) ) {
			continue;
		}
		$key     = sanitize_title( $solution['title'] );
		$post_id = guru_find_seeded( 'solution', $key, $solution['title'] );

		if ( ! $post_id ) {
			$post_id = wp_insert_post(
				array(
					'post_type'    => 'solution',
					'post_title'   => $solution['title'],
					'post_content' => isset( $solution['content'] ) ? $solution['content'] : '',
					'post_excerpt' => isset( $solution['excerpt'] ) ? $solution['excerpt'] : '',
					'post_status'  => 'publish',
					'menu_order'   => $index + 1,
				)
			);
			if ( ! $post_id || is_wp_error( $post_id ) ) {
				continue;
			}
			update_post_meta( $post_id, '_guru_seed_key', $key );
			++$added['solutions'];
		}

		guru_import_solution_media( $post_id, $solution, $source );
	}

	foreach ( (array) ( isset( $data['works'] ) ? $data['works'] : array() ) as $index => $work ) {
		if ( empty( $work['title'] ) ) {
			continue;
		}
		$key     = sanitize_title( $work['title'] );
		$post_id = guru_find_seeded( 'work', $key, $work['title'] );

		if ( ! $post_id ) {
			$post_id = wp_insert_post(
				array(
					'post_type'   => 'work',
					'post_title'  => $work['title'],
					'post_status' => 'publish',
					'menu_order'  => $index + 1,
				)
			);
			if ( ! $post_id || is_wp_error( $post_id ) ) {
				continue;
			}
			update_post_meta( $post_id, '_guru_seed_key', $key );
			++$added['works'];
		}

		if ( ! empty( $work['client'] ) ) {
			guru_seed_meta( $post_id, '_guru_client', sanitize_text_field( $work['client'] ) );
		}
		if ( ! empty( $work['video'] ) ) {
			guru_seed_meta( $post_id, '_guru_video_url', esc_url_raw( $source . '/' . ltrim( $work['video'], '/' ) ) );
		}
		if ( ! empty( $work['home'] ) ) {
			guru_seed_meta( $post_id, '_guru_home_order', (int) $work['home'] );
		}
		if ( ! empty( $work['tags'] ) ) {
			guru_seed_meta( $post_id, '_guru_tags', array_values( array_filter( array_map( 'sanitize_text_field', (array) $work['tags'] ) ) ) );
		}
		if ( ! empty( $work['cats'] ) && guru_seed_once( $post_id, 'work_category' ) ) {
			$current = wp_get_object_terms( $post_id, 'work_category', array( 'fields' => 'ids' ) );
			if ( ! is_wp_error( $current ) && ! $current ) {
				wp_set_object_terms( $post_id, array_map( 'sanitize_key', (array) $work['cats'] ), 'work_category' );
			}
		}
	}

	return sprintf(
		/* translators: 1: categories, 2: solutions, 3: showcase items. */
		__( 'Content imported. Added %1$d categories, %2$d solutions and %3$d showcase items; items that already existed were left as they are.', 'guru' ),
		$added['terms'],
		$added['solutions'],
		$added['works']
	);
}

/* -------------------------------------------------------------------------
 * Step 2 — pages, front page, menu
 * ---------------------------------------------------------------------- */

/**
 * Creates the site pages, assigns their templates, and builds the primary menu.
 *
 * Existing pages are left alone (apart from giving an untouched auto-generated
 * slug its clean address); the front page / posts page are only set when
 * WordPress still has its defaults.
 *
 * @return string Result message.
 */
function guru_import_pages() {
	$data  = guru_seed_data();
	$about = isset( $data['pages']['about'] ) ? $data['pages']['about'] : array();

	$pages = array(
		'home'      => array(
			'title'    => __( 'Home', 'guru' ),
			'template' => '',
		),
		'about'     => array(
			'title'    => isset( $about['title'] ) ? $about['title'] : __( 'About Guru', 'guru' ),
			'template' => 'page-about.php',
			'content'  => isset( $about['content'] ) ? $about['content'] : '',
			'excerpt'  => isset( $about['excerpt'] ) ? $about['excerpt'] : '',
		),
		'solutions' => array(
			'title'    => __( 'Solutions', 'guru' ),
			'template' => 'page-services.php',
		),
		'investor'  => array(
			'title'    => __( 'Investor Relations', 'guru' ),
			'template' => 'page-investor.php',
		),
		'blog'      => array(
			'title'    => __( 'Blog', 'guru' ),
			'template' => '',
		),
	);

	$ids   = array();
	$added = 0;

	foreach ( $pages as $key => $page ) {
		$page_id = guru_find_seeded_page( 'page-' . $key, $page['title'], $page['template'] );

		// A page from the previous site may already own this address (the old
		// "Investor" page at /investor/, for instance). Move it aside as a draft
		// so the imported page — and its template — take over the URL.
		$holder = get_page_by_path( $key );
		if ( $holder && (int) $holder->ID !== (int) $page_id && 'page-' . $key !== get_post_meta( $holder->ID, '_guru_seed_key', true ) ) {
			wp_update_post(
				array(
					'ID'          => $holder->ID,
					'post_name'   => wp_unique_post_slug( $key . '-old', $holder->ID, 'draft', 'page', $holder->post_parent ),
					'post_status' => 'draft',
				)
			);
			update_post_meta( $holder->ID, '_guru_replaced_by', $key );
		}

		if ( $page_id ) {
			// Give an auto-generated or collision slug the clean address (/about/,
			// /investor/ …); a slug the editor deliberately changed is left alone.
			$current = get_post_field( 'post_name', $page_id );
			if ( $current !== $key && ( $current === sanitize_title( $page['title'] ) || preg_match( '/^' . preg_quote( $key, '/' ) . '-\d+$/', $current ) ) ) {
				wp_update_post(
					array(
						'ID'        => $page_id,
						'post_name' => $key,
					)
				);
			}
		} else {
			$args = array(
				'post_type'   => 'page',
				'post_title'  => $page['title'],
				'post_status' => 'publish',
				'post_name'   => $key,
			);
			if ( ! empty( $page['content'] ) ) {
				$args['post_content'] = $page['content'];
			}
			if ( ! empty( $page['excerpt'] ) ) {
				$args['post_excerpt'] = $page['excerpt'];
			}
			$page_id = wp_insert_post( $args );
			if ( ! $page_id || is_wp_error( $page_id ) ) {
				continue;
			}
			update_post_meta( $page_id, '_guru_seed_key', 'page-' . $key );
			++$added;
		}

		$ids[ $key ] = (int) $page_id;

		$template = (string) get_post_meta( $page_id, '_wp_page_template', true );
		if ( $page['template'] && ( '' === $template || 'default' === $template ) ) {
			update_post_meta( $page_id, '_wp_page_template', $page['template'] );
		}
	}

	// This step's job is to point the site at the imported pages — an old theme's
	// front page or blog page must not stay in place after a theme switch.
	if ( ! empty( $ids['home'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $ids['home'] );
	}

	if ( ! empty( $ids['blog'] ) ) {
		update_option( 'page_for_posts', $ids['blog'] );
	}

	guru_build_menu( $ids );

	return sprintf(
		/* translators: %d: number of pages created. */
		__( 'Pages ready. Created %d new pages; the front page, blog page and primary menu are set up (existing pages and menu items were kept).', 'guru' ),
		$added
	);
}

/**
 * Creates the primary navigation menu, adds any of the five site links that are
 * missing, and assigns it to the primary location if nothing is assigned yet.
 * Items the editor added are never removed.
 *
 * @param array $ids Page IDs keyed by slug.
 */
function guru_build_menu( $ids ) {
	$menu_id = guru_menu_id();

	if ( ! $menu_id ) {
		// Tracked by ID (not name), so a menu the previous site happened to call
		// the same thing is never mistaken for ours.
		$menu = wp_get_nav_menu_object( 'Guru Primary' );
		if ( $menu ) {
			$menu_id = (int) $menu->term_id;
		} else {
			$menu_id = wp_create_nav_menu( 'Guru Primary' );
			if ( is_wp_error( $menu_id ) ) {
				return;
			}
		}
		update_option( 'guru_menu_id', (int) $menu_id );
	}

	// Same order as the static site's navigation.
	$items = array(
		array(
			'title' => __( 'About', 'guru' ),
			'page'  => 'about',
		),
		array(
			'title'   => __( 'Work', 'guru' ),
			'archive' => 'work',
		),
		array(
			'title' => __( 'Solutions', 'guru' ),
			'page'  => 'solutions',
		),
		array(
			'title' => __( 'Blog', 'guru' ),
			'page'  => 'blog',
		),
		array(
			'title' => __( 'Investor', 'guru' ),
			'page'  => 'investor',
		),
	);

	$existing = wp_get_nav_menu_items( $menu_id );
	$existing = is_array( $existing ) ? $existing : array();
	$position = count( $existing );

	foreach ( $items as $item ) {
		$have = false;

		if ( isset( $item['archive'] ) ) {
			foreach ( $existing as $e ) {
				if ( 'post_type_archive' === $e->type && $e->object === $item['archive'] ) {
					$have = true;
				}
			}
			if ( $have ) {
				continue;
			}
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'    => $item['title'],
					'menu-item-type'     => 'post_type_archive',
					'menu-item-object'   => $item['archive'],
					'menu-item-status'   => 'publish',
					'menu-item-position' => ++$position,
				)
			);
			continue;
		}

		if ( empty( $ids[ $item['page'] ] ) ) {
			continue;
		}
		foreach ( $existing as $e ) {
			if ( 'post_type' === $e->type && (int) $e->object_id === (int) $ids[ $item['page'] ] ) {
				$have = true;
			}
		}
		if ( $have ) {
			continue;
		}
		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'     => $item['title'],
				'menu-item-object-id' => $ids[ $item['page'] ],
				'menu-item-object'    => 'page',
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
				'menu-item-position'  => ++$position,
			)
		);
	}

	// WordPress maps the previous theme's menu into our location on switch;
	// the primary navigation must be the menu built here.
	$locations = (array) get_theme_mod( 'nav_menu_locations', array() );
	$current   = isset( $locations['primary'] ) ? (int) $locations['primary'] : 0;
	if ( $current !== (int) $menu_id ) {
		$locations['primary'] = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
	}
}

/* -------------------------------------------------------------------------
 * Step 3 — client logos
 * ---------------------------------------------------------------------- */

/**
 * Copies the bundled client logos into the Media Library as Client logo posts.
 *
 * The file is sideloaded first and the post created only on success, so a
 * failed upload never leaves an image-less logo behind.
 *
 * @return string Result message.
 */
function guru_import_logos() {
	$dir = get_stylesheet_directory() . '/assets/media/client-logos';

	if ( ! is_dir( $dir ) ) {
		return __( 'No bundled client logos found in the theme.', 'guru' );
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$files = guru_image_files( $dir );
	$added = 0;
	$start = time();

	foreach ( (array) $files as $file ) {
		// Resumable: stop before PHP's own limit and let the user click again.
		if ( time() - $start > 20 ) {
			break;
		}

		$name  = basename( $file );
		$title = ucwords( str_replace( array( '-', '_' ), ' ', pathinfo( $name, PATHINFO_FILENAME ) ) );

		if ( guru_find_by_title( 'client_logo', $title ) ) {
			continue;
		}

		$tmp = wp_tempnam( $name );

		if ( ! $tmp || ! copy( $file, $tmp ) ) {
			continue;
		}

		$attachment_id = media_handle_sideload(
			array(
				'name'     => $name,
				'tmp_name' => $tmp,
			),
			0,
			$title
		);

		if ( is_wp_error( $attachment_id ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
			@unlink( $tmp );
			continue;
		}

		$post_id = wp_insert_post(
			array(
				'post_type'   => 'client_logo',
				'post_title'  => $title,
				'post_status' => 'publish',
			)
		);

		if ( ! $post_id || is_wp_error( $post_id ) ) {
			wp_delete_attachment( $attachment_id, true );
			continue;
		}

		wp_update_post(
			array(
				'ID'          => $attachment_id,
				'post_parent' => $post_id,
			)
		);
		set_post_thumbnail( $post_id, $attachment_id );
		++$added;
	}

	$with_logo = get_posts(
		array(
			'post_type'   => 'client_logo',
			'post_status' => 'any',
			'numberposts' => -1,
			'fields'      => 'ids',
			'meta_key'    => '_thumbnail_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		)
	);
	$remaining = count( (array) $files ) - count( $with_logo );

	if ( $remaining > 0 ) {
		return sprintf(
			/* translators: 1: imported this run, 2: remaining. */
			__( 'Imported %1$d logos. %2$d still to go — click the button again to continue.', 'guru' ),
			$added,
			$remaining
		);
	}

	return sprintf(
		/* translators: %d: number of logos imported. */
		__( 'Client logos imported: %d added this run. All bundled logos are now in the Media Library.', 'guru' ),
		$added
	);
}

/* -------------------------------------------------------------------------
 * Step 4 — showcase videos
 * ---------------------------------------------------------------------- */

/**
 * Downloads the showcase videos onto this site and repoints the Showcase items.
 *
 * Resumable: it stops starting new downloads well inside the PHP time limit
 * (30 s on many hosts) and reports how many are left, so a slow connection
 * can never cut a file off halfway.
 *
 * @param string $source Base URL to download from.
 * @return string Result message.
 */
function guru_import_videos( $source ) {
	if ( ! $source ) {
		return __( 'Enter the address to download the videos from, then try again.', 'guru' );
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$data      = guru_seed_data();
	$added     = 0;
	$failed    = 0;
	$remaining = 0;
	$start     = time();
	$uploads   = wp_get_upload_dir();

	// Ask for more time where the host allows it, then size our own budget to
	// whatever limit is actually in force so a download is never killed mid-file.
	if ( function_exists( 'set_time_limit' ) ) {
		@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	}
	$hard   = (int) ini_get( 'max_execution_time' );
	$hard   = $hard > 0 ? $hard : 300;
	$budget = min( 30, max( 8, $hard - 12 ) ); // Stop starting new downloads after this many seconds.

	foreach ( (array) ( isset( $data['works'] ) ? $data['works'] : array() ) as $work ) {
		if ( empty( $work['video'] ) ) {
			continue;
		}

		$post_id = guru_find_by_title( 'work', $work['title'] );

		if ( ! $post_id ) {
			continue;
		}

		$current = (string) get_post_meta( $post_id, '_guru_video_url', true );

		// Already hosted here — nothing to do.
		if ( $current && false !== strpos( $current, $uploads['baseurl'] ) ) {
			continue;
		}

		if ( time() - $start > $budget ) {
			++$remaining;
			continue;
		}

		$url     = $source . '/' . ltrim( $work['video'], '/' );
		// Stay under a typical 60 s proxy timeout even when PHP would allow more.
		$cap     = $hard >= 300 ? 55 : 25;
		$timeout = max( 8, min( $cap, $hard - ( time() - $start ) - 3 ) );
		$temp    = download_url( $url, $timeout );

		if ( is_wp_error( $temp ) ) {
			++$failed;
			continue;
		}

		$attachment_id = media_handle_sideload(
			array(
				'name'     => basename( $work['video'] ),
				'tmp_name' => $temp,
			),
			$post_id,
			$work['title']
		);

		if ( is_wp_error( $attachment_id ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
			@unlink( $temp );
			++$failed;
			continue;
		}

		$new_url = esc_url_raw( wp_get_attachment_url( $attachment_id ) );
		update_post_meta( $post_id, '_guru_video_url', $new_url );
		guru_repoint_solution_media( basename( $work['video'] ), $new_url );
		++$added;
	}

	$message = sprintf(
		/* translators: %d: number of videos imported. */
		__( 'Imported %d videos into the Media Library.', 'guru' ),
		$added
	);

	if ( $remaining ) {
		$message .= ' ' . sprintf(
			/* translators: %d: number of videos left. */
			__( '%d left — click the button again to continue.', 'guru' ),
			$remaining
		);
	}

	if ( $failed ) {
		$message .= ' ' . sprintf(
			/* translators: %d: number of videos that could not be downloaded. */
			__( '%d could not be downloaded — check the address above, or upload those videos by hand.', 'guru' ),
			$failed
		);
	}

	return $message;
}
