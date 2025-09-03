<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">レッスンスケジュール一覧</h2>
    </x-slot>

    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-4">
            <a href="{{ route('admin.lesson-schedules.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded">新規作成</a>
        </div>

    @if (session('status'))
        <div class="mb-4 text-green-700">{{ session('status') }}</div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-2 py-1 text-left">ID</th>
                    <th class="px-2 py-1 text-left">レッスン</th>
                    <th class="px-2 py-1 text-left">店舗</th>
                    <th class="px-2 py-1 text-left">カテゴリ</th>
                    <th class="px-2 py-1 text-left">開始</th>
                    <th class="px-2 py-1 text-left">終了</th>
                    <th class="px-2 py-1 text-left">予約数</th>
                    <th class="px-2 py-1 text-left">有効</th>
                    <th class="px-2 py-1 text-left">操作</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($schedules as $schedule)
                    <tr class="border-t">
                        <td class="px-2 py-1">{{ $schedule->id }}</td>
                        <td class="px-2 py-1">
                            <a class="text-blue-700 underline" href="{{ route('admin.lesson-schedules.show', $schedule) }}">{{ $schedule->lesson?->name ?? '-' }}</a>
                        </td>
                        <td class="px-2 py-1">{{ $schedule->lesson?->store?->name ?? '-' }}</td>
                        <td class="px-2 py-1">{{ $schedule->lesson?->category?->name ?? '-' }}</td>
                        <td class="px-2 py-1">{{ $schedule->formatted_start_time }} ({{ $schedule->start_datetime->format('Y-m-d') }})</td>
                        <td class="px-2 py-1">{{ $schedule->formatted_end_time }} ({{ $schedule->end_datetime->format('Y-m-d') }})</td>
                        <td class="px-2 py-1">{{ $schedule->current_bookings }}</td>
                        <td class="px-2 py-1">{{ $schedule->is_active ? 'はい' : 'いいえ' }}</td>
                        <td class="px-2 py-1 space-x-2">
                            <a href="{{ route('admin.lesson-schedules.edit', $schedule) }}" class="text-blue-700 underline">編集</a>
                            <form class="inline" method="POST" action="{{ route('admin.lesson-schedules.destroy', $schedule) }}" onsubmit="return confirm('削除しますか？')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-700 underline">削除</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-2 py-4 text-center" colspan="9">データがありません</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $schedules->links() }}
    </div>
    </div>
</x-app-layout>


