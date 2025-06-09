<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Quick Actions -->
                        <div class="bg-white p-6 rounded-lg border">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                            <div class="space-y-4">
                                <a href="{{ route('articles.create') }}" class="block w-full text-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                    Save New Article
                                </a>
                                <a href="{{ route('articles.index') }}" class="block w-full text-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                                    View All Articles
                                </a>
                            </div>
                        </div>

                        <!-- Article Stats -->
                        <div class="bg-white p-6 rounded-lg border">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Article Stats</h3>
                            <div class="grid grid-cols-3 gap-4">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-gray-900">{{ $inboxCount }}</div>
                                    <div class="text-sm text-gray-500">Inbox</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-gray-900">{{ $summarizeCount }}</div>
                                    <div class="text-sm text-gray-500">Summarized</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-gray-900">{{ $archivedCount }}</div>
                                    <div class="text-sm text-gray-500">Archived</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Articles -->
                    <div class="mt-8">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-medium text-gray-900">Inbox</h2>
                            <a href="{{ route('articles.index', ['status' => 'inbox']) }}" class="text-sm font-medium text-gray-700 hover:text-gray-900">
                                View all {{ $inboxCount }} articles
                            </a>
                        </div>

                        <div class="mt-4 space-y-4">
                            @forelse($recentArticles as $article)
                                <div class="bg-white shadow rounded-lg p-4">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-lg font-medium text-gray-900 truncate">
                                                <a href="{{ route('articles.show', $article) }}" class="hover:underline">
                                                    {{ $article->title }}
                                                </a>
                                            </h3>
                                            <p class="mt-1 text-sm text-gray-500">
                                                {{ $article->excerpt }}
                                            </p>
                                            <div class="mt-2 flex items-center text-sm text-gray-500">
                                                @if($article->author)
                                                    <span>{{ $article->author }}</span>
                                                    <span class="mx-2">•</span>
                                                @endif
                                                @if($article->site_name)
                                                    <span>{{ $article->site_name }}</span>
                                                    <span class="mx-2">•</span>
                                                @endif
                                                <span>{{ $article->created_at ? $article->created_at->diffForHumans() : 'Unknown date' }}</span>
                                            </div>
                                        </div>
                                        <div class="ml-4 flex-shrink-0 flex space-x-2">
                                            <form method="POST" action="{{ route('articles.summarize', $article) }}">
                                                @csrf
                                                <button type="submit" class="text-sm text-gray-600 hover:text-gray-900">
                                                    Mark for Summarization
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('articles.archive', $article) }}">
                                                @csrf
                                                <button type="submit" class="text-sm text-gray-600 hover:text-gray-900">
                                                    Archive
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-12">
                                    <h3 class="text-lg font-medium text-gray-900">No articles in inbox</h3>
                                    <p class="mt-1 text-sm text-gray-500">Get started by saving your first article.</p>
                                    <div class="mt-6">
                                        <a href="{{ route('articles.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                            {{ __('Save New Article') }}
                                        </a>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
</x-app-layout>
