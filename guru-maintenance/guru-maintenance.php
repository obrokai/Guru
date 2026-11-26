<?php
/**
 * Plugin Name: Guru Maintenance Page
 * Description: While active, visitors see a "website updating" page (HTTP 503, so search engines come back later). Logged-in editors and admins see the real site. Deactivate to open the site.
 * Version:     1.1.0
 * Author:      GURU Online
 * Requires PHP: 7.4
 * License:     GPL-2.0-or-later
 * Text Domain: guru-maintenance
 *
 * Used for the launch of the new website (2026-11): turned on before the move, off once checked.
 * Only front-end page views are held back; wp-admin, wp-login, admin-ajax, cron and the REST API
 * keep working, so backups, migration transfers and the admin keep running.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Secret for the preview link, created once and stored in the database.
 *
 * @return string
 */
function guru_maintenance_preview_key() {
	$key = get_option( 'guru_maintenance_preview_key' );
	if ( ! $key ) {
		$key = wp_generate_password( 24, false );
		update_option( 'guru_maintenance_preview_key', $key, false );
	}
	return $key;
}

/**
 * Whether the current visitor may see the real site: logged-in editors, or anyone who opened
 * the preview link (…?guru_preview=<key>), which sets a cookie for 12 hours. Lets testers and the
 * client check the site without an admin account.
 *
 * @return bool
 */
function guru_maintenance_bypass() {
	if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
		return true;
	}
	$key = guru_maintenance_preview_key();
	if ( isset( $_GET['guru_preview'] ) && hash_equals( $key, (string) wp_unslash( $_GET['guru_preview'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		setcookie( 'guru_preview', $key, time() + 12 * HOUR_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true );
		return true;
	}
	return isset( $_COOKIE['guru_preview'] ) && hash_equals( $key, (string) wp_unslash( $_COOKIE['guru_preview'] ) );
}

/**
 * Serve the maintenance page instead of the front end.
 */
function guru_maintenance_page() {
	if ( guru_maintenance_bypass() || is_admin() || wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}

	status_header( 503 );
	header( 'Retry-After: 3600' );
	header( 'Content-Type: text/html; charset=utf-8' );
	nocache_headers();

	$name = get_bloginfo( 'name' );
	?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title><?php echo esc_html( $name ); ?> — Updating</title>
<style>
:root{--bg:#0b0f0c;--fg:#f4f6f3;--muted:#a8b1aa;--accent:#3ddc84}
*{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%}
body{background:var(--bg);color:var(--fg);font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"PingFang HK","Microsoft JhengHei",sans-serif;display:flex;align-items:center;justify-content:center;padding:24px 16px;text-align:center}
main{max-width:560px}
.mark{font-weight:800;letter-spacing:.08em;font-size:clamp(28px,6vw,40px)}
.mark span{color:var(--accent)}
h1{font-size:clamp(22px,4.5vw,30px);font-weight:600;margin:28px 0 12px;line-height:1.3}
p{color:var(--muted);font-size:16px;line-height:1.7}
.zh{margin-top:20px}
.bar{width:64px;height:3px;background:var(--accent);margin:28px auto 0;border-radius:2px}
</style>
</head>
<body>
<main>
	<div class="mark">GURU<span>.</span></div>
	<h1>We&rsquo;re updating our website</h1>
	<p>Our new website is on its way. Please check back shortly.</p>
	<p class="zh">網站更新中，請稍後再來。</p>
	<div class="bar" aria-hidden="true"></div>
</main>
</body>
</html>
	<?php
	exit;
}
add_action( 'template_redirect', 'guru_maintenance_page', 0 );

/**
 * Reminder in the admin bar for logged-in users, so nobody forgets the site is closed.
 *
 * @param WP_Admin_Bar $bar Admin bar.
 */
function guru_maintenance_admin_bar( $bar ) {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}
	$bar->add_node(
		array(
			'id'     => 'guru-maintenance-preview',
			'parent' => 'guru-maintenance',
			'title'  => 'Preview link for testers (12 hours)',
			'href'   => add_query_arg( 'guru_preview', guru_maintenance_preview_key(), home_url( '/' ) ),
		)
	);
	$bar->add_node(
		array(
			'id'    => 'guru-maintenance',
			'title' => '<span style="background:#d63638;color:#fff;padding:2px 8px;border-radius:3px">Maintenance ON — visitors see the updating page</span>',
			'href'  => admin_url( 'plugins.php' ),
		)
	);
}
add_action( 'admin_bar_menu', 'guru_maintenance_admin_bar', 100 );
