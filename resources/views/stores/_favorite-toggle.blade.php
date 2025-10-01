@php $isFav = in_array($store->id, $favoriteStoreIds ?? [], true); @endphp
<form method="POST" action="{{ route('stores.favorite.toggle', $store) }}" class="inline-block">
    @csrf
    <button type="submit" aria-label="お気に入りに{{ $isFav ? '解除' : '追加' }}" class="p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition">
        <svg class="w-5 h-5 {{ $isFav ? 'fill-pink-500' : 'fill-gray-400 dark:fill-gray-500' }}" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 6 4 4 6.5 4c1.74 0 3.41 1.01 4.22 2.5C11.09 5.01 12.76 4 14.5 4 17 4 19 6 19 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
        </svg>
    </button>
</form>
