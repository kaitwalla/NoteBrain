<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Note;
use App\Models\User;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use League\HTMLToMarkdown\HtmlConverter;

class GoogleDriveService
{
    protected $client;
    protected $service;
    protected $user;

    /**
     * List folders from Google Drive
     *
     * @return array|null Array of folders with id and name, or null if error
     */
    public function listFolders(): ?array
    {
        $this->authenticate();
        if (!$this->user || !$this->user->hasGoogleDriveToken()) {
            Log::info('Google Drive not configured for user', [
                'user_id' => $this->user?->id,
            ]);
            return null;
        }

        // Refresh token if needed
        if (!$this->setAccessToken($this->user)) {
            return null;
        }

        try {
            // Query for folders owned by the user
            $results = $this->service->files->listFiles([
                'q' => "mimeType='application/vnd.google-apps.folder' and trashed=false",
                'fields' => 'files(id, name)',
                'spaces' => 'drive'
            ]);

            $folders = [];
            foreach ($results->getFiles() as $folder) {
                $folders[] = [
                    'id' => $folder->getId(),
                    'name' => $folder->getName()
                ];
            }

            return $folders;
        } catch (\Exception $e) {
            Log::error('Failed to list folders from Google Drive', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function authenticate()
    {
        if ($this->user)
            return;
        $this->user = Auth::user();
        $this->client = new Client();
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setScopes([Drive::DRIVE_FILE]);

        if ($this->user && $this->user->hasGoogleDriveToken()) {
            $this->setAccessToken($this->user);
        }

        $this->service = new Drive($this->client);
    }

    /**
     * Set the access token for the Google API client.
     * Refreshes the token if it's expired.
     *
     * @param User $user
     * @return bool
     */
    protected function setAccessToken(User $user): bool
    {
        if (!$user->hasGoogleDriveToken()) {
            return false;
        }

        $this->client->setAccessToken($user->google_access_token);

        // If the token is expired, refresh it
        if ($user->isGoogleDriveTokenExpired() && $user->google_refresh_token) {
            try {
                $this->client->fetchAccessTokenWithRefreshToken($user->google_refresh_token);
                $token = $this->client->getAccessToken();

                $user->update([
                    'google_access_token' => $token['access_token'],
                    'google_token_expires_at' => now()->addSeconds($token['expires_in']),
                ]);

                return true;
            } catch (\Exception $e) {
                Log::error('Failed to refresh Google Drive token', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);

                return false;
            }
        }

        return true;
    }

    public function saveArticleText(Article $article): ?string
    {
        $this->authenticate();
        if (!$this->user || !$this->user->hasGoogleDriveToken() || !$this->user->google_drive_folder_id) {
            Log::info('Google Drive not configured for user', [
                'article_id' => $article->id,
                'user_id' => $this->user?->id,
            ]);
            return null;
        }

        // Refresh token if needed
        if (!$this->setAccessToken($this->user)) {
            return null;
        }

        try {
            // Create a new file in Google Drive as a Google Doc
            $fileMetadata = new DriveFile([
                'name' => $article->title,
                'mimeType' => 'application/vnd.google-apps.document',
                'description' => 'Article saved from NoteBrain: ' . $article->url,
                'parents' => [$this->user->google_drive_folder_id],
            ]);

            // Convert HTML content to Markdown and strip any remaining HTML tags
            $converter = app()->make(HtmlConverter::class);
            if (method_exists($converter, 'setOptions')) {
                $converter->setOptions(['strip_tags' => true]);
            }
            $markdownContent = $converter->convert($article->content);

            // Prepare the content
            $content = "Title: {$article->title}\n\n";
            $content .= "URL: {$article->url}\n\n";
            if ($article->author) {
                $content .= "Author: {$article->author}\n\n";
            }
            if ($article->site_name) {
                $content .= "Source: {$article->site_name}\n\n";
            }
            $content .= "Content:\n\n{$markdownContent}";

            // Upload the file as a Google Doc
            $file = $this->service->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => 'text/plain', // Source content is plain text
                'uploadType' => 'multipart',
                'fields' => 'id',
            ]);

            Log::info('Article saved to Google Drive', [
                'article_id' => $article->id,
                'drive_file_id' => $file->id,
            ]);

            return $file->id;
        } catch (\Exception $e) {
            Log::error('Failed to save article to Google Drive', [
                'article_id' => $article->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Save a note to Google Drive.
     *
     * @param Note $note The note to save
     * @return string|null The Google Drive file ID if successful, null otherwise
     */
    public function saveNoteText(Note $note): ?string
    {
        $this->authenticate();
        if (!$this->user || !$this->user->hasGoogleDriveToken() || !$this->user->google_drive_folder_id) {
            Log::info('Google Drive not configured for user', [
                'note_id' => $note->id,
                'user_id' => $this->user?->id,
            ]);
            return null;
        }

        // Refresh token if needed
        if (!$this->setAccessToken($this->user)) {
            return null;
        }

        try {
            // Create a new file in Google Drive as a Google Doc
            $fileMetadata = new DriveFile([
                'name' => 'Note | ' . $note->title,
                'mimeType' => 'application/vnd.google-apps.document',
                'description' => 'Note saved from NoteBrain',
                'parents' => [$this->user->google_drive_folder_id],
            ]);

            // Prepare the content
            $content = "Title: {$note->title}\n\n";
            $content .= "Content:\n\n{$note->content}";

            // Upload the file as a Google Doc
            $file = $this->service->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => 'text/plain', // Source content is plain text
                'uploadType' => 'multipart',
                'fields' => 'id',
            ]);

            Log::info('Note saved to Google Drive', [
                'note_id' => $note->id,
                'drive_file_id' => $file->id,
            ]);

            return $file->id;
        } catch (\Exception $e) {
            Log::error('Failed to save note to Google Drive', [
                'note_id' => $note->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Delete a file from Google Drive.
     *
     * @param string $fileId The Google Drive file ID to delete
     * @return bool Whether the deletion was successful
     */
    public function deleteFile(string $fileId): bool
    {
        $this->authenticate();
        if (!$this->user || !$this->user->hasGoogleDriveToken()) {
            Log::info('Google Drive not configured for user', [
                'user_id' => $this->user?->id,
            ]);
            return false;
        }

        // Refresh token if needed
        if (!$this->setAccessToken($this->user)) {
            return false;
        }

        try {
            // Delete the file from Google Drive
            $this->service->files->delete($fileId);

            Log::info('Article deleted from Google Drive', [
                'drive_file_id' => $fileId,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete article from Google Drive', [
                'drive_file_id' => $fileId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
