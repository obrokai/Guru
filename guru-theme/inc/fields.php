<?php
/**
 * Editor fields for showcase items and solutions.
 *
 * Implemented as native meta boxes so adding content needs no plugin.
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const GURU_WORK_META = array(
	'_guru_client'    => 'sanitize_text_field',
	'_guru_video_url' => 'esc_url_raw',
);

/**
 * Register the "Showcase details" panel.
 */
function guru_add_work_meta_box() {
	add_meta_box(
		'guru_work_details',
		__( 'Showcase details', 'guru' ),
		'guru_render_work_meta_box',
		'work',
		'guru_top',
		'high'
	);
}
add_action( 'add_meta_boxes', 'guru_add_work_meta_box' );

/**
 * Render the "guru_top" meta boxes directly under the title, above the content
 * editor. WordPress puts ordinary meta boxes below the editor, where editors
 * missed them and pasted cover images into the body instead.
 *
 * @param WP_Post $post Post being edited.
 */
function guru_meta_boxes_after_title( $post ) {
	if ( ! in_array( $post->post_type, array( 'work', 'solution' ), true ) ) {
		return;
	}
	echo '<div id="guru-top-boxes" style="margin-top:20px">';
	do_meta_boxes( get_current_screen(), 'guru_top', $post );
	echo '</div>';
}
add_action( 'edit_form_after_title', 'guru_meta_boxes_after_title' );

const GURU_TOP_BOXES = array( 'guru_work_details', 'guru_solution_media' );

/**
 * Keep the Guru panels open. A collapsed panel shrinks to its title bar and
 * WordPress remembers that per user, so editors thought the video field had
 * disappeared. Collapsed or hidden states saved before 1.2.8 are ignored.
 *
 * @param string[] $classes Postbox classes.
 * @return string[]
 */
function guru_keep_meta_box_open( $classes ) {
	return array_values( array_diff( $classes, array( 'closed' ) ) );
}
add_filter( 'postbox_classes_work_guru_work_details', 'guru_keep_meta_box_open' );
add_filter( 'postbox_classes_solution_guru_solution_media', 'guru_keep_meta_box_open' );

/**
 * Never hide the Guru panels through Screen Options.
 *
 * @param string[] $hidden Hidden meta box IDs.
 * @return string[]
 */
function guru_never_hide_top_boxes( $hidden ) {
	return array_values( array_diff( (array) $hidden, GURU_TOP_BOXES ) );
}
add_filter( 'hidden_meta_boxes', 'guru_never_hide_top_boxes' );

/**
 * Shared admin styles for the meta boxes.
 */
function guru_meta_box_styles() {
	?>
	<style>
		.guru-field { margin: 0 0 18px; }
		.guru-field label { display: block; font-weight: 600; margin-bottom: 6px; }
		.guru-field input[type="text"],
		.guru-field input[type="url"],
		.guru-field select,
		.guru-field textarea { width: 100%; }
		.guru-field textarea { font-family: Menlo, Consolas, monospace; font-size: 12px; line-height: 1.6; }
		.guru-field .description { margin-top: 6px; }
		.guru-media-row { display: flex; gap: 8px; }
		.guru-media-row input { flex: 1; }
		/* No collapse or reorder controls on the Guru panels: they always stay open. */
		#guru-top-boxes .postbox .handle-actions { display: none; }
		#guru-top-boxes .postbox .hndle { pointer-events: none; cursor: default; }
		#guru-top-boxes .postbox.closed .inside { display: block; }
		.metabox-prefs label[for="guru_work_details-hide"],
		.metabox-prefs label[for="guru_solution_media-hide"] { display: none; }
	</style>
	<?php
}

/**
 * Panel markup.
 *
 * @param WP_Post $post Current post.
 */
