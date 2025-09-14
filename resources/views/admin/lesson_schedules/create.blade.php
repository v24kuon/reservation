<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">レッスンスケジュール作成</h2>
    </x-slot>

    <div class="container mx-auto px-4 py-6">

    <form method="POST" action="{{ route('admin.lesson-schedules.store') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium">レッスン</label>
            <select name="lesson_id" class="border rounded w-full p-2" required>
                <option value="" disabled selected>選択してください</option>
                @foreach ($lessons as $lesson)
                    <option value="{{ $lesson->id }}" data-duration="{{ $lesson->duration }}" @selected(old('lesson_id') == $lesson->id)>{{ $lesson->name }} (ID:{{ $lesson->id }})</option>
                @endforeach
            </select>
            @error('lesson_id') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">開始日時</label>
            <input type="datetime-local" name="start_datetime" class="border rounded w-full p-2" value="{{ old('start_datetime') }}" required>
            @error('start_datetime') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">終了日時</label>
            <input type="datetime-local" name="end_datetime" class="border rounded w-full p-2" value="{{ old('end_datetime') }}" required>
            @error('end_datetime') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">現在予約数</label>
            <input type="number" name="current_bookings" min="0" step="1" class="border rounded w-full p-2" value="{{ old('current_bookings', 0) }}" required>
            @error('current_bookings') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="inline-flex items-center">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', '1'))>
                <span class="ml-2">有効</span>
            </label>
            @error('is_active') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>

        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">保存</button>
            <a href="{{ route('admin.lesson-schedules.index') }}" class="px-4 py-2 bg-gray-200 rounded">一覧へ戻る</a>
        </div>
    </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const lessonSelect = document.querySelector('select[name="lesson_id"]');
            const startInput = document.querySelector('input[name="start_datetime"]');
            const endInput = document.querySelector('input[name="end_datetime"]');

            const getSelectedDuration = () => {
                const opt = lessonSelect?.options[lessonSelect.selectedIndex];
                const d = parseInt(opt?.dataset.duration || '0', 10);
                return Number.isFinite(d) ? d : 0;
            };

            const pad = (n) => String(n).padStart(2, '0');

            const addMinutesToDatetimeLocal = (value, minutesToAdd) => {
                if (!value || !minutesToAdd) return value;
                const dt = new Date(value);
                if (isNaN(dt.getTime())) return value;
                dt.setMinutes(dt.getMinutes() + minutesToAdd);
                const y = dt.getFullYear();
                const m = pad(dt.getMonth() + 1);
                const d = pad(dt.getDate());
                const hh = pad(dt.getHours());
                const mm = pad(dt.getMinutes());
                return `${y}-${m}-${d}T${hh}:${mm}`;
            };

            const updateEndFromStart = () => {
                const dur = getSelectedDuration();
                if (!dur) return;
                if (!startInput?.value) return;
                endInput.value = addMinutesToDatetimeLocal(startInput.value, dur);
            };

            lessonSelect?.addEventListener('change', updateEndFromStart);
            startInput?.addEventListener('change', updateEndFromStart);
            startInput?.addEventListener('blur', updateEndFromStart);
        });
    </script>
</x-admin-layout>
