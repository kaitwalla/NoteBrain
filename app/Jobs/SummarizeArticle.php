<?php

namespace App\Jobs;

use App\Models\Article;
use App\Services\ArticleSummarizer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SummarizeArticle implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The article instance.
     *
     * @var \App\Models\Article
     */
    protected $article;

    /**
     * Create a new job instance.
     *
     * @param  \App\Models\Article  $article
     * @return void
     */
    public function __construct(Article $article)
    {
        $this->article = $article;
    }

    /**
     * Execute the job.
     *
     * @param  \App\Services\ArticleSummarizer  $summarizer
     * @return void
     */
    public function handle(ArticleSummarizer $summarizer)
    {
        Log::info('Starting to summarize article', ['article_id' => $this->article->id]);

        // Update the article with the summary
        $summary = $summarizer->summarize($this->article);

        $this->article->update([
            'summarized_at' => now(),
            'summary' => $summary,
        ]);

        Log::info('Article summarization completed successfully', ['article_id' => $this->article->id]);
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        Log::error('Article summarization failed', [
            'article_id' => $this->article->id,
            'exception' => $exception->getMessage(),
        ]);
    }
}
