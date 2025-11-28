<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BibleController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\TaskAdminController;
use App\Http\Controllers\TaskProgressController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\UserPreferenceController;
use App\Http\Controllers\UserGoalController;
use App\Http\Controllers\IdentityController;
use App\Http\Controllers\ReflectionController;
use App\Http\Controllers\CustomNotificationController;
use App\Http\Controllers\BlogNotificationController;
use App\Http\Middleware\VerifyWebhookSignature;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Test endpoint to verify API is working
Route::get('/test', function () {
    return response()->json(['message' => 'API is working', 'timestamp' => now()]);
});

// Test POST endpoint to verify POST requests work
Route::post('/test-post', function (Request $request) {
    return response()->json([
        'message' => 'POST request working',
        'received_data' => $request->all(),
        'timestamp' => now()
    ]);
});

// Test registration endpoint (temporary debug)
Route::post('/test-register', function (Request $request) {
    return response()->json([
        'message' => 'Registration endpoint reached',
        'data' => $request->all(),
        'headers' => $request->headers->all(),
        'timestamp' => now()
    ]);
});

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

    // User Preferences
    Route::get('/user/preferences', [UserPreferenceController::class, 'show']);
    Route::post('/user/preferences', [UserPreferenceController::class, 'store']);
    Route::put('/user/preferences', [UserPreferenceController::class, 'update']);
    Route::delete('/user/preferences', [UserPreferenceController::class, 'destroy']);
    Route::get('/user/preferences/options', [UserPreferenceController::class, 'options']);

    // User Goals
    Route::get('/user/goals', [UserGoalController::class, 'index']);
    Route::post('/user/goals', [UserGoalController::class, 'store']);
    Route::get('/user/goals/{id}', [UserGoalController::class, 'show']);
    Route::put('/user/goals/{id}', [UserGoalController::class, 'update']);
    Route::delete('/user/goals/{id}', [UserGoalController::class, 'destroy']);
    Route::patch('/user/goals/{id}/progress', [UserGoalController::class, 'updateProgress']);
    Route::patch('/user/goals/{id}/toggle', [UserGoalController::class, 'toggleCompletion']);
    Route::get('/user/goals/options', [UserGoalController::class, 'options']);
    Route::get('/user/goals/statistics', [UserGoalController::class, 'statistics']);

    // User Identities
    Route::get('/identities', [IdentityController::class, 'index']);
    Route::post('/identities', [IdentityController::class, 'store']);
    Route::put('/identities/{identity}', [IdentityController::class, 'update']);
    Route::delete('/identities/{identity}', [IdentityController::class, 'destroy']);

    // Daily Reflections
    Route::get('/reflections', [ReflectionController::class, 'index']);

    // Custom Notifications
    Route::get('/custom-notifications/{notification}', [CustomNotificationController::class, 'show']);
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

// Webhook endpoints (secured by API key + domain validation)
Route::middleware(VerifyWebhookSignature::class)->group(function () {
    Route::post('/webhook/blog-notify', [BlogNotificationController::class, 'notify']);
});
