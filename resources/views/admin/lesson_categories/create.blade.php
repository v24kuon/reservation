<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            レッスンカテゴリ作成
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.lesson-categories.store') }}" class="space-y-4">
                        @csrf

                        <div>
                            <label for="name" class="block text-sm font-medium">名称</label>
                            <input id="name" name="name" type="text" class="mt-1 w-full border rounded p-2" value="{{ old('name') }}" required>
                            @error('name')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="parent_id" class="block text-sm font-medium">親カテゴリ</label>
                            <select id="parent_id" name="parent_id" class="mt-1 w-full border rounded p-2">
                                <option value="">なし</option>
                                @foreach($parents as $parent)
                                    <option value="{{ $parent->id }}" @selected(old('parent_id') == $parent->id)>{{ $parent->name }}</option>
                                @endforeach
                            </select>
                            @error('parent_id')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium">説明</label>
                            <textarea id="description" name="description" class="mt-1 w-full border rounded p-2" rows="3">{{ old('description') }}</textarea>
                            @error('description')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="sort_order" class="block text-sm font-medium">表示順</label>
                                <input id="sort_order" name="sort_order" type="number" class="mt-1 w-full border rounded p-2" value="{{ old('sort_order', 0) }}" min="0" max="100000" required>
                                @error('sort_order')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div class="flex items-center space-x-2 mt-6">
                                <input type="hidden" name="is_active" value="0">
                                <input id="is_active" name="is_active" type="checkbox" value="1" class="rounded" {{ old('is_active', 1) ? 'checked' : '' }}>
                                <label for="is_active">有効</label>
                            </div>
                        </div>

                        <div class="pt-4 flex space-x-2">
                            <button class="px-4 py-2 bg-indigo-600 text-white rounded" type="submit">保存</button>
                            <a href="{{ route('admin.lesson-categories.index') }}" class="px-4 py-2 bg-gray-200 rounded">戻る</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


