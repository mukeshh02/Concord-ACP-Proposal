<?php

use Illuminate\Support\Facades\Route;

// SPA — Vue Router handles all frontend routing.
// Requires auth so the core::app view has a valid user.
Route::middleware('auth')->group(function () {
    Route::get('/acp-proposals/{any?}', function () {
        return view('acpproposals::index');
    })->where('any', '.*');
});
