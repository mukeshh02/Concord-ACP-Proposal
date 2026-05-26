<?php

use Modules\Core\Facades\Module;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Schema;

return Module::configure('acpproposals')

    ->enabled(function (Application $app) {
        // Run migrations when module is enabled
        $app->make(\Illuminate\Database\Migrations\Migrator::class)
            ->run([__DIR__ . '/../database/migrations']);
    })

    ->disabled(function (Application $app) {
        // Nothing to deactivate — routes/menus are removed automatically
    })

    ->deleted(function (Application $app) {
        // Drop table on module deletion
        Schema::dropIfExists('acp_proposals');

        // Remove generated PDFs
        $pdfDir = storage_path('app/public/acp-proposals');
        if (is_dir($pdfDir)) {
            array_map('unlink', glob("{$pdfDir}/*.pdf") ?: []);
            @rmdir($pdfDir);
        }

        // Remove template background images
        $tplDir = storage_path('app/acp-proposals/templates');
        if (is_dir($tplDir)) {
            array_map('unlink', glob("{$tplDir}/*.jpg") ?: []);
            array_map('unlink', glob("{$tplDir}/*.png") ?: []);
            @rmdir($tplDir);
            @rmdir(storage_path('app/acp-proposals'));
        }
    });
