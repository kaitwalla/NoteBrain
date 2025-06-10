<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Select Google Drive Folder') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if (session('error'))
                        <div class="mb-4 text-sm text-red-600">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if (session('status'))
                        <div class="mb-4 text-sm text-green-600">
                            {{ session('status') }}
                        </div>
                    @endif

                    <p class="mb-4">
                        {{ __('Please select a folder where your articles will be saved in Google Drive.') }}
                    </p>

                    <form method="POST" action="{{ route('google.drive.select-folder') }}">
                        @csrf

                        <div class="mb-4">
                            <x-input-label for="folder_id" :value="__('Select a folder')" />
                            <select id="folder_id" name="folder_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" onchange="toggleNewFolderInput(this.value)">
                                @foreach ($folders as $folder)
                                    <option value="{{ $folder['id'] }}" {{ $currentFolderId === $folder['id'] ? 'selected' : '' }}>
                                        {{ $folder['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div id="new_folder_container" class="mb-4 hidden">
                            <x-input-label for="new_folder_name" :value="__('New folder name')" />
                            <x-text-input id="new_folder_name" name="new_folder_name" type="text" class="mt-1 block w-full" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150 mr-3">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{ __('Save') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleNewFolderInput(value) {
            const newFolderContainer = document.getElementById('new_folder_container');
            if (value === 'new') {
                newFolderContainer.classList.remove('hidden');
            } else {
                newFolderContainer.classList.add('hidden');
            }
        }
    </script>
</x-app-layout>
