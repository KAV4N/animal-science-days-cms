<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;

use App\Http\Controllers\Api\ConferenceController;
use App\Http\Controllers\Api\ConferenceEditorController;
use App\Http\Controllers\Api\UniversityController;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/refresh', [AuthController::class, 'refresh']);

        // Protected routes
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/change-password', [AuthController::class, 'changePassword']);
            Route::get('/user', [AuthController::class, 'getUser']);
        });
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/users/me', [UserController::class, 'current']);
        /*
        //TODO: implement this in admin controller its just a example code
        Route::middleware('role:admin|super_admin')->group(function () {
            Route::apiResource('users', UserController::class);
        });
        //-----------------------------
        */
        Route::apiResource('universities', UniversityController::class)->only(['index', 'show']);

        Route::middleware(['role:super_admin'])->group(function () {
            Route::apiResource('universities', UniversityController::class)->except(['index', 'show']);
        });


        Route::apiResource('conferences', ConferenceController::class)->only(['index', 'show']);
        Route::middleware(['role:admin|super_admin'])->group(function () {
            Route::apiResource('conferences', ConferenceController::class)->except(['index', 'show']);
            Route::patch('conferences/{conference}', [ConferenceController::class, 'updateStatus']);
            Route::get('/conferences/{conference}/editors', [ConferenceController::class, 'getEditors']);
            Route::post('/conferences/{conference}/editors', [ConferenceController::class, 'attachEditor']);
            Route::delete('/conferences/{conference}/editors/{user}', [ConferenceController::class, 'detachEditor']);
            Route::get('/conferences/latest', [ConferenceController::class, 'latest']);
        });
        /*
        //TODO: IMPLEMENT THESE ROUTES IN FUTURE!!!
        // Page menus
        Route::get('/conferences/{conferenceId}/menus', [PageMenuController::class, 'index']);
        Route::get('/conferences/{conferenceId}/menus/{id}', [PageMenuController::class, 'show']);

        Route::middleware('permission:edit.pages')->group(function () {
            Route::post('/conferences/{conferenceId}/menus', [PageMenuController::class, 'store']);
            Route::put('/conferences/{conferenceId}/menus/{id}', [PageMenuController::class, 'update']);
            Route::delete('/conferences/{conferenceId}/menus/{id}', [PageMenuController::class, 'destroy']);
        });

        // Page content/data
        Route::get('/menus/{menuId}/data', [PageDataController::class, 'index']);
        Route::get('/menus/{menuId}/data/{id}', [PageDataController::class, 'show']);

        Route::middleware('permission:edit.content')->group(function () {
            Route::post('/menus/{menuId}/data', [PageDataController::class, 'store']);
            Route::put('/menus/{menuId}/data/{id}', [PageDataController::class, 'update']);
            Route::delete('/menus/{menuId}/data/{id}', [PageDataController::class, 'destroy']);
        });

        // Media management
        Route::get('/media', [MediaController::class, 'index']);
        Route::get('/media/{id}', [MediaController::class, 'show']);

        Route::middleware('permission:upload.media')->group(function () {
            Route::post('/media', [MediaController::class, 'store']);
            Route::delete('/media/{id}', [MediaController::class, 'destroy']);
        });
    */
    });
});
