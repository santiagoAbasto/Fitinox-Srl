<?php

use App\Http\Controllers\Api\LegacyIntegrationController;
use App\Http\Controllers\Api\OpenApiDocumentationController;
use Illuminate\Support\Facades\Route;

Route::get('/openapi.json', [OpenApiDocumentationController::class, 'spec'])
    ->middleware('throttle:30,1')
    ->name('integration.openapi');

Route::prefix('v1/integration')
    ->middleware(['integration.token', 'throttle:120,1'])
    ->group(function () {
        Route::get('/health', [LegacyIntegrationController::class, 'health']);
        Route::get('/articles', [LegacyIntegrationController::class, 'articles']);
        Route::post('/articles/upsert', [LegacyIntegrationController::class, 'upsertArticles']);
        Route::get('/clients', [LegacyIntegrationController::class, 'clients']);
        Route::post('/clients/upsert', [LegacyIntegrationController::class, 'upsertClients']);
        Route::get('/events', [LegacyIntegrationController::class, 'events']);
        Route::post('/events/acknowledge', [LegacyIntegrationController::class, 'acknowledgeEvents']);
        Route::patch('/orders/{clientOrder:number}', [LegacyIntegrationController::class, 'updateOrder']);
    });
