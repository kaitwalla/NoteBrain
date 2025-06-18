<?php

namespace App\Actions;

use App\Models\Article;

class KeepUnreadArticle
{
    /**
     * Keep an article unread by moving it to inbox.
     *
     * @param Article $article The article to keep unread
     * @return array The result of the operation
     */
    public function __invoke(Article $article): array
    {
        $article->update([
            'status' => Article::STATUS_INBOX,
            'read_at' => null,
        ]);

        return [
            'success' => true,
            'message' => 'Article kept unread',
            'article' => $article,
            'error' => null
        ];
    }
}