function guru_render_work_meta_box( $post ) {
	wp_nonce_field( 'guru_save_work_meta', 'guru_work_nonce' );

	$client = get_post_meta( $post->ID, '_guru_client', true );
	$video  = get_post_meta( $post->ID, '_guru_video_url', true );
	$home   = (int) get_post_meta( $post->ID, '_guru_home_order', true );
	$tags   = get_post_meta( $post->ID, '_guru_tags', true );
	$tags   = is_array( $tags ) ? $tags : array();

	guru_meta_box_styles();
	?>
	<p class="description" style="margin:0 0 16px;padding:10px 12px;background:#f0f6fc;border-left:4px solid #72aee6">
		<?php esc_html_e( 'Cover image: use the "Cover image" box in the right-hand column — that is what the card shows. The text editor below is optional and only appears on the project’s own page.', 'guru' ); ?>
	</p>
	<div class="guru-field">
		<label for="guru_home_order"><?php esc_html_e( 'Homepage position', 'guru' ); ?></label>
		<input type="number" id="guru_home_order" name="guru_home_order" min="1" max="4" step="1" style="width:90px"
			value="<?php echo $home ? (int) $home : ''; ?>" />
		<p class="description">
			<?php esc_html_e( '1–4 to feature this item in the "Our Work" block on the homepage (1 is the large card). One item per number: if another item already has it, that item is moved off the homepage when you save. Leave empty to keep it off the homepage; empty slots are filled by the first items in the Showcase list.', 'guru' ); ?>
		</p>
	</div>

	<div class="guru-field">
		<label for="guru_client"><?php esc_html_e( 'Client name', 'guru' ); ?></label>
		<input type="text" id="guru_client" name="guru_client"
			value="<?php echo esc_attr( $client ); ?>"
			placeholder="<?php esc_attr_e( 'e.g. Hennessy', 'guru' ); ?>" />
		<p class="description">
			<?php esc_html_e( 'Shown under the project title.', 'guru' ); ?>
		</p>
	</div>

	<div class="guru-field">
		<label for="guru_tags"><?php esc_html_e( 'Card labels', 'guru' ); ?></label>
		<input type="text" id="guru_tags" name="guru_tags"
			value="<?php echo esc_attr( implode( ', ', $tags ) ); ?>"
			placeholder="<?php esc_attr_e( 'e.g. Influencer Marketing, Campaign / PR', 'guru' ); ?>" />
		<p class="description">
			<?php esc_html_e( 'Comma-separated labels shown on the card and in the lightbox. Leave empty to show the item’s categories.', 'guru' ); ?>
		</p>
	</div>

	<div class="guru-field">
		<label for="guru_video_url"><?php esc_html_e( 'Video (optional)', 'guru' ); ?></label>
		<div class="guru-media-row">
			<input type="url" id="guru_video_url" name="guru_video_url"
				value="<?php echo esc_url( $video ); ?>"
				placeholder="https://…/project.mp4" />
			<button type="button" class="button guru-pick-media" data-target="guru_video_url" data-type="video">
				<?php esc_html_e( 'Choose video', 'guru' ); ?>
			</button>
			<button type="button" class="button guru-clear-field" data-target="guru_video_url">
				<?php esc_html_e( 'Clear', 'guru' ); ?>
			</button>
		</div>
		<p class="description">
			<?php esc_html_e( 'Leave empty for an image-only project — the cover image is used instead.', 'guru' ); ?>
		</p>
	</div>
	<?php
}

/**
 * Persist the showcase panel.
 *
 * @param int $post_id Post ID.
 */
