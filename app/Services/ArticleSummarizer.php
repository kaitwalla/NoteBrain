<?php

namespace App\Services;

use App\Models\Article;
use OpenAI\Laravel\Facades\OpenAI;

class ArticleSummarizer
{
    public function summarize(Article $article): string
    {
        $response = OpenAI::chat()->create([
            'model' => 'gpt-4',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a helpful assistant that summarizes articles. Your goal is to capture all the important ideas and insights from the article, even if that means the summary is longer than usual. Focus on expressing the complete thoughts and arguments rather than being concise. Include key quotes and examples that illustrate the main points.'
                ],
                [
                    'role' => 'user',
                    'content' => "Please provide a comprehensive summary of this article:\n\nTitle: {$article->title}\n\nContent: {$article->content}"
                ]
            ],
            'temperature' => 0.7,
            'max_tokens' => 2000,
        ]);

        return $response->choices[0]->message->content;
    }
} 