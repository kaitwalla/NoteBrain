<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;

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

Route::middleware('web')->group(function () {
    Route::post('/user/preferences', function (Request $request) {
        $user = $request->user();
        $preferences = $user->preferences;
        
        // Update article preferences
        $preferences['article_preferences'] = array_merge(
            $preferences['article_preferences'] ?? [],
            $request->only([
                'font_size',
                'paragraph_spacing',
                'content_width',
                'font_family',
                'line_height'
            ])
        );
        
        $user->preferences = $preferences;
        $user->save();
        
        return response()->json(['success' => true]);
    });
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_middleware', 'inertia')
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

require __DIR__.'/auth.php'; 