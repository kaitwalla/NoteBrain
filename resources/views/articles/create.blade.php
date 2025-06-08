<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Save Article') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('articles.store') }}" class="space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="url" :value="__('Article URL')" />
                            <div class="mt-2">
                                <x-text-input id="url" name="url" type="url" class="mt-1 block w-full" 
                                    :value="old('url')" required autofocus />
                                <x-input-error class="mt-2" :messages="$errors->get('url')" />
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Save Article') }}</x-primary-button>
                            <a href="{{ route('articles.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                                {{ __('Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 