<?php

namespace App\Actions;

use App\Models\Article;
use App\Services\GoogleDriveService;

class StarArticle
{
    protected $googleDriveService;

    public function __construct(GoogleDriveService $googleDriveService)
    {
        $this->googleDriveService = $googleDriveService;
    }

    /**
     * Star an article.
     *
     * @param Article $article The article to star
     * @return array The result of the operation
     */
    public function __invoke(Article $article): array
    {
        try {
            // Check if article is already starred
            if ($article->starred) {
                return [
                    'success' => true,
                    'message' => 'Article already starred',
                    'article' => $article,
                    'error' => null
                ];
            }

            // Star the article
            $article->star();

            // If article doesn't have a Google Drive file ID, save it to Google Drive
            if (!$article->google_drive_file_id) {
                $driveFileId = $this->googleDriveService->saveArticleText($article);
                if ($driveFileId) {
                    $article->update(['google_drive_file_id' => $driveFileId]);
                }
            }

            return [
                'success' => true,
                'message' => 'Article starred successfully',
                'article' => $article,
                'error' => null
            ];
        } catch (\Exception $e) {
            \Log::error('Failed to star article: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to star article',
                'article' => $article,
                'error' => $e->getMessage()
            ];
        }
    }
}
