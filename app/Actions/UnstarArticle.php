<?php

namespace App\Actions;

use App\Models\Article;
use App\Services\GoogleDriveService;

class UnstarArticle
{
    protected $googleDriveService;

    public function __construct(GoogleDriveService $googleDriveService)
    {
        $this->googleDriveService = $googleDriveService;
    }

    /**
     * Unstar an article.
     *
     * @param Article $article The article to unstar
     * @return array The result of the operation
     */
    public function __invoke(Article $article): array
    {
        try {
            // Check if article is already unstarred
            if (!$article->starred) {
                return [
                    'success' => true,
                    'message' => 'Article already unstarred',
                    'article' => $article,
                    'error' => null
                ];
            }

            // If article has a Google Drive file ID, delete it from Google Drive
            if ($article->google_drive_file_id) {
                $this->googleDriveService->deleteFile($article->google_drive_file_id);
                $article->update(['google_drive_file_id' => null]);
            }

            // Unstar the article
            $article->unstar();

            return [
                'success' => true,
                'message' => 'Article unstarred successfully',
                'article' => $article,
                'error' => null
            ];
        } catch (\Exception $e) {
            \Log::error('Failed to unstar article: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to unstar article',
                'article' => $article,
                'error' => $e->getMessage()
            ];
        }
    }
}
