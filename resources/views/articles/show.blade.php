<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $article->title }}
            </h2>
            <div class="flex space-x-4">
                @if($article->status === 'unread')
                    <form method="POST" action="{{ route('articles.archive', $article) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            Archive
                        </button>
                    </form>
                @endif
                @if($article->status !== 'archived')
                    <form method="POST" action="{{ route('articles.archive', $article) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            Archive
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('articles.inbox', $article) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            Move to Inbox
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Article Content -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <article class="prose prose-xl max-w-none" id="article-content">
                        @if($article->featured_image)
                            <div class="mb-8">
                                <img src="{{ $article->featured_image }}" alt="{{ $article->title }}" class="w-full h-96 object-cover rounded-lg shadow-lg">
                            </div>
                        @endif

                        <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ $article->title }}</h1>

                        <div class="flex items-center text-sm text-gray-500 mb-8">
                            @if($article->author)
                                <span class="font-medium">{{ $article->author }}</span>
                                <span class="mx-2">•</span>
                            @endif
                            @if($article->site_name)
                                <span>{{ $article->site_name }}</span>
                                <span class="mx-2">•</span>
                            @endif
                            <span>{{ $article->created_at->format('F j, Y') }}</span>
                        </div>

                        <div class="prose max-w-none">
                            {!! $article->content !!}
                        </div>

                        @if($article->summary)
                            <div class="mt-12 border-t border-gray-200 pt-8">
                                <h2 class="text-2xl font-bold text-gray-900 mb-4">Summary</h2>
                                <div class="prose max-w-none bg-gray-50 p-6 rounded-lg">
                                    {!! nl2br(e($article->summary)) !!}
                                </div>
                            </div>
                        @endif
                    </article>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .article-content {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        .article-content h1 {
            font-size: 2.5rem;
            margin-top: 2.5rem;
            margin-bottom: 2rem;
        }
        .article-content h2 {
            font-size: 2rem;
            margin-top: 2rem;
            margin-bottom: 1.5rem;
        }
        .article-content h3 {
            font-size: 1.75rem;
            margin-top: 1.75rem;
            margin-bottom: 1.25rem;
        }
        .article-content p {
            margin-bottom: 2rem;
            line-height: 1.8;
        }
        .article-content ul, .article-content ol {
            margin-bottom: 2rem;
            padding-left: 1.5rem;
        }
        .article-content li {
            margin-bottom: 0.75rem;
        }
        .article-content blockquote {
            margin: 2rem 0;
            padding: 1.5rem 2rem;
            background-color: #f9fafb;
            border-left: 4px solid #e5e7eb;
            font-size: 1.25rem;
        }
        .article-content pre {
            margin: 2rem 0;
            padding: 1.5rem;
            background-color: #f3f4f6;
            border-radius: 0.375rem;
            overflow-x: auto;
            font-size: 1rem;
        }
        .article-content code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 0.875em;
            padding: 0.2em 0.4em;
            background-color: #f3f4f6;
            border-radius: 0.25rem;
        }
        .article-content img {
            margin: 2.5rem 0;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .article-content a {
            color: #2563eb;
            text-decoration: none;
        }
        .article-content a:hover {
            text-decoration: underline;
        }
        .article-content hr {
            margin: 2.5rem 0;
            border: 0;
            border-top: 1px solid #e5e7eb;
        }

        /* Custom range input styling */
        input[type="range"] {
            -webkit-appearance: none;
            appearance: none;
            height: 6px;
            background: #e5e7eb;
            border-radius: 3px;
            outline: none;
        }

        input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 18px;
            height: 18px;
            background: #4f46e5;
            border-radius: 50%;
            cursor: pointer;
            transition: background 0.15s ease-in-out;
        }

        input[type="range"]::-webkit-slider-thumb:hover {
            background: #4338ca;
        }

        input[type="range"]::-moz-range-thumb {
            width: 18px;
            height: 18px;
            background: #4f46e5;
            border-radius: 50%;
            cursor: pointer;
            transition: background 0.15s ease-in-out;
            border: none;
        }

        input[type="range"]::-moz-range-thumb:hover {
            background: #4338ca;
        }

        /* Focus styles */
        input[type="range"]:focus {
            outline: none;
        }

        input[type="range"]:focus::-webkit-slider-thumb {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
        }

        input[type="range"]:focus::-moz-range-thumb {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
        }

        /* Slide-over menu styles */
        #controls-menu {
            z-index: 50;
        }

        #controls-menu .bg-white {
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
        }

        #controls-menu.active .bg-white {
            transform: translateX(0);
        }

        #controls-menu .bg-opacity-75 {
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }

        #controls-menu.active .bg-opacity-75 {
            opacity: 1;
        }

        /* Ensure the navbar stays on top of everything */
        .sticky {
            position: sticky;
            top: 0;
            z-index: 50;
        }

        /* Add a subtle transition for the shadow */
        .sticky {
            transition: box-shadow 0.2s ease-in-out;
        }

        /* Add a more pronounced shadow when scrolling */
        .sticky.shadow-scrolled {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
    </style>
    @endpush

</x-app-layout> 