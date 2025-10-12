<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BibleController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\TaskAdminController;
use App\Http\Controllers\TaskProgressController;
use App\Http\Controllers\JournalController;

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

    // Task progress (user-specific)
    Route::post('/tasks/{task}/progress/toggle', [TaskProgressController::class, 'toggle']);
    Route::get('/tasks/{task}/progress', [TaskProgressController::class, 'show']);

    // Journals (user-specific)
    Route::get('/journals', [JournalController::class, 'index']);
    Route::post('/journals', [JournalController::class, 'store']);
    Route::put('/journals/{journal}', [JournalController::class, 'update']);
    Route::delete('/journals/{journal}', [JournalController::class, 'destroy']);
});

// Bible proxy endpoints (API.Bible)
Route::prefix('bible')->group(function () {
    Route::get('/versions', [BibleController::class, 'versions']);
    Route::get('/books', [BibleController::class, 'books']);
    Route::get('/chapters', [BibleController::class, 'chapters']);
    Route::get('/passage', [BibleController::class, 'passage']);
});

// Exercises and Tasks
Route::get('/exercises', [ExerciseController::class, 'index']);
Route::get('/exercises/{exercise}', [ExerciseController::class, 'show']);
Route::get('/exercises/{exercise}/tasks', [ExerciseController::class, 'tasks']);

// Admin task management (requires auth; controller enforces is_admin)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/exercises/{exercise}/admin/tasks', [TaskAdminController::class, 'index']);
    Route::post('/exercises/{exercise}/tasks', [TaskAdminController::class, 'store']);
    Route::put('/tasks/{task}', [TaskAdminController::class, 'update']);
    Route::delete('/tasks/{task}', [TaskAdminController::class, 'destroy']);
});
