<?php

namespace App\Console\Commands;

use App\Actions\SaveArticle;
use App\Models\User;
use Illuminate\Console\Command;

class SaveArticleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'article:save 
                            {url : The URL of the article to save}
                            {--user= : The ID of the user saving the article}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Save an article from a URL';

    /**
     * Execute the console command.
     */
    public function handle(SaveArticle $saveArticle)
    {
        $url = $this->argument('url');
        $userId = $this->option('user');

        if (!$userId) {
            $userId = $this->ask('Enter the user ID');
        }

        $user = User::find($userId);
        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return 1;
        }

        $this->info("Saving article from: {$url}");
        
        $article = $saveArticle($url, $user);

        if ($article) {
            $this->info('Article saved successfully!');
            $this->table(
                ['Title', 'Author', 'Site'],
                [[$article->title, $article->author, $article->site_name]]
            );
            return 0;
        }

        $this->error('Failed to save article.');
        return 1;
    }
}
