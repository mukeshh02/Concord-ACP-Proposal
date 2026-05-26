#!/bin/bash
# ═══════════════════════════════════════════════════════════════
#  ACP_Proposals — Production Install Script
#  Usage:  bash install.sh [/path/to/concordcrm]
#  Example: bash install.sh /var/www/sales
# ═══════════════════════════════════════════════════════════════

set -e

CRM="${1:-$(pwd)}"
MODULE_SRC="$(cd "$(dirname "$0")/ACP_Proposals" && pwd)"

# ── Validate paths ────────────────────────────────────────────
if [ ! -f "$CRM/artisan" ]; then
  echo "❌  Concord CRM not found at: $CRM"
  echo "    Run: bash install.sh /correct/path/to/crm"
  exit 1
fi

if [ ! -d "$MODULE_SRC" ]; then
  echo "❌  ACP_Proposals module folder not found at: $MODULE_SRC"
  exit 1
fi

echo ""
echo "══════════════════════════════════════════════"
echo "  Installing ACP_Proposals → $CRM/modules/"
echo "══════════════════════════════════════════════"

# ── 1. Copy module ────────────────────────────────────────────
echo "▶ Copying module files..."
cp -r "$MODULE_SRC" "$CRM/modules/"
echo "  ✓ Module copied"

cd "$CRM"

# ── 2. Run migrations ─────────────────────────────────────────
echo "▶ Running migrations..."
php artisan migrate --force
echo "  ✓ Migrations done"

# ── 3. Clear all caches ───────────────────────────────────────
echo "▶ Clearing caches..."
php artisan optimize:clear
php artisan core:clear-cache 2>/dev/null || true
echo "  ✓ Caches cleared"

# ── 4. Storage symlink ────────────────────────────────────────
echo "▶ Setting up storage link..."
if [ ! -L "public/storage" ]; then
  php artisan storage:link
  echo "  ✓ Storage linked"
else
  echo "  ✓ Storage link already exists"
fi

# ── 5. Create template directory ─────────────────────────────
echo "▶ Creating template image directory..."
mkdir -p "storage/app/acp-proposals/templates"
chmod 775 "storage/app/acp-proposals/templates"
echo "  ✓ Directory ready: storage/app/acp-proposals/templates/"

# ── 6. Build assets ───────────────────────────────────────────
echo "▶ Building frontend assets..."
if command -v npm &>/dev/null; then
  npm run build
  echo "  ✓ Assets built"
else
  echo "  ⚠  npm not found — run 'npm run build' manually"
fi

echo ""
echo "══════════════════════════════════════════════"
echo "  ✅  ACP_Proposals installed successfully!"
echo "══════════════════════════════════════════════"
echo ""
echo "  Next steps:"
echo "  1. Log in to Concord CRM admin"
echo "  2. Settings → Roles → assign 'View & Manage Proposals'"
echo "  3. Go to /acp-proposals → Manage Images → upload 5 page backgrounds"
echo "  4. Create your first proposal!"
echo ""
