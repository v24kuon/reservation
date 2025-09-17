<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            月謝プラン一覧
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-4">
                        <x-primary-button as="a" href="{{ route('admin.subscription-plans.create') }}">新規作成</x-primary-button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th scope="col" class="px-4 py-2 text-left">ID</th>
                                    <th scope="col" class="px-4 py-2 text-left">プラン名</th>
                                    <th scope="col" class="px-4 py-2 text-left">価格</th>
                                    <th scope="col" class="px-4 py-2 text-left">月間回数</th>
                                    <th scope="col" class="px-4 py-2 text-left">状態</th>
                                    <th scope="col" class="px-4 py-2 text-left">操作</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($plans as $plan)
                                    <tr>
                                        <td class="px-4 py-2">{{ $plan->id }}</td>
                                        <td class="px-4 py-2">
                                            <a class="text-indigo-600" href="{{ route('admin.subscription-plans.show', $plan) }}">{{ $plan->name }}</a>
                                        </td>
                                        <td class="px-4 py-2">¥{{ number_format($plan->price) }}</td>
                                        <td class="px-4 py-2">{{ $plan->lesson_count }}回</td>
                                        <td class="px-4 py-2">{{ $plan->is_active ? '有効' : '無効' }}</td>
                                        <td class="px-4 py-2 space-x-2">
                                            @can('update', $plan)
                                                <a href="{{ route('admin.subscription-plans.edit', $plan) }}" class="text-blue-600">編集</a>
                                            @endcan
                                            @can('delete', $plan)
                                                <form action="{{ route('admin.subscription-plans.destroy', $plan) }}" method="POST" class="inline" onsubmit="return confirm('プラン「{{ $plan->name }}」を削除しますか？この操作は取り消せません。');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600">削除</button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">プランがありません。</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">{{ $plans->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
