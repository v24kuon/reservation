<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            レッスンカテゴリ詳細
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-4">
                    <div class="flex justify-between">
                        <a href="{{ route('admin.lesson-categories.edit', $category) }}" class="px-3 py-2 bg-indigo-600 text-white rounded">編集</a>
                        <a href="{{ route('admin.lesson-categories.index') }}" class="px-3 py-2 bg-gray-200 rounded">一覧へ</a>
                    </div>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm text-gray-500">名称</dt>
                            <dd class="mt-1 text-gray-900">{{ $category->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">親カテゴリ</dt>
                            <dd class="mt-1 text-gray-900">{{ $category->parent?->name ?? '-' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-sm text-gray-500">説明</dt>
                            <dd class="mt-1 text-gray-900 whitespace-pre-line">{{ $category->description }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">表示順</dt>
                            <dd class="mt-1 text-gray-900">{{ $category->sort_order }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">状態</dt>
                            <dd class="mt-1 text-gray-900">{{ $category->is_active ? '有効' : '無効' }}</dd>
                        </div>
                    </dl>

                    <div>
                        <h3 class="text-lg font-semibold">子カテゴリ</h3>
                        <ul class="list-disc ml-6">
                            @forelse($category->children as $child)
                                <li><a class="text-blue-600" href="{{ route('admin.lesson-categories.show', $child) }}">{{ $child->name }}</a></li>
                            @empty
                                <li class="text-gray-500">なし</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


