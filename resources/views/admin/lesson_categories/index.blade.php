<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            レッスンカテゴリ一覧
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-4">
                        <x-primary-button as="a" href="{{ route('admin.lesson-categories.create') }}">新規作成</x-primary-button>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th scope="col" class="px-4 py-2 text-left">ID</th>
                                <th scope="col" class="px-4 py-2 text-left">名称</th>
                                <th scope="col" class="px-4 py-2 text-left">親カテゴリ</th>
                                <th scope="col" class="px-4 py-2 text-left">表示順</th>
                                <th scope="col" class="px-4 py-2 text-left">状態</th>
                                <th scope="col" class="px-4 py-2 text-left">操作</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($categories as $category)
                                <tr>
                                    <td class="px-4 py-2">{{ $category->id }}</td>
                                    <td class="px-4 py-2">
                                        <a class="text-indigo-600" href="{{ route('admin.lesson-categories.show', $category) }}">{{ $category->name }}</a>
                                    </td>
                                    <td class="px-4 py-2">{{ $category->parent?->name ?? '-' }}</td>
                                    <td class="px-4 py-2">{{ $category->sort_order }}</td>
                                    <td class="px-4 py-2">{{ $category->is_active ? '有効' : '無効' }}</td>
                                    <td class="px-4 py-2 space-x-2">
                                        @can('update', $category)
                                            <a href="{{ route('admin.lesson-categories.edit', $category) }}" class="text-blue-600">編集</a>
                                        @endcan
                                        @can('delete', $category)
                                            @if($category->parent_id)
                                            <form action="{{ route('admin.lesson-categories.destroy', $category) }}" method="POST" class="inline" onsubmit="return confirm({{ Js::from("カテゴリ「{$category->name}」を削除しますか？この操作は取り消せません。") }});">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600">削除</button>
                                            </form>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $categories->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
