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

    // ── Enabled: run migrations + publish pre-built JS to public/ ─
    ->enabled(function (Application $app) {

        // 1. Run database migrations
        $app->make(\Illuminate\Database\Migrations\Migrator::class)
            ->run([__DIR__ . '/../database/migrations']);

        // 2. Copy pre-built IIFE JS → public/modules/acpproposals/
        //    This allows the ServiceProvider to inject it via <script> tag
        //    without needing "npm run build" on the server.
        $src  = __DIR__ . '/../dist/acpproposals.iife.js';
        $dest = public_path('modules/acpproposals/acpproposals.iife.js');

        if (file_exists($src)) {
            if (! is_dir(dirname($dest))) {
                mkdir(dirname($dest), 0755, true);
            }
            copy($src, $dest);
        }
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

        // 3. Remove pre-built JS from public/
        acpproposals_delete_dir(public_path('modules/acpproposals'));

        // 4. Wipe all generated PDFs and design-set background images
        acpproposals_delete_dir(storage_path('app/public/acp-proposals'));
    });
