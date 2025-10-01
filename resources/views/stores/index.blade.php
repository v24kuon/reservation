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
                                <form method="POST" action="{{ route('stores.favorite.toggle', $store) }}" class="inline-block">
                                    @csrf
                                    @php $isFav = in_array($store->id, $favoriteStoreIds ?? [], true); @endphp
                                    <button type="submit" aria-label="お気に入りに{{ $isFav ? '解除' : '追加' }}" class="p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                        <svg class="w-5 h-5 {{ $isFav ? 'fill-pink-500' : 'fill-gray-400 dark:fill-gray-500' }}" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 6 4 4 6.5 4c1.74 0 3.41 1.01 4.22 2.5C11.09 5.01 12.76 4 14.5 4 17 4 19 6 19 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="flex items-center justify-end mt-1">
                                <form method="POST" action="{{ route('stores.favorite.toggle', $store) }}" class="inline-block">
                                    @csrf
                                    @php $isFav = in_array($store->id, $favoriteStoreIds ?? [], true); @endphp
                                    <button type="submit" aria-label="お気に入りに{{ $isFav ? '解除' : '追加' }}" class="p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                        <svg class="w-5 h-5 {{ $isFav ? 'fill-pink-500' : 'fill-gray-400 dark:fill-gray-500' }}" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 6 4 4 6.5 4c1.74 0 3.41 1.01 4.22 2.5C11.09 5.01 12.76 4 14.5 4 17 4 19 6 19 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                        </svg>
                                    </button>
                                </form>
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
