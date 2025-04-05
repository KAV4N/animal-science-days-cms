<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public route - does not require authentication
Route::get('/test', function (Request $request) {
    return ['success' => 'ok'];
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
