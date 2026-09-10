<?php
/**
 * Enquiry storage — a dedicated table, not a post type.
 *
 * Contact-form submissions used to be archived as a private post type. That
 * lives in wp_posts, which a staging → production "push" overwrites wholesale,
 * silently deleting every enquiry received since the staging site was cloned.
 * Keeping enquiries in their own table lets a push exclude it, so production
 * data is never lost. The table is also excluded from nothing the client
 * edits, so there is no reason to ever copy it between environments.
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const GURU_ENQUIRIES_DB_VERSION = '1';

/**
 * Fully-prefixed table name.
 *
 * @return string
 */
function guru_enquiries_table() {
	global $wpdb;
	return $wpdb->prefix . 'guru_enquiries';
}

/**
 * Create (or upgrade) the table. Safe to call repeatedly.
 */
function guru_enquiries_install() {
	global $wpdb;

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$table   = guru_enquiries_table();
	$charset = $wpdb->get_charset_collate();

	dbDelta(
		"CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			created_at datetime NOT NULL,
			name varchar(191) NOT NULL DEFAULT '',
			company varchar(191) NOT NULL DEFAULT '',
			email varchar(191) NOT NULL DEFAULT '',
			phone varchar(64) NOT NULL DEFAULT '',
			service varchar(191) NOT NULL DEFAULT '',
			project varchar(191) NOT NULL DEFAULT '',
			message text NOT NULL,
			source_url varchar(255) NOT NULL DEFAULT '',
			PRIMARY KEY  (id),
			KEY created_at (created_at)
		) {$charset};"
	);

	update_option( 'guru_enquiries_db_version', GURU_ENQUIRIES_DB_VERSION );
}
add_action( 'after_switch_theme', 'guru_enquiries_install' );

/**
 * Whether the enquiries table exists (checked once per request).
 *
 * @param bool $recheck Force a fresh look.
 * @return bool
 */
function guru_enquiries_table_exists( $recheck = false ) {
	global $wpdb;
	static $exists = null;

	if ( null === $exists || $recheck ) {
		$table  = guru_enquiries_table();
		$exists = ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	}

	return $exists;
}

/**
 * Make sure the table exists — after a fresh activation, after installing the
 * theme by copying files, and after a staging → production push that carried
 * the options table but (correctly) left this table out.
 */
function guru_enquiries_maybe_install() {
	if ( get_option( 'guru_enquiries_db_version' ) !== GURU_ENQUIRIES_DB_VERSION || ! guru_enquiries_table_exists() ) {
		guru_enquiries_install();
		guru_enquiries_table_exists( true );
	}
}
add_action( 'admin_init', 'guru_enquiries_maybe_install' );

/**
 * Store one enquiry.
 *
 * @param array $data Keys: name, company, email, phone, service, project, message, source_url.
 * @return int Row ID, or 0 on failure.
 */
