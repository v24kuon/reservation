<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $store->name }}
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        <div class="rounded-xl bg-white dark:bg-gray-800 p-6 shadow-[inset_0_1px_0_rgba(255,255,255,0.1),0_1px_3px_rgba(0,0,0,0.1)] dark:shadow-[inset_0_1px_0_rgba(255,255,255,0.05),0_1px_3px_rgba(0,0,0,0.3)]">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm text-gray-500 dark:text-gray-400">住所</dt>
                    <dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $store->address }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500 dark:text-gray-400">電話番号</dt>
                    <dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $store->phone }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500 dark:text-gray-400">アクセス情報</dt>
                    <dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $store->access_info }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500 dark:text-gray-400">駐車場情報</dt>
                    <dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $store->parking_info }}</dd>
                </div>
                @if(!empty($mapUrl))
                    <div class="sm:col-span-2">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">地図</dt>
                        <dd class="mt-1">
                            <a href="{{ $mapUrl }}" target="_blank" rel="noopener noreferrer" class="text-indigo-600 dark:text-indigo-400 hover:underline">Googleマップで開く</a>
                        </dd>
                    </div>
                @endif
                @if($store->notes)
                    <div class="sm:col-span-2">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">備考</dt>
                        <dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $store->notes }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <div class="rounded-xl bg-white dark:bg-gray-800 p-6 shadow-[inset_0_1px_0_rgba(255,255,255,0.1),0_1px_3px_rgba(0,0,0,0.1)] dark:shadow-[inset_0_1px_0_rgba(255,255,255,0.05),0_1px_3px_rgba(0,0,0,0.3)]">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">この店舗の今後のレッスン</h3>
            @if($upcomingSchedules->isEmpty())
                <p class="text-sm text-gray-600 dark:text-gray-400">近日開催予定のレッスンはありません。</p>
            @else
                <ul class="space-y-3">
                    @foreach($upcomingSchedules as $schedule)
                        <li class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-900 dark:text-gray-100 font-medium">{{ $schedule->lesson?->name ?? '未設定' }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">講師: {{ $schedule->lesson?->instructor?->name ?? '未設定' }}</p>
                            </div>
                            <time datetime="{{ $schedule->start_datetime?->toIso8601String() }}" class="text-sm text-gray-700 dark:text-gray-300">
                                {{ $schedule->start_datetime?->format('n月j日 H:i') }}
                            </time>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</x-app-layout>
