<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            店舗詳細
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-4">
                    <div class="flex justify-between">
                        <a href="{{ route('admin.stores.edit', $store) }}" class="px-3 py-2 bg-indigo-600 text-white rounded">編集</a>
                        <a href="{{ route('admin.stores.index') }}" class="px-3 py-2 bg-gray-200 rounded">一覧へ</a>
                    </div>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm text-gray-500">名称</dt>
                            <dd class="mt-1 text-gray-900">{{ $store->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">電話</dt>
                            @php $tel = preg_replace('/\D+/', '', $store->phone ?? ''); @endphp
                            <dd class="mt-1 text-gray-900">
                                @if($tel)
                                    <a class="text-blue-600" href="tel:{{ $tel }}">{{ $store->formatted_phone ?? $store->phone }}</a>
                                @else
                                    {{ $store->formatted_phone ?? $store->phone }}
                                @endif
                            </dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-sm text-gray-500">住所</dt>
                            <dd class="mt-1 text-gray-900 whitespace-pre-line">{{ $store->address }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-sm text-gray-500">アクセス情報</dt>
                            <dd class="mt-1 text-gray-900 whitespace-pre-line">{{ $store->access_info }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-sm text-gray-500">Googleマップ</dt>
                            <dd class="mt-1 text-gray-900">
                                @if($store->google_map_url)
                                    <a class="text-blue-600" target="_blank" rel="noopener" href="{{ $store->google_map_url }}">地図を開く</a>
                                @endif
                            </dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-sm text-gray-500">駐車場情報</dt>
                            <dd class="mt-1 text-gray-900 whitespace-pre-line">{{ $store->parking_info }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-sm text-gray-500">備考</dt>
                            <dd class="mt-1 text-gray-900 whitespace-pre-line">{{ $store->notes }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">状態</dt>
                            <dd class="mt-1 text-gray-900">{{ $store->is_active ? '有効' : '無効' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
