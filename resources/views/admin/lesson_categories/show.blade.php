<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            レッスンカテゴリ詳細
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-2">
                    <p><strong>名称:</strong> {{ $category->name }}</p>
                    <p><strong>親カテゴリ:</strong> {{ $category->parent?->name ?? '-' }}</p>
                    <p><strong>説明:</strong> <span class="whitespace-pre-line">{{ $category->description }}</span></p>
                    <p><strong>表示順:</strong> {{ $category->sort_order }}</p>
                    <p><strong>状態:</strong> {{ $category->is_active ? '有効' : '無効' }}</p>

                    <div class="pt-4 flex space-x-2">
                        <x-primary-button as="a" href="{{ route('admin.lesson-categories.edit', $category) }}">編集</x-primary-button>
                        <x-secondary-button href="{{ route('admin.lesson-categories.index') }}">戻る</x-secondary-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
