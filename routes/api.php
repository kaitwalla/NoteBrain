<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\API\UserPreferenceController;

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

// Use web authentication for API routes
Route::middleware('auth')->group(function () {
    Route::post('/articles/{article}/keep-unread', [ArticleController::class, 'keepUnread']);
});

require __DIR__.'/auth.php';
