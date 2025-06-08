<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        $unreadCount = Article::where('user_id', $user->id)
            ->whereIn('status', ['unread', 'inbox'])
            ->count();

        $readCount = Article::where('user_id', $user->id)
            ->where('status', 'read')
            ->count();

        $archivedCount = Article::where('user_id', $user->id)
            ->where('status', 'archived')
            ->count();

        $recentArticles = Article::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'unreadCount',
            'readCount',
            'archivedCount',
            'recentArticles'
        ));
    }
} 