<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\UserPreferenceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes for authentication
Route::post('/login', [AuthController::class, 'login']);
// JSONP endpoints for bookmarklet (public, authentication handled by token in request)
Route::get('/articles/jsonp', [ArticleController::class, 'storeJsonp']);
Route::get('/articles/{article}/star/jsonp', [ArticleController::class, 'starJsonp']);
Route::get('/articles/{article}/summarize/jsonp', [ArticleController::class, 'summarizeJsonp']);

// Protected routes with Sanctum authentication
Route::middleware('auth:sanctum')->group(function () {
    // Authentication routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Article routes
    Route::get('/articles', [ArticleController::class, 'listAll']);
    Route::get('/articles/archived', [ArticleController::class, 'listArchived']);
    Route::post('/articles', [ArticleController::class, 'store']);
    Route::post('/articles/{article}/keep-unread', [ArticleController::class, 'keepUnread']);
    Route::post('/articles/{article}/read', [ArticleController::class, 'read']);
    Route::post('/articles/{article}/summarize', [ArticleController::class, 'summarize']);
    Route::post('/articles/{article}/delete-summary', [ArticleController::class, 'deleteSummary']);
    Route::post('/articles/{article}/star', [ArticleController::class, 'star']);
    Route::post('/articles/{article}/unstar', [ArticleController::class, 'unstar']);
    Route::post('/articles/{article}/toggle-star', [ArticleController::class, 'toggleStar']);
    Route::delete('/articles/{article}', [ArticleController::class, 'delete']);
});

// API routes don't need web authentication routes
// require __DIR__ . '/auth.php';
