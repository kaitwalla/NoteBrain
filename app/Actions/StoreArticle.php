<?php

namespace App\Actions;

use App\Models\Article;
use App\Jobs\SummarizeArticle as SummarizeArticleJob;
use Illuminate\Support\Facades\Log;

class StoreArticle
{
    /**
     * Store a new article.
     *
     * @param array $data The article data
     * @param int $userId The ID of the user creating the article
     * @param bool $summarize Whether to summarize the article
     * @return array The result of the operation
     */
    public function __invoke(array $data, int $userId, bool $summarize = false): array
    {
        Log::info('Creating article with user ID: ' . $userId);

        // Fetch article metadata
        $fetchMetadata = new FetchArticleMetadata();
        $metadata = $fetchMetadata($data['url']);

        if (empty($metadata)) {
            throw new \Exception('Failed to fetch article metadata');
        }

        $article = new Article([
            'url' => $data['url'],
            'status' => Article::STATUS_INBOX,
            'user_id' => $userId,
            'title' => $metadata['title'] ?? 'Untitled Article',
            'content' => $metadata['content'] ?? '',
            'author' => $metadata['author'] ?? null,
            'site_name' => $metadata['site_name'] ?? null,
            'featured_image' => $metadata['featured_image'] ?? null,
            'excerpt' => $metadata['excerpt'] ?? null,
        ]);

        try {
            $article->save();
        } catch (\Exception $e) {
            Log::error('Failed to save article: ' . $e->getMessage());
            Log::error('User ID: ' . $userId);

            return [
                'success' => false,
                'message' => 'Failed to save article',
                'error' => $e->getMessage(),
                'article' => null
            ];
        }

        if ($summarize) {
            // Dispatch the job to summarize the article asynchronously
            SummarizeArticleJob::dispatch($article);

            // Update the summarized_at timestamp immediately
            $article->update([
                'summarized_at' => now(),
            ]);
        }

        return [
            'success' => true,
            'message' => 'Article created successfully',
            'article' => $article,
            'error' => null
        ];
    }
}
