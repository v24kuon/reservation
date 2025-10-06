<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('グループレッスン') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if (session('status'))
                        <div class="mb-4">
                            <x-auth-session-status :status="__(session('status'))" />
                        </div>
                    @endif
                    @if ($errors->reservation ?? false)
                        <div class="mb-4">
                            <x-input-error :messages="$errors->get('reservation')" />
                        </div>
                    @endif
                    <h3 class="text-lg font-semibold mb-4">グループレッスン予約</h3>
                    @livewire('reservation-booking', ['mode' => 'group'])
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
