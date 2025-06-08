<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('My Articles') }}
            </h2>
            <a href="{{ route('articles.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                {{ __('Save New Article') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($articles->isEmpty())
                        <div class="text-center py-12">
                            <h3 class="text-lg font-medium text-gray-900">No articles yet</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by saving your first article.</p>
                            <div class="mt-6">
                                <a href="{{ route('articles.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                    {{ __('Save New Article') }}
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="space-y-6">
                            @foreach($articles as $article)
                                <div class="flex items-start justify-between p-4 bg-white border rounded-lg shadow-sm">
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
                                            <span>{{ $article->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-4 flex-shrink-0 flex space-x-2">
                                        @if($article->status === 'unread')
                                            <form method="POST" action="{{ route('articles.read', $article) }}">
                                                @csrf
                                                <button type="submit" class="text-sm text-gray-600 hover:text-gray-900">
                                                    Mark as Read
                                                </button>
                                            </form>
                                        @endif
                                        @if($article->status !== 'archived')
                                            <form method="POST" action="{{ route('articles.archive', $article) }}">
                                                @csrf
                                                <button type="submit" class="text-sm text-gray-600 hover:text-gray-900">
                                                    Archive
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('articles.inbox', $article) }}">
                                                @csrf
                                                <button type="submit" class="text-sm text-gray-600 hover:text-gray-900">
                                                    Move to Inbox
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            {{ $articles->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 