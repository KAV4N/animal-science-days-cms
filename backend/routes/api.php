<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;

use App\Http\Controllers\Api\ConferenceController;
use App\Http\Controllers\Api\ConferenceEditorController;
use App\Http\Controllers\Api\UniversityController;

//ONLY FOR TESTING
// Universities

//Route::get('/conferences/latest', [ConferenceController::class, 'latest']);
Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            //TODO: implement this route in controller for changing password
            //Route::post('/change-password', [AuthController::class, 'changePassword']);
        });
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/users/me', [UserController::class, 'current']);
        Route::middleware('permission:access.admin')->group(function () {
            Route::get('/users', [UserController::class, 'index']);
        });

        Route::apiResource('universities', UniversityController::class)->only(['index', 'show']);

        Route::middleware('role:super_admin')->group(function () {
            Route::apiResource('universities', UniversityController::class)->except(['index', 'show']);
        });
  
       
        Route::middleware('permission:access.admin')->group(function () {
            Route::apiResource('conferences', ConferenceController::class)->except(['index', 'show']);
            Route::get('/conferences/{conference}/editors', [ConferenceEditorController::class, 'index']);
            Route::get('/conferences/{conference}/editors/unattached', [ConferenceEditorController::class, 'unattached']);
            Route::post('/conferences/{conference}/editors', [ConferenceEditorController::class, 'store']);
            Route::delete('/conferences/{conference}/editors/{editor}', [ConferenceEditorController::class, 'destroy']);
            Route::get('/conferences/latest', [ConferenceController::class, 'latest']);
        });
        Route::apiResource('conferences', ConferenceController::class)->only(['index', 'show']);
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