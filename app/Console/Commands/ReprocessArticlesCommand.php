<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Services\HtmlToMarkdownConverter;
use Illuminate\Console\Command;

class ReprocessArticlesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'articles:reprocess';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reprocess all existing articles to update their Markdown content with the new paragraph handling';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $htmlConverter = app(HtmlToMarkdownConverter::class);
        $articles = Article::all();
        $count = $articles->count();

        $this->info("Starting to reprocess {$count} articles...");
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        foreach ($articles as $article) {
            // Update content_markdown
            if ($article->content) {
                $article->content_markdown = $htmlConverter->convert($article->content);
            }

            // Update excerpt_markdown
            if ($article->excerpt) {
                $article->excerpt_markdown = $htmlConverter->convert($article->excerpt);
            }

            // Update summary_markdown
            if ($article->summary) {
                $article->summary_markdown = $htmlConverter->convert($article->summary);
            }

            // Save without triggering the model's saving event
            $article->saveQuietly();

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Successfully reprocessed {$count} articles.");

        return Command::SUCCESS;
    }
}
