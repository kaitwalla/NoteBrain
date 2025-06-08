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
                                    <div class="text-2xl font-bold text-gray-900">{{ $unreadCount }}</div>
                                    <div class="text-sm text-gray-500">Unread</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-gray-900">{{ $readCount }}</div>
                                    <div class="text-sm text-gray-500">Read</div>
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
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Recent Articles</h3>
                            <a href="{{ route('articles.index') }}" class="text-sm text-gray-600 hover:text-gray-900">View all</a>
                        </div>
                        
                        @if($recentArticles->isEmpty())
                            <div class="text-center py-8 bg-gray-50 rounded-lg">
                                <p class="text-gray-500">No articles yet. Start by saving your first article!</p>
                            </div>
                        @else
                            <div class="space-y-4">
                                @foreach($recentArticles as $article)
                                    <div class="flex items-start justify-between p-4 bg-white border rounded-lg shadow-sm">
                                        <div class="flex-1 min-w-0">
                                            <h4 class="text-lg font-medium text-gray-900 truncate">
                                                <a href="{{ route('articles.show', $article) }}" class="hover:underline">
                                                    {{ $article->title }}
                                                </a>
                                            </h4>
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
                                                <span>{{ $article->created_at->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                        <div class="ml-4 flex-shrink-0">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($article->status === 'unread') bg-yellow-100 text-yellow-800
                                                @elseif($article->status === 'read') bg-green-100 text-green-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                {{ ucfirst($article->status) }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