function guru_save_work_meta( $post_id ) {
	if ( ! isset( $_POST['guru_work_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['guru_work_nonce'] ) ), 'guru_save_work_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$map = array(
		'_guru_client'    => 'guru_client',
		'_guru_video_url' => 'guru_video_url',
	);

	foreach ( $map as $meta_key => $field ) {
		$sanitizer = GURU_WORK_META[ $meta_key ];
		$raw       = isset( $_POST[ $field ] ) ? wp_unslash( $_POST[ $field ] ) : '';
		$value     = call_user_func( $sanitizer, $raw );

		if ( '' === $value ) {
			delete_post_meta( $post_id, $meta_key );
		} else {
			update_post_meta( $post_id, $meta_key, $value );
		}
	}

	$home = isset( $_POST['guru_home_order'] ) ? absint( wp_unslash( $_POST['guru_home_order'] ) ) : 0;
	if ( $home >= 1 && $home <= 4 ) {
		update_post_meta( $post_id, '_guru_home_order', $home );
		// The slot belongs to this item now; tell the editor who lost it.
		$demoted = guru_claim_home_slot( $post_id, $home );
		if ( $demoted ) {
			set_transient(
				'guru_home_slot_notice_' . get_current_user_id(),
				array(
					'slot'  => $home,
					'names' => array_map( 'get_the_title', $demoted ),
				),
				MINUTE_IN_SECONDS
			);
		}
	} else {
		delete_post_meta( $post_id, '_guru_home_order' );
	}

	$tags = isset( $_POST['guru_tags'] ) ? sanitize_text_field( wp_unslash( $_POST['guru_tags'] ) ) : '';
	$tags = array_values( array_filter( array_map( 'trim', explode( ',', $tags ) ) ) );
	if ( $tags ) {
		update_post_meta( $post_id, '_guru_tags', $tags );
	} else {
		delete_post_meta( $post_id, '_guru_tags' );
	}
}
add_action( 'save_post_work', 'guru_save_work_meta' );

/* -------------------------------------------------------------------------
 * Solutions — media block and highlight cards
 * ---------------------------------------------------------------------- */

/**
 * Register the "Solution media" panel.
 */
function guru_add_solution_meta_box() {
	add_meta_box(
		'guru_solution_media',
		__( 'Solution media & highlights', 'guru' ),
		'guru_render_solution_meta_box',
		'solution',
		'guru_top',
		'high'
	);
}
add_action( 'add_meta_boxes', 'guru_add_solution_meta_box' );

/**
 * Turn stored rows back into the one-per-line " | " text the editor shows.
 *
 * @param array    $rows Rows.
 * @param string[] $keys Keys in display order.
 * @return string
 */
function guru_rows_to_lines( $rows, $keys ) {
	$lines = array();

	foreach ( (array) $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$parts = array();
		foreach ( $keys as $key ) {
			$parts[] = isset( $row[ $key ] ) ? $row[ $key ] : '';
		}
		// Drop trailing empties so "url" alone stays "url", not "url |  | ".
		while ( $parts && '' === end( $parts ) ) {
			array_pop( $parts );
		}
		$lines[] = implode( ' | ', $parts );
	}

	return implode( "\n", $lines );
}

/**
 * Parse one-per-line " | " text into rows keyed by $keys.
 *
 * @param string   $text       Raw textarea value.
 * @param string[] $keys       Keys in order.
 * @param string[] $sanitizers Sanitizer per key (defaults to sanitize_text_field).
 * @return array[]
 */
function guru_lines_to_rows( $text, $keys, $sanitizers = array() ) {
	$rows = array();

	foreach ( guru_pipe_rows( $text ) as $parts ) {
		$row = array();
		foreach ( $keys as $i => $key ) {
			$fn          = isset( $sanitizers[ $key ] ) ? $sanitizers[ $key ] : 'sanitize_text_field';
			$row[ $key ] = isset( $parts[ $i ] ) ? call_user_func( $fn, $parts[ $i ] ) : '';
		}
		if ( '' === $row[ $keys[0] ] ) {
			continue;
		}
		$rows[] = $row;
	}

	return $rows;
}

/**
 * Solution panel markup.
 *
 * @param WP_Post $post Current post.
 */
function guru_render_solution_meta_box( $post ) {
	wp_nonce_field( 'guru_save_solution_meta', 'guru_solution_nonce' );

	$m = guru_get_solution_media( $post->ID );

	guru_meta_box_styles();
	?>
	<div class="guru-field">
		<label for="guru_media_type"><?php esc_html_e( 'Media block', 'guru' ); ?></label>
		<select id="guru_media_type" name="guru_media_type">
			<?php foreach ( guru_solution_media_types() as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $m['type'], $value ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<p class="description"><?php esc_html_e( 'Shown under the intro on the Solutions page. Choose "None" for a text-only panel.', 'guru' ); ?></p>
	</div>

	<div class="guru-field">
		<label for="guru_panel_tag"><?php esc_html_e( 'Short tag', 'guru' ); ?></label>
		<input type="text" id="guru_panel_tag" name="guru_panel_tag"
			value="<?php echo esc_attr( get_post_meta( $post->ID, '_guru_panel_tag', true ) ); ?>"
			placeholder="<?php echo esc_attr( get_the_title( $post ) ); ?>" />
		<p class="description"><?php esc_html_e( 'The small pill above the panel title on the Solutions page. Leave empty to use the title.', 'guru' ); ?></p>
	</div>

	<div class="guru-field">
		<label for="guru_media_label"><?php esc_html_e( 'Media caption', 'guru' ); ?></label>
		<input type="text" id="guru_media_label" name="guru_media_label"
			value="<?php echo esc_attr( $m['label'] ); ?>"
			placeholder="<?php esc_attr_e( 'e.g. Hennessy × NBA · Campaign', 'guru' ); ?>" />
		<p class="description"><?php esc_html_e( 'Small label above the media block.', 'guru' ); ?></p>
	</div>

	<div class="guru-field">
		<label for="guru_media_items"><?php esc_html_e( 'Media files', 'guru' ); ?></label>
		<textarea id="guru_media_items" name="guru_media_items" rows="5"
			placeholder="https://…/video.mp4&#10;https://…/screen.png | Caption under the phone | Alt text"><?php echo esc_textarea( guru_rows_to_lines( $m['items'], array( 'src', 'caption', 'alt' ) ) ); ?></textarea>
		<p>
			<button type="button" class="button guru-pick-media" data-target="guru_media_items" data-type="" data-multiple="1" data-append="1">
				<?php esc_html_e( 'Add from Media Library', 'guru' ); ?>
			</button>
		</p>
		<p class="description"><?php esc_html_e( 'One file per line: URL | caption (phones only) | alt text. Carousel and phone blocks use every line; the single-video block uses the first.', 'guru' ); ?></p>
	</div>

	<div class="guru-field">
		<label for="guru_highlights"><?php esc_html_e( 'Highlight cards', 'guru' ); ?></label>
		<textarea id="guru_highlights" name="guru_highlights" rows="5"
			placeholder="🌏 | Cross-Cultural Strategy | Localised messaging that resonates across markets."><?php echo esc_textarea( guru_rows_to_lines( $m['highlights'], array( 'icon', 'title', 'desc' ) ) ); ?></textarea>
		<p class="description"><?php esc_html_e( 'One card per line: emoji | title | description. Shown below the media block (four cards look best). Leave empty for none.', 'guru' ); ?></p>
	</div>
	<?php
}

/**
 * Persist the solution panel.
 *
 * @param int $post_id Post ID.
 */
function guru_save_solution_meta( $post_id ) {
	if ( ! isset( $_POST['guru_solution_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['guru_solution_nonce'] ) ), 'guru_save_solution_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$type = isset( $_POST['guru_media_type'] ) ? sanitize_key( wp_unslash( $_POST['guru_media_type'] ) ) : '';
	if ( ! array_key_exists( $type, guru_solution_media_types() ) ) {
		$type = '';
	}

	$label = isset( $_POST['guru_media_label'] ) ? sanitize_text_field( wp_unslash( $_POST['guru_media_label'] ) ) : '';
	$items = guru_lines_to_rows(
		isset( $_POST['guru_media_items'] ) ? wp_unslash( $_POST['guru_media_items'] ) : '', // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitised per part below.
		array( 'src', 'caption', 'alt' ),
		array( 'src' => 'esc_url_raw' )
	);
	$hl    = guru_lines_to_rows(
		isset( $_POST['guru_highlights'] ) ? wp_unslash( $_POST['guru_highlights'] ) : '', // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitised per part below.
		array( 'icon', 'title', 'desc' )
	);

	$store = array(
		'_guru_media_type'  => $type,
		'_guru_media_label' => $label,
		'_guru_media_items' => $items,
		'_guru_highlights'  => $hl,
		'_guru_panel_tag'   => isset( $_POST['guru_panel_tag'] ) ? sanitize_text_field( wp_unslash( $_POST['guru_panel_tag'] ) ) : '',
	);

	foreach ( $store as $key => $value ) {
		if ( '' === $value || array() === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
}
add_action( 'save_post_solution', 'guru_save_solution_meta' );

/* -------------------------------------------------------------------------
 * Shared admin script — media picker for any .guru-pick-media button
 * ---------------------------------------------------------------------- */

/**
 * Media picker for the video / media fields.
 *
 * @param string $hook Current admin page.
 */
function guru_meta_box_admin_assets( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	if ( ! in_array( get_post_type(), array( 'work', 'solution' ), true ) ) {
		return;
	}

	wp_enqueue_media();
	wp_add_inline_script(
		'jquery-core',
		"jQuery(function($){
			$('.guru-pick-media').on('click', function(e){
				e.preventDefault();
				var btn = $(this), target = $('#' + btn.data('target')), type = btn.data('type');
				var frame = wp.media({
					title: btn.text().trim(),
					library: type ? { type: type } : {},
					button: { text: 'Use selected' },
					multiple: !!btn.data('multiple')
				});
				frame.on('select', function(){
					var urls = frame.state().get('selection').map(function(a){ return a.toJSON().url; });
					if (btn.data('append')) {
						var cur = target.val().replace(/\\s+$/, '');
						target.val((cur ? cur + '\\n' : '') + urls.join('\\n'));
					} else {
						target.val(urls[0] || '');
					}
				});
				frame.open();
			});
			$('.guru-clear-field').on('click', function(e){
				e.preventDefault();
				$('#' + $(this).data('target')).val('');
			});
		});"
	);
}
add_action( 'admin_enqueue_scripts', 'guru_meta_box_admin_assets' );

/* -------------------------------------------------------------------------
 * List-table columns
 * ---------------------------------------------------------------------- */

/**
 * Show the client name as a column in the showcase list.
 *
 * @param array $columns Existing columns.
 * @return array
 */
function guru_work_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['guru_client'] = __( 'Client', 'guru' );
			$new['guru_media']  = __( 'Media', 'guru' );
			$new['guru_home']   = __( 'Homepage', 'guru' );
		}
	}
	return $new;
}
add_filter( 'manage_work_posts_columns', 'guru_work_columns' );

/**
 * Render the custom columns.
 *
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 */
function guru_work_column_content( $column, $post_id ) {
	if ( 'guru_client' === $column ) {
		echo esc_html( get_post_meta( $post_id, '_guru_client', true ) );
	}
	if ( 'guru_media' === $column ) {
		if ( get_post_meta( $post_id, '_guru_video_url', true ) ) {
			esc_html_e( 'Video', 'guru' );
		} elseif ( has_post_thumbnail( $post_id ) ) {
			esc_html_e( 'Image', 'guru' );
		} else {
			echo '<span style="color:#b32d2e">' . esc_html__( 'None', 'guru' ) . '</span>';
		}
	}
	if ( 'guru_home' === $column ) {
		$home = (int) get_post_meta( $post_id, '_guru_home_order', true );
		echo $home ? '<strong>#' . (int) $home . '</strong>' : '&mdash;';
	}
}
add_action( 'manage_work_posts_custom_column', 'guru_work_column_content', 10, 2 );

/**
 * Media column in the solutions list.
 *
 * @param array $columns Existing columns.
 * @return array
 */
function guru_solution_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['guru_media'] = __( 'Media', 'guru' );
		}
	}
	return $new;
}
add_filter( 'manage_solution_posts_columns', 'guru_solution_columns' );

/**
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 */
function guru_solution_column_content( $column, $post_id ) {
	if ( 'guru_media' !== $column ) {
		return;
	}
	$m     = guru_get_solution_media( $post_id );
	$types = guru_solution_media_types();
	$bits  = array();
	if ( $m['type'] && $m['items'] ) {
		$bits[] = sprintf( '%s (%d)', $types[ $m['type'] ], count( $m['items'] ) );
	}
	if ( $m['highlights'] ) {
		/* translators: %d: number of highlight cards. */
		$bits[] = sprintf( _n( '%d highlight', '%d highlights', count( $m['highlights'] ), 'guru' ), count( $m['highlights'] ) );
	}
	echo $bits ? esc_html( implode( ' · ', $bits ) ) : '<span style="color:#787c82">' . esc_html__( 'Text only', 'guru' ) . '</span>';
}
add_action( 'manage_solution_posts_custom_column', 'guru_solution_column_content', 10, 2 );

/* -------------------------------------------------------------------------
 * Client logos — opt in to the homepage ticker
 * ---------------------------------------------------------------------- */

/**
 * The homepage marquee shows six brand wordmarks by default (as on the static
 * site). Square client-wall logos look wrong at that height, so a logo only
 * joins the ticker when the editor ticks this box.
 */
function guru_add_client_logo_meta_box() {
	add_meta_box(
		'guru_client_logo_ticker',
		__( 'Homepage ticker', 'guru' ),
		'guru_render_client_logo_meta_box',
		'client_logo',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'guru_add_client_logo_meta_box' );

/**
 * @param WP_Post $post Current post.
 */
function guru_render_client_logo_meta_box( $post ) {
	wp_nonce_field( 'guru_save_client_logo_meta', 'guru_client_logo_nonce' );
	$on = (bool) get_post_meta( $post->ID, '_guru_in_ticker', true );
	?>
	<label>
		<input type="checkbox" name="guru_in_ticker" value="1" <?php checked( $on ); ?> />
		<?php esc_html_e( 'Show this logo in the homepage marquee', 'guru' ); ?>
	</label>
	<p class="description">
		<?php esc_html_e( 'Wide wordmark logos work best (the strip is 34px tall). When no logo is ticked, the six brands from the original design are shown.', 'guru' ); ?>
	</p>
	<?php
}

/**
 * @param int $post_id Post ID.
 */
function guru_save_client_logo_meta( $post_id ) {
	if ( ! isset( $_POST['guru_client_logo_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['guru_client_logo_nonce'] ) ), 'guru_save_client_logo_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( ! empty( $_POST['guru_in_ticker'] ) ) {
		update_post_meta( $post_id, '_guru_in_ticker', 1 );
	} else {
		delete_post_meta( $post_id, '_guru_in_ticker' );
	}
}
add_action( 'save_post_client_logo', 'guru_save_client_logo_meta' );

/**
 * @param array $columns Existing columns.
 * @return array
 */
function guru_client_logo_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['guru_ticker'] = __( 'Homepage ticker', 'guru' );
		}
	}
	return $new;
}
add_filter( 'manage_client_logo_posts_columns', 'guru_client_logo_columns' );

/**
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 */
function guru_client_logo_column_content( $column, $post_id ) {
	if ( 'guru_ticker' === $column ) {
		echo get_post_meta( $post_id, '_guru_in_ticker', true ) ? '<strong>' . esc_html__( 'Yes', 'guru' ) . '</strong>' : '&mdash;';
	}
}
add_action( 'manage_client_logo_posts_custom_column', 'guru_client_logo_column_content', 10, 2 );

/**
 * After a showcase item takes a homepage slot, say which item lost it — the
 * editor otherwise has no way to know why another card disappeared.
 */
function guru_home_slot_notice() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'work' !== $screen->post_type ) {
		return;
	}

	$key    = 'guru_home_slot_notice_' . get_current_user_id();
	$notice = get_transient( $key );
	if ( ! $notice || empty( $notice['names'] ) ) {
		return;
	}
	delete_transient( $key );

	printf(
		'<div class="notice notice-info is-dismissible"><p>%s</p></div>',
		esc_html(
			sprintf(
				/* translators: 1: homepage slot number, 2: titles of the items that lost it. */
				__( 'Homepage position %1$d now belongs to this item. Moved off the homepage: %2$s.', 'guru' ),
				(int) $notice['slot'],
				implode( ', ', array_map( 'strval', (array) $notice['names'] ) )
			)
		)
	);
}
add_action( 'admin_notices', 'guru_home_slot_notice' );
