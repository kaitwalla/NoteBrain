<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\ArticleSummarizer;
use App\Services\GoogleDriveService;
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

    public function listAll()
    {
        $articles = Article::where('user_id', auth()->id())->where('status', 'inbox')->get();
        return response()->json($articles);
    }

    /**
     * List archived articles with pagination.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listArchived(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = 20;

        $articles = Article::where('user_id', auth()->id())
            ->where('status', Article::STATUS_ARCHIVED)
            ->orderBy('archived_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json($articles);
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
        $fetchMetadata = new \App\Actions\FetchArticleMetadata();
        $metadata = $fetchMetadata($url);

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

        $storeArticle = new \App\Actions\StoreArticle();
        $result = $storeArticle($validated, $userId, $request->boolean('summarize'));

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
                'error' => $result['error'],
            ], 500);
        }

        return response()->json([
            'message' => $result['message'],
            'article' => $result['article'],
        ], 201);
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
        $summarizeArticle = new \App\Actions\SummarizeArticle();
        $result = $summarizeArticle($article);

        return response()->json([
            'message' => $result['message'],
            'article' => $result['article']
        ]);
    }

    public function keepUnread(Article $article)
    {
        $keepUnreadArticle = new \App\Actions\KeepUnreadArticle();
        $result = $keepUnreadArticle($article);

        return response()->json([
            'message' => $result['message'],
            'article' => $result['article']
        ]);
    }

    public function read(Article $article)
    {
        $readArticle = new \App\Actions\ReadArticle();
        $result = $readArticle($article);

        return response()->json([
            'message' => $result['message'],
            'article' => $result['article']
        ]);
    }

    /**
     * Toggle the star status of an article.
     */
    public function toggleStar(Article $article)
    {
        $toggleStarArticle = app(\App\Actions\ToggleStarArticle::class);
        $result = $toggleStarArticle($article);

        if ($result['success']) {
            return response()->json([
                'message' => $result['message'],
                'article' => $result['article']
            ]);
        } else {
            return response()->json([
                'message' => $result['message'],
                'error' => $result['error'],
            ], 500);
        }
    }

    /**
     * Unstar an article and delete it from Google Drive.
     */
    public function unstar(Article $article)
    {
        $unstarArticle = app(\App\Actions\UnstarArticle::class);
        $result = $unstarArticle($article);

        if ($result['success']) {
            return response()->json([
                'message' => $result['message'],
                'article' => $result['article']
            ]);
        } else {
            return response()->json([
                'message' => $result['message'],
                'error' => $result['error'],
            ], 500);
        }
    }

    /**
     * Archive an article.
     */
    public function archive(Article $article)
    {
        $archiveArticle = app(\App\Actions\ArchiveArticle::class);
        $result = $archiveArticle($article);

        if ($result['success']) {
            return response()->json([
                'message' => $result['message'],
                'article' => $result['article']
            ]);
        } else {
            return response()->json([
                'message' => $result['message'],
                'error' => $result['error'],
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

        $starArticle = app(\App\Actions\StarArticle::class);
        $result = $starArticle($article);

        if ($result['success']) {
            return response()->json([
                'message' => $result['message'],
                'article' => $result['article']
            ])->setCallback($callback);
        } else {
            return response()->json([
                'message' => $result['message'],
                'error' => $result['error'],
            ])->setCallback($callback);
        }
    }

    public function star(Article $article)
    {
        $starArticle = app(\App\Actions\StarArticle::class);
        $result = $starArticle($article);

        if ($result['success']) {
            return response()->json([
                'message' => $result['message'],
                'article' => $result['article']
            ]);
        } else {
            return response()->json([
                'message' => $result['message'],
                'error' => $result['error'],
            ], 500);
        }
    }
}
