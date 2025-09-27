<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            パーソナルレッスン予約管理
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="GET" class="mb-4 grid grid-cols-1 md:grid-cols-5 gap-3">
                        <div class="flex flex-col">
                            <label for="filter_instructor" class="text-sm text-gray-700 dark:text-gray-300">インストラクター</label>
                            <select id="filter_instructor" name="instructor_id" class="border rounded p-2">
                                <option value="">すべて</option>
                                @foreach($instructors as $i)
                                    <option value="{{ $i->id }}" @selected(($filters['instructor_id'] ?? '') == $i->id)>{{ $i->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-col">
                            <label for="filter_lesson" class="text-sm text-gray-700 dark:text-gray-300">レッスン</label>
                            <select id="filter_lesson" name="lesson_id" class="border rounded p-2">
                                <option value="">すべて</option>
                                @foreach($lessons as $l)
                                    <option value="{{ $l->id }}" @selected(($filters['lesson_id'] ?? '') == $l->id)>{{ $l->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-col">
                            <label for="filter_user" class="text-sm text-gray-700 dark:text-gray-300">ユーザー名/メール</label>
                            <input id="filter_user" type="text" name="user" value="{{ $filters['user'] ?? '' }}" class="border rounded p-2">
                        </div>
                        <div class="flex flex-col">
                            <label for="filter_status" class="text-sm text-gray-700 dark:text-gray-300">状態</label>
                            <select id="filter_status" name="status" class="border rounded p-2">
                                <option value="">すべて</option>
                                <option value="pending" @selected(($filters['status'] ?? '') == 'pending')>pending</option>
                                <option value="confirmed" @selected(($filters['status'] ?? '') == 'confirmed')>confirmed</option>
                                <option value="canceled" @selected(($filters['status'] ?? '') == 'canceled')>canceled</option>
                                <option value="completed" @selected(($filters['status'] ?? '') == 'completed')>completed</option>
                                <option value="no_show" @selected(($filters['status'] ?? '') == 'no_show')>no_show</option>
                            </select>
                        </div>
                        <div class="flex flex-col">
                            <label for="filter_date_from" class="text-sm text-gray-700 dark:text-gray-300">予約日(開始)</label>
                            <input id="filter_date_from" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="border rounded p-2">
                        </div>
                        <div class="flex flex-col">
                            <label for="filter_date_to" class="text-sm text-gray-700 dark:text-gray-300">予約日(終了)</label>
                            <input id="filter_date_to" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="border rounded p-2">
                        </div>
                        <div class="md:col-span-5">
                            <x-primary-button type="submit">検索</x-primary-button>
                        </div>
                    </form>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left">予約日時</th>
                                <th class="px-4 py-2 text-left">レッスン</th>
                                <th class="px-4 py-2 text-left">インストラクター</th>
                                <th class="px-4 py-2 text-left">ユーザー</th>
                                <th class="px-4 py-2 text-left">状態</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($reservations as $r)
                                <tr>
                                    <td class="px-4 py-2">{{ $r->reserved_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                    <td class="px-4 py-2">{{ $r->lessonSchedule?->lesson?->name ?? '-' }}</td>
                                    <td class="px-4 py-2">{{ $r->lessonSchedule?->lesson?->instructor?->name ?? '-' }}</td>
                                    <td class="px-4 py-2">{{ $r->user?->name ?? '-' }} ({{ $r->user?->email ?? '' }})</td>
                                    <td class="px-4 py-2">
                                        @php
                                            $badgeClass = match ($r->status) {
                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                'confirmed' => 'bg-green-100 text-green-800',
                                                'canceled' => 'bg-red-100 text-red-800',
                                                'completed' => 'bg-blue-100 text-blue-800',
                                                'no_show' => 'bg-orange-100 text-orange-800',
                                                default => 'bg-gray-100 text-gray-800',
                                            };
                                        @endphp
                                        <span class="px-2 py-1 text-xs font-medium rounded {{ $badgeClass }}">
                                            {{ $r->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">該当する予約がありません</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $reservations->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>


