<x-app-layout>
    <x-slot name="header">
        <style>
            .selected-article {
                background-color: #4a5568 !important; /* dark gray background */
                color: white !important;
            }

            .selected-article h3,
            .selected-article p,
            .selected-article div,
            .selected-article span {
                color: white !important;
            }

            .selected-article svg {
                color: white !important;
            }

            .selected-article a {
                color: #90cdf4 !important; /* light blue for links */
            }
        </style>
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('My Articles') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('articles.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    {{ __('Save New Article') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Tabs -->
                    <div class="border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                <a href="{{ route('articles.index', ['status' => 'inbox']) }}"
                                   class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $currentStatus === 'inbox' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                    Inbox
                                    <span
                                        class="ml-2 bg-gray-100 text-gray-900 py-0.5 px-2 rounded-full text-xs">{{ $inboxCount }}</span>
                                </a>
                                <a href="{{ route('articles.index', ['status' => 'archived']) }}"
                                   class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $currentStatus === 'archived' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                    Archived
                                    <span
                                        class="ml-2 bg-gray-100 text-gray-900 py-0.5 px-2 rounded-full text-xs">{{ $archivedCount }}</span>
                                </a>
                            </nav>
                            <button id="bulk-edit-button"
                                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500">
                                {{ __('Bulk Edit') }}
                            </button>
                            <div id="bulk-actions" class="hidden">
                                <div class="flex space-x-2">
                                    <select id="bulk-action-select"
                                            class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <option value="">Select Action</option>
                                        @if($currentStatus === 'inbox')
                                            <option value="archive">Archive</option>
                                        @else
                                            <option value="inbox">Move to Inbox</option>
                                        @endif
                                        <option value="star">Star</option>
                                        <option value="unstar">Unstar</option>
                                        <option value="delete">Delete</option>
                                    </select>
                                    <button type="button" id="apply-bulk-action"
                                            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                        Apply
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Articles List -->
                    @if($articles->isEmpty())
                        <div class="text-center py-12">
                            <h3 class="text-lg font-medium text-gray-900">No articles
                                in {{ ucfirst($currentStatus) }}</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by saving your first article.</p>
                            <div class="mt-6">
                                <a href="{{ route('articles.create') }}"
                                   class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                    {{ __('Save New Article') }}
                                </a>
                            </div>
                        </div>
                    @else
                        <div id="bulk-select-controls" class="py-4 flex items-center hidden">
                            <input type="checkbox" id="select-all"
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <label for="select-all" class="ml-2 text-sm text-gray-700">Select All</label>
                            <button id="exit-bulk-edit" class="ml-4 text-sm text-red-600 hover:text-red-800">Exit Bulk
                                Edit
                            </button>
                        </div>
                        <div class="space-y-6">
                            @foreach($articles as $article)
                                <div
                                    class="flex items-start p-4 bg-white border rounded-lg shadow-sm article-item cursor-pointer"
                                    data-article-id="{{ $article->id }}">
                                    <div class="flex-shrink-0 mr-3 article-checkbox-container hidden">
                                        <input type="checkbox"
                                               class="article-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                               data-article-id="{{ $article->id }}">
                                    </div>
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
                                        <!-- Star Toggle Button -->
                                        <form method="POST" action="{{ route('articles.toggle-star', $article) }}"
                                              class="star-form">
                                            @csrf
                                            <button type="submit"
                                                    class="p-1 text-gray-600 hover:text-gray-900 rounded-full"
                                                    title="{{ $article->starred ? 'Unstar' : 'Star' }}">
                                                <svg class="w-5 h-5"
                                                     fill="{{ $article->starred ? 'currentColor' : 'none' }}"
                                                     stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                                </svg>
                                            </button>
                                        </form>

                                        @if($article->status === 'inbox')
                                            @if(!$article->summary)
                                                <form method="POST" action="{{ route('articles.summarize', $article) }}"
                                                      class="summarize-form">
                                                    @csrf
                                                    <button type="submit"
                                                            class="summarize-button p-1 text-gray-600 hover:text-gray-900 rounded-full"
                                                            title="Summarize">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                             viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('articles.archive', $article) }}">
                                                @csrf
                                                <button type="submit"
                                                        class="p-1 text-gray-600 hover:text-gray-900 rounded-full"
                                                        title="Archive">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                         viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2"
                                                              d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('articles.inbox', $article) }}">
                                                @csrf
                                                <button type="submit"
                                                        class="p-1 text-gray-600 hover:text-gray-900 rounded-full"
                                                        title="Move to Inbox">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                         viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              stroke-width="2"
                                                              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
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
            document.addEventListener('DOMContentLoaded', function () {
                // Add event listeners for all summarize forms
                const summarizeForms = document.querySelectorAll('.summarize-form');
                summarizeForms.forEach(form => {
                    form.addEventListener('submit', function (e) {
                        const button = this.querySelector('.summarize-button');
                        if (button) {
                            // Disable the button and show loading state
                            button.disabled = true;
                            button.innerHTML = '<span class="inline-flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Summarizing...</span>';
                        }
                    });
                });

                // Bulk selection functionality
                const bulkEditButton = document.getElementById('bulk-edit-button');
                const exitBulkEditButton = document.getElementById('exit-bulk-edit');
                const selectAllCheckbox = document.getElementById('select-all');
                const articleCheckboxes = document.querySelectorAll('.article-checkbox');
                const bulkActionsDiv = document.getElementById('bulk-actions');
                const bulkSelectControls = document.getElementById('bulk-select-controls');
                const bulkActionSelect = document.getElementById('bulk-action-select');
                const applyBulkActionButton = document.getElementById('apply-bulk-action');
                const articleItems = document.querySelectorAll('.article-item');
                const checkboxContainers = document.querySelectorAll('.article-checkbox-container');

                let bulkEditMode = false;

                // Function to toggle bulk edit mode
                function toggleBulkEditMode(enabled) {
                    bulkEditMode = enabled;

                    // Show/hide bulk edit controls
                    if (enabled) {
                        bulkSelectControls.classList.remove('hidden');
                        bulkActionsDiv.classList.remove('hidden');
                        checkboxContainers.forEach(container => container.classList.remove('hidden'));
                        bulkEditButton.classList.add('bg-indigo-600');
                        bulkEditButton.classList.remove('bg-gray-600');
                    } else {
                        bulkSelectControls.classList.add('hidden');
                        bulkActionsDiv.classList.add('hidden');
                        checkboxContainers.forEach(container => container.classList.add('hidden'));
                        bulkEditButton.classList.remove('bg-indigo-600');
                        bulkEditButton.classList.add('bg-gray-600');

                        // Uncheck all checkboxes and reset article styles
                        selectAllCheckbox.checked = false;
                        articleCheckboxes.forEach(checkbox => {
                            checkbox.checked = false;
                        });
                        articleItems.forEach(item => {
                            item.classList.remove('selected-article');
                        });
                    }
                }

                // Function to update bulk actions visibility
                function updateBulkActionsVisibility() {
                    const checkedBoxes = document.querySelectorAll('.article-checkbox:checked');
                    if (checkedBoxes.length > 0) {
                        bulkActionsDiv.classList.remove('hidden');
                    } else if (!bulkEditMode) {
                        bulkActionsDiv.classList.add('hidden');
                    }
                }

                // Toggle article selection
                function toggleArticleSelection(articleItem) {
                    if (!bulkEditMode) return;

                    const articleId = articleItem.getAttribute('data-article-id');
                    const checkbox = document.querySelector(`.article-checkbox[data-article-id="${articleId}"]`);

                    if (checkbox) {
                        checkbox.checked = !checkbox.checked;

                        // Toggle selected class for visual feedback
                        if (checkbox.checked) {
                            articleItem.classList.add('selected-article');
                        } else {
                            articleItem.classList.remove('selected-article');
                        }

                        // Update select all checkbox
                        const allChecked = document.querySelectorAll('.article-checkbox:checked').length === articleCheckboxes.length;
                        selectAllCheckbox.checked = allChecked;
                    }
                }

                if (articleItems.length > 0) {

                    // Handle bulk edit button click
                    bulkEditButton.addEventListener('click', function () {
                        toggleBulkEditMode(!bulkEditMode);
                    });

                    // Handle exit bulk edit button click
                    exitBulkEditButton.addEventListener('click', function () {
                        toggleBulkEditMode(false);
                    });

                    // Handle select all checkbox
                    selectAllCheckbox.addEventListener('change', function () {
                        articleCheckboxes.forEach(checkbox => {
                            checkbox.checked = this.checked;

                            // Update article item styling
                            const articleItem = document.querySelector(`.article-item[data-article-id="${checkbox.getAttribute('data-article-id')}"]`);
                            if (articleItem) {
                                if (this.checked) {
                                    articleItem.classList.add('selected-article');
                                } else {
                                    articleItem.classList.remove('selected-article');
                                }
                            }
                        });
                        updateBulkActionsVisibility();
                    });

                    // Handle article item click for selection
                    articleItems.forEach(item => {
                        item.addEventListener('click', function (e) {
                            // Only handle clicks on the article item itself, not on links or buttons inside it
                            if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON' ||
                                e.target.closest('a') || e.target.closest('button') ||
                                e.target.closest('form')) {
                                return;
                            }

                            toggleArticleSelection(this);
                            updateBulkActionsVisibility();

                            // Prevent event propagation
                            e.preventDefault();
                            e.stopPropagation();
                        });
                    });

                    // Handle individual article checkboxes
                    articleCheckboxes.forEach(checkbox => {
                        checkbox.addEventListener('change', function (e) {
                            // Update article item styling
                            const articleItem = document.querySelector(`.article-item[data-article-id="${this.getAttribute('data-article-id')}"]`);
                            if (articleItem) {
                                if (this.checked) {
                                    articleItem.classList.add('selected-article');
                                } else {
                                    articleItem.classList.remove('selected-article');
                                }
                            }

                            // Update select all checkbox
                            const allChecked = document.querySelectorAll('.article-checkbox:checked').length === articleCheckboxes.length;
                            selectAllCheckbox.checked = allChecked;

                            updateBulkActionsVisibility();

                            // Prevent event propagation
                            e.stopPropagation();
                        });
                    });

                    // Handle apply bulk action button
                    applyBulkActionButton.addEventListener('click', function () {
                        const selectedAction = bulkActionSelect.value;
                        if (!selectedAction) {
                            alert('Please select an action');
                            return;
                        }

                        const selectedArticleIds = Array.from(document.querySelectorAll('.article-checkbox:checked'))
                            .map(checkbox => checkbox.getAttribute('data-article-id'));

                        if (selectedArticleIds.length === 0) {
                            alert('Please select at least one article');
                            return;
                        }

                        // Confirm deletion
                        if (selectedAction === 'delete' && !confirm('Are you sure you want to delete the selected articles?')) {
                            return;
                        }

                        // Create a form to submit the bulk action
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '/articles/bulk-action';
                        form.style.display = 'none';

                        // Add CSRF token
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = csrfToken;
                        form.appendChild(csrfInput);

                        // Add action
                        const actionInput = document.createElement('input');
                        actionInput.type = 'hidden';
                        actionInput.name = 'action';
                        actionInput.value = selectedAction;
                        form.appendChild(actionInput);

                        // Add article IDs
                        selectedArticleIds.forEach(id => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'article_ids[]';
                            input.value = id;
                            form.appendChild(input);
                        });

                        // Submit the form
                        document.body.appendChild(form);
                        form.submit();
                    });


                    // Initialize in non-bulk edit mode
                    toggleBulkEditMode(false);
                }
            });
        </script>
    @endpush
</x-app-layout>
