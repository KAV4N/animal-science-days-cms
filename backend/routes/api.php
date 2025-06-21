<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ConferenceController;
use App\Http\Controllers\Api\PublicConferenceController;
use App\Http\Controllers\Api\PreviewConferenceController;
use App\Http\Controllers\Api\ConferenceEditorController;
use App\Http\Controllers\Api\UniversityController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PageMenuController;
use App\Http\Controllers\Api\PageDataController;
use App\Http\Controllers\Api\ConferenceLockController;
use App\Http\Controllers\Api\PublicPageMenuController;
use App\Http\Controllers\Api\MediaController;


Route::prefix('v1')->group(function () {
    // Public routes for universities
    Route::get('universities', [UniversityController::class, 'index']);
    Route::get('universities/{university}', [UniversityController::class, 'show']);

    // Public routes for conferences and pages
    Route::get('/conferences/decades', [PublicConferenceController::class, 'getDecades']);
    Route::get('/conferences/decades/{decade}', [PublicConferenceController::class, 'getByDecade']);

    // Latest conference routes
    Route::get('/conferences', [PublicConferenceController::class, 'index']);
    Route::get('/conferences/{conferenceSlug}', [PublicConferenceController::class, 'show']);

    // Public routes for page menus
    Route::get('/conferences/{conferenceSlug}/pages', [PublicPageMenuController::class, 'index']);

    // Public route for specific page with its data components
    Route::get('/conferences/{conferenceSlug}/pages/{pageSlug}', [PublicPageMenuController::class, 'show']);

    // Preview routes
    Route::middleware('auth:sanctum')->prefix('preview')->group(function () {
        Route::get('/conferences/{conferenceSlug}', [PreviewConferenceController::class, 'show']);
        Route::get('/conferences/{conferenceSlug}/pages', [PreviewConferenceController::class, 'pages']);
        Route::get('/conferences/{conferenceSlug}/pages/{pageSlug}', [PreviewConferenceController::class, 'page']);
    });

    Route::get('/conferences/{conference}/media/{mediaId}/serve', [MediaController::class, 'serve'])
        ->name('api.media.serve');
    Route::get('/conferences/{conference}/media/{mediaId}/download', [MediaController::class, 'download'])
        ->name('api.media.download');


    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/refresh', [AuthController::class, 'refresh']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::middleware('ensure.must_change_password')->post('/change-password', [AuthController::class, 'changePassword']);
        });
    });

    // Routes for authenticated users
    Route::middleware('auth:sanctum')->group(function () {
        // User-specific route
        Route::get('/user-management/users/me', [UserController::class, 'current']);

        // Protected routes requiring password change
        Route::middleware('ensure.password.changed')->group(function () {
            Route::prefix('conference-management')->group(function () {
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

                // Media management routes
                Route::prefix('conferences/{conference}/media')->group(function () {
                    Route::get('/', [MediaController::class, 'index']);
                    Route::post('/', [MediaController::class, 'store'])
                        ->middleware('check.conference.lock');
                    Route::get('/{media}', [MediaController::class, 'show']);
                    Route::put('/{media}', [MediaController::class, 'update'])
                        ->middleware('check.conference.lock');
                    Route::patch('/{media}', [MediaController::class, 'update'])
                        ->middleware('check.conference.lock');
                    Route::delete('/{media}', [MediaController::class, 'destroy'])
                        ->middleware('check.conference.lock');
                });

                // Admin-only routes
                Route::middleware('permission:access.admin')->group(function () {
                    Route::get('/conferences/{conference}/editors/unattached', [ConferenceEditorController::class, 'unattached']);
                    Route::get('/conferences/{conference}/editors', [ConferenceEditorController::class, 'index']);
                    Route::post('/conferences/{conference}/editors', [ConferenceEditorController::class, 'store']);
                    Route::delete('/conferences/{conference}/editors/{editor}', [ConferenceEditorController::class, 'destroy']);

                    
                });

                Route::middleware('check.conference.lock')->group(function () {
                    // Page Menu routes
                    Route::apiResource('conferences.menus', PageMenuController::class);
                    Route::patch('conferences/{conference}/menus/{menu}/position', [PageMenuController::class, 'updatePosition']);

                    // Page Data routes
                    Route::apiResource('conferences.menus.data', PageDataController::class);
                    Route::patch('conferences/{conference}/menus/{menu}/data/{data}/position', [PageDataController::class, 'updatePosition']);
                });
            });

            // User management
            Route::middleware('permission:access.admin')->group(function () {
                Route::prefix('user-management')->group(function () {
                    Route::get('/users', [UserController::class, 'index']);
                    Route::post('/users', [UserController::class, 'store']);
                    Route::put('/users/{user}', [UserController::class, 'update']);
                    Route::delete('/users/{user}', [UserController::class, 'destroy']);
                    Route::get('/roles/available', [RoleController::class, 'availableRoles']);
                });
            });
        });
    });
});
