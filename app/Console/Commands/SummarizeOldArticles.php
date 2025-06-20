<?php

namespace App\Console\Commands;

use App\Jobs\SummarizeArticle;
use App\Models\Article;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SummarizeOldArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'articles:summarize-old';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Summarize articles that are older than 3 weeks and don\'t have a summary';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $threeWeeksAgo = Carbon::now()->subWeeks(3);

        $articles = Article::where('created_at', '<', $threeWeeksAgo)
            ->whereNull('summary')
            ->whereNull('summarized_at')
            ->get();

        $count = $articles->count();

        $this->info("Found {$count} articles to summarize");
        Log::info("Starting to summarize {$count} old articles");

        foreach ($articles as $article) {
            $this->info("Dispatching summarization job for article ID: {$article->id}");
            SummarizeArticle::dispatch($article);
        }

        Log::info("Completed dispatching summarization jobs for {$count} old articles");
        $this->info("Completed dispatching summarization jobs");

        return Command::SUCCESS;
    }
}
