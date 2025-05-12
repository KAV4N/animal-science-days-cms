<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;

use App\Http\Controllers\Api\ConferenceController;
use App\Http\Controllers\Api\ConferenceEditorController;
use App\Http\Controllers\Api\UniversityController;

Route::prefix('v1')->group(function () {
    // Public routes for universities and conferences
    Route::apiResource('universities', UniversityController::class)->only(['index', 'show']);

    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::middleware('ensure.must_change_password')->post('/change-password', [AuthController::class, 'changePassword']);
        });
    });

    // Routes for authenticated users
    Route::middleware('auth:sanctum')->group(function () {


        // User-specific route outside of password change middleware
        Route::get('/users/me', [UserController::class, 'current']);
        
        // For retrieving attached conferences to a user
        Route::get('/conferences/my', [ConferenceController::class, 'myConferences'])->name('conferences.my');

        // Protected routes requiring password change
        Route::middleware('ensure.password.changed')->group(function () {
            // Admin-only routes
            Route::middleware('permission:access.admin')->group(function () {
                Route::get('/users', [UserController::class, 'index']);
                
                // University management for super admins
                Route::middleware('role:super_admin')->group(function () {
                    Route::apiResource('universities', UniversityController::class)->except(['index', 'show']);
                });

                // Conference management
                Route::get('/conferences/latest', [ConferenceController::class, 'latest']);
                Route::apiResource('conferences', ConferenceController::class)->only(['index', 'show']);

                Route::apiResource('conferences', ConferenceController::class)->except(['index', 'show']);
                
                // Conference editors management
                Route::get('/conferences/{conference}/editors', [ConferenceEditorController::class, 'index']);
                Route::get('/conferences/{conference}/editors/unattached', [ConferenceEditorController::class, 'unattached']);
                Route::post('/conferences/{conference}/editors', [ConferenceEditorController::class, 'store']);
                Route::delete('/conferences/{conference}/editors/{editor}', [ConferenceEditorController::class, 'destroy']);
            });
        });
    });
});