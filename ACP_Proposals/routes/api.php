<?php

use Illuminate\Support\Facades\Route;
use Modules\ACP_Proposals\Http\Controllers\Api\ProposalController;
use Modules\ACP_Proposals\Http\Controllers\Api\ProposalPdfController;

Route::middleware('auth:sanctum')->prefix('acp-proposals')->group(function () {

    // Proposal CRUD
    Route::get('/',              [ProposalController::class, 'index']);
    Route::post('/',             [ProposalController::class, 'store']);
    Route::get('/defaults',      [ProposalController::class, 'defaults']);
    Route::get('/{proposal}',    [ProposalController::class, 'show']);
    Route::put('/{proposal}',    [ProposalController::class, 'update']);
    Route::delete('/{proposal}', [ProposalController::class, 'destroy']);

    // PDF generation
    Route::post('/{proposal}/generate-pdf', [ProposalPdfController::class, 'generate']);
    Route::get('/{proposal}/preview-pdf',   [ProposalPdfController::class, 'preview']);
});
