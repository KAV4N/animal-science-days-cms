<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\Spa\LoginController;
use App\Http\Controllers\Api\Auth\Spa\LogoutController;
use App\Http\Controllers\Api\Auth\Spa\RegisterController;
use App\Http\Controllers\Api\Auth\Spa\ChangePasswordController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/auth/change-password', ChangePasswordController::class);
    Route::post('/auth/logout', LogoutController::class);
});

Route::post('/auth/login', LoginController::class);
Route::post('/auth/register', RegisterController::class);
