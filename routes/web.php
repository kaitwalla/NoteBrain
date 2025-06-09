<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserPreferenceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
    Route::get('/articles/create', [ArticleController::class, 'create'])->name('articles.create');
    Route::post('/articles', [ArticleController::class, 'store'])->name('articles.store');
    Route::get('/articles/{article}', [ArticleController::class, 'show'])->name('articles.show');
    Route::get('/articles/{article}/edit', [ArticleController::class, 'edit'])->name('articles.edit');
    Route::put('/articles/{article}', [ArticleController::class, 'update'])->name('articles.update');
    Route::delete('/articles/{article}', [ArticleController::class, 'destroy'])->name('articles.destroy');
    Route::post('/articles/{article}/archive', [ArticleController::class, 'archive'])->name('articles.archive');
    Route::post('/articles/{article}/inbox', [ArticleController::class, 'inbox'])->name('articles.inbox');
    Route::post('/articles/{article}/restore', [ArticleController::class, 'restore'])->name('articles.restore');
    Route::post('/articles/{article}/summarize', [ArticleController::class, 'summarize'])->name('articles.summarize');
    Route::post('/articles/{article}/keep-unread', [ArticleController::class, 'keepUnread']);
    Route::post('/user/preferences', [UserPreferenceController::class, 'update'])->name('preferences.update');
    Route::get('/user/preferences', [UserPreferenceController::class, 'show'])->name('preferences.show');
});

require __DIR__.'/auth.php';
