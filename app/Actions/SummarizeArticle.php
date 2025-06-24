<?php

namespace App\Actions;

use App\Models\Article;
use App\Services\ArticleSummarizer;
use App\Services\HtmlToMarkdownConverter;
use Illuminate\Support\Facades\Log;

class SummarizeArticle
{
    protected $summarizer;
    protected $htmlConverter;

    /**
     * Create a new action instance.
     *
     * @param ArticleSummarizer $summarizer
     * @param HtmlToMarkdownConverter $htmlConverter
     * @return void
     */
    public function __construct(ArticleSummarizer $summarizer, HtmlToMarkdownConverter $htmlConverter)
    {
        $this->summarizer = $summarizer;
        $this->htmlConverter = $htmlConverter;
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

                // Convert summary HTML to Markdown
                $summaryMarkdown = $this->htmlConverter->convert($summary);

                // Update the article with the summary and its Markdown representation
                $article->update([
                    'summarized_at' => now(),
                    'summary' => $summary,
                    'summary_json' => $summaryMarkdown,
                ]);
            } else {
                // If the article already has a summary but no Markdown representation, create it
                if (!$article->summary_json) {
                    $summaryMarkdown = $this->htmlConverter->convert($article->summary);
                    $article->update([
                        'summarized_at' => now(),
                        'summary_json' => $summaryMarkdown,
                    ]);
                } else {
                    $article->update([
                        'summarized_at' => now(),
                    ]);
                }
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
