<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        $user = auth()->user();
        $inboxCount = Article::where('user_id', $user->id)
            ->where('status', 'inbox')
            ->count();
        $archivedCount = Article::where('user_id', $user->id)
            ->where('status', 'archived')
            ->count();
        $summarizeCount = Article::where('user_id', $user->id)
            ->where('status', 'summarize')
            ->count();
        $recentArticles = Article::where('user_id', $user->id)
            ->where('status', 'inbox')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Get note counts
        $noteInboxCount = Note::where('user_id', $user->id)
            ->where('status', Note::STATUS_INBOX)
            ->count();
        $noteArchivedCount = Note::where('user_id', $user->id)
            ->where('status', Note::STATUS_ARCHIVED)
            ->count();

        return view('dashboard', compact(
            'inboxCount',
            'archivedCount',
            'summarizeCount',
            'recentArticles',
            'noteInboxCount',
            'noteArchivedCount'
        ));
    }
}
