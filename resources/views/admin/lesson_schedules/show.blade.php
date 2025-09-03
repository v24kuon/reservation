<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">スケジュール詳細</h2>
            <div class="space-x-2">
                <a href="{{ route('admin.lesson-schedules.edit', $schedule) }}" class="bg-blue-600 text-white px-4 py-2 rounded">編集へ</a>
                <a href="{{ route('admin.lesson-schedules.index') }}" class="px-4 py-2 border rounded">一覧へ戻る</a>
            </div>
        </div>
    </x-slot>

    <div class="container mx-auto px-4 py-6">
        <div class="space-y-2">
            <p><strong>ID:</strong> {{ $schedule->id }}</p>
            <p><strong>レッスン:</strong> {{ $schedule->lesson?->name ?? '-' }}</p>
            <p><strong>店舗:</strong> {{ $schedule->lesson?->store?->name ?? '-' }}</p>
            <p><strong>カテゴリ:</strong> {{ $schedule->lesson?->category?->name ?? '-' }}</p>
            <p><strong>開始:</strong> {{ $schedule->start_datetime->format('Y-m-d H:i') }}</p>
            <p><strong>終了:</strong> {{ $schedule->end_datetime->format('Y-m-d H:i') }}</p>
            <p><strong>現在予約数:</strong> {{ $schedule->current_bookings }}</p>
            <p><strong>有効:</strong> {{ $schedule->is_active ? 'はい' : 'いいえ' }}</p>
        </div>
    </div>
</x-app-layout>


