<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            インストラクター一覧
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-4">
                        <x-primary-button as="a" href="{{ route('admin.instructors.create') }}">新規作成</x-primary-button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="text-left">
                                    <th class="px-2 py-1">ID</th>
                                    <th class="px-2 py-1">氏名</th>
                                    <th class="px-2 py-1">メール</th>
                                    <th class="px-2 py-1">作成日</th>
                                    <th class="px-2 py-1">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($instructors as $instructor)
                                    <tr class="border-t">
                                        <td class="px-2 py-1">{{ $instructor->id }}</td>
                                        <td class="px-2 py-1">
                                            <a href="{{ route('admin.instructors.show', $instructor) }}" class="text-indigo-600">{{ $instructor->name }}</a>
                                        </td>
                                        <td class="px-2 py-1">{{ $instructor->email }}</td>
                                        <td class="px-2 py-1">{{ $instructor->created_at?->format('Y-m-d') }}</td>
                                        <td class="px-2 py-1 space-x-2">
                                            @can('update', $instructor)
                                            <a href="{{ route('admin.instructors.edit', $instructor) }}" class="text-blue-600">編集</a>
                                            @endcan
                                            @can('delete', $instructor)
                                            <form action="{{ route('admin.instructors.destroy', $instructor) }}" method="POST" class="inline" onsubmit="return confirm('削除しますか？');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600">削除</button>
                                            </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="border-t">
                                        <td colspan="5" class="px-2 py-6 text-center text-gray-500">インストラクターが登録されていません</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $instructors->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
