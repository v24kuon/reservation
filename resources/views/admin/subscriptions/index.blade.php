<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            サブスクリプション一覧
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="GET" class="mb-4 grid grid-cols-1 md:grid-cols-6 gap-3">
                        <input type="text" name="user" value="{{ $filters['user'] ?? '' }}" placeholder="ユーザー名/メール" class="border rounded p-2">
                        <select name="plan_id" class="border rounded p-2">
                            <option value="">プラン</option>
                            @foreach($plans as $p)
                                <option value="{{ $p->id }}" @selected(($filters['plan_id'] ?? '') == $p->id)>{{ $p->name }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="status" value="{{ $filters['status'] ?? '' }}" placeholder="status" class="border rounded p-2">
                        <input type="text" name="payment_status" value="{{ $filters['payment_status'] ?? '' }}" placeholder="payment_status" class="border rounded p-2">
                        <input type="date" name="period_from" value="{{ $filters['period_from'] ?? '' }}" class="border rounded p-2">
                        <input type="date" name="period_to" value="{{ $filters['period_to'] ?? '' }}" class="border rounded p-2">
                        <div class="md:col-span-6">
                            <x-primary-button type="submit">検索</x-primary-button>
                            <x-secondary-button as="a" href="{{ route('admin.subscriptions.create') }}">新規作成</x-secondary-button>
                        </div>
                    </form>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left">ID</th>
                                <th class="px-4 py-2 text-left">ユーザー</th>
                                <th class="px-4 py-2 text-left">プラン</th>
                                <th class="px-4 py-2 text-left">期間</th>
                                <th class="px-4 py-2 text-left">状態</th>
                                <th class="px-4 py-2 text-left">支払い</th>
                                <th class="px-4 py-2 text-left">操作</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($subscriptions as $s)
                                <tr>
                                    <td class="px-4 py-2">{{ $s->id }}</td>
                                    <td class="px-4 py-2">
                                        <a class="text-indigo-600" href="{{ route('admin.subscriptions.show', $s) }}">{{ $s->user?->name }} ({{ $s->user?->email }})</a>
                                    </td>
                                    <td class="px-4 py-2">{{ $s->plan?->name }}</td>
                                    <td class="px-4 py-2">{{ $s->current_period_start?->format('Y-m-d') }} ～ {{ $s->current_period_end?->format('Y-m-d') }}</td>
                                    <td class="px-4 py-2">{{ $s->status }}</td>
                                    <td class="px-4 py-2">{{ $s->payment_status }}</td>
                                    <td class="px-4 py-2 space-x-2">
                                        <a href="{{ route('admin.subscriptions.edit', $s) }}" class="text-blue-600">編集</a>
                                        <form action="{{ route('admin.subscriptions.destroy', $s) }}" method="POST" class="inline" onsubmit="return confirm(@js('削除しますか？キャンセル済のみ削除可能です')); ">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600">削除</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            @if($subscriptions->isEmpty())
                                <tr>
                                    <td colspan="7" class="px-4 py-6 text-center text-gray-500">該当するサブスクリプションがありません</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $subscriptions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
