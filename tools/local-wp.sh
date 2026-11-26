#!/bin/zsh
# Build a disposable local WordPress (SQLite, no MySQL) and install this theme into it.
#
#   tools/local-wp.sh            # creates ~/wptest, installs WP + theme, runs the importer
#   tools/local-wp.sh deploy     # copies the theme again after edits
#   tools/local-wp.sh serve      # starts the dev server on http://localhost:8899
#
# The site's credentials are meaningless (admin / localpass) — it never leaves this machine.
set -e
W=${WPTEST:-$HOME/wptest}
REPO=$(cd "$(dirname "$0")/.." && pwd)
TMP=$(mktemp -d)

deploy() {
  rm -rf "$W/wp-content/themes/guru-theme"
  cp -R "$REPO/guru-theme" "$W/wp-content/themes/"
  find "$W/wp-content/themes/guru-theme" -name '.DS_Store' -delete
  echo "theme deployed to $W/wp-content/themes/guru-theme"
}

serve() {
  echo "serving http://localhost:8899 (Ctrl-C to stop)"
  PHP_CLI_SERVER_WORKERS=8 php -S localhost:8899 -t "$W" "$REPO/tools/router.php"
}

case "${1:-create}" in
  deploy) deploy ;;
  serve)  serve ;;
  create)
    rm -rf "$W"; mkdir -p "$W"
    curl -sSL -o "$TMP/wp.zip" https://wordpress.org/wordpress-7.1.zip
    unzip -q "$TMP/wp.zip" -d "$TMP" && cp -R "$TMP/wordpress/." "$W/"
    curl -sSL -o "$TMP/sqlite.zip" https://downloads.wordpress.org/plugin/sqlite-database-integration.zip
    unzip -q "$TMP/sqlite.zip" -d "$W/wp-content/plugins/"
    P="$W/wp-content/plugins/sqlite-database-integration"
    cp "$P/db.copy" "$W/wp-content/db.php"
    sed -i '' "s#{SQLITE_IMPLEMENTATION_FOLDER_PATH}#$P#; s#{SQLITE_PLUGIN}#sqlite-database-integration/load.php#" "$W/wp-content/db.php"
    cat > "$W/wp-config.php" <<'EOF'
<?php
define( 'DB_NAME', 'wp' ); define( 'DB_USER', '' ); define( 'DB_PASSWORD', '' ); define( 'DB_HOST', 'localhost' );
define( 'DB_CHARSET', 'utf8mb4' ); define( 'DB_COLLATE', '' );
$table_prefix = 'wp_';
define( 'WP_DEBUG', true ); define( 'WP_DEBUG_LOG', true ); define( 'WP_DEBUG_DISPLAY', false );
define( 'WP_HOME', 'http://localhost:8899' ); define( 'WP_SITEURL', 'http://localhost:8899' );
define( 'FS_METHOD', 'direct' ); define( 'DISABLE_WP_CRON', true );
define( 'AUTH_KEY', 'local-1' ); define( 'SECURE_AUTH_KEY', 'local-2' ); define( 'LOGGED_IN_KEY', 'local-3' ); define( 'NONCE_KEY', 'local-4' );
define( 'AUTH_SALT', 'local-5' ); define( 'SECURE_AUTH_SALT', 'local-6' ); define( 'LOGGED_IN_SALT', 'local-7' ); define( 'NONCE_SALT', 'local-8' );
if ( ! defined( 'ABSPATH' ) ) { define( 'ABSPATH', __DIR__ . '/' ); }
require_once ABSPATH . 'wp-settings.php';
EOF
    php -r 'define("WP_INSTALLING", true); require "'"$W"'/wp-load.php"; require ABSPATH . "wp-admin/includes/upgrade.php"; wp_install("Guru Local Test", "admin", "admin@example.com", true, "", "localpass"); echo "WordPress installed\n";'
    deploy
    # switch_theme() and the importer must run in separate PHP processes (functions.php is not reloaded).
    php -r 'require "'"$W"'/wp-load.php"; switch_theme("guru-theme"); update_option("permalink_structure", "/%postname%/"); echo "theme active\n";'
    php -r 'require "'"$W"'/wp-load.php"; wp_set_current_user(1); echo guru_import_content(), "\n", guru_import_pages(), "\n"; for ($i = 0; $i < 8; $i++) { $m = guru_import_logos(); echo $m, "\n"; if (false === strpos($m, "still to go")) break; } flush_rewrite_rules(); print_r(guru_importer_status());'
    echo; echo "Done. Run: tools/local-wp.sh serve   then open http://localhost:8899 (admin / localpass)"
    ;;
esac
