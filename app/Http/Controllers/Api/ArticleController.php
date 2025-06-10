<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\ArticleSummarizer;
use App\Services\GoogleDriveService;
use fivefilters\Readability\Configuration;
use fivefilters\Readability\ParseException;
use fivefilters\Readability\Readability;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ArticleController extends Controller
{
    protected $summarizer;
    protected $googleDriveService;

    public function __construct(ArticleSummarizer $summarizer, GoogleDriveService $googleDriveService)
    {
        $this->summarizer = $summarizer;
        $this->googleDriveService = $googleDriveService;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'url' => 'required|url',
            'summarize' => 'boolean',
        ]);

        if (!auth()->check()) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }

        $userId = auth()->id();
        \Log::info('Creating article with user ID: ' . $userId);

        // Fetch article metadata
        $metadata = $this->fetchArticleMetadata($validated['url']);

        $article = new Article([
            'url' => $validated['url'],
            'status' => Article::STATUS_INBOX,
            'user_id' => $userId,
            'title' => $metadata['title'] ?? 'Untitled Article',
            'content' => $metadata['content'] ?? '',
            'author' => $metadata['author'] ?? null,
            'site_name' => $metadata['site_name'] ?? null,
            'featured_image' => $metadata['featured_image'] ?? null,
            'excerpt' => $metadata['excerpt'] ?? null,
        ]);

        try {
            $article->save();

            // Save article text to Google Drive
            $driveFileId = $this->googleDriveService->saveArticleText($article);
            if ($driveFileId) {
                $article->update(['google_drive_file_id' => $driveFileId]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to save article: ' . $e->getMessage());
            \Log::error('User ID: ' . $userId);
            return response()->json([
                'message' => 'Failed to save article',
                'error' => $e->getMessage(),
            ], 500);
        }

        if ($request->boolean('summarize')) {
            $article->update([
                'summarized_at' => now(),
                'summary' => $this->summarizer->summarize($article),
            ]);
        }

        return response()->json([
            'message' => 'Article created successfully',
            'article' => $article,
        ], 201);
    }

    public function keepUnread(Article $article)
    {
        $article->update([
            'status' => Article::STATUS_INBOX,
            'read_at' => null,
        ]);

        return response()->json([
            'message' => 'Article kept unread',
            'article' => $article
        ]);
    }

    public function read(Article $article)
    {
        $article->archive();

        return response()->json([
            'message' => 'Article marked as read',
            'article' => $article
        ]);
    }

    public function summarize(Article $article)
    {
        if (!$article->summary) {
            $article->update([
                'summarized_at' => now(),
                'summary' => $this->summarizer->summarize($article),
            ]);
        } else {
            $article->update([
                'summarized_at' => now(),
            ]);
        }

        return response()->json([
            'message' => 'Article summarized successfully',
            'article' => $article
        ]);
    }

    private function fetchArticleMetadata(string $url): array
    {
        try {
            $html = file_get_contents($url);
            if ($html === false) {
                return [];
            }

            $readability = new Readability(new Configuration());
            $readability->parse($html);

            return [
                'title' => $readability->getTitle(),
                'content' => $readability->getContent(),
                'author' => $readability->getAuthor(),
                'site_name' => $readability->getSiteName(),
                'featured_image' => $readability->getImage(),
                'excerpt' => $readability->getExcerpt(),
            ];
        } catch (ParseException $e) {
            \Log::error('Failed to parse article: ' . $e->getMessage());
            return [];
        } catch (\Exception $e) {
            \Log::error('Failed to fetch article metadata: ' . $e->getMessage());
            return [];
        }
    }
}
