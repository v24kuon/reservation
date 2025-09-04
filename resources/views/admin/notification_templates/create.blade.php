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
                <label class="block text-sm">利用できる変数（JSON文字列で入力）</label>
                <input type="text" name="variables" value='{{ old('variables', "[\"user_name\",\"lesson_name\",\"store_name\",\"datetime\"]") }}' class="border rounded w-full p-2">
                <p class="text-xs text-gray-600 mt-1">例: ["user_name","lesson_name","store_name","datetime"]。本文中では @{{user_name}}、@{{lesson_name}}、@{{store_name}}、@{{datetime}} のように記述します。</p>
                @error('variables') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
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
                <a href="{{ route('admin.notification-templates.index') }}" class="px-4 py-2 rounded border">一覧へ戻る</a>
            </div>
        </form>
    </div>
</x-app-layout>
