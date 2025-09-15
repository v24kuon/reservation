<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">スケジュール詳細</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-2">
            <p><strong>ID:</strong> {{ $schedule->id }}</p>
            <p><strong>レッスン:</strong> {{ $schedule->lesson?->name ?? '-' }}</p>
            <p><strong>店舗:</strong> {{ $schedule->lesson?->store?->name ?? '-' }}</p>
            <p><strong>カテゴリ:</strong> {{ $schedule->lesson?->category?->name ?? '-' }}</p>
            <p><strong>開始:</strong> {{ optional($schedule->start_datetime)?->format('Y-m-d H:i') ?? '-' }}</p>
            <p><strong>終了:</strong> {{ optional($schedule->end_datetime)?->format('Y-m-d H:i') ?? '-' }}</p>
            <p><strong>現在予約数:</strong> {{ $schedule->current_bookings }}</p>
            <p><strong>有効:</strong> {{ $schedule->is_active ? 'はい' : 'いいえ' }}</p>
                    <div class="pt-4 flex space-x-2">
                        <x-primary-button as="a" href="{{ route('admin.lesson-schedules.edit', $schedule) }}">編集</x-primary-button>
                        <x-secondary-button href="{{ route('admin.lesson-schedules.index') }}">戻る</x-secondary-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
