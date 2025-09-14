<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">通知テンプレート一覧</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-4">
                        <a href="{{ route('admin.notification-templates.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded">新規作成</a>
                    </div>

                    @if (session('status'))
                        <div class="mb-4 text-green-700">{{ session('status') }}</div>
                    @endif

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th scope="col" class="px-4 py-2 text-left">ID</th>
                                <th scope="col" class="px-4 py-2 text-left">名称</th>
                                <th scope="col" class="px-4 py-2 text-left">種別</th>
                                <th scope="col" class="px-4 py-2 text-left">件名</th>
                                <th scope="col" class="px-4 py-2 text-left">有効</th>
                                <th scope="col" class="px-4 py-2 text-left">操作</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($templates as $template)
                                <tr>
                                    <td class="px-4 py-2">{{ $template->id }}</td>
                                    <td class="px-4 py-2">
                                        <a class="text-indigo-600" href="{{ route('admin.notification-templates.show', $template) }}">{{ $template->name }}</a>
                                    </td>
                                    <td class="px-4 py-2">{{ $template->type }}</td>
                                    <td class="px-4 py-2 max-w-[40ch] truncate">{{ $template->subject }}</td>
                                    <td class="px-4 py-2">{{ $template->is_active ? 'はい' : 'いいえ' }}</td>
                                    <td class="px-4 py-2 space-x-2">
                                        @can('update', $template)
                                        <a href="{{ route('admin.notification-templates.edit', $template) }}" class="text-blue-600" aria-label="『{{ $template->name }}』を編集">編集</a>
                                        @endcan
                                        @can('delete', $template)
                                        <form action="{{ route('admin.notification-templates.destroy', $template) }}" method="POST" class="inline" onsubmit="return confirm('削除しますか？');" aria-label="『{{ $template->name }}』を削除">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600">削除</button>
                                        </form>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $templates->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
