#!/bin/bash
# =============================================================
# InstaShop — one-shot dev environment starter (user-space)
# Usage: bash scripts/dev-start.sh
# =============================================================
set -e

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
export PATH="$HOME/.local/bin:$HOME/.local/opt/mysql/bin:$PATH"

echo "── [1/3] MySQL ───────────────────────────────"
bash "$HOME/.local/start-mysql.sh"

echo "── [2/3] Migrations (if pending) ─────────────"
cd "$APP_DIR"
php artisan migrate --force || true

echo "── [3/3] Laravel dev server :8000 ────────────"
# stop previous instance if any
if [ -f /tmp/instashop-serve.pid ] && kill -0 "$(cat /tmp/instashop-serve.pid)" 2>/dev/null; then
  kill "$(cat /tmp/instashop-serve.pid)" || true
  sleep 1
fi
setsid nohup php artisan serve --host=0.0.0.0 --port=8000 > "$APP_DIR/serve.log" 2>&1 &
echo $! > /tmp/instashop-serve.pid
sleep 3

if curl -sf -o /dev/null http://127.0.0.1:8000; then
  echo "✅ InstaShop is UP → http://127.0.0.1:8000  (log: $APP_DIR/serve.log)"
else
  echo "❌ Server did not respond — check $APP_DIR/serve.log"
  exit 1
fi
