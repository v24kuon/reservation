<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">レッスンスケジュール編集</h2>
    </x-slot>

    <div class="container mx-auto px-4 py-6">

    <form method="POST" action="{{ route('admin.lesson-schedules.update', $schedule) }}" class="space-y-4">
        @csrf
        @method('PATCH')

        <div>
            <label class="block text-sm font-medium">レッスン</label>
            <select name="lesson_id" class="border rounded w-full p-2">
                @foreach ($lessons as $lesson)
                    <option value="{{ $lesson->id }}" @selected(old('lesson_id', $schedule->lesson_id) == $lesson->id)>{{ $lesson->name }} (ID:{{ $lesson->id }})</option>
                @endforeach
            </select>
            @error('lesson_id') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">開始日時</label>
            <input type="datetime-local" name="start_datetime" class="border rounded w-full p-2" value="{{ old('start_datetime', $schedule->start_datetime->format('Y-m-d\TH:i')) }}">
            @error('start_datetime') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">終了日時</label>
            <input type="datetime-local" name="end_datetime" class="border rounded w-full p-2" value="{{ old('end_datetime', $schedule->end_datetime->format('Y-m-d\TH:i')) }}">
            @error('end_datetime') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">現在予約数</label>
            <input type="number" name="current_bookings" min="0" step="1" class="border rounded w-full p-2" value="{{ old('current_bookings', $schedule->current_bookings) }}">
            @error('current_bookings') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="inline-flex items-center">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $schedule->is_active))>
                <span class="ml-2">有効</span>
            </label>
            @error('is_active') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>

        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">更新</button>
            <a href="{{ route('admin.lesson-schedules.index') }}" class="px-4 py-2 border rounded">一覧へ戻る</a>
        </div>
    </form>
    </div>
</x-app-layout>


