<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">レッスンスケジュール一括作成</h2>
    </x-slot>

    <div class="container mx-auto px-4 py-6">
        <form method="POST" action="{{ route('admin.lesson-schedules.bulk.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium">レッスン</label>
                <select name="lesson_id" class="border rounded w-full p-2" required>
                    <option value="" disabled selected>選択してください</option>
                    @foreach ($lessons as $lesson)
                        <option value="{{ $lesson->id }}" @selected(old('lesson_id') == $lesson->id)>{{ $lesson->name }} (ID:{{ $lesson->id }})</option>
                    @endforeach
                </select>
                @error('lesson_id') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
            </div>

            <div class="rounded border p-4 space-y-3">
                <h3 class="font-semibold">繰り返し生成（任意）</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium">期間（開始）</label>
                        <input type="date" id="rec-start-date" class="border rounded w-full p-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium">期間（終了）</label>
                        <input type="date" id="rec-end-date" class="border rounded w-full p-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium">開始時刻</label>
                        <input type="time" id="rec-start-time" class="border rounded w-full p-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium">終了時刻</label>
                        <input type="time" id="rec-end-time" class="border rounded w-full p-2">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">曜日</label>
                    <div class="flex flex-wrap gap-3">
                        <label class="inline-flex items-center gap-1"><input type="checkbox" class="rec-weekday" value="1"><span>月</span></label>
                        <label class="inline-flex items-center gap-1"><input type="checkbox" class="rec-weekday" value="2"><span>火</span></label>
                        <label class="inline-flex items-center gap-1"><input type="checkbox" class="rec-weekday" value="3"><span>水</span></label>
                        <label class="inline-flex items-center gap-1"><input type="checkbox" class="rec-weekday" value="4"><span>木</span></label>
                        <label class="inline-flex items-center gap-1"><input type="checkbox" class="rec-weekday" value="5"><span>金</span></label>
                        <label class="inline-flex items-center gap-1"><input type="checkbox" class="rec-weekday" value="6"><span>土</span></label>
                        <label class="inline-flex items-center gap-1"><input type="checkbox" class="rec-weekday" value="0"><span>日</span></label>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium">間隔（週）</label>
                        <input type="number" id="rec-interval" class="border rounded w-full p-2" min="1" value="1">
                    </div>
                    <div class="md:col-span-2 flex gap-2">
                        <button type="button" id="rec-generate-server" class="px-4 py-2 border rounded">行を自動生成</button>
                        <span class="text-sm text-gray-600 self-center">例：毎週火曜を選べば、期間内の火曜分が追加されます</span>
                    </div>
                </div>
            </div>

            <div id="items" class="space-y-4">
                @php $oldItems = old('items', [['start_datetime' => '', 'end_datetime' => '', 'is_active' => true]]); @endphp
                @foreach ($oldItems as $i => $item)
                    <div class="border rounded p-4 space-y-3 item-row">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium">開始日時</label>
                                <input type="datetime-local" name="items[{{ $i }}][start_datetime]" class="border rounded w-full p-2" value="{{ $item['start_datetime'] }}" required>
                                @error("items.$i.start_datetime") <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium">終了日時</label>
                                <input type="datetime-local" name="items[{{ $i }}][end_datetime]" class="border rounded w-full p-2" value="{{ $item['end_datetime'] }}" required>
                                @error("items.$i.end_datetime") <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div>
                            <label class="inline-flex items-center">
                                <input type="hidden" name="items[{{ $i }}][is_active]" value="0">
                                <input type="checkbox" name="items[{{ $i }}][is_active]" value="1" @checked($item['is_active'])>
                                <span class="ml-2">有効</span>
                            </label>
                            @error("items.$i.is_active") <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                        </div>
                        <div class="flex justify-end">
                            <button type="button" class="px-3 py-1 border rounded text-red-700 remove-row">行を削除</button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex gap-2">
                <button type="button" id="add-row" class="px-4 py-2 border rounded">行を追加</button>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">一括作成</button>
                <a href="{{ route('admin.lesson-schedules.index') }}" class="px-4 py-2 border rounded">一覧へ戻る</a>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('items');
            const addBtn = document.getElementById('add-row');
            const recGenerateServerBtn = document.getElementById('rec-generate-server');
            const recStartDate = document.getElementById('rec-start-date');
            const recEndDate = document.getElementById('rec-end-date');
            const recStartTime = document.getElementById('rec-start-time');
            const recEndTime = document.getElementById('rec-end-time');
            const recInterval = document.getElementById('rec-interval');

            addBtn.addEventListener('click', () => {
                const index = container.querySelectorAll('.item-row').length;
                const wrapper = document.createElement('div');
                wrapper.className = 'border rounded p-4 space-y-3 item-row';
                wrapper.innerHTML = `
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium">開始日時</label>
                            <input type="datetime-local" name="items[${index}][start_datetime]" class="border rounded w-full p-2" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">終了日時</label>
                            <input type="datetime-local" name="items[${index}][end_datetime]" class="border rounded w-full p-2" required>
                        </div>
                    </div>
                    <div>
                        <label class="inline-flex items-center">
                            <input type="hidden" name="items[${index}][is_active]" value="0">
                            <input type="checkbox" name="items[${index}][is_active]" value="1" checked>
                            <span class="ml-2">有効</span>
                        </label>
                    </div>
                    <div class="flex justify-end">
                        <button type="button" class="px-3 py-1 border rounded text-red-700 remove-row">行を削除</button>
                    </div>
                `;
                container.appendChild(wrapper);
            });

            container.addEventListener('click', (e) => {
                if (e.target.classList.contains('remove-row')) {
                    const row = e.target.closest('.item-row');
                    row.remove();
                }
            });



            recGenerateServerBtn.addEventListener('click', async () => {
                const sDate = recStartDate.value;
                const eDate = recEndDate.value;
                const startTime = recStartTime.value;
                const endTime = recEndTime.value;
                const intervalWeeks = Math.max(1, Number(recInterval.value || 1));
                const weekdayCheckboxes = Array.from(document.querySelectorAll('.rec-weekday'));
                const weekdays = weekdayCheckboxes.filter(cb => cb.checked).map(cb => Number(cb.value));

                if (!sDate || !eDate || !startTime || !endTime || weekdays.length === 0) {
                    alert('期間、曜日、開始・終了時刻をすべて指定してください。');
                    return;
                }

                const payload = {
                    start_date: sDate,
                    end_date: eDate,
                    start_time: startTime,
                    end_time: endTime,
                    interval_weeks: intervalWeeks,
                    weekdays,
                };

                try {
                    const res = await fetch("{{ route('admin.lesson-schedules.bulk.generate') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(payload),
                    });
                    if (!res.ok) {
                        const txt = await res.text();
                        throw new Error(txt || '生成に失敗しました');
                    }
                    const data = await res.json();
                    const items = Array.isArray(data.items) ? data.items : [];
                    for (const item of items) {
                        const index = container.querySelectorAll('.item-row').length;
                        const wrapper = document.createElement('div');
                        wrapper.className = 'border rounded p-4 space-y-3 item-row';
                        // Convert Y-m-d H:i:s -> datetime-local
                        const toLocal = (s) => s.replace(' ', 'T').slice(0, 16);
                        wrapper.innerHTML = `
                            <div class=\"grid grid-cols-1 md:grid-cols-2 gap-4\">
                                <div>
                                    <label class=\"block text-sm font-medium\">開始日時</label>
                                    <input type=\"datetime-local\" name=\"items[${index}][start_datetime]\" class=\"border rounded w-full p-2\" value=\"${toLocal(item.start_datetime)}\" required>
                                </div>
                                <div>
                                    <label class=\"block text-sm font-medium\">終了日時</label>
                                    <input type=\"datetime-local\" name=\"items[${index}][end_datetime]\" class=\"border rounded w-full p-2\" value=\"${toLocal(item.end_datetime)}\" required>
                                </div>
                            </div>
                            <div>
                                <label class=\"inline-flex items-center\">
                                    <input type=\"hidden\" name=\"items[${index}][is_active]\" value=\"0\">
                                    <input type=\"checkbox\" name=\"items[${index}][is_active]\" value=\"1\" checked>
                                    <span class=\"ml-2\">有効</span>
                                </label>
                            </div>
                            <div class=\"flex justify-end\">
                                <button type=\"button\" class=\"px-3 py-1 border rounded text-red-700 remove-row\">行を削除</button>
                            </div>
                        `;
                        container.appendChild(wrapper);
                    }
                } catch (err) {
                    alert(err.message || '生成に失敗しました');
                }
            });
        });
    </script>
</x-app-layout>
