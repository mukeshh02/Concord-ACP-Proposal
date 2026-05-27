<?php

use Modules\Core\Facades\Module;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Schema;

/**
 * Recursively delete a directory and all its contents.
 * Prefixed to avoid collisions with other modules.
 */
if (! function_exists('acpproposals_delete_dir')) {
    function acpproposals_delete_dir(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            $item->isDir() ? @rmdir($item->getRealPath()) : @unlink($item->getRealPath());
        }
        @rmdir($dir);
    }
}

return Module::configure('acpproposals')

    // ── Enabled: run all module migrations ───────────────────────
    ->enabled(function (Application $app) {
        $app->make(\Illuminate\Database\Migrations\Migrator::class)
            ->run([__DIR__ . '/../database/migrations']);
    })

    // ── Disabled: nothing to do (routes/menus auto-removed) ──────
    ->disabled(function (Application $app) {
        //
    })

    // ── Deleted: drop all tables + wipe all module storage ───────
    ->deleted(function (Application $app) {

        // 1. Drop FK constraint before dropping parent table
        if (Schema::hasTable('acp_proposals') && Schema::hasColumn('acp_proposals', 'set_id')) {
            Schema::table('acp_proposals', function ($table) {
                try { $table->dropForeign(['set_id']); } catch (\Throwable $e) { /* already gone */ }
            });
        }

        // 2. Drop module tables (proposals first, then sets)
        Schema::dropIfExists('acp_proposals');
        Schema::dropIfExists('acp_proposal_sets');

        // 3. Wipe all generated PDFs and design-set background images
        acpproposals_delete_dir(storage_path('app/public/acp-proposals'));
    });
