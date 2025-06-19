<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $folderName = null;

        if ($user->hasGoogleDriveToken() && $user->google_drive_folder_id) {
            $googleDriveController = new GoogleDriveController();
            $folderName = $googleDriveController->getCurrentFolderName();
        }

        // Check if we need to generate a new token
        $generateNewToken = $request->has('new_token');

        // If not explicitly requested, check if we already have a token in the session
        if (!$generateNewToken && session()->has('bookmarklet_token')) {
            $token = session('bookmarklet_token');
            \Log::info('Using existing bookmarklet token from session');
        } else {
            // Generate a new token
            // First, delete any existing bookmarklet tokens to avoid accumulating too many
            $user->tokens()->where('name', 'bookmarklet')->delete();

            // Create a fresh token that will work with Sanctum's findToken method
            $token = $user->createToken('bookmarklet')->plainTextToken;

            // Store the token in the session for future use
            session(['bookmarklet_token' => $token]);

            \Log::info('Generated new bookmarklet token');
        }

        return view('profile.edit', [
            'user' => $user,
            'googleDriveFolderName' => $folderName,
            'bookmarkletToken' => $token,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
