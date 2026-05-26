#!/bin/bash
# ─────────────────────────────────────────────
# Setup local git hooks for auto-sync to CRM
# Run once: bash setup-hooks.sh
# ─────────────────────────────────────────────

CRM_PATH="${1:-/c/xampp/htdocs/sales}"
HOOK_FILE=".git/hooks/post-commit"

cat > "$HOOK_FILE" << HOOK
#!/bin/bash
# Auto-sync modules to Concord CRM after every commit
CRM="$CRM_PATH"

echo ""
echo "🔄 Post-commit hook: syncing modules to CRM..."

cp -r ACP_Proposals      "\$CRM/modules/" && echo "  ✓ ACP_Proposals"
cp -r ACP_Sales_Guide    "\$CRM/modules/" && echo "  ✓ ACP_Sales_Guide"
cp -r AkashSalesPipeline "\$CRM/modules/" && echo "  ✓ AkashSalesPipeline"

# Clear CRM caches
cd "\$CRM"
php artisan route:clear 2>/dev/null
php artisan view:clear 2>/dev/null

echo "✅ CRM synced! Run 'npm run build' in CRM folder to rebuild assets."
echo ""
HOOK

chmod +x "$HOOK_FILE"
echo "✅ Post-commit hook installed!"
echo "   Every 'git commit' will now auto-sync modules to: $CRM_PATH"
