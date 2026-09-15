<?php
/**
 * Upload robustness on a slow host.
 *
 * The client's server caps PHP at 30 seconds per request and runs an old
 * ImageMagick. Uploading a large image made WordPress time out while it was
 * generating the intermediate sizes, and the editor reported "The response is
 * not a valid JSON response" with nothing saved. Two mitigations:
 *
 *  1. Ask for more time while a file is being uploaded and processed.
 *  2. Skip the 1536px and 2048px sizes — nothing in the theme uses them, and
 *     they are the two most expensive resizes.
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lift the execution-time limit for the duration of an upload.
 *
 * Runs before the uploaded file is moved into place (so the limit is in force
 * for the image processing that follows). Ignored where the host forbids it.
 *
 * @param array $file The uploaded file (unchanged).
 * @return array
 */
function guru_upload_time_limit( $file ) {
	if ( function_exists( 'set_time_limit' ) ) {
		@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	}
	return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'guru_upload_time_limit' );
add_filter( 'wp_handle_sideload_prefilter', 'guru_upload_time_limit' );

/**
 * Trim the intermediate sizes generated for every image.
 *
 * Also a second chance to lift the time limit, right before the resizing starts.
 *
 * @param array $sizes Sizes about to be generated.
 * @return array
 */
function guru_trim_image_sizes( $sizes ) {
	if ( function_exists( 'set_time_limit' ) ) {
		@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	}
	unset( $sizes['1536x1536'], $sizes['2048x2048'] );
	return $sizes;
}
add_filter( 'intermediate_image_sizes_advanced', 'guru_trim_image_sizes' );
