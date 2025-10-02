<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            予約履歴
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        <form method="GET" action="{{ route('reservations.history') }}" class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                <div class="flex flex-col">
                    <label class="text-sm text-gray-700 dark:text-gray-300">ステータス</label>
                    <select name="status" class="border rounded p-2 w-full">
                        <option value="">すべて</option>
                        @php $statusLabels = __('reservation.status'); @endphp
                        @foreach(\App\Models\Reservation::STATUSES as $st)
                            <option value="{{ $st }}" @selected(($filters['status'] ?? '') === $st)>{{ $statusLabels[$st] ?? $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col">
                    <label class="text-sm text-gray-700 dark:text-gray-300">開始日</label>
                    <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="border rounded p-2 w-full" />
                </div>
                <div class="flex flex-col">
                    <label class="text-sm text-gray-700 dark:text-gray-300">終了日</label>
                    <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="border rounded p-2 w-full" />
                </div>
                <div class="md:col-span-5">
                    <x-primary-button type="submit">検索</x-primary-button>
                </div>
            </div>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="rounded-xl bg-white dark:bg-gray-800 p-4 shadow">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">今後の予約</h3>
                @forelse($upcoming as $reservation)
                    <div class="border-b border-gray-100 dark:border-gray-700 py-3 flex items-center justify-between">
                        <div>
                            <p class="text-gray-900 dark:text-gray-100 font-medium">{{ $reservation->lessonSchedule?->lesson?->name ?? '未設定' }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">店舗: {{ $reservation->lessonSchedule?->lesson?->store?->name ?? '未設定' }}</p>
                        </div>
                        @if($reservation->lessonSchedule?->start_datetime)
                            <time datetime="{{ $reservation->lessonSchedule->start_datetime->toIso8601String() }}" class="text-sm text-gray-700 dark:text-gray-300">
                                {{ $reservation->lessonSchedule->start_datetime->format('Y/m/d H:i') }}
                            </time>
                        @else
                            <span class="text-sm text-gray-700 dark:text-gray-300">未設定</span>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-gray-600 dark:text-gray-400">今後の予約はありません。</p>
                @endforelse
            </div>
            <div class="rounded-xl bg-white dark:bg-gray-800 p-4 shadow">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">過去の予約</h3>
                @forelse($past as $reservation)
                    <div class="border-b border-gray-100 dark:border-gray-700 py-3 flex items-center justify-between">
                        <div>
                            <p class="text-gray-900 dark:text-gray-100 font-medium">{{ $reservation->lessonSchedule?->lesson?->name ?? '未設定' }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">店舗: {{ $reservation->lessonSchedule?->lesson?->store?->name ?? '未設定' }}</p>
                            <span class="inline-block text-xs mt-1 px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">{{ $reservation->formatted_status }}</span>
                        </div>
                        @if($reservation->lessonSchedule?->start_datetime)
                            <time datetime="{{ $reservation->lessonSchedule->start_datetime->toIso8601String() }}" class="text-sm text-gray-700 dark:text-gray-300">
                                {{ $reservation->lessonSchedule->start_datetime->format('Y/m/d H:i') }}
                            </time>
                        @else
                            <span class="text-sm text-gray-700 dark:text-gray-300">未設定</span>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-gray-600 dark:text-gray-400">過去の予約はありません。</p>
                @endforelse
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                {{ $upcoming->links() }}
            </div>
            <div>
                {{ $past->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
