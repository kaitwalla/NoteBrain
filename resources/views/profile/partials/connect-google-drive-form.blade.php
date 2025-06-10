<x-section-title>
    <x-slot name="title">{{ __('Google Drive Integration') }}</x-slot>
    <x-slot name="description"></x-slot>
</x-section-title>

<div class="mt-5">
    <div class="max-w-xl">
        @if (auth()->user()->hasGoogleDriveToken())
            <div class="flex items-center">
                <div class="text-sm text-gray-600">
                    {{ __('Your Google Drive account is connected.') }}
                </div>
                <div class="ml-3">
                    <form method="POST" action="{{ route('google.drive.disconnect') }}">
                        @csrf
                        @method('DELETE')
                        <x-danger-button>{{ __('Disconnect') }}</x-danger-button>
                    </form>
                </div>
            </div>

            <div class="mt-4">
                <div class="text-sm text-gray-600">
                    @if (auth()->user()->google_drive_folder_id)
                        @if (isset($googleDriveFolderName))
                            {{ __('Articles will be saved to the folder:') }} <strong>{{ $googleDriveFolderName }}</strong>
                        @else
                            {{ __('Articles will be saved to your selected Google Drive folder.') }}
                        @endif
                        <a href="{{ route('google.drive.folders') }}" class="text-indigo-600 hover:text-indigo-900 ml-2">
                            {{ __('Change folder') }}
                        </a>
                    @else
                        {{ __('Please select a folder to save your articles.') }}
                        <a href="{{ route('google.drive.folders') }}" class="text-indigo-600 hover:text-indigo-900 ml-2">
                            {{ __('Select folder') }}
                        </a>
                    @endif
                </div>
            </div>
        @else
            <div class="flex items-center">
                <div class="text-sm text-gray-600">
                    {{ __('Connect your Google Drive account to automatically save articles.') }}
                </div>
                <div class="ml-3">
                    <a href="{{ route('google.drive.connect') }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        {{ __('Connect') }}
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
