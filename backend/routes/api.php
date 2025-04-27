<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\UserController;

use App\Http\Controllers\Api\ConferenceController;
use App\Http\Controllers\Api\ConferenceEditorController;
use App\Http\Controllers\Api\UniversityController;

//ONLY FOR TESTING
// Universities
Route::apiResource('universities', UniversityController::class);
// Conferences
Route::apiResource('conferences', ConferenceController::class);
Route::patch('conferences/{conference}/status', [ConferenceController::class, 'updateStatus'])->name('conferences.update-status');

// Conference Editors
Route::get('/conferences/{conference}/editors', [ConferenceEditorController::class, 'index']);
Route::post('/conferences/{conference}/editors', [ConferenceEditorController::class, 'store']);
Route::delete('/conferences/{conference}/editors/{user}', [ConferenceEditorController::class, 'destroy']);
//--------------------------------


Route::middleware('auth:sanctum')->group(function () {


});

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
        /*
        //TODO: implement this in admin controller its just a example code
        Route::middleware('permission:access.admin')->group(function () {
            Route::apiResource('users', UserController::class);
        });
        //-----------------------------

        // CONFERENCE SECTION
           Route::apiResource('universities', UniversityController::class)
        ->middleware('permission:manage.universities');
        

        // Conference management
        Route::get('/conferences', [ConferenceController::class, 'index']);
        Route::get('/conferences/{id}', [ConferenceController::class, 'show']);
        
        Route::middleware('permission:access.admin')->group(function () {
            Route::post('/conferences', [ConferenceController::class, 'store']);
            Route::put('/conferences/{id}', [ConferenceController::class, 'update']);
            Route::delete('/conferences/{id}', [ConferenceController::class, 'destroy']);
        });
        
        
        // Conference editors
        Route::get('/conferences/{conferenceId}/editors', [ConferenceEditorController::class, 'index']);
        
        Route::middleware('permission:manage.conference_editors')->group(function () {
            Route::post('/conferences/{conferenceId}/editors', [ConferenceEditorController::class, 'store']);
            Route::delete('/conferences/{conferenceId}/editors/{userId}', [ConferenceEditorController::class, 'destroy']);
        });
        
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