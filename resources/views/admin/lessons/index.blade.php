<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            レッスン一覧
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-4">
                        <a href="{{ route('admin.lessons.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded">新規作成</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="text-left">
                                    <th class="px-2 py-1">ID</th>
                                    <th class="px-2 py-1">名称</th>
                                    <th class="px-2 py-1">店舗</th>
                                    <th class="px-2 py-1">カテゴリ</th>
                                    <th class="px-2 py-1">インストラクター</th>
                                    <th class="px-2 py-1">有効</th>
                                    <th class="px-2 py-1">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lessons as $lesson)
                                    <tr class="border-t">
                                        <td class="px-2 py-1">{{ $lesson->id }}</td>
                                        <td class="px-2 py-1">{{ $lesson->name }}</td>
                                        <td class="px-2 py-1">{{ $lesson->store->name ?? '-' }}</td>
                                        <td class="px-2 py-1">{{ $lesson->category->name ?? '-' }}</td>
                                        <td class="px-2 py-1">{{ $lesson->instructor->name ?? '-' }}</td>
                                        <td class="px-2 py-1">{{ $lesson->is_active ? '有効' : '無効' }}</td>
                                        <td class="px-2 py-1 space-x-2">
                                            <a href="{{ route('admin.lessons.edit', $lesson) }}" class="text-blue-600">編集</a>
                                            <form action="{{ route('admin.lessons.destroy', $lesson) }}" method="POST" class="inline" onsubmit="return confirm('削除しますか？');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600">削除</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $lessons->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


