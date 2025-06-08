<?php

namespace App\Http\Controllers;

use App\Actions\SaveArticle;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ArticleController extends Controller
{
    /**
     * Display a listing of the articles.
     */
    public function index()
    {
        $articles = Article::where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('articles.index', compact('articles'));
    }

    /**
     * Show the form for saving a new article.
     */
    public function create()
    {
        return view('articles.create');
    }

    /**
     * Save a new article.
     */
    public function store(Request $request, SaveArticle $saveArticle)
    {
        $request->validate([
            'url' => ['required', 'url'],
        ]);

        $article = $saveArticle($request->url, Auth::user());

        if (!$article) {
            return back()->with('error', 'Failed to save article. Please check the URL and try again.');
        }

        return redirect()->route('articles.show', $article)
            ->with('success', 'Article saved successfully!');
    }

    /**
     * Display the specified article.
     */
    public function show(Article $article)
    {
        $this->authorize('view', $article);
        
        // Mark the article as read if it's not already read
        if ($article->status !== 'read') {
            $article->markAsRead();
        }
        
        // Share the article with the layout
        view()->share('article', $article);
        
        return view('articles.show', compact('article'));
    }

    /**
     * Mark an article as read.
     */
    public function markAsRead(Article $article)
    {
        $this->authorize('update', $article);
        
        $article->markAsRead();
        
        return back()->with('success', 'Article marked as read.');
    }

    /**
     * Archive an article.
     */
    public function archive(Article $article)
    {
        $this->authorize('update', $article);
        
        $article->archive();
        
        return back()->with('success', 'Article archived.');
    }

    /**
     * Move an article to inbox.
     */
    public function moveToInbox(Article $article)
    {
        $this->authorize('update', $article);
        
        $article->moveToInbox();
        
        return back()->with('success', 'Article moved to inbox.');
    }

    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(['inbox', 'read', 'archived', 'deleted'])],
        ]);

        $article->update($validated);

        return redirect()->route('dashboard')->with('success', 'Article status updated successfully.');
    }
} 