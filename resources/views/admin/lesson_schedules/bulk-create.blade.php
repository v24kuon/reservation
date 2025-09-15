<x-admin-layout>
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
                        <option value="{{ $lesson->id }}" data-duration="{{ $lesson->duration }}" @selected(old('lesson_id') == $lesson->id)>{{ $lesson->name }} (ID:{{ $lesson->id }})</option>
                    @endforeach
                </select>
                @error('lesson_id') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
            </div>

            <div class="rounded border p-4 space-y-3">
                <h3 class="font-semibold">繰り返し生成（任意）</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium">期間（開始）</label>
                        <input type="date" id="rec-start-date" class="border rounded w-full p-2" placeholder="YYYY-MM-DD">
                    </div>
                    <div>
                        <label class="block text-sm font-medium">期間（終了）</label>
                        <input type="date" id="rec-end-date" class="border rounded w-full p-2" placeholder="YYYY-MM-DD">
                    </div>
                    <div>
                        <label class="block text-sm font-medium">開始時刻</label>
                        <input type="time" id="rec-start-time" class="border rounded w-full p-2" placeholder="HH:MM">
                    </div>
                    <div>
                        <label class="block text-sm font-medium">終了時刻</label>
                        <input type="time" id="rec-end-time" class="border rounded w-full p-2" placeholder="HH:MM">
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
                <button type="submit" class="bg-primary text-primary-foreground px-4 py-2 rounded">一括作成</button>
                <a href="{{ route('admin.lesson-schedules.index') }}" class="px-4 py-2 bg-muted text-foreground rounded">一覧へ戻る</a>
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
            const lessonSelect = document.querySelector('select[name="lesson_id"]');

            // 既存行の最大 index を検出し、次に使う index を単調増加で採番
            let nextIndex = (() => {
                const names = Array.from(container.querySelectorAll('input[name*="[start_datetime]"], input[name*="[end_datetime]"]'))
                    .map(el => el.name);
                const idxs = names.map(n => {
                    const m = n.match(/items\[(\d+)\]/);
                    return m ? Number(m[1]) : NaN;
                }).filter(Number.isFinite);
                return idxs.length ? Math.max(...idxs) + 1 : 0;
            })();

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

            // 共通の終了時刻計算関数
            const fillEndFromStart = (startValue) => {
                const dur = getSelectedDuration();
                if (!dur || !startValue) return null;
                const dt = new Date(startValue);
                if (Number.isNaN(dt.getTime())) return null;
                dt.setMinutes(dt.getMinutes() + dur);
                return toDatetimeLocal(dt);
            };

            // 全行の終了時刻を再計算
            const recalcAllEnds = () => {
                const dur = getSelectedDuration();
                if (!dur) return;
                document.querySelectorAll('.item-row').forEach(row => {
                    const s = row.querySelector('input[name*="[start_datetime]"]');
                    const e = row.querySelector('input[name*="[end_datetime]"]');
                    if (s?.value) {
                        const v = fillEndFromStart(s.value);
                        if (v) e.value = v;
                    }
                });
                // 繰り返しセクション
                updateRecEndTime();
            };

            addBtn.addEventListener('click', () => {
                const index = nextIndex++;
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

                // start -> end 自動補完
                const startEl = wrapper.querySelector(`input[name="items[${index}][start_datetime]"]`);
                const endEl = wrapper.querySelector(`input[name="items[${index}][end_datetime]"]`);
                const updateEnd = () => {
                    const v = fillEndFromStart(startEl?.value);
                    if (v) endEl.value = v;
                };
                startEl?.addEventListener('change', updateEnd);
                startEl?.addEventListener('input', updateEnd);
                startEl?.addEventListener('blur', updateEnd);
            });

            container.addEventListener('click', (e) => {
                if (e.target.classList.contains('remove-row')) {
                    const row = e.target.closest('.item-row');
                    row.remove();
                }
            });

            // Auto-open native date/time pickers on focus (supported browsers)
            const autoOpenPicker = (el) => {
                if (!el || el.dataset.pickerBound === '1') { return; }
                el.addEventListener('focus', () => {
                    if (typeof el.showPicker === 'function') {
                        try { el.showPicker(); } catch(_) {}
                    }
                });
                el.dataset.pickerBound = '1';
            };

            [recStartDate, recEndDate, recStartTime, recEndTime].forEach(autoOpenPicker);

            // Delegate for dynamically added inputs
            container.addEventListener('focusin', (e) => {
                const t = e.target;
                if (t && (t.type === 'date' || t.type === 'time' || t.type === 'datetime-local')) {
                    autoOpenPicker(t);
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
                        const index = nextIndex++;
                        const wrapper = document.createElement('div');
                        wrapper.className = 'border rounded p-4 space-y-3 item-row';
                        // Robustly convert API payload to datetime-local (TZ-aware when possible)
                        const toLocal = (s) => {
                            const isoLike = s.includes('T') ? s : s.replace(' ', 'T');
                            const m = isoLike.match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})(?::(\d{2}))?/);
                            if (!m) return isoLike.slice(0, 16);
                            const [_, y, mo, d, hh, mm, ss] = m;
                            const local = new Date(Number(y), Number(mo) - 1, Number(d), Number(hh), Number(mm), Number(ss || 0));
                            return toDatetimeLocal(local);
                        };
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

                        // start -> end 自動補完（生成分にも適用）
                        const startEl = wrapper.querySelector(`input[name=\"items[${index}][start_datetime]\"]`);
                        const endEl = wrapper.querySelector(`input[name=\"items[${index}][end_datetime]\"]`);
                        const updateEnd = () => {
                            const v = fillEndFromStart(startEl?.value);
                            if (v) endEl.value = v;
                        };
                        startEl?.addEventListener('change', updateEnd);
                        startEl?.addEventListener('input', updateEnd);
                        startEl?.addEventListener('blur', updateEnd);
                    }
                } catch (err) {
                    alert(err.message || '生成に失敗しました');
                }
            });

            // 既存の行にもイベントリスナーを設定
            const setupRowListeners = (row) => {
                const startInput = row.querySelector('input[name*="[start_datetime]"]');
                const endInput = row.querySelector('input[name*="[end_datetime]"]');
                if (startInput && endInput) {
                    const updateEnd = () => {
                        const v = fillEndFromStart(startInput.value);
                        if (v) endInput.value = v;
                    };
                    startInput.addEventListener('change', updateEnd);
                    startInput.addEventListener('input', updateEnd);
                    startInput.addEventListener('blur', updateEnd);
                }
            };

            // 既存の行すべてにイベントリスナーを設定
            document.querySelectorAll('.item-row').forEach(setupRowListeners);

            // 繰り返し生成セクション: 開始時刻から終了時刻を自動補完
            const updateRecEndTime = () => {
                const dur = getSelectedDuration();
                if (!dur || !recStartTime?.value) return;
                const parts = recStartTime.value.split(':');
                if (parts.length < 2) return;
                const hours = parseInt(parts[0], 10);
                const minutes = parseInt(parts[1], 10);
                if (!Number.isFinite(hours) || !Number.isFinite(minutes)) return;
                const total = hours * 60 + minutes + dur;
                const endHours = Math.floor(total / 60) % 24;
                const endMinutes = total % 60;
                recEndTime.value = `${pad(endHours)}:${pad(endMinutes)}`;
            };
            recStartTime?.addEventListener('change', updateRecEndTime);
            recStartTime?.addEventListener('input', updateRecEndTime);
            recStartTime?.addEventListener('blur', updateRecEndTime);

            // レッスン変更時に全行の終了時刻を再計算
            lessonSelect?.addEventListener('change', recalcAllEnds);
            // 初期実行（選択済みレッスンや既存行がある場合の安定化）
            recalcAllEnds();
        });
    </script>
</x-admin-layout>
