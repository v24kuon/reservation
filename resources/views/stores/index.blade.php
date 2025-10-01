<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            店舗一覧
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @if($stores->count() === 0)
            <p class="text-sm text-gray-600 dark:text-gray-400">公開中の店舗はありません。</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($stores as $store)
                    <div class="rounded-xl bg-white dark:bg-gray-800 p-4 shadow-[inset_0_1px_0_rgba(255,255,255,0.1),0_1px_3px_rgba(0,0,0,0.1)] dark:shadow-[inset_0_1px_0_rgba(255,255,255,0.05),0_1px_3px_rgba(0,0,0,0.3)] hover:shadow-md transition">
                        <a href="{{ route('stores.show', $store) }}" class="block">
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $store->name }}</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $store->address }}</p>
                        </a>
                        @if($store->phone)
                            <div class="flex items-center justify-between mt-1">
                                <p class="text-sm text-gray-600 dark:text-gray-400">TEL: {{ $store->phone }}</p>
                                @include('stores._favorite-toggle', ['store' => $store, 'favoriteStoreIds' => $favoriteStoreIds])
                            </div>
                        @else
                            <div class="flex items-center justify-end mt-1">
                                @include('stores._favorite-toggle', ['store' => $store, 'favoriteStoreIds' => $favoriteStoreIds])
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $stores->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
