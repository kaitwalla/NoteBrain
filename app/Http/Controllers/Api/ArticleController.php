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

    /**
     * Toggle the star status of an article.
     */
    public function toggleStar(Article $article)
    {
        try {
            if ($article->starred) {
                // If article is starred, unstar it
                if ($article->google_drive_file_id) {
                    $this->googleDriveService->deleteFile($article->google_drive_file_id);
                    $article->update(['google_drive_file_id' => null]);
                }
                $article->unstar();
                $message = 'Article unstarred successfully';
            } else {
                // If article is not starred, star it
                $article->star();
                if (!$article->google_drive_file_id) {
                    $driveFileId = $this->googleDriveService->saveArticleText($article);
                    if ($driveFileId) {
                        $article->update(['google_drive_file_id' => $driveFileId]);
                    }
                }
                $message = 'Article starred successfully';
            }

            return response()->json([
                'message' => $message,
                'article' => $article
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to toggle star status: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to toggle star status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Unstar an article and delete it from Google Drive.
     */
    public function unstar(Article $article)
    {
        try {
            // Check if article is already unstarred
            if (!$article->starred) {
                return response()->json([
                    'message' => 'Article already unstarred',
                    'article' => $article
                ]);
            }

            // If article has a Google Drive file ID, delete it from Google Drive
            if ($article->google_drive_file_id) {
                $this->googleDriveService->deleteFile($article->google_drive_file_id);
                $article->update(['google_drive_file_id' => null]);
            }

            // Unstar the article
            $article->unstar();

            return response()->json([
                'message' => 'Article unstarred successfully',
                'article' => $article
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to unstar article: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to unstar article',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Star an article and save it to Google Drive.
     */
    public function star(Article $article)
    {
        try {
            // Check if article is already starred
            if ($article->starred) {
                return response()->json([
                    'message' => 'Article already starred',
                    'article' => $article
                ]);
            }

            // Star the article
            $article->star();

            // If article doesn't have a Google Drive file ID, save it to Google Drive
            if (!$article->google_drive_file_id) {
                $driveFileId = $this->googleDriveService->saveArticleText($article);
                if ($driveFileId) {
                    $article->update(['google_drive_file_id' => $driveFileId]);
                }
            }

            return response()->json([
                'message' => 'Article starred successfully',
                'article' => $article
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to star article: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to star article',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
