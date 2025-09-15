<x-admin-layout>
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
                    <option value="{{ $lesson->id }}" data-duration="{{ $lesson->duration }}" @selected(old('lesson_id', $schedule->lesson_id) == $lesson->id)>{{ $lesson->name }} (ID:{{ $lesson->id }})</option>
                @endforeach
            </select>
            @error('lesson_id') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">開始日時</label>
            <input type="datetime-local" name="start_datetime" class="border rounded w-full p-2" value="{{ old('start_datetime', $schedule->start_datetime?->format('Y-m-d\TH:i')) }}">
            @error('start_datetime') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">終了日時</label>
            <input type="datetime-local" name="end_datetime" class="border rounded w-full p-2" value="{{ old('end_datetime', $schedule->end_datetime?->format('Y-m-d\TH:i')) }}">
            @error('end_datetime') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">現在予約数</label>
            <input type="number" name="current_bookings" min="0" step="1" class="border rounded w-full p-2" value="{{ old('current_bookings', $schedule->current_bookings) }}">
            @error('current_bookings') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="inline-flex items-center">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $schedule->is_active ? '1' : '0'))>
                <span class="ml-2">有効</span>
            </label>
            @error('is_active') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>

        <div class="flex gap-2">
            <x-primary-button type="submit">更新</x-primary-button>
            <x-secondary-button href="{{ route('admin.lesson-schedules.index') }}">戻る</x-secondary-button>
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
            const toDatetimeLocal = (date) => {
                const y = date.getFullYear();
                const m = pad(date.getMonth() + 1);
                const d = pad(date.getDate());
                const hh = pad(date.getHours());
                const mm = pad(date.getMinutes());
                return `${y}-${m}-${d}T${hh}:${mm}`;
            };

            const updateEndFromStart = () => {
                const dur = getSelectedDuration();
                if (!dur) return;
                if (!startInput?.value) return;
                const dt = new Date(startInput.value);
                if (isNaN(dt.getTime())) return;
                dt.setMinutes(dt.getMinutes() + dur);
                endInput.value = toDatetimeLocal(dt);
            };

            lessonSelect?.addEventListener('change', updateEndFromStart);
            startInput?.addEventListener('change', updateEndFromStart);
            startInput?.addEventListener('blur', updateEndFromStart);

            // 初期表示時にも反映（編集時に開始が埋まっているケース）
            updateEndFromStart();
        });
    </script>
</x-admin-layout>
