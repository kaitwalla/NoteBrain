<?php

namespace App\Actions;

use App\Models\Article;
use App\Jobs\SummarizeArticle as SummarizeArticleJob;
use App\Services\HtmlToMarkdownConverter;
use Illuminate\Support\Facades\Log;

class StoreArticle
{
    protected $htmlConverter;

    public function __construct(HtmlToMarkdownConverter $htmlConverter)
    {
        $this->htmlConverter = $htmlConverter;
    }
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

        // Fetch article metadata to get the final URL after redirects
        $fetchMetadata = new FetchArticleMetadata();
        $metadata = $fetchMetadata($data['url']);

        // Even if metadata is empty, we'll still create the article with the URL
        // This prevents failures when we can't fetch metadata but still have the URL
        if (empty($metadata)) {
            Log::warning('Failed to fetch article metadata for URL: ' . $data['url'] . '. Creating article with minimal information.');
            $metadata = [];
        }

        // Use the final URL after redirects if available, otherwise use the original URL
        $finalUrl = $metadata['final_url'] ?? $data['url'];

        // Check if article with this URL already exists for this user
        $existingArticle = Article::where('user_id', $userId)
            ->where('url', $finalUrl)
            ->first();

        if ($existingArticle) {
            return [
                'success' => false,
                'message' => 'Article with this URL already exists',
                'error' => 'Duplicate URL',
                'article' => $existingArticle
            ];
        }

        // Check if content is blank
        if (empty($metadata['content'])) {
            return [
                'success' => false,
                'message' => 'Cannot save article with blank content',
                'error' => 'Blank content',
                'article' => null
            ];
        }

        // Convert HTML content to Markdown
        $content = $metadata['content'] ?? '';
        $excerpt = $metadata['excerpt'] ?? null;

        $article = new Article([
            'url' => $finalUrl,
            'status' => Article::STATUS_INBOX,
            'user_id' => $userId,
            'title' => $metadata['title'] ?? 'Untitled Article',
            'content' => $content,
            'content_json' => $this->htmlConverter->convert($content),
            'author' => $metadata['author'] ?? null,
            'site_name' => $metadata['site_name'] ?? null,
            'featured_image' => $metadata['featured_image'] ?? null,
            'excerpt' => $excerpt,
            'excerpt_json' => $this->htmlConverter->convert($excerpt),
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
