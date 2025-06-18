<?php

namespace App\Actions;

use App\Models\Article;
use App\Services\GoogleDriveService;
use Illuminate\Support\Facades\Log;

class ToggleStarArticle
{
    protected $googleDriveService;

    public function __construct(GoogleDriveService $googleDriveService)
    {
        $this->googleDriveService = $googleDriveService;
    }

    /**
     * Toggle the star status of an article.
     *
     * @param Article $article The article to toggle star status
     * @return array The result of the operation
     */
    public function __invoke(Article $article): array
    {
        try {
            if ($article->starred) {
                // If article is starred, unstar it
                if ($article->google_drive_file_id) {
                    $this->googleDriveService->deleteFile($article->google_drive_file_id);
                    $article->update(['google_drive_file_id' => null]);
                }
                $article->unstar();
                $message = 'Article unstarred successfully';
            } else {
                // If article is not starred, star it
                $article->star();
                if (!$article->google_drive_file_id) {
                    $driveFileId = $this->googleDriveService->saveArticleText($article);
                    if ($driveFileId) {
                        $article->update(['google_drive_file_id' => $driveFileId]);
                    }
                }
                $message = 'Article starred successfully';
            }

            return [
                'success' => true,
                'message' => $message,
                'article' => $article,
                'error' => null
            ];
        } catch (\Exception $e) {
            Log::error('Failed to toggle star status: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to toggle star status',
                'error' => $e->getMessage(),
                'article' => $article
            ];
        }
    }
}
