<?php
/**
 * Editor fields for showcase items.
 *
 * Implemented as native meta boxes so adding showcase work needs no plugin.
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
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'guru_add_work_meta_box' );

/**
 * Panel markup.
 *
 * @param WP_Post $post Current post.
 */
function guru_render_work_meta_box( $post ) {
	wp_nonce_field( 'guru_save_work_meta', 'guru_work_nonce' );

	$client = get_post_meta( $post->ID, '_guru_client', true );
	$video  = get_post_meta( $post->ID, '_guru_video_url', true );
	?>
	<style>
		.guru-field { margin: 0 0 18px; }
		.guru-field label { display: block; font-weight: 600; margin-bottom: 6px; }
		.guru-field input[type="text"],
		.guru-field input[type="url"] { width: 100%; }
		.guru-field .description { margin-top: 6px; }
		.guru-video-row { display: flex; gap: 8px; }
		.guru-video-row input { flex: 1; }
	</style>

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
		<label for="guru_video_url"><?php esc_html_e( 'Video (optional)', 'guru' ); ?></label>
		<div class="guru-video-row">
			<input type="url" id="guru_video_url" name="guru_video_url"
				value="<?php echo esc_url( $video ); ?>"
				placeholder="https://…/project.mp4" />
			<button type="button" class="button" id="guru_video_pick">
				<?php esc_html_e( 'Choose video', 'guru' ); ?>
			</button>
			<button type="button" class="button" id="guru_video_clear">
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
 * Persist the panel.
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
}
add_action( 'save_post_work', 'guru_save_work_meta' );

/**
 * Media picker for the video field.
 *
 * @param string $hook Current admin page.
 */
function guru_work_admin_assets( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	if ( 'work' !== get_post_type() ) {
		return;
	}

	wp_enqueue_media();
	wp_add_inline_script(
		'jquery-core',
		"jQuery(function($){
			var frame;
			$('#guru_video_pick').on('click', function(e){
				e.preventDefault();
				if (frame) { frame.open(); return; }
				frame = wp.media({
					title: 'Select a video',
					library: { type: 'video' },
					button: { text: 'Use this video' },
					multiple: false
				});
				frame.on('select', function(){
					var a = frame.state().get('selection').first().toJSON();
					$('#guru_video_url').val(a.url);
				});
				frame.open();
			});
			$('#guru_video_clear').on('click', function(e){
				e.preventDefault();
				$('#guru_video_url').val('');
			});
		});"
	);
}
add_action( 'admin_enqueue_scripts', 'guru_work_admin_assets' );

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
}
add_action( 'manage_work_posts_custom_column', 'guru_work_column_content', 10, 2 );
