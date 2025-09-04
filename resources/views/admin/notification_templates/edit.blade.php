<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">通知テンプレート編集</h2>
    </x-slot>

    <div class="container mx-auto px-4 py-6">
        <form method="POST" action="{{ route('admin.notification-templates.update', $template) }}" class="space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label class="block text-sm">名称</label>
                <input type="text" name="name" value="{{ old('name', $template->name) }}" class="border rounded w-full p-2">
                @error('name') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="block text-sm">種別</label>
                <select name="type" class="border rounded w-full p-2">
                    <option value="reservation_confirmation" @selected(old('type', $template->type)==='reservation_confirmation')>予約確認</option>
                    <option value="reminder" @selected(old('type', $template->type)==='reminder')>リマインダー</option>
                    <option value="cancellation" @selected(old('type', $template->type)==='cancellation')>キャンセル</option>
                    <option value="subscription_update" @selected(old('type', $template->type)==='subscription_update')>サブスク更新</option>
                </select>
                @error('type') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="block text-sm">件名</label>
                <input type="text" name="subject" value="{{ old('subject', $template->subject) }}" class="border rounded w-full p-2">
                @error('subject') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="block text-sm">本文（テキスト）</label>
                <textarea name="body_text" rows="6" class="border rounded w-full p-2">{{ old('body_text', $template->body_text) }}</textarea>
                @error('body_text') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="block text-sm">利用できる変数（JSON文字列で入力）</label>
                <input id="variables-json" type="text" name="variables" value='{{ old('variables', json_encode($template->variables)) }}' class="border rounded w-full p-2" placeholder='["users_name","lessons_name"]'>
                <p class="text-xs text-gray-600 mt-1">本文中では @{{users_name}} のように記述します。チェックの選択内容は上のJSONに同期されます。</p>
                @error('variables') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
            </div>

            @if(!empty($availableByTable))
            <div class="border rounded p-3 bg-gray-50">
                <div class="font-semibold mb-2">テーブル別の候補</div>
                <div class="grid md:grid-cols-2 gap-4">
                    @foreach($availableByTable as $table => $placeholders)
                        <fieldset class="border rounded p-2">
                            <legend class="text-sm font-medium px-1">{{ $tableLabels[$table] ?? $table }}</legend>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-2">
                                @foreach($placeholders as $placeholder => $column)
                                    <label class="inline-flex items-center text-sm">
                                        <input type="checkbox" class="var-checkbox" value="{{ $placeholder }}">
                                        <span class="ml-2">&#123;&#123;{{ $placeholder }}&#125;&#125; <span class="text-gray-500">({{ $column }})</span></span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    @endforeach
                </div>
            </div>
            @endif

            <div>
                <label class="inline-flex items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $template->is_active ? '1' : '0'))>
                    <span class="ml-2">有効</span>
                </label>
                @error('is_active') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
            </div>

            <div class="flex gap-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">更新</button>
                <a href="{{ route('admin.notification-templates.index') }}" class="px-4 py-2 rounded border">一覧へ戻る</a>
            </div>
        </form>
    </div>

    <script>
        (function() {
            const jsonInput = document.getElementById('variables-json');
            const checkboxes = Array.from(document.querySelectorAll('.var-checkbox'));

            function applyJsonToCheckboxes() {
                try {
                    const list = JSON.parse(jsonInput.value || '[]');
                    const selected = new Set(Array.isArray(list) ? list : []);
                    checkboxes.forEach(cb => { cb.checked = selected.has(cb.value); });
                } catch (e) {
                    // ignore invalid json
                }
            }

            function applyCheckboxesToJson() {
                const values = checkboxes.filter(cb => cb.checked).map(cb => cb.value);
                jsonInput.value = JSON.stringify(values);
            }

            checkboxes.forEach(cb => cb.addEventListener('change', applyCheckboxesToJson));
            jsonInput.addEventListener('change', applyJsonToCheckboxes);
            document.addEventListener('DOMContentLoaded', applyJsonToCheckboxes);
            applyJsonToCheckboxes();
        })();
    </script>
</x-app-layout>
