<x-app-layout>
    <x-slot name="header">
        <style>
            .selected-note {
                background-color: #4a5568 !important; /* dark gray background */
                color: white !important;
            }

            .selected-note h3,
            .selected-note p,
            .selected-note div,
            .selected-note span {
                color: white !important;
            }

            .selected-note svg {
                color: white !important;
            }

            .selected-note a {
                color: #90cdf4 !important; /* light blue for links */
            }
        </style>
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('My Notes') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('notes.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    {{ __('Create New Note') }}
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
                                <a href="{{ route('notes.index', ['status' => 'inbox']) }}"
                                   class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $currentStatus === 'inbox' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                    Inbox
                                    <span
                                        class="ml-2 bg-gray-100 text-gray-900 py-0.5 px-2 rounded-full text-xs">{{ $inboxCount }}</span>
                                </a>
                                <a href="{{ route('notes.index', ['status' => 'archived']) }}"
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

                    <!-- Notes List -->
                    @if($notes->isEmpty())
                        <div class="text-center py-12">
                            <h3 class="text-lg font-medium text-gray-900">No notes
                                in {{ ucfirst($currentStatus) }}</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by creating your first note.</p>
                            <div class="mt-6">
                                <a href="{{ route('notes.create') }}"
                                   class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                    {{ __('Create New Note') }}
                                </a>
                            </div>
                        </div>
                    @else
                        <div id="bulk-select-controls" class="py-4 flex items-center hidden">
                            <input type="checkbox" id="select-all"
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <label for="select-all" class="ml-2 text-sm text-gray-700">Select All</label>
                        </div>
                        <div class="space-y-6">
                            @foreach($notes as $note)
                                <div
                                    class="flex items-start p-4 bg-white border rounded-lg shadow-sm note-item cursor-pointer"
                                    data-note-id="{{ $note->id }}">
                                    <div class="flex-shrink-0 mr-3 note-checkbox-container hidden">
                                        <input type="checkbox"
                                               class="note-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                               data-note-id="{{ $note->id }}">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-lg font-medium text-gray-900 truncate">
                                            <a href="{{ route('notes.show', $note) }}" class="hover:underline">
                                                {{ $note->title }}
                                            </a>
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-500">
                                            {{ Str::limit(strip_tags($note->content), 150) }}
                                        </p>
                                        <div class="mt-2 flex items-center text-sm text-gray-500">
                                            <span>{{ $note->created_at ? $note->created_at->diffForHumans() : 'Unknown date' }}</span>
                                            @if($note->updated_at && $note->updated_at->ne($note->created_at))
                                                <span class="mx-2">•</span>
                                                <span>Updated {{ $note->updated_at->diffForHumans() }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="ml-4 flex-shrink-0 flex space-x-2">
                                        <!-- Star Toggle Button -->
                                        <form method="POST" action="{{ route('notes.toggle-star', $note) }}"
                                              class="star-form">
                                            @csrf
                                            <button type="submit"
                                                    class="p-1 text-gray-600 hover:text-gray-900 rounded-full"
                                                    title="{{ $note->starred ? 'Unstar' : 'Star' }}">
                                                <svg class="w-5 h-5"
                                                     fill="{{ $note->starred ? 'currentColor' : 'none' }}"
                                                     stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                                </svg>
                                            </button>
                                        </form>

                                        <!-- Edit Button -->
                                        <a href="{{ route('notes.edit', $note) }}"
                                           class="p-1 text-gray-600 hover:text-gray-900 rounded-full"
                                           title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>

                                        @if($note->status === 'inbox')
                                            <form method="POST" action="{{ route('notes.archive', $note) }}">
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
                                            <form method="POST" action="{{ route('notes.inbox', $note) }}">
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
                            {{ $notes->appends(['status' => $currentStatus])->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Bulk selection functionality
                const bulkEditButton = document.getElementById('bulk-edit-button');
                const exitBulkEditButton = document.getElementById('exit-bulk-edit');
                const selectAllCheckbox = document.getElementById('select-all');
                const noteCheckboxes = document.querySelectorAll('.note-checkbox');
                const bulkActionsDiv = document.getElementById('bulk-actions');
                const bulkSelectControls = document.getElementById('bulk-select-controls');
                const bulkActionSelect = document.getElementById('bulk-action-select');
                const applyBulkActionButton = document.getElementById('apply-bulk-action');
                const noteItems = document.querySelectorAll('.note-item');
                const checkboxContainers = document.querySelectorAll('.note-checkbox-container');

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

                        // Uncheck all checkboxes and reset note styles
                        selectAllCheckbox.checked = false;
                        noteCheckboxes.forEach(checkbox => {
                            checkbox.checked = false;
                        });
                        noteItems.forEach(item => {
                            item.classList.remove('selected-note');
                        });
                    }
                }

                // Function to update bulk actions visibility
                function updateBulkActionsVisibility() {
                    const checkedBoxes = document.querySelectorAll('.note-checkbox:checked');
                    if (checkedBoxes.length > 0) {
                        bulkActionsDiv.classList.remove('hidden');
                    } else if (!bulkEditMode) {
                        bulkActionsDiv.classList.add('hidden');
                    }
                }

                // Toggle note selection
                function toggleNoteSelection(noteItem) {
                    if (!bulkEditMode) return;

                    const noteId = noteItem.getAttribute('data-note-id');
                    const checkbox = document.querySelector(`.note-checkbox[data-note-id="${noteId}"]`);

                    if (checkbox) {
                        checkbox.checked = !checkbox.checked;

                        // Toggle selected class for visual feedback
                        if (checkbox.checked) {
                            noteItem.classList.add('selected-note');
                        } else {
                            noteItem.classList.remove('selected-note');
                        }

                        // Update select all checkbox
                        const allChecked = document.querySelectorAll('.note-checkbox:checked').length === noteCheckboxes.length;
                        selectAllCheckbox.checked = allChecked;
                    }
                }

                if (noteItems.length > 0) {

                    // Handle bulk edit button click
                    bulkEditButton.addEventListener('click', function () {
                        toggleBulkEditMode(!bulkEditMode);
                    });

                    // Handle exit bulk edit button click
                    if (exitBulkEditButton) {
                        exitBulkEditButton.addEventListener('click', function () {
                            toggleBulkEditMode(false);
                        });
                    }

                    // Handle select all checkbox
                    selectAllCheckbox.addEventListener('change', function () {
                        noteCheckboxes.forEach(checkbox => {
                            checkbox.checked = this.checked;

                            // Update note item styling
                            const noteItem = document.querySelector(`.note-item[data-note-id="${checkbox.getAttribute('data-note-id')}"]`);
                            if (noteItem) {
                                if (this.checked) {
                                    noteItem.classList.add('selected-note');
                                } else {
                                    noteItem.classList.remove('selected-note');
                                }
                            }
                        });
                        updateBulkActionsVisibility();
                    });

                    // Handle note item click for selection
                    noteItems.forEach(item => {
                        item.addEventListener('click', function (e) {
                            // Only handle clicks on the note item itself, not on links or buttons inside it
                            if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON' ||
                                e.target.closest('a') || e.target.closest('button') ||
                                e.target.closest('form')) {
                                return;
                            }

                            toggleNoteSelection(this);
                            updateBulkActionsVisibility();

                            // Prevent event propagation
                            e.preventDefault();
                            e.stopPropagation();
                        });
                    });

                    // Handle individual note checkboxes
                    noteCheckboxes.forEach(checkbox => {
                        checkbox.addEventListener('change', function (e) {
                            // Update note item styling
                            const noteItem = document.querySelector(`.note-item[data-note-id="${this.getAttribute('data-note-id')}"]`);
                            if (noteItem) {
                                if (this.checked) {
                                    noteItem.classList.add('selected-note');
                                } else {
                                    noteItem.classList.remove('selected-note');
                                }
                            }

                            // Update select all checkbox
                            const allChecked = document.querySelectorAll('.note-checkbox:checked').length === noteCheckboxes.length;
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

                        const selectedNoteIds = Array.from(document.querySelectorAll('.note-checkbox:checked'))
                            .map(checkbox => checkbox.getAttribute('data-note-id'));

                        if (selectedNoteIds.length === 0) {
                            alert('Please select at least one note');
                            return;
                        }

                        // Confirm deletion
                        if (selectedAction === 'delete' && !confirm('Are you sure you want to delete the selected notes?')) {
                            return;
                        }

                        // Create a form to submit the bulk action
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '/notes/bulk-action';
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

                        // Add note IDs
                        selectedNoteIds.forEach(id => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'note_ids[]';
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
