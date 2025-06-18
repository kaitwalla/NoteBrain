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

        // Create or retrieve a token for the bookmarklet
        $token = $user->tokens()->first()?->plainTextToken;
        if (!$token) {
            $token = $user->createToken('bookmarklet')->plainTextToken;
        }

        // Debug the token
        \Log::info('Generated bookmarklet token: ' . $token);

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
