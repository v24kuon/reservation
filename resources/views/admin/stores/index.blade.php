<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            店舗一覧
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-4">
                        <a href="{{ route('admin.stores.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded">新規作成</a>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left">ID</th>
                                <th class="px-4 py-2 text-left">名称</th>
                                <th class="px-4 py-2 text-left">電話</th>
                                <th class="px-4 py-2 text-left">状態</th>
                                <th class="px-4 py-2 text-left">操作</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($stores as $store)
                                <tr>
                                    <td class="px-4 py-2">{{ $store->id }}</td>
                                    <td class="px-4 py-2">
                                        <a class="text-indigo-600" href="{{ route('admin.stores.show', $store) }}">{{ $store->name }}</a>
                                    </td>
                                    <td class="px-4 py-2">{{ $store->formatted_phone ?? $store->phone }}</td>
                                    <td class="px-4 py-2">{{ $store->is_active ? '有効' : '無効' }}</td>
                                    <td class="px-4 py-2 space-x-2">
                                        <a href="{{ route('admin.stores.edit', $store) }}" class="text-blue-600">編集</a>
                                        <form action="{{ route('admin.stores.destroy', $store) }}" method="POST" class="inline" onsubmit="return confirm('削除しますか？');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600">削除</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $stores->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
