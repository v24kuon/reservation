<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            店舗詳細
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-2">
                    <p><strong>名称:</strong> {{ $store->name }}</p>
                    @php $tel = preg_replace('/\D+/', '', $store->phone ?? ''); @endphp
                    <p><strong>電話:</strong>
                        @if($tel)
                            <a class="text-blue-600" href="tel:{{ $tel }}">{{ $store->formatted_phone ?? $store->phone }}</a>
                        @else
                            {{ $store->formatted_phone ?? $store->phone }}
                        @endif
                    </p>
                    <p><strong>住所:</strong> <span class="whitespace-pre-line">{{ $store->address }}</span></p>
                    <p><strong>アクセス情報:</strong> <span class="whitespace-pre-line">{{ $store->access_info }}</span></p>
                    @php
                        $url = $store->google_map_url;
                        $scheme = $url ? parse_url($url, PHP_URL_SCHEME) : null;
                    @endphp
                    <p><strong>Googleマップ:</strong>
                        @if($url && in_array($scheme, ['http','https'], true))
                            <a class="text-blue-600" target="_blank" rel="noopener noreferrer" href="{{ $url }}">地図を開く</a>
                        @endif
                    </p>
                    <p><strong>駐車場情報:</strong> <span class="whitespace-pre-line">{{ $store->parking_info }}</span></p>
                    <p><strong>備考:</strong> <span class="whitespace-pre-line">{{ $store->notes }}</span></p>
                    <p><strong>状態:</strong> {{ $store->is_active ? '有効' : '無効' }}</p>

                    <div class="pt-4 flex space-x-2">
                        <x-primary-button as="a" href="{{ route('admin.stores.edit', $store) }}">編集</x-primary-button>
                        <x-secondary-button href="{{ route('admin.stores.index') }}">戻る</x-secondary-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
