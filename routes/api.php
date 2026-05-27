<?php

use Illuminate\Support\Facades\Route;
use Modules\ACP_Proposals\Http\Controllers\Api\ProposalController;
use Modules\ACP_Proposals\Http\Controllers\Api\ProposalPdfController;
use Modules\ACP_Proposals\Http\Controllers\Api\ProposalSetController;
use Modules\ACP_Proposals\Http\Controllers\Api\TemplateImageController;

Route::middleware('auth:sanctum')->prefix('acp-proposals')->group(function () {

    // ── Design Sets (MUST come BEFORE /{proposal} wildcard) ──────────
    Route::get('/sets',                                [ProposalSetController::class, 'index']);
    Route::post('/sets',                               [ProposalSetController::class, 'store']);
    Route::put('/sets/{set}',                          [ProposalSetController::class, 'update']);
    Route::delete('/sets/{set}',                       [ProposalSetController::class, 'destroy']);
    Route::post('/sets/{set}/upload/{page}',           [ProposalSetController::class, 'upload']);
    Route::delete('/sets/{set}/pages/{page}',          [ProposalSetController::class, 'deletePage']);
    Route::put('/sets/{set}/layout',                   [ProposalSetController::class, 'updateLayout']);
    Route::put('/sets/{set}/page-settings',            [ProposalSetController::class, 'updatePageSettings']);
    Route::put('/sets/{set}/reorder',                  [ProposalSetController::class, 'reorder']);

    // Template background images (must come before /{proposal} wildcard)
    Route::get('/templates/status',       [TemplateImageController::class, 'status']);
    Route::post('/templates/{page}',      [TemplateImageController::class, 'upload']);
    Route::delete('/templates/{page}',    [TemplateImageController::class, 'delete']);

    // Proposal CRUD
    Route::get('/',              [ProposalController::class, 'index']);
    Route::post('/',             [ProposalController::class, 'store']);
    Route::get('/defaults',      [ProposalController::class, 'defaults']);

    // PDF generation (must come before /{proposal} wildcard)
    Route::post('/{proposal}/generate-pdf', [ProposalPdfController::class, 'generate']);
    Route::get('/{proposal}/preview-pdf',   [ProposalPdfController::class, 'preview']);

    // Proposal CRUD with wildcard (LAST — these match anything)
    Route::get('/{proposal}',    [ProposalController::class, 'show']);
    Route::put('/{proposal}',    [ProposalController::class, 'update']);
    Route::delete('/{proposal}', [ProposalController::class, 'destroy']);
});
