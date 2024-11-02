<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/**
 * Registration and login routes
 */
Route::post('register', [UserController::class, 'registerUser']);
Route::post('login', [UserController::class, 'loginUser']);

/**
 * Authentication required routes
 */
Route::middleware(['auth:sanctum'])->group(function() {
    Route::get('/users', [UserController::class, 'getAllUsers']);
});
