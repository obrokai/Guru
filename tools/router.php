<?php
/**
 * Router for PHP's built-in server so pretty permalinks work like nginx/Apache.
 *
 *   PHP_CLI_SERVER_WORKERS=8 php -S localhost:8899 -t ~/wptest tools/router.php
 *
 * Note: the built-in server does not support HTTP Range requests, so <video>
 * playback of large local files stalls in some browsers. For visual checks,
 * point the hero / world-map video settings at a real host.
 */
$root = $_SERVER['DOCUMENT_ROOT'];
$path = urldecode( (string) parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) );

if ( '/' !== $path && file_exists( $root . $path ) ) {
	if ( is_dir( $root . $path ) ) {
		$index = rtrim( $path, '/' ) . '/index.php';
		if ( file_exists( $root . $index ) ) {
			$_SERVER['SCRIPT_NAME']     = $index;
			$_SERVER['SCRIPT_FILENAME'] = $root . $index;
			require $root . $index;
			return true;
		}
	} else {
		return false; // static file — let the server send it
	}
}

$_SERVER['SCRIPT_NAME']     = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = $root . '/index.php';
require $root . '/index.php';
