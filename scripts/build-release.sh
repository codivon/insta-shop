#!/bin/bash
# =============================================================
# InstaShop — build the shared-host deployment package
#
# Produces a zip that contains EVERYTHING the host needs,
# including a production `vendor/` (--no-dev), so no composer,
# SSH or exec() is required on the host:
#
#   bash scripts/build-release.sh [version]     e.g. v0.7.0
#
# Output: ../instashop-dist/instashop-<version>-host.zip
# =============================================================
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
VERSION="${1:-v0.7.0}"
OUT_DIR="$APP_DIR/../instashop-dist"
OUT_FILE="$OUT_DIR/instashop-$VERSION-host.zip"

export PATH="$HOME/.local/bin:$HOME/.local/opt/mysql/bin:$PATH"
export LD_LIBRARY_PATH="$HOME/.local/lib${LD_LIBRARY_PATH:+:$LD_LIBRARY_PATH}"

[ -f "$HOME/.local/bin/composer" ] || { echo "❌ composer not found"; exit 1; }

echo "── [1/5] Exporting clean tree from git (HEAD) ──"
TMP="$(mktemp -d /tmp/instashop-pkg.XXXXXX)"
trap 'rm -rf "$TMP"' EXIT
git -C "$APP_DIR" archive --format=tar HEAD | tar -xf - -C "$TMP"
COMMIT="$(git -C "$APP_DIR" rev-parse --short HEAD)"

cat > "$TMP/RELEASE.txt" <<EOF
InstaShop $VERSION — host deployment package
Built: $(date '+%Y-%m-%d %H:%M %Z') | Commit: $COMMIT
Vendor: production only (composer install --no-dev --optimize-autoloader)
Install: open https://your-domain/ and the /install wizard will start.
Docs: docs/host-install-fa.md
EOF

echo "── [2/5] composer install --no-dev (production vendor) ──"
cd "$TMP"
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --quiet 2>&1 | tail -3 || true

echo "── [3/5] Sanity checks ──"
[ -f "$TMP/vendor/autoload.php" ]            || { echo "❌ vendor/autoload.php missing"; exit 1; }
[ -f "$TMP/public/build/manifest.json" ]     || { echo "❌ public/build/manifest.json missing"; exit 1; }
[ -f "$TMP/public/index.php" ]               || { echo "❌ public/index.php missing"; exit 1; }
[ -f "$TMP/.htaccess" ]                      || { echo "❌ root .htaccess missing"; exit 1; }
[ ! -e "$TMP/.env" ]                         || { echo "❌ .env must NOT ship in the package"; exit 1; }
# path-repo symlink must be dereferenced into real files inside the zip
[ -d "$TMP/vendor/instashop/installer/src" ] || { echo "❌ vendor/instashop/installer missing"; exit 1; }
chmod -R u+rwX,go+rX "$TMP/storage" "$TMP/bootstrap/cache" 2>/dev/null || true

echo "── [4/5] Packing zip ──"
mkdir -p "$OUT_DIR"
rm -f "$OUT_FILE"
( cd "$TMP" && zip -qr9 "$OUT_FILE" . )

echo "── [5/5] Result ──"
SIZE=$(du -h "$OUT_FILE" | cut -f1)
SHA=$(sha256sum "$OUT_FILE" | cut -d' ' -f1)
FILES=$(unzip -l "$OUT_FILE" | tail -1 | awk '{print $2}')
echo "📦 $OUT_FILE"
echo "   size: $SIZE | files: $FILES"
echo "   sha256: $SHA"
