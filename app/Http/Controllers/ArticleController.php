<?php

namespace App\Http\Controllers;

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

    /**
     * Display a listing of the articles.
     */
    public function index(Request $request)
    {
        $perPage = $request->user()->preferences->article_preferences['articles_per_page'] ?? 20;
        $status = $request->get('status', 'inbox');

        $query = Article::query();

        if ($status === 'inbox') {
            $query->where('status', 'inbox');
        } elseif ($status === 'archived') {
            $query->where('status', 'archived');
        }

        $query->orderBy('created_at', 'desc');

        $articles = $query->paginate($perPage);

        return view('articles.index', [
            'articles' => $articles,
            'currentStatus' => $status,
            'inboxCount' => Article::where('status', 'inbox')->count(),
            'archivedCount' => Article::where('status', 'archived')->count(),
        ]);
    }

    /**
     * Show the form for saving a new article.
     */
    public function create(Request $request)
    {
        return view('articles.create', [
            'url' => $request->query('url')
        ]);
    }

    /**
     * Save a new article.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'url' => 'required|url',
            'summarize' => 'boolean',
        ]);

        if (!auth()->check()) {
            return redirect()->route('login');
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

            // Google Drive integration has been disabled for article save
            // Articles are only saved to Google Drive when starred

        } catch (\Exception $e) {
            \Log::error('Failed to save article: ' . $e->getMessage());
            \Log::error('User ID: ' . $userId);
            throw $e;
        }

        if ($request->boolean('summarize')) {
            // Dispatch the job to summarize the article asynchronously
            \App\Jobs\SummarizeArticle::dispatch($article);

            // Update the summarized_at timestamp immediately
            $article->update([
                'summarized_at' => now(),
            ]);
        }

        return redirect()->route('articles.show', $article)
            ->with('success', 'Article saved successfully.');
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

    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(['inbox', 'read', 'archived', 'deleted'])],
        ]);

        $article->update($validated);

        return redirect()->route('dashboard')->with('success', 'Article status updated successfully.');
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

        return redirect()->route('articles.index')->with('success', 'Article summarization started.');
    }

    /**
     * Display the specified article.
     */
    public function show(Article $article)
    {
        $this->authorize('view', $article);

        // Share the article with the layout
        view()->share('article', $article);

        return view('articles.show', compact('article'));
    }

    /**
     * Move an article to inbox.
     */
    public function inbox(Article $article)
    {
        $article->moveToInbox();
        return redirect()->back()->with('success', 'Article moved to inbox.');
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
        $this->authorize('update', $article);

        $article->archive();

        return back()->with('success', 'Article archived.');
    }

    /**
     * Archive an article.
     */
    public function archive(Article $article)
    {
        $article->archive();
        return redirect()->back()->with('success', 'Article archived.');
    }

    public function destroy(Article $article)
    {
        $this->authorize('delete', $article);

        // Delete Google Drive document if it exists
        if ($article->google_drive_file_id) {
            $this->googleDriveService->deleteFile($article->google_drive_file_id);
        }

        $article->delete();
        return redirect()->route('articles.index')->with('success', 'Article deleted successfully.');
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

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Failed to toggle star status: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to toggle star status');
        }
    }

    /**
     * Handle bulk actions on multiple articles.
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|string|in:archive,inbox,star,unstar,delete',
            'article_ids' => 'required|array',
            'article_ids.*' => 'exists:articles,id',
        ]);

        $action = $validated['action'];
        $articleIds = $validated['article_ids'];
        $count = count($articleIds);
        $successMessage = '';

        try {
            // Get articles that belong to the current user
            $articles = Article::whereIn('id', $articleIds)
                ->where('user_id', auth()->id())
                ->get();

            if ($articles->isEmpty()) {
                return redirect()->back()->with('error', 'No valid articles selected');
            }

            switch ($action) {
                case 'archive':
                    foreach ($articles as $article) {
                        $article->archive();
                    }
                    $successMessage = $count . ' article(s) archived successfully';
                    break;

                case 'inbox':
                    foreach ($articles as $article) {
                        $article->moveToInbox();
                    }
                    $successMessage = $count . ' article(s) moved to inbox successfully';
                    break;

                case 'star':
                    foreach ($articles as $article) {
                        if (!$article->starred) {
                            $article->star();
                            // Save to Google Drive if not already saved
                            if (!$article->google_drive_file_id) {
                                $driveFileId = $this->googleDriveService->saveArticleText($article);
                                if ($driveFileId) {
                                    $article->update(['google_drive_file_id' => $driveFileId]);
                                }
                            }
                        }
                    }
                    $successMessage = $count . ' article(s) starred successfully';
                    break;

                case 'unstar':
                    foreach ($articles as $article) {
                        if ($article->starred) {
                            // Delete from Google Drive if saved
                            if ($article->google_drive_file_id) {
                                $this->googleDriveService->deleteFile($article->google_drive_file_id);
                                $article->update(['google_drive_file_id' => null]);
                            }
                            $article->unstar();
                        }
                    }
                    $successMessage = $count . ' article(s) unstarred successfully';
                    break;

                case 'delete':
                    foreach ($articles as $article) {
                        // Delete Google Drive document if it exists
                        if ($article->google_drive_file_id) {
                            $this->googleDriveService->deleteFile($article->google_drive_file_id);
                        }
                        $article->delete();
                    }
                    $successMessage = $count . ' article(s) deleted successfully';
                    break;
            }

            return redirect()->back()->with('success', $successMessage);
        } catch (\Exception $e) {
            \Log::error('Failed to perform bulk action: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to perform bulk action');
        }
    }
}
