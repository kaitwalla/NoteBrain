<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $article->title }}
            </h2>
            <div class="flex space-x-4">
                <!-- Star Toggle Button -->
                <form method="POST" action="{{ route('articles.toggle-star', $article) }}" class="star-form">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 {{ $article->starred ? 'bg-yellow-500' : 'bg-gray-800' }} border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:{{ $article->starred ? 'bg-yellow-600' : 'bg-gray-700' }}">
                        <svg class="w-4 h-4 mr-2" fill="{{ $article->starred ? 'currentColor' : 'none' }}"
                             stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                        </svg>
                        {{ $article->starred ? 'Starred' : 'Star' }}
                    </button>
                </form>

                @if($article->status === 'unread')
                    <form method="POST" action="{{ route('articles.archive', $article) }}">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            Archive
                        </button>
                    </form>
                @endif
                @if($article->status !== 'archived')
                    <form method="POST" action="{{ route('articles.archive', $article) }}">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            Archive
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('articles.inbox', $article) }}">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            Move to Inbox
                        </button>
                    </form>
                @endif

                <!-- Delete Button -->
                <form method="POST" action="{{ route('articles.destroy', $article) }}" onsubmit="return confirm('Are you sure you want to delete this article? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Delete
                    </button>
                </form>
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
                                <img src="{{ $article->featured_image }}" alt="{{ $article->title }}"
                                     class="w-full h-96 object-cover rounded-lg shadow-lg">
                            </div>
                        @endif

                        <div class="flex items-center text-sm text-gray-500 mb-8">
                            @if($article->author)
                                <span class="font-medium">{{ $article->author }}</span>
                                <span class="mx-2">•</span>
                            @endif
                            @if($article->site_name)
                                <span>{{ $article->site_name }}</span>
                                <span class="mx-2">•</span>
                            @endif
                            <span>{{ $article->created_at ? $article->created_at->format('F j, Y') : 'Unknown date' }}</span>
                        </div>

                        @if($article->summary)
                            <!-- Tab Navigation -->
                            <div class="border-b border-gray-200 mb-6">
                                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                    <button type="button"
                                            class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-indigo-500 text-indigo-600"
                                            data-tab="summary">
                                        Summary
                                    </button>
                                    <button type="button"
                                            class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300"
                                            data-tab="original">
                                        Original Content
                                    </button>
                                </nav>
                            </div>

                            <!-- Tab Content -->
                            <div class="tab-content" id="summary-tab">
                                <div class="prose max-w-none">
                                    {!! nl2br(e($article->summary)) !!}
                                </div>
                            </div>
                            <div class="tab-content hidden" id="original-tab">
                                <div class="prose max-w-none">
                                    {!! $article->content !!}
                                </div>
                            </div>
                        @else
                            <div class="prose max-w-none">
                                {!! $article->content !!}
                            </div>
                        @endif
                    </article>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Get all tab buttons
                const tabButtons = document.querySelectorAll('.tab-button');

                // Only initialize tabs if they exist
                if (tabButtons.length === 0) {
                    return; // Exit early if no tabs exist
                }

                // Add click event listeners to each tab button
                tabButtons.forEach(button => {
                    button.addEventListener('click', function () {
                        const tabId = this.getAttribute('data-tab');
                        switchTab(tabId);
                    });
                });

                // Function to switch tabs
                function switchTab(tabId) {
                    // Hide all tab contents
                    document.querySelectorAll('.tab-content').forEach(content => {
                        content.classList.add('hidden');
                    });

                    // Show the selected tab content
                    document.getElementById(`${tabId}-tab`).classList.remove('hidden');

                    // Update tab button styles
                    tabButtons.forEach(button => {
                        if (button.getAttribute('data-tab') === tabId) {
                            button.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
                            button.classList.add('border-indigo-500', 'text-indigo-600');
                        } else {
                            button.classList.remove('border-indigo-500', 'text-indigo-600');
                            button.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
                        }
                    });
                }
            });
        </script>
    @endpush

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
