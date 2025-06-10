<?php

namespace App\Services;

use App\Models\Article;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Support\Facades\Log;

class GoogleDriveService
{
    protected $client;
    protected $service;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setAccessToken(config('services.google.access_token'));
        $this->client->setScopes([Drive::DRIVE_FILE]);

        $this->service = new Drive($this->client);
    }

    public function saveArticleText(Article $article): ?string
    {
        try {
            // Create a new file in Google Drive
            $fileMetadata = new DriveFile([
                'name' => $article->title . '.txt',
                'mimeType' => 'text/plain',
                'description' => 'Article saved from NoteBrain: ' . $article->url,
                'parents' => [config('services.google.folder_id')],
            ]);

            // Prepare the content
            $content = "Title: {$article->title}\n\n";
            $content .= "URL: {$article->url}\n\n";
            if ($article->author) {
                $content .= "Author: {$article->author}\n\n";
            }
            if ($article->site_name) {
                $content .= "Source: {$article->site_name}\n\n";
            }
            $content .= "Content:\n\n{$article->content}";

            // Upload the file
            $file = $this->service->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => 'text/plain',
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
}
