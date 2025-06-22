<?php

namespace App\Actions;

use App\Models\Article;

class DeleteArticle
{
    /**
     * Delete an article (set status to deleted).
     *
     * @param Article $article The article to delete
     * @return array The result of the operation
     */
    public function __invoke(Article $article): array
    {
        try {
            $article->delete();

            return [
                'success' => true,
                'message' => 'Article deleted',
                'article' => $article,
                'error' => null
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete article',
                'article' => null,
                'error' => $e->getMessage()
            ];
        }
    }
}
