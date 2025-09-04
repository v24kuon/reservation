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
                <label class="block text-sm">種別（例: reservation_confirmation, reminder など）</label>
                <input type="text" name="type" value="{{ old('type') }}" class="border rounded w-full p-2">
                @error('type') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="block text-sm">件名</label>
                <input type="text" name="subject" value="{{ old('subject') }}" class="border rounded w-full p-2">
                @error('subject') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="block text-sm">本文（テキスト）</label>
                <textarea name="body_text" rows="4" class="border rounded w-full p-2">{{ old('body_text') }}</textarea>
                @error('body_text') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="block text-sm">本文（HTML）</label>
                <textarea name="body_html" rows="4" class="border rounded w-full p-2">{{ old('body_html') }}</textarea>
                @error('body_html') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="block text-sm">変数（JSON配列例: ["user_name","lesson_name"])</label>
                <input type="text" name="variables" value='{{ old('variables') }}' class="border rounded w-full p-2" placeholder='["user_name","lesson_name"]'>
                @error('variables') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
            </div>

            @include('admin.notification_templates._form-variables-note')

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


