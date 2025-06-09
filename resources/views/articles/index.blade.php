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
                    <!-- Tabs -->
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                            <a href="{{ route('articles.index', ['status' => 'inbox']) }}"
                               class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $currentStatus === 'inbox' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                Inbox
                                <span class="ml-2 bg-gray-100 text-gray-900 py-0.5 px-2 rounded-full text-xs">{{ $inboxCount }}</span>
                            </a>
                            <a href="{{ route('articles.index', ['status' => 'archived']) }}"
                               class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $currentStatus === 'archived' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                Archived
                                <span class="ml-2 bg-gray-100 text-gray-900 py-0.5 px-2 rounded-full text-xs">{{ $archivedCount }}</span>
                            </a>
                        </nav>
                    </div>

                    <!-- Articles List -->
                    @if($articles->isEmpty())
                        <div class="text-center py-12">
                            <h3 class="text-lg font-medium text-gray-900">No articles in {{ ucfirst($currentStatus) }}</h3>
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
                                            <span>{{ $article->created_at ? $article->created_at->diffForHumans() : 'Unknown date' }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-4 flex-shrink-0 flex space-x-2">
                                        @if($article->status === 'inbox')
                                            @if(!$article->summary)
                                                <form method="POST" action="{{ route('articles.summarize', $article) }}" class="summarize-form">
                                                    @csrf
                                                    <button type="submit" class="summarize-button p-1 text-gray-600 hover:text-gray-900 rounded-full" title="Summarize">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('articles.archive', $article) }}">
                                                @csrf
                                                <button type="submit" class="p-1 text-gray-600 hover:text-gray-900 rounded-full" title="Archive">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('articles.inbox', $article) }}">
                                                @csrf
                                                <button type="submit" class="p-1 text-gray-600 hover:text-gray-900 rounded-full" title="Move to Inbox">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            {{ $articles->appends(['status' => $currentStatus])->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add event listeners for all summarize forms
            const summarizeForms = document.querySelectorAll('.summarize-form');
            summarizeForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const button = this.querySelector('.summarize-button');
                    if (button) {
                        // Disable the button and show loading state
                        button.disabled = true;
                        button.innerHTML = '<span class="inline-flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Summarizing...</span>';
                    }
                });
            });
        });
    </script>
    @endpush
</x-app-layout>
