<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CompetitionController;
use App\Http\Controllers\Api\V1\ConfigurationController;
use App\Http\Controllers\Api\V1\OrganizationController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\TrackController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - v1
|--------------------------------------------------------------------------
|
| Tutti gli endpoint sono protetti dal middleware api.secret (header secret-key).
| Gli endpoint autenticati richiedono anche Bearer Token (auth:sanctum).
|
*/

Route::prefix('v1')->middleware(['api.secret', 'api.locale'])->group(function () {

    // ==================== CONFIGURAZIONE ====================
    Route::get('/configuration', ConfigurationController::class);

    // ==================== GARE PUBBLICHE (no auth, solo secret-key) ====================
    Route::get('/competition-abstract/{id}', [CompetitionController::class, 'publicAbstract']);

    // ==================== AUTENTICAZIONE ====================
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/signup', [AuthController::class, 'signup']);

    // ==================== ENDPOINT AUTENTICATI ====================
    Route::middleware('auth:sanctum')->group(function () {

        // ----- Profilo -----
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::get('/profile/detail', [ProfileController::class, 'detail']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::post('/profile', [ProfileController::class, 'update']); // POST alternativo per multipart/form-data
        Route::get('/profile/competitions', [ProfileController::class, 'competitions']);

        // ----- Gare -----
        Route::get('/competitions', [CompetitionController::class, 'index']);
        Route::get('/competition/{id}', [CompetitionController::class, 'show']);
        Route::get('/competition/{id}/partners', [CompetitionController::class, 'partners']);
        Route::post('/competition/subscribe', [CompetitionController::class, 'subscribe']);
        Route::post('/competition/unsubscribe/{id}', [CompetitionController::class, 'unsubscribe']);

        // ----- Tracce -----
        Route::get('/tracks', [TrackController::class, 'index']);
        Route::post('/track_upload', [TrackController::class, 'upload']);
        Route::get('/track/{id}', [TrackController::class, 'show']);
        Route::delete('/track/{id}', [TrackController::class, 'destroy']);

        // ----- Enti -----
        Route::get('/organizations', [OrganizationController::class, 'index']);
        Route::get('/organization/{id}', [OrganizationController::class, 'show']);
        Route::post('/organization/{id}', [OrganizationController::class, 'subscribe']);
        Route::put('/current-organization/{id}', [OrganizationController::class, 'setCurrent']);
    });
});
