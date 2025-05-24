<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ConferenceController;
use App\Http\Controllers\Api\PublicConferenceController;
use App\Http\Controllers\Api\ConferenceEditorController;
use App\Http\Controllers\Api\UniversityController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PageMenuController;
use App\Http\Controllers\Api\PageDataController;
use App\Http\Controllers\Api\ConferenceLockController;
use App\Http\Controllers\Api\PublicPageMenuController;

Route::prefix('v1')->group(function () {
    // Public routes for universities
    Route::apiResource('universities', UniversityController::class)->only(['index', 'show']);
    
    // Public routes for conferences and pages
    Route::prefix('public')->group(function() {
        Route::get('/conferences/decades', [PublicConferenceController::class, 'getDecades']);
        Route::get('/conferences/decades/{decade}', [PublicConferenceController::class, 'getByDecade']);
        
        // New routes for latest conference
        Route::get('/conferences/latest', [PublicConferenceController::class, 'latest']);
        Route::get('/conferences/latest/with-pages', [PublicConferenceController::class, 'latestWithPages']);
        
        Route::get('/conferences/{conferenceSlug}', [PublicConferenceController::class, 'show']);
        Route::get('/conferences', [PublicConferenceController::class, 'index']);
    
        
        // Public routes for page menus
        Route::get('/conferences/{conferenceSlug}/pages', [PublicPageMenuController::class, 'index']);
        Route::get('/conferences/{conferenceSlug}/pages/{pageSlug}', [PublicPageMenuController::class, 'show']);
    });

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
        
        // Protected routes requiring password change
        Route::middleware('ensure.password.changed')->group(function () {
            // For retrieving attached conferences to a user
            Route::get('/conferences/my', [ConferenceController::class, 'myConferences'])->name('conferences.my');
            Route::get('/conferences/latest', [ConferenceController::class, 'latest']);
            Route::patch('/conferences/{conference}/status', [ConferenceController::class, 'updateStatus']);
            Route::apiResource('conferences', ConferenceController::class);
           

            // Conference lock management
            Route::prefix('conferences/{conference}/lock')->group(function () {
                Route::get('/', [ConferenceLockController::class, 'checkLock']);
                Route::post('/', [ConferenceLockController::class, 'acquireLock']);
                Route::delete('/', [ConferenceLockController::class, 'releaseLock']);
                Route::post('/refresh', [ConferenceLockController::class, 'refreshLock']);
            });
            
            // Admin-only routes
            Route::middleware('permission:access.admin')->group(function () {
                Route::get('/users', [UserController::class, 'index']);
                
                // University management for super admins
                Route::middleware('role:super_admin')->group(function () {
                    Route::apiResource('universities', UniversityController::class)->except(['index', 'show']);
                });

                // Conference editors management
                Route::middleware('check.conference.lock')->group(function () {
                    Route::get('/conferences/{conference}/editors/unattached', [ConferenceEditorController::class, 'unattached']);
                    Route::get('/conferences/{conference}/editors', [ConferenceEditorController::class, 'index']);
                    Route::post('/conferences/{conference}/editors', [ConferenceEditorController::class, 'store']);
                    Route::delete('/conferences/{conference}/editors/{editor}', [ConferenceEditorController::class, 'destroy']);
                });

                // Page Menu & Page Data API routes
                Route::middleware('check.conference.lock')->group(function () {
                    // Page Menu routes 
                    Route::apiResource('conferences.menus', PageMenuController::class);
                    
                    // Page Menu ordering routes (new arrow-based approach)
                    Route::patch('conferences/{conference}/menus/{menu}/move-up', [PageMenuController::class, 'moveUp']);
                    Route::patch('conferences/{conference}/menus/{menu}/move-down', [PageMenuController::class, 'moveDown']);
                    
                    // Page Menu position update route (legacy - kept for compatibility)
                    Route::patch('conferences/{conference}/menus/{menu}/position', [PageMenuController::class, 'updatePosition']);

                    // Page Data routes
                    Route::apiResource('conferences.menus.data', PageDataController::class);
                    
                    // Page Data ordering routes (new arrow-based approach)
                    Route::patch('conferences/{conference}/menus/{menu}/data/{data}/move-up', [PageDataController::class, 'moveUp']);
                    Route::patch('conferences/{conference}/menus/{menu}/data/{data}/move-down', [PageDataController::class, 'moveDown']);
                    
                    // Page Data position update route (legacy - kept for compatibility)
                    Route::patch('conferences/{conference}/menus/{menu}/data/{data}/position', [PageDataController::class, 'updatePosition']);
                });

                // User management
                Route::get('/users', [UserController::class, 'index']);
                Route::post('/users', [UserController::class, 'store']);
                Route::put('/users/{user}', [UserController::class, 'update']);
                Route::delete('/users/{user}', [UserController::class, 'destroy']);

                Route::get('/roles', [RoleController::class, 'index']);
                Route::get('/roles/available', [RoleController::class, 'availableRoles']);
            });
        });
    });
});