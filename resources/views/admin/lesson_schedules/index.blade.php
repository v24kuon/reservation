<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">レッスンスケジュール一覧</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-4 space-x-2">
                        <x-primary-button as="a" href="{{ route('admin.lesson-schedules.create') }}">新規作成</x-primary-button>
                        <x-primary-button as="a" href="{{ route('admin.lesson-schedules.bulk.create') }}">一括作成</x-primary-button>
                    </div>

    @if (session('status'))
        <div class="mb-4 text-green-700">{{ session('status') }}</div>
    @endif

    <form method="GET" action="{{ route('admin.lesson-schedules.index') }}" class="mb-4 grid grid-cols-1 md:grid-cols-5 gap-2 items-end">
        <div>
            <label for="date_from" class="block text-xs mb-1">開始日</label>
            <input id="date_from" type="date" name="date_from" value="{{ old('date_from', request('date_from')) }}" class="border rounded px-2 py-1 w-full">
        </div>
        <div>
            <label for="date_to" class="block text-xs mb-1">終了日</label>
            <input id="date_to" type="date" name="date_to" value="{{ old('date_to', request('date_to')) }}" class="border rounded px-2 py-1 w-full">
        </div>
        <div>
            <label for="lesson_id" class="block text-xs mb-1">レッスン</label>
            <select id="lesson_id" name="lesson_id" class="border rounded px-2 py-1 w-full">
                <option value="">すべて</option>
                @foreach($lessons as $lesson)
                    <option value="{{ $lesson->id }}" @selected((string)$lesson->id === (string)old('lesson_id', request('lesson_id')))>{{ $lesson->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="instructor_user_id" class="block text-xs mb-1">インストラクター</label>
            <select id="instructor_user_id" name="instructor_user_id" class="border rounded px-2 py-1 w-full">
                <option value="">すべて</option>
                @foreach($instructors as $inst)
                    <option value="{{ $inst->id }}" @selected((string)$inst->id === (string)old('instructor_user_id', request('instructor_user_id')))>{{ $inst->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="is_active" class="block text-xs mb-1">有効</label>
            <select id="is_active" name="is_active" class="border rounded px-2 py-1 w-full">
                <option value="">すべて</option>
                <option value="1" @selected((string)old('is_active', request('is_active'))==='1')>はい</option>
                <option value="0" @selected((string)old('is_active', request('is_active'))==='0')>いいえ</option>
            </select>
        </div>
        <div class="md:col-span-5 flex gap-2">
            <x-primary-button type="submit">検索</x-primary-button>
            <x-secondary-button as="a" href="{{ route('admin.lesson-schedules.index') }}">リセット</x-secondary-button>
        </div>
    </form>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="text-left">
                                    <th scope="col" class="px-2 py-1">ID</th>
                                    <th scope="col" class="px-2 py-1">レッスン</th>
                                    <th scope="col" class="px-2 py-1">店舗</th>
                                    <th scope="col" class="px-2 py-1">カテゴリ</th>
                                    <th scope="col" class="px-2 py-1">開始</th>
                                    <th scope="col" class="px-2 py-1">終了</th>
                                    <th scope="col" class="px-2 py-1">予約数</th>
                                    <th scope="col" class="px-2 py-1">有効</th>
                                    <th scope="col" class="px-2 py-1">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($schedules as $schedule)
                                    <tr class="border-t">
                                        <td class="px-2 py-1">{{ $schedule->id }}</td>
                                        <td class="px-2 py-1">
                                            <a class="text-indigo-600" href="{{ route('admin.lesson-schedules.show', $schedule) }}">{{ $schedule->lesson?->name ?? '-' }}</a>
                                        </td>
                                        <td class="px-2 py-1">{{ $schedule->lesson?->store?->name ?? '-' }}</td>
                                        <td class="px-2 py-1">{{ $schedule->lesson?->category?->name ?? '-' }}</td>
                                        <td class="px-2 py-1">{{ $schedule->formatted_start_time }} ({{ ($schedule->start_datetime?->format('Y-m-d')) ?? '-' }})</td>
                                        <td class="px-2 py-1">{{ $schedule->formatted_end_time }} ({{ ($schedule->end_datetime?->format('Y-m-d')) ?? '-' }})</td>
                                        <td class="px-2 py-1">{{ $schedule->current_bookings }}</td>
                                        <td class="px-2 py-1">{{ $schedule->is_active ? 'はい' : 'いいえ' }}</td>
                                        <td class="px-2 py-1 space-x-2">
                                            @can('update', $schedule)
                                            <a href="{{ route('admin.lesson-schedules.edit', $schedule) }}" class="text-blue-600">編集</a>
                                            @endcan
                                            @can('delete', $schedule)
                                            <form class="inline" method="POST" action="{{ route('admin.lesson-schedules.destroy', $schedule) }}" onsubmit="return confirm('スケジュールID {{ $schedule->id }} を削除しますか？この操作は取り消せません。')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600">削除</button>
                                            </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="border-t">
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
            </div>
        </div>
    </div>
</x-admin-layout>
