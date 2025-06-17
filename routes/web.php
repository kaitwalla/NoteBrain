<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GoogleDriveController;
use App\Http\Controllers\NotesController;
use App\Http\Controllers\ProfileController;
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
    Route::post('/articles/{article}/keep-unread', [ArticleController::class, 'keepUnread'])->name('articles.keep-unread');
    Route::post('/articles/{article}/toggle-star', [ArticleController::class, 'toggleStar'])->name('articles.toggle-star');
    Route::post('/articles/bulk-action', [ArticleController::class, 'bulkAction'])->name('articles.bulk-action');
    Route::post('/user/preferences', [UserPreferenceController::class, 'update'])->name('preferences.update');
    Route::get('/user/preferences', [UserPreferenceController::class, 'show'])->name('preferences.show');

    // Notes routes
    Route::get('/notes', [NotesController::class, 'index'])->name('notes.index');
    Route::get('/notes/create', [NotesController::class, 'create'])->name('notes.create');
    Route::post('/notes', [NotesController::class, 'store'])->name('notes.store');
    Route::get('/notes/{note}', [NotesController::class, 'show'])->name('notes.show');
    Route::get('/notes/{note}/edit', [NotesController::class, 'edit'])->name('notes.edit');
    Route::put('/notes/{note}', [NotesController::class, 'update'])->name('notes.update');
    Route::delete('/notes/{note}', [NotesController::class, 'destroy'])->name('notes.destroy');
    Route::post('/notes/{note}/archive', [NotesController::class, 'archive'])->name('notes.archive');
    Route::post('/notes/{note}/inbox', [NotesController::class, 'inbox'])->name('notes.inbox');
    Route::post('/notes/{note}/toggle-star', [NotesController::class, 'toggleStar'])->name('notes.toggle-star');
    Route::post('/notes/bulk-action', [NotesController::class, 'bulkAction'])->name('notes.bulk-action');

    // Google Drive OAuth routes
    Route::get('/google/drive/connect', [GoogleDriveController::class, 'redirectToGoogle'])->name('google.drive.connect');
    Route::get('/google/drive/callback', [GoogleDriveController::class, 'handleGoogleCallback'])->name('google.drive.callback');
    Route::delete('/google/drive/disconnect', [GoogleDriveController::class, 'disconnect'])->name('google.drive.disconnect');
    Route::get('/google/drive/folders', [GoogleDriveController::class, 'showFolders'])->name('google.drive.folders');
    Route::post('/google/drive/folders', [GoogleDriveController::class, 'selectFolder'])->name('google.drive.select-folder');
});

require __DIR__ . '/auth.php';
