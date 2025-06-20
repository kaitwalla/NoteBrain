<?php

namespace App\Actions;

use App\Models\Article;
use Illuminate\Support\Facades\Log;

class DeleteArticleSummary
{
    /**
     * Delete the summary of an article.
     *
     * @param  \App\Models\Article  $article
     * @return array
     */
    public function __invoke(Article $article)
    {
        try {
            // Check if the article has a summary
            if (!$article->summary) {
                return [
                    'success' => false,
                    'message' => 'Article does not have a summary',
                    'article' => $article,
                ];
            }

            // Update the article to remove the summary
            $article->update([
                'summary' => null,
                'summarized_at' => null,
            ]);

            Log::info('Article summary deleted successfully', ['article_id' => $article->id]);

            return [
                'success' => true,
                'message' => 'Article summary deleted successfully',
                'article' => $article,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to delete article summary', [
                'article_id' => $article->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to delete article summary',
                'error' => $e->getMessage(),
                'article' => $article,
            ];
        }
    }
}
