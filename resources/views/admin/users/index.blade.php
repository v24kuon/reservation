<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            ユーザー一覧
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="GET" class="mb-4 grid grid-cols-1 md:grid-cols-5 gap-3">
                        <input type="text" name="name" value="{{ $filters['name'] ?? '' }}" placeholder="名前" class="border rounded p-2">
                        <input type="text" name="email" value="{{ $filters['email'] ?? '' }}" placeholder="メール" class="border rounded p-2">
                        <!-- role filter removed: this page manages only general users -->
                        <input type="date" name="registered_from" value="{{ $filters['registered_from'] ?? '' }}" class="border rounded p-2">
                        <input type="date" name="registered_to" value="{{ $filters['registered_to'] ?? '' }}" class="border rounded p-2">
                        <div class="md:col-span-5">
                            <x-primary-button type="submit">検索</x-primary-button>
                            <x-secondary-button as="a" href="{{ route('admin.users.create') }}">新規作成</x-secondary-button>
                        </div>
                    </form>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left">ID</th>
                                <th class="px-4 py-2 text-left">名前</th>
                                <th class="px-4 py-2 text-left">メール</th>
                                <!-- role column removed -->
                                <th class="px-4 py-2 text-left">登録日</th>
                                <th class="px-4 py-2 text-left">操作</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($users as $user)
                                <tr>
                                    <td class="px-4 py-2">{{ $user->id }}</td>
                                    <td class="px-4 py-2"><a class="text-indigo-600" href="{{ route('admin.users.show', $user) }}">{{ $user->name }}</a></td>
                                    <td class="px-4 py-2">{{ $user->email }}</td>
                                    <!-- role value hidden -->
                                    <td class="px-4 py-2">{{ $user->created_at?->format('Y-m-d') }}</td>
                                    <td class="px-4 py-2 space-x-2">
                                        <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-600">編集</a>
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm(@js('削除しますか？')); ">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600">削除</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            @if($users->isEmpty())
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">該当するユーザーがいません</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
