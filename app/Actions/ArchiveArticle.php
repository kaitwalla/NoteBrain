<?php

namespace App\Actions;

use App\Models\Article;

class ArchiveArticle
{
    /**
     * Archive an article.
     *
     * @param Article $article The article to archive
     * @return array The result of the operation
     */
    public function __invoke(Article $article): array
    {
        $article->archive();

        return [
            'success' => true,
            'message' => 'Article archived',
            'article' => $article,
            'error' => null
        ];
    }
}
