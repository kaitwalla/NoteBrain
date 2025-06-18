<?php

namespace App\Actions;

use App\Models\Article;
use App\Jobs\SummarizeArticle as SummarizeArticleJob;
use Illuminate\Support\Facades\Log;

class SummarizeArticle
{
    /**
     * Summarize an article.
     *
     * @param Article $article The article to summarize
     * @return array The result of the operation
     */
    public function __invoke(Article $article): array
    {
        try {
            if (!$article->summary) {
                // Dispatch the job to summarize the article asynchronously
                SummarizeArticleJob::dispatch($article);

                // Update the summarized_at timestamp immediately
                $article->update([
                    'summarized_at' => now(),
                ]);
            } else {
                $article->update([
                    'summarized_at' => now(),
                ]);
            }

            return [
                'success' => true,
                'message' => 'Article summarization started',
                'article' => $article,
                'error' => null
            ];
        } catch (\Exception $e) {
            Log::error('Failed to start article summarization: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to start article summarization',
                'error' => $e->getMessage(),
                'article' => $article
            ];
        }
    }
}
