<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">通知テンプレート作成</h2>
    </x-slot>

    <div class="container mx-auto px-4 py-6">
        <form method="POST" action="{{ route('admin.notification-templates.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm">名称</label>
                <input type="text" name="name" value="{{ old('name') }}" class="border rounded w-full p-2">
                @error('name') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="block text-sm">種別</label>
                <select name="type" class="border rounded w-full p-2">
                    <option value="">-- 選択してください --</option>
                    <option value="reservation_confirmation" @selected(old('type')==='reservation_confirmation')>予約確認</option>
                    <option value="reminder" @selected(old('type')==='reminder')>リマインダー</option>
                    <option value="cancellation" @selected(old('type')==='cancellation')>キャンセル</option>
                    <option value="subscription_update" @selected(old('type')==='subscription_update')>サブスク更新</option>
                </select>
                @error('type') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="block text-sm">件名</label>
                <input type="text" name="subject" value="{{ old('subject') }}" class="border rounded w-full p-2">
                @error('subject') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="block text-sm">本文（テキスト）</label>
                <textarea name="body_text" rows="6" class="border rounded w-full p-2" placeholder="例: @{{user_name}} 様、@{{lesson_name}} のご予約を受け付けました。日時: @{{datetime}}">{{ old('body_text') }}</textarea>
                @error('body_text') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="block text-sm">利用できる変数（システム設定の許可リスト）</label>
                <p class="text-xs text-gray-600 mt-1">本文中では &#123;&#123;user_name&#125;&#125; のように記述します（下のチップをクリックでコピー）。</p>
            </div>

            @php
                $grouped = [];
                foreach (($whitelist ?? []) as $ph) {
                    $pos = strpos($ph, '_');
                    $tbl = $pos !== false ? substr($ph, 0, $pos) : 'others';
                    $grouped[$tbl][] = $ph;
                }
            @endphp

            @if(!empty($grouped))
            <div class="border rounded p-3 bg-gray-50">
                <div class="font-semibold mb-2">テーブル別</div>
                <div class="grid md:grid-cols-2 gap-4">
                    @foreach($grouped as $tbl => $items)
                        <fieldset class="border rounded p-2">
                            <legend class="text-sm font-medium px-1">{{ $tableLabels[$tbl] ?? $tbl }}</legend>
                            <div class="flex flex-wrap gap-2 mt-2">
                                @foreach($items as $ph)
                                    <button type="button"
                                            class="px-2 py-1 text-xs border rounded bg-white copy-chip"
                                            data-ph="{{ $ph }}"
                                            data-copy="{{ '{{' . $ph . '}}' }}"
                                    >&#123;&#123;{{ $ph }}&#125;&#125;</button>
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
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', '1'))>
                    <span class="ml-2">有効</span>
                </label>
                @error('is_active') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
            </div>

            <div class="flex gap-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">保存</button>
                <a href="{{ route('admin.notification-templates.index') }}" class="px-4 py-2 rounded border">一覧へ戻る</a>
            </div>
        </form>
    </div>
    <script>
        (function() {
            function copy(text){
                if(navigator.clipboard){ navigator.clipboard.writeText(text); return; }
                const ta=document.createElement('textarea'); ta.value=text; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta);
            }
            document.querySelectorAll('.copy-chip').forEach(btn => btn.addEventListener('click', () => copy(btn.dataset.copy)));
        })();
    </script>
</x-app-layout>
