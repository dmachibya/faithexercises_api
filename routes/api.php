<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BibleController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/auth/google', [AuthController::class, 'google']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Basic email/password auth
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

// Profile
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
});

// Bible proxy endpoints (API.Bible)
Route::prefix('bible')->group(function () {
    Route::get('/versions', [BibleController::class, 'versions']);
    Route::get('/books', [BibleController::class, 'books']);
    Route::get('/chapters', [BibleController::class, 'chapters']);
    Route::get('/passage', [BibleController::class, 'passage']);
});
