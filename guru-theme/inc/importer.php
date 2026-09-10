<?php
/**
 * One-click content importer.
 *
 * Fills a fresh install with the real Guru content so the admin is never empty:
 * Solutions, Showcase items, work categories, the site pages, the primary menu,
 * and — optionally — the client logos and showcase videos as Media Library items.
 *
 * Every step is idempotent: running it twice updates the existing posts rather
 * than creating duplicates, so it is safe to re-run after a content update.
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
		'enquiries'  => (int) wp_count_posts( 'guru_enquiry' )->publish,
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
			'desc'  => __( 'Creates the Solutions, Showcase items and Showcase categories. Re-running updates them instead of duplicating.', 'guru' ),
			'button' => __( 'Import content', 'guru' ),
		),
		'pages'   => array(
			'title' => __( '2. Create pages and menu', 'guru' ),
			'desc'  => __( 'Creates Home, About, Solutions, Investor and Blog with the right templates, sets the front page, and builds the primary navigation menu.', 'guru' ),
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

	$notice = isset( $_GET['guru_done'] ) ? sanitize_text_field( wp_unslash( $_GET['guru_done'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Guru setup', 'guru' ); ?></h1>

		<?php if ( $notice ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $notice ); ?></p></div>
		<?php endif; ?>

		<p class="description" style="max-width:46em">
			<?php esc_html_e( 'Run the steps in order on a new site. Everything imported here stays fully editable — Solutions and Showcase items are ordinary posts, and the rest lives in Appearance → Customize.', 'guru' ); ?>
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

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'      => 'guru-setup',
				'guru_done' => rawurlencode( $message ),
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
		$term = get_term_by( 'slug', $slug, 'work_category' );

		if ( ! $term ) {
			wp_insert_term( $name, 'work_category', array( 'slug' => $slug ) );
			++$added['terms'];
		} elseif ( $term->name !== $name ) {
			wp_update_term( $term->term_id, 'work_category', array( 'name' => $name ) );
		}
	}

	foreach ( (array) ( isset( $data['solutions'] ) ? $data['solutions'] : array() ) as $index => $solution ) {
		$existing = guru_find_by_title( 'solution', $solution['title'] );

		$args = array(
			'post_type'    => 'solution',
			'post_title'   => $solution['title'],
			'post_content' => $solution['content'],
			'post_excerpt' => $solution['excerpt'],
			'post_status'  => 'publish',
			'menu_order'   => $index + 1,
		);

		if ( $existing ) {
			$args['ID'] = $existing;
			wp_update_post( $args );
		} else {
			wp_insert_post( $args );
			++$added['solutions'];
		}
	}

	$source = untrailingslashit( get_option( 'guru_import_source', 'https://guru-ebon-eight.vercel.app' ) );

	foreach ( (array) ( isset( $data['works'] ) ? $data['works'] : array() ) as $index => $work ) {
		$existing = guru_find_by_title( 'work', $work['title'] );

		$args = array(
			'post_type'   => 'work',
			'post_title'  => $work['title'],
			'post_status' => 'publish',
			'menu_order'  => $index + 1,
		);

		if ( $existing ) {
			$args['ID'] = $existing;
			$post_id    = wp_update_post( $args );
		} else {
			$post_id = wp_insert_post( $args );
			++$added['works'];
		}

		if ( ! $post_id || is_wp_error( $post_id ) ) {
			continue;
		}

		if ( ! empty( $work['client'] ) ) {
			update_post_meta( $post_id, '_guru_client', sanitize_text_field( $work['client'] ) );
		}

		// Only set the video URL if the editor has not pointed it somewhere else.
		if ( ! empty( $work['video'] ) && ! get_post_meta( $post_id, '_guru_video_url', true ) ) {
			update_post_meta( $post_id, '_guru_video_url', esc_url_raw( $source . '/' . ltrim( $work['video'], '/' ) ) );
		}

		if ( ! empty( $work['cats'] ) ) {
			wp_set_object_terms( $post_id, array_map( 'sanitize_key', (array) $work['cats'] ), 'work_category' );
		}
	}

	return sprintf(
		/* translators: 1: categories, 2: solutions, 3: showcase items. */
		__( 'Content imported. Added %1$d categories, %2$d solutions and %3$d showcase items; existing items were updated.', 'guru' ),
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
		$existing = guru_find_by_title( 'page', $page['title'] );

		$args = array(
			'post_type'   => 'page',
			'post_title'  => $page['title'],
			'post_status' => 'publish',
		);

		if ( $existing ) {
			$args['ID'] = $existing;
			$page_id    = wp_update_post( $args );
		} else {
			// Seed copy only on creation, so later edits are never overwritten.
			if ( ! empty( $page['content'] ) ) {
				$args['post_content'] = $page['content'];
			}
			if ( ! empty( $page['excerpt'] ) ) {
				$args['post_excerpt'] = $page['excerpt'];
			}
			$page_id = wp_insert_post( $args );
			++$added;
		}

		if ( ! $page_id || is_wp_error( $page_id ) ) {
			continue;
		}

		$ids[ $key ] = (int) $page_id;

		if ( $page['template'] ) {
			update_post_meta( $page_id, '_wp_page_template', $page['template'] );
		}
	}

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
		__( 'Pages ready. Created %d new pages, set the front page and blog page, and built the primary menu.', 'guru' ),
		$added
	);
}

