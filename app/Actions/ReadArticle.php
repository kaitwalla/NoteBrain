<?php

namespace App\Actions;

use App\Models\Article;

class ReadArticle
{
    /**
     * Mark an article as read by archiving it.
     *
     * @param Article $article The article to mark as read
     * @return array The result of the operation
     */
    public function __invoke(Article $article): array
    {
        $article->archive();

        return [
            'success' => true,
            'message' => 'Article marked as read',
            'article' => $article,
            'error' => null
        ];
    }
}
