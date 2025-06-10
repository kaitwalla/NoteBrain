<?php

namespace App\Http\Controllers;

use App\Models\User;
use Google\Client;
use Google\Service\Drive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class GoogleDriveController extends Controller
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setRedirectUri(config('services.google.redirect_uri'));
        $this->client->setScopes([Drive::DRIVE_FILE]);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
        $this->client->setIncludeGrantedScopes(true);
    }

    /**
     * Redirect the user to Google's OAuth consent screen.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToGoogle()
    {
        $authUrl = $this->client->createAuthUrl();
        return redirect()->away($authUrl);
    }

    /**
     * Handle the callback from Google after the user has authorized the app.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleGoogleCallback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()->route('profile.edit')
                ->with('error', 'Google Drive authorization was canceled.');
        }

        try {
            // Exchange authorization code for an access token
            $token = $this->client->fetchAccessTokenWithAuthCode($request->code);

            if (isset($token['error'])) {
                return redirect()->route('profile.edit')
                    ->with('error', 'Failed to get access token: ' . $token['error_description']);
            }

            // Save the tokens to the user's record
            $user = Auth::user();
            $user->update([
                'google_access_token' => $token['access_token'],
                'google_refresh_token' => $token['refresh_token'] ?? $user->google_refresh_token,
                'google_token_expires_at' => now()->addSeconds($token['expires_in']),
            ]);

            // Redirect to folder selection
            return redirect()->route('google.drive.folders')
                ->with('status', 'Google Drive connected successfully! Please select a folder to save your articles.');
        } catch (\Exception $e) {
            Log::error('Google Drive callback error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('profile.edit')
                ->with('error', 'Failed to connect Google Drive: ' . $e->getMessage());
        }
    }

    /**
     * Check if a Google Drive folder exists.
     *
     * @param string $folderId The folder ID to check
     * @return bool True if the folder exists, false otherwise
     */
    protected function folderExists(string $folderId): bool
    {
        $service = new Drive($this->client);

        try {
            $service->files->get($folderId);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Show the folder selection page.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showFolders()
    {
        $user = Auth::user();

        if (!$user->hasGoogleDriveToken()) {
            return redirect()->route('profile.edit')
                ->with('error', 'You need to connect your Google Drive account first.');
        }

        // Get folders from Google Drive
        $driveService = new \App\Services\GoogleDriveService($user);
        $folders = $driveService->listFolders();

        if ($folders === null) {
            return redirect()->route('profile.edit')
                ->with('error', 'Failed to retrieve folders from Google Drive. Please try reconnecting your account.');
        }

        // Add option to create a new folder
        $folders[] = [
            'id' => 'new',
            'name' => '+ Create a new folder'
        ];

        return view('google-drive.folders', [
            'folders' => $folders,
            'currentFolderId' => $user->google_drive_folder_id
        ]);
    }

    /**
     * Get the name of the currently selected folder.
     *
     * @return string|null The folder name or null if no folder is selected
     */
    public function getCurrentFolderName()
    {
        $user = Auth::user();

        if (!$user->hasGoogleDriveToken() || !$user->google_drive_folder_id) {
            return null;
        }

        $this->client->setAccessToken($user->google_access_token);

        try {
            $service = new Drive($this->client);
            $folder = $service->files->get($user->google_drive_folder_id, ['fields' => 'name']);
            return $folder->getName();
        } catch (\Exception $e) {
            Log::error('Failed to get folder name', [
                'error' => $e->getMessage(),
                'folder_id' => $user->google_drive_folder_id,
            ]);

            return null;
        }
    }

    /**
     * Handle folder selection or creation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function selectFolder(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasGoogleDriveToken()) {
            return redirect()->route('profile.edit')
                ->with('error', 'You need to connect your Google Drive account first.');
        }

        $folderId = $request->input('folder_id');
        $newFolderName = $request->input('new_folder_name');

        // If user chose to create a new folder
        if ($folderId === 'new') {
            if (empty($newFolderName)) {
                return redirect()->route('google.drive.folders')
                    ->with('error', 'Please provide a name for the new folder.');
            }

            // Create a new folder
            $service = new Drive($this->client);
            $this->client->setAccessToken($user->google_access_token);

            try {
                $fileMetadata = new Drive\DriveFile([
                    'name' => $newFolderName,
                    'mimeType' => 'application/vnd.google-apps.folder',
                ]);

                $folder = $service->files->create($fileMetadata, [
                    'fields' => 'id',
                ]);

                $folderId = $folder->id;
            } catch (\Exception $e) {
                Log::error('Failed to create Google Drive folder', [
                    'error' => $e->getMessage(),
                ]);

                return redirect()->route('google.drive.folders')
                    ->with('error', 'Failed to create folder: ' . $e->getMessage());
            }
        } else {
            // Verify that the selected folder exists
            $this->client->setAccessToken($user->google_access_token);
            if (!$this->folderExists($folderId)) {
                return redirect()->route('google.drive.folders')
                    ->with('error', 'The selected folder does not exist or you do not have access to it.');
            }
        }

        // Update the user's selected folder
        $user->update([
            'google_drive_folder_id' => $folderId,
        ]);

        return redirect()->route('profile.edit')
            ->with('status', 'Google Drive folder selected successfully.');
    }

    /**
     * Disconnect Google Drive from the user's account.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function disconnect()
    {
        $user = Auth::user();

        if ($user->hasGoogleDriveToken()) {
            try {
                // Revoke the token
                $this->client->setAccessToken($user->google_access_token);
                $this->client->revokeToken();
            } catch (\Exception $e) {
                Log::error('Failed to revoke Google Drive token', [
                    'error' => $e->getMessage(),
                ]);
            }

            // Clear the tokens from the user's record
            $user->update([
                'google_access_token' => null,
                'google_refresh_token' => null,
                'google_token_expires_at' => null,
                'google_drive_folder_id' => null,
            ]);
        }

        return redirect()->route('profile.edit')
            ->with('status', 'Google Drive disconnected successfully.');
    }
}
