#!/bin/bash
# ─────────────────────────────────────────────────────────
# ACP Modules Installer for Concord CRM
# Usage: bash install.sh /path/to/your/crm
# ─────────────────────────────────────────────────────────

CRM_PATH="${1:-/c/xampp/htdocs/sales}"

if [ ! -d "$CRM_PATH/modules" ]; then
    echo "❌ ERROR: $CRM_PATH does not look like a Concord CRM installation."
    echo "   Usage: bash install.sh /path/to/crm"
    exit 1
fi

echo "📦 Installing modules to: $CRM_PATH"

# 1. Copy modules
echo "→ Copying modules..."
cp -r ACP_Proposals      "$CRM_PATH/modules/"
cp -r ACP_Sales_Guide    "$CRM_PATH/modules/"
cp -r AkashSalesPipeline "$CRM_PATH/modules/"

# 2. Enable in modules_statuses.json
echo "→ Enabling modules in modules_statuses.json..."
cd "$CRM_PATH"
# Add entries if not already present
node -e "
const fs = require('fs');
const f = 'modules_statuses.json';
const s = JSON.parse(fs.readFileSync(f));
s['ACP_Proposals'] = true;
s['ACP_Sales_Guide'] = true;
s['AkashSalesPipeline'] = true;
fs.writeFileSync(f, JSON.stringify(s, null, 4));
console.log('  ✓ modules_statuses.json updated');
"

# 3. Clear module caches
echo "→ Clearing module caches..."
rm -f bootstrap/cache/modules.php
rm -f bootstrap/cache/module_autoload.php
php artisan core:clear-cache

# 4. Run migrations
echo "→ Running migrations..."
php artisan migrate

# 5. Build frontend
echo "→ Building frontend..."
npm run build

# 6. Create template directory
echo "→ Creating template directory..."
mkdir -p storage/app/acp-proposals/templates
echo "  ✓ Place your 5 JPGs in: storage/app/acp-proposals/templates/"

echo ""
echo "✅ Installation complete!"
echo ""
echo "⚠️  IMPORTANT — Add these 3 lines to resources/js/app.js (before the CSS imports):"
echo "   import '@/AkashSalesPipeline/app.js'"
echo "   import '@/ACP_Sales_Guide/app.js'"
echo "   import '@/ACP_Proposals/app.js'"
echo ""
echo "   Then run: npm run build"
echo ""
echo "   Also fix vite.config.js alias regex:"
echo "   Change: [a-zA-Z]+"
echo "   To:     [a-zA-Z0-9_]+"
