<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">通知テンプレート一覧</h2>
    </x-slot>

    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-4">
            <a href="{{ route('admin.notification-templates.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded">新規作成</a>
        </div>

        @if (session('status'))
            <div class="mb-4 text-green-700">{{ session('status') }}</div>
        @endif

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-2 py-1 text-left">ID</th>
                        <th class="px-2 py-1 text-left">名称</th>
                        <th class="px-2 py-1 text-left">種別</th>
                        <th class="px-2 py-1 text-left">件名</th>
                        <th class="px-2 py-1 text-left">有効</th>
                        <th class="px-2 py-1 text-left">操作</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($templates as $template)
                    <tr class="border-t">
                        <td class="px-2 py-1">{{ $template->id }}</td>
                        <td class="px-2 py-1">
                            <a class="text-blue-700 underline" href="{{ route('admin.notification-templates.show', $template) }}">{{ $template->name }}</a>
                        </td>
                        <td class="px-2 py-1">{{ $template->type }}</td>
                        <td class="px-2 py-1">{{ $template->subject }}</td>
                        <td class="px-2 py-1">{{ $template->is_active ? 'はい' : 'いいえ' }}</td>
                        <td class="px-2 py-1 space-x-2">
                            <a href="{{ route('admin.notification-templates.edit', $template) }}" class="text-blue-700 underline">編集</a>
                            <form class="inline" method="POST" action="{{ route('admin.notification-templates.destroy', $template) }}" onsubmit="return confirm('削除しますか？')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-700 underline">削除</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-2 py-4 text-center" colspan="6">データがありません</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $templates->links() }}
        </div>
    </div>
</x-app-layout>


