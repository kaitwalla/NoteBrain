<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $note->title }}
            </h2>
            <div class="flex space-x-4">
                <!-- Star Toggle Button -->
                <form method="POST" action="{{ route('notes.toggle-star', $note) }}" class="star-form">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 {{ $note->starred ? 'bg-yellow-500' : 'bg-gray-800' }} border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:{{ $note->starred ? 'bg-yellow-600' : 'bg-gray-700' }}">
                        <svg class="w-4 h-4 mr-2" fill="{{ $note->starred ? 'currentColor' : 'none' }}"
                             stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                        </svg>
                        {{ $note->starred ? 'Starred' : 'Star' }}
                    </button>
                </form>

                <!-- Edit Button -->
                <a href="{{ route('notes.edit', $note) }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    Edit
                </a>

                @if($note->status !== 'archived')
                    <form method="POST" action="{{ route('notes.archive', $note) }}">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            Archive
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('notes.inbox', $note) }}">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            Move to Inbox
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Note Content -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <article class="prose prose-xl max-w-none" id="note-content">
                        <div class="flex items-center text-sm text-gray-500 mb-8">
                            <span>Created {{ $note->created_at ? $note->created_at->format('F j, Y') : 'Unknown date' }}</span>
                            @if($note->updated_at && $note->updated_at->ne($note->created_at))
                                <span class="mx-2">•</span>
                                <span>Updated {{ $note->updated_at->format('F j, Y') }}</span>
                            @endif
                        </div>

                        <div class="prose max-w-none note-content">
                            {!! $note->content !!}
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .note-content {
                font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            }

            .note-content h1 {
                font-size: 2.5rem;
                margin-top: 2.5rem;
                margin-bottom: 2rem;
            }

            .note-content h2 {
                font-size: 2rem;
                margin-top: 2rem;
                margin-bottom: 1.5rem;
            }

            .note-content h3 {
                font-size: 1.75rem;
                margin-top: 1.75rem;
                margin-bottom: 1.25rem;
            }

            .note-content p {
                margin-bottom: 2rem;
                line-height: 1.8;
            }

            .note-content ul, .note-content ol {
                margin-bottom: 2rem;
                padding-left: 1.5rem;
            }

            .note-content li {
                margin-bottom: 0.75rem;
            }

            .note-content blockquote {
                margin: 2rem 0;
                padding: 1.5rem 2rem;
                background-color: #f9fafb;
                border-left: 4px solid #e5e7eb;
                font-size: 1.25rem;
            }

            .note-content pre {
                margin: 2rem 0;
                padding: 1.5rem;
                background-color: #f3f4f6;
                border-radius: 0.375rem;
                overflow-x: auto;
                font-size: 1rem;
            }

            .note-content code {
                font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
                font-size: 0.875em;
                padding: 0.2em 0.4em;
                background-color: #f3f4f6;
                border-radius: 0.25rem;
            }

            .note-content img {
                margin: 2.5rem 0;
                border-radius: 0.5rem;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            }

            .note-content a {
                color: #2563eb;
                text-decoration: none;
            }

            .note-content a:hover {
                text-decoration: underline;
            }

            .note-content hr {
                margin: 2.5rem 0;
                border: 0;
                border-top: 1px solid #e5e7eb;
            }
        </style>
    @endpush
</x-app-layout>
