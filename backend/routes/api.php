<?php

use App\Http\Controllers\Api\Auth\Spa\LoginController as SpaLoginController;
use App\Http\Controllers\Api\Auth\Spa\LogoutController as SpaLogoutController;
use App\Http\Controllers\Api\Auth\Spa\RegisterController as SpaRegisterController;
use App\Http\Controllers\Api\Auth\Token\LoginController as TokenLoginController;
use App\Http\Controllers\Api\Auth\Token\LogoutController as TokenLogoutController;
use App\Http\Controllers\Api\Auth\Token\RegisterController as TokenRegisterController;
use App\Http\Controllers\Api\AccessController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// SPA Authentication Routes
Route::prefix('auth/spa')->group(function () {
    Route::post('/login', SpaLoginController::class);
    Route::post('/register', SpaRegisterController::class);
    Route::post('/logout', SpaLogoutController::class)->middleware('auth:sanctum');
});

// TODO: NOT CURRENTLY IN USE, REMOVE IT IN FUTURE OR REWORK THE PROJECT TO USE THIS AUTHENTICATION
// API Token Authentication Routes
/*
Route::prefix('auth/token')->group(function () {
    Route::post('/login', TokenLoginController::class);
    Route::post('/register', TokenRegisterController::class);
    Route::post('/logout', TokenLogoutController::class)->middleware('auth:sanctum');
});
*/


// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    // User information
    Route::get('/user', [UserController::class, 'current']);
    // TODO: REMOVE IN FUTURE, THESE ARE TEST ROUTES
    Route::get('/access/editor', [AccessController::class, 'editor'])->middleware('permission:access.editor');
    Route::get('/access/admin', [AccessController::class, 'admin'])->middleware('permission:access.admin');
    Route::get('/access/super-admin', [AccessController::class, 'superAdmin'])->middleware('permission:access.super_admin');

});
