<?php

namespace App\Actions;

use App\Models\Article;
use App\Services\ArticleSummarizer;
use Illuminate\Support\Facades\Log;

class SummarizeArticle
{
    protected $summarizer;

    /**
     * Create a new action instance.
     *
     * @param ArticleSummarizer $summarizer
     * @return void
     */
    public function __construct(ArticleSummarizer $summarizer)
    {
        $this->summarizer = $summarizer;
    }

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
                // Perform summarization synchronously
                $summary = $this->summarizer->summarize($article);

                // Update the article with the summary
                $article->update([
                    'summarized_at' => now(),
                    'summary' => $summary,
                ]);
            } else {
                $article->update([
                    'summarized_at' => now(),
                ]);
            }

            return [
                'success' => true,
                'message' => 'Article summarized successfully',
                'article' => $article,
                'error' => null
            ];
        } catch (\Exception $e) {
            Log::error('Failed to summarize article: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to summarize article',
                'error' => $e->getMessage(),
                'article' => $article
            ];
        }
    }
}
