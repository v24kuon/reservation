<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $instructor->name }}
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        <div class="rounded-xl bg-white dark:bg-gray-800 p-6 shadow-[inset_0_1px_0_rgba(255,255,255,0.1),0_1px_3px_rgba(0,0,0,0.1)] dark:shadow-[inset_0_1px_0_rgba(255,255,255,0.05),0_1px_3px_rgba(0,0,0,0.3)]">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">プロフィール</h3>
            <div class="text-sm text-gray-600 dark:text-gray-400 space-y-2">
                @if(optional($instructor->instructorProfile)->bio)
                    <p>{{ $instructor->instructorProfile->bio }}</p>
                @else
                    <p>プロフィール情報は未設定です。</p>
                @endif
                @if(optional($instructor->instructorProfile)->qualifications)
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">資格・経歴</h4>
                        <p class="mt-1">{{ $instructor->instructorProfile->qualifications }}</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="rounded-xl bg-white dark:bg-gray-800 p-6 shadow-[inset_0_1px_0_rgba(255,255,255,0.1),0_1px_3px_rgba(0,0,0,0.1)] dark:shadow-[inset_0_1px_0_rgba(255,255,255,0.05),0_1px_3px_rgba(0,0,0,0.3)]">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">今後のスケジュール</h3>
            @if($upcomingSchedules->isEmpty())
                <p class="text-sm text-gray-600 dark:text-gray-400">近日開催予定のレッスンはありません。</p>
            @else
                <ul class="space-y-3">
                    @foreach($upcomingSchedules as $schedule)
                        <li class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-900 dark:text-gray-100 font-medium">{{ $schedule->lesson?->name ?? '未設定' }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">店舗: {{ $schedule->lesson?->store?->name ?? '未設定' }}</p>
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
