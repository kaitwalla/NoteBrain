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

    /**
     * Store a new article using JSONP to avoid CORS issues.
     * This endpoint is specifically for the bookmarklet.
     */
    public function storeJsonp(Request $request)
    {
        \Log::info('API ArticleController storeJsonp method called');
        \Log::info('Request data: ' . json_encode($request->all()));

        // Get the callback function name from the request
        $callback = $request->input('callback', 'callback');

        // Validate the request
        if (!$request->has('url') || !$request->has('token')) {
            return response()->json([
                'error' => 'Missing required parameters',
            ])->setCallback($callback);
        }

        $url = $request->input('url');
        $token = $request->input('token');

        // Authenticate the user using the token
        $tokenModel = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
        if (!$tokenModel) {
            return response()->json([
                'error' => 'Invalid token',
            ])->setCallback($callback);
        }

        // Get the user from the token
        $user = $tokenModel->tokenable;
        if (!$user) {
            return response()->json([
                'error' => 'User not found',
            ])->setCallback($callback);
        }

        // Set the authenticated user
        auth()->login($user);

        // Fetch article metadata
        $metadata = $this->fetchArticleMetadata($url);

        // Create the article
        $article = new Article([
            'url' => $url,
            'status' => Article::STATUS_INBOX,
            'user_id' => $user->id,
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
            \Log::error('User ID: ' . $user->id);
            return response()->json([
                'error' => 'Failed to save article: ' . $e->getMessage(),
            ])->setCallback($callback);
        }

        return response()->json([
            'message' => 'Article created successfully',
            'article' => $article,
        ])->setCallback($callback);
    }

    public function store(Request $request)
    {
        \Log::info('API ArticleController store method called');
        \Log::info('Request data: ' . json_encode($request->all()));
        \Log::info('Auth user: ' . (auth()->check() ? auth()->id() : 'Not authenticated'));

        $validated = $request->validate([
            'url' => 'required|url',
            'summarize' => 'boolean',
        ]);

        if (!auth()->check()) {
            \Log::error('API ArticleController: User not authenticated');
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
            // Dispatch the job to summarize the article asynchronously
            \App\Jobs\SummarizeArticle::dispatch($article);

            // Update the summarized_at timestamp immediately
            $article->update([
                'summarized_at' => now(),
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

    /**
     * Summarize an article via JSONP for the bookmarklet.
     */
    public function summarizeJsonp(Request $request, Article $article)
    {
        \Log::info('API ArticleController summarizeJsonp method called');
        \Log::info('Request data: ' . json_encode($request->all()));

        // Get the callback function name from the request
        $callback = $request->input('callback', 'callback');

        // Validate the request
        if (!$request->has('token')) {
            return response()->json([
                'error' => 'Missing required token parameter',
            ])->setCallback($callback);
        }

        $token = $request->input('token');

        // Authenticate the user using the token
        $tokenModel = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
        if (!$tokenModel) {
            return response()->json([
                'error' => 'Invalid token',
            ])->setCallback($callback);
        }

        // Get the user from the token
        $user = $tokenModel->tokenable;
        if (!$user) {
            return response()->json([
                'error' => 'User not found',
            ])->setCallback($callback);
        }

        // Set the authenticated user
        auth()->login($user);

        // Check if the article belongs to the user
        if ($article->user_id !== $user->id) {
            return response()->json([
                'error' => 'Unauthorized access to article',
            ])->setCallback($callback);
        }

        try {
            if (!$article->summary) {
                // Dispatch the job to summarize the article asynchronously
                \App\Jobs\SummarizeArticle::dispatch($article);

                // Update the summarized_at timestamp immediately
                $article->update([
                    'summarized_at' => now(),
                ]);
            } else {
                $article->update([
                    'summarized_at' => now(),
                ]);
            }

            return response()->json([
                'message' => 'Article summarization started',
                'article' => $article
            ])->setCallback($callback);
        } catch (\Exception $e) {
            \Log::error('Failed to start article summarization: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to start article summarization',
                'error' => $e->getMessage(),
            ])->setCallback($callback);
        }
    }

    public function summarize(Article $article)
    {
        if (!$article->summary) {
            // Dispatch the job to summarize the article asynchronously
            \App\Jobs\SummarizeArticle::dispatch($article);

            // Update the summarized_at timestamp immediately
            $article->update([
                'summarized_at' => now(),
            ]);
        } else {
            $article->update([
                'summarized_at' => now(),
            ]);
        }

        return response()->json([
            'message' => 'Article summarization started',
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
     * Star an article via JSONP for the bookmarklet.
     */
    public function starJsonp(Request $request, Article $article)
    {
        \Log::info('API ArticleController starJsonp method called');
        \Log::info('Request data: ' . json_encode($request->all()));

        // Get the callback function name from the request
        $callback = $request->input('callback', 'callback');

        // Validate the request
        if (!$request->has('token')) {
            return response()->json([
                'error' => 'Missing required token parameter',
            ])->setCallback($callback);
        }

        $token = $request->input('token');

        // Authenticate the user using the token
        $tokenModel = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
        if (!$tokenModel) {
            return response()->json([
                'error' => 'Invalid token',
            ])->setCallback($callback);
        }

        // Get the user from the token
        $user = $tokenModel->tokenable;
        if (!$user) {
            return response()->json([
                'error' => 'User not found',
            ])->setCallback($callback);
        }

        // Set the authenticated user
        auth()->login($user);

        // Check if the article belongs to the user
        if ($article->user_id !== $user->id) {
            return response()->json([
                'error' => 'Unauthorized access to article',
            ])->setCallback($callback);
        }

        try {
            // Check if article is already starred
            if ($article->starred) {
                return response()->json([
                    'message' => 'Article already starred',
                    'article' => $article
                ])->setCallback($callback);
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
            ])->setCallback($callback);
        } catch (\Exception $e) {
            \Log::error('Failed to star article: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to star article',
                'error' => $e->getMessage(),
            ])->setCallback($callback);
        }
    }

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