function guru_enquiry_insert( $data ) {
	global $wpdb;

	guru_enquiries_maybe_install();

	$row = array(
		'created_at' => current_time( 'mysql' ),
		'name'       => isset( $data['name'] ) ? $data['name'] : '',
		'company'    => isset( $data['company'] ) ? $data['company'] : '',
		'email'      => isset( $data['email'] ) ? $data['email'] : '',
		'phone'      => isset( $data['phone'] ) ? $data['phone'] : '',
		'service'    => isset( $data['service'] ) ? $data['service'] : '',
		'project'    => isset( $data['project'] ) ? $data['project'] : '',
		'message'    => isset( $data['message'] ) ? $data['message'] : '',
		'source_url' => isset( $data['source_url'] ) ? $data['source_url'] : '',
	);

	$ok = $wpdb->insert( guru_enquiries_table(), $row, array_fill( 0, count( $row ), '%s' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

	if ( ! $ok && ! guru_enquiries_table_exists( true ) ) {
		// The table has gone missing (e.g. dropped by a migration) — recreate and retry once.
		guru_enquiries_install();
		$ok = $wpdb->insert( guru_enquiries_table(), $row, array_fill( 0, count( $row ), '%s' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	}

	return $ok ? (int) $wpdb->insert_id : 0;
}

/**
 * Number of stored enquiries.
 *
 * @return int
 */
function guru_enquiries_count() {
	global $wpdb;

	if ( get_option( 'guru_enquiries_db_version' ) !== GURU_ENQUIRIES_DB_VERSION ) {
		return 0;
	}

	if ( ! guru_enquiries_table_exists() ) {
		return 0;
	}

	$table = guru_enquiries_table();
	return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
}

/**
 * One-time migration of enquiries archived by the earlier post-type approach.
 */
function guru_enquiries_migrate_posts() {
	if ( get_option( 'guru_enquiries_migrated' ) ) {
		return;
	}

	$posts = get_posts(
		array(
			'post_type'   => 'guru_enquiry',
			'post_status' => 'any',
			'numberposts' => -1,
		)
	);

	$all_ok = true;

	foreach ( $posts as $post ) {
		$id = guru_enquiry_insert(
			array(
				'name'    => (string) get_post_meta( $post->ID, '_guru_name', true ),
				'company' => (string) get_post_meta( $post->ID, '_guru_company', true ),
				'email'   => (string) get_post_meta( $post->ID, '_guru_email', true ),
				'phone'   => (string) get_post_meta( $post->ID, '_guru_phone', true ),
				'service' => (string) get_post_meta( $post->ID, '_guru_service', true ),
				'message' => $post->post_content,
			)
		);

		// Only remove the old record once its copy is safely in the table.
		if ( $id ) {
			wp_delete_post( $post->ID, true );
		} else {
			$all_ok = false;
		}
	}

	if ( $all_ok ) {
		update_option( 'guru_enquiries_migrated', 1 );
	}
}
add_action( 'admin_init', 'guru_enquiries_migrate_posts', 20 );

/* -------------------------------------------------------------------------
 * Admin screen
 * ---------------------------------------------------------------------- */

/**
 * Who may read enquiries — editors and above.
 *
 * @return string
 */
function guru_enquiries_cap() {
	return 'edit_others_posts';
}

/**
 * Top-level "Enquiries" menu, where the post type used to be.
 */
function guru_enquiries_menu() {
	add_menu_page(
		__( 'Enquiries', 'guru' ),
		__( 'Enquiries', 'guru' ),
		guru_enquiries_cap(),
		'guru-enquiries',
		'guru_enquiries_screen',
		'dashicons-email-alt',
		26
	);
}
add_action( 'admin_menu', 'guru_enquiries_menu' );

/**
 * Render the list screen.
 */
function guru_enquiries_screen() {
	if ( ! current_user_can( guru_enquiries_cap() ) ) {
		return;
	}

	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

	if ( ! class_exists( 'Guru_Enquiries_Table' ) ) {
		/**
		 * Paginated list of enquiries.
		 */
		class Guru_Enquiries_Table extends WP_List_Table {

			/** Columns. */
			public function get_columns() {
				return array(
					'created_at' => __( 'Received', 'guru' ),
					'name'       => __( 'Name', 'guru' ),
					'company'    => __( 'Company', 'guru' ),
					'contact'    => __( 'Contact', 'guru' ),
					'service'    => __( 'Service', 'guru' ),
					'message'    => __( 'Message', 'guru' ),
				);
			}

			/** Load one page of rows. */
			public function prepare_items() {
				global $wpdb;

				$per_page = 20;
				$paged    = max( 1, $this->get_pagenum() );
				$table    = guru_enquiries_table();
				$total    = guru_enquiries_count();

				$this->items = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$wpdb->prepare(
						"SELECT * FROM {$table} ORDER BY created_at DESC, id DESC LIMIT %d OFFSET %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
						$per_page,
						( $paged - 1 ) * $per_page
					),
					ARRAY_A
				);

				$this->set_pagination_args(
					array(
						'total_items' => $total,
						'per_page'    => $per_page,
					)
				);

				$this->_column_headers = array( $this->get_columns(), array(), array() );
			}

			/**
			 * Default cell renderer.
			 *
			 * @param array  $item   Row.
			 * @param string $column Column key.
			 */
			public function column_default( $item, $column ) {
				return isset( $item[ $column ] ) ? esc_html( $item[ $column ] ) : '';
			}

			/**
			 * @param array $item Row.
			 */
			public function column_created_at( $item ) {
				$date   = esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $item['created_at'] ) );
				$delete = wp_nonce_url(
					add_query_arg(
						array(
							'action' => 'guru_enquiry_delete',
							'id'     => (int) $item['id'],
						),
						admin_url( 'admin-post.php' )
					),
					'guru_enquiry_delete_' . (int) $item['id']
				);

				return $date . $this->row_actions(
					array(
						'delete' => '<a href="' . esc_url( $delete ) . '" onclick="return confirm(\'' . esc_js( __( 'Delete this enquiry permanently?', 'guru' ) ) . '\')">' . esc_html__( 'Delete', 'guru' ) . '</a>',
					)
				);
			}

			/**
			 * @param array $item Row.
			 */
			public function column_contact( $item ) {
				$out = '';
				if ( $item['email'] ) {
					$out .= '<a href="mailto:' . esc_attr( $item['email'] ) . '">' . esc_html( $item['email'] ) . '</a><br />';
				}
				if ( $item['phone'] ) {
					$out .= esc_html( $item['phone'] );
				}
				return $out;
			}

			/**
			 * @param array $item Row.
			 */
			public function column_message( $item ) {
				$msg = trim( (string) $item['message'] );
				$out = '';
				if ( $item['project'] ) {
					$out .= '<em>' . esc_html( $item['project'] ) . '</em><br />';
				}
				if ( '' === $msg ) {
					return $out . '&mdash;';
				}
				if ( mb_strlen( $msg ) <= 120 ) {
					return $out . nl2br( esc_html( $msg ) );
				}
				return $out . '<details><summary>' . esc_html( mb_substr( $msg, 0, 120 ) ) . '&hellip;</summary>' . nl2br( esc_html( $msg ) ) . '</details>';
			}

			/** Empty state. */
			public function no_items() {
				esc_html_e( 'No enquiries yet. Submissions from the contact forms will appear here.', 'guru' );
			}
		}
	}

	$table = new Guru_Enquiries_Table();
	$table->prepare_items();

	$export = wp_nonce_url( admin_url( 'admin-post.php?action=guru_enquiries_export' ), 'guru_enquiries_export' );
	$notice = isset( $_GET['guru_deleted'] ) ? (int) $_GET['guru_deleted'] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	?>
	<div class="wrap">
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Enquiries', 'guru' ); ?></h1>
		<a href="<?php echo esc_url( $export ); ?>" class="page-title-action"><?php esc_html_e( 'Download CSV', 'guru' ); ?></a>
		<hr class="wp-header-end" />

		<?php if ( $notice ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Enquiry deleted.', 'guru' ); ?></p></div>
		<?php endif; ?>

		<p class="description">
			<?php esc_html_e( 'Every contact-form submission is stored here and emailed to the enquiry address set in Appearance → Customize → Contact details. This list lives in its own database table, so pushing a staging site to production never overwrites it.', 'guru' ); ?>
		</p>

		<?php $table->display(); ?>
	</div>
	<?php
}

/**
 * Delete one enquiry.
 */
function guru_enquiry_delete() {
	if ( ! current_user_can( guru_enquiries_cap() ) ) {
		wp_die( esc_html__( 'You are not allowed to do this.', 'guru' ), 403 );
	}

	$id = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;
	check_admin_referer( 'guru_enquiry_delete_' . $id );

	global $wpdb;
	$wpdb->delete( guru_enquiries_table(), array( 'id' => $id ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

	wp_safe_redirect( add_query_arg( array( 'page' => 'guru-enquiries', 'guru_deleted' => 1 ), admin_url( 'admin.php' ) ) );
	exit;
}
add_action( 'admin_post_guru_enquiry_delete', 'guru_enquiry_delete' );

/**
 * Stream every enquiry as CSV.
 */
function guru_enquiries_export() {
	if ( ! current_user_can( guru_enquiries_cap() ) ) {
		wp_die( esc_html__( 'You are not allowed to do this.', 'guru' ), 403 );
	}
	check_admin_referer( 'guru_enquiries_export' );

	global $wpdb;
	$table = guru_enquiries_table();
	$rows  = $wpdb->get_results( "SELECT created_at, name, company, email, phone, service, project, message, source_url FROM {$table} ORDER BY created_at DESC", ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="guru-enquiries-' . gmdate( 'Y-m-d' ) . '.csv"' );

	$out = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	fprintf( $out, "\xEF\xBB\xBF" ); // UTF-8 BOM so Excel opens Chinese text correctly.
	fputcsv( $out, array( 'Received', 'Name', 'Company', 'Email', 'Phone', 'Service', 'Project', 'Message', 'Page' ) );
	foreach ( (array) $rows as $row ) {
		// Neutralise spreadsheet formula injection.
		foreach ( $row as $k => $v ) {
			if ( is_string( $v ) && preg_match( '/^[=+\-@]/', $v ) ) {
				$row[ $k ] = "'" . $v;
			}
		}
		fputcsv( $out, array_values( $row ) );
	}
	fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	exit;
}
add_action( 'admin_post_guru_enquiries_export', 'guru_enquiries_export' );