/**
 * Builds the primary navigation menu and assigns it to the primary location.
 *
 * @param array $ids Page IDs keyed by slug.
 */
function guru_build_menu( $ids ) {
	$name = __( 'Primary', 'guru' );
	$menu = wp_get_nav_menu_object( $name );

	if ( ! $menu ) {
		$menu_id = wp_create_nav_menu( $name );
		if ( is_wp_error( $menu_id ) ) {
			return;
		}
	} else {
		$menu_id = (int) $menu->term_id;
	}

	// Rebuild from scratch so re-running never stacks up duplicate items.
	foreach ( wp_get_nav_menu_items( $menu_id ) as $item ) {
		wp_delete_post( $item->ID, true );
	}

	$items = array(
		array(
			'title' => __( 'About', 'guru' ),
			'page'  => 'about',
		),
		array(
			'title' => __( 'Solutions', 'guru' ),
			'page'  => 'solutions',
		),
		array(
			'title'   => __( 'Work', 'guru' ),
			'archive' => 'work',
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

	$position = 0;

	foreach ( $items as $item ) {
		++$position;

		if ( isset( $item['archive'] ) ) {
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'     => $item['title'],
					'menu-item-type'      => 'post_type_archive',
					'menu-item-object'    => $item['archive'],
					'menu-item-status'    => 'publish',
					'menu-item-position'  => $position,
				)
			);
			continue;
		}

		if ( empty( $ids[ $item['page'] ] ) ) {
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
				'menu-item-position'  => $position,
			)
		);
	}

	$locations             = (array) get_theme_mod( 'nav_menu_locations', array() );
	$locations['primary']  = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
}

/* -------------------------------------------------------------------------
 * Step 3 — client logos
 * ---------------------------------------------------------------------- */

/**
 * Copies the bundled client logos into the Media Library as Client logo posts.
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

	$files = glob( $dir . '/*.{png,jpg,jpeg,webp,svg}', GLOB_BRACE );
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

		$post_id = wp_insert_post(
			array(
				'post_type'   => 'client_logo',
				'post_title'  => $title,
				'post_status' => 'publish',
			)
		);

		if ( ! $post_id || is_wp_error( $post_id ) ) {
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
			$post_id,
			$title
		);

		if ( is_wp_error( $attachment_id ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
			@unlink( $tmp );
			continue;
		}

		set_post_thumbnail( $post_id, $attachment_id );
		++$added;
	}

	$remaining = count( (array) $files ) - (int) wp_count_posts( 'client_logo' )->publish;

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
 * Resumable: it stops after roughly 45 seconds so a slow connection cannot hit
 * the PHP time limit, and reports how many are left.
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

		if ( time() - $start > 45 ) {
			++$remaining;
			continue;
		}

		$url  = $source . '/' . ltrim( $work['video'], '/' );
		$temp = download_url( $url, 300 );

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

		update_post_meta( $post_id, '_guru_video_url', esc_url_raw( wp_get_attachment_url( $attachment_id ) ) );
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
