#!/bin/zsh
# Build the client-facing theme packages.
#
#   tools/package.sh            -> guru-theme-v<version>-<YYYYMMDD>.zip  (+ -slim)
#   tools/package.sh --date 20260911   -> use that date instead of today
#
# The version comes from the "Version:" header in guru-theme/style.css, so bump
# it there (and GURU_VERSION in functions.php) before packaging.
#
# The folder inside the zip is always "guru-theme": WordPress identifies a theme
# by that folder name, so uploading a new package offers to replace the installed
# theme instead of creating a second copy.
set -e

REPO=$(cd "$(dirname "$0")/.." && pwd)
cd "$REPO"

VERSION=$(sed -n 's/^Version:[[:space:]]*//p' guru-theme/style.css | head -1 | tr -d '[:space:]')
if [[ -z "$VERSION" ]]; then
  echo "Could not read Version: from guru-theme/style.css" >&2
  exit 1
fi

DATE=${2:-$(date +%Y%m%d)}
[[ "$1" == "--date" && -n "$2" ]] && DATE=$2

FULL="guru-theme-v${VERSION}-${DATE}.zip"
SLIM="guru-theme-v${VERSION}-${DATE}-slim.zip"

find guru-theme -name '.DS_Store' -delete
rm -f "$FULL" "$SLIM"

zip -rq "$FULL" guru-theme
zip -rq "$SLIM" guru-theme \
  -x 'guru-theme/assets/media/hero-bg.mp4' \
     'guru-theme/assets/media/hero-bg-mobile.mp4'

# Sanity checks: the folder name must be right and the slim package must have no videos.
unzip -l "$FULL" | grep -q ' guru-theme/style.css$' || { echo "unexpected folder name inside $FULL" >&2; exit 1; }
unzip -l "$SLIM" | grep -qE 'hero-bg|\.mp4$' && { echo "slim package still contains videos" >&2; exit 1; }

ls -lh "$FULL" "$SLIM" | awk '{print $5, $9}'
echo "theme version $VERSION ($(unzip -l "$FULL" | grep -c '\.php$') php files)"

# Keep a copy at the repo root of the main checkout for easy upload.
if [[ "$REPO" != "/Users/mike/Guru" && -d /Users/mike/Guru ]]; then
  cp "$FULL" "$SLIM" /Users/mike/Guru/
  echo "copied to /Users/mike/Guru/"
fi
