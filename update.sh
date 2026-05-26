#!/bin/bash
# ═══════════════════════════════════════════════════════════════
#  ACP_Proposals — Update Script (pull latest from GitHub)
#  Usage:  bash update.sh [/path/to/concordcrm]
#  Run from the cloned GitHub repo directory.
# ═══════════════════════════════════════════════════════════════

set -e

REPO_DIR="$(cd "$(dirname "$0")" && pwd)"
CRM="${1:-}"

if [ -z "$CRM" ]; then
  echo "Usage: bash update.sh /path/to/concordcrm"
  exit 1
fi

if [ ! -f "$CRM/artisan" ]; then
  echo "❌  Concord CRM not found at: $CRM"
  exit 1
fi

echo ""
echo "══════════════════════════════════════════════"
echo "  Updating ACP_Proposals Module"
echo "══════════════════════════════════════════════"

# ── 1. Pull latest code ───────────────────────────────────────
echo "▶ Pulling latest from GitHub..."
cd "$REPO_DIR"
git pull origin main
echo "  ✓ Code updated"

# ── 2. Copy module to CRM ─────────────────────────────────────
echo "▶ Syncing module files..."
cp -r "$REPO_DIR/ACP_Proposals/." "$CRM/modules/ACP_Proposals/"
echo "  ✓ Module synced"

cd "$CRM"

# ── 3. Run any new migrations ────────────────────────────────
echo "▶ Running migrations..."
php artisan migrate --force
echo "  ✓ Migrations done"

# ── 4. Clear caches ───────────────────────────────────────────
echo "▶ Clearing caches..."
php artisan optimize:clear
php artisan core:clear-cache 2>/dev/null || true
php artisan view:clear
echo "  ✓ Caches cleared"

# ── 5. Rebuild assets ────────────────────────────────────────
echo "▶ Rebuilding frontend assets..."
if command -v npm &>/dev/null; then
  npm run build
  echo "  ✓ Assets built"
else
  echo "  ⚠  npm not found — run 'npm run build' manually"
fi

echo ""
echo "══════════════════════════════════════════════"
echo "  ✅  Update complete!"
echo "══════════════════════════════════════════════"
echo ""
