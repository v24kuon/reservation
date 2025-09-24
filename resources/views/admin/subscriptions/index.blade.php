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
                    <form method="GET" class="mb-4 grid grid-cols-1 md:grid-cols-5 gap-3">
                        <div class="flex flex-col">
                            <label for="filter_user" class="text-sm text-gray-700 dark:text-gray-300">ユーザー名/メール</label>
                            <input id="filter_user" type="text" name="user" value="{{ $filters['user'] ?? '' }}" placeholder="ユーザー名/メール" class="border rounded p-2">
                        </div>
                        <div class="flex flex-col">
                            <label for="filter_plan_id" class="text-sm text-gray-700 dark:text-gray-300">プラン</label>
                            <select id="filter_plan_id" name="plan_id" class="border rounded p-2">
                                <option value="">プラン</option>
                                @foreach($plans as $p)
                                    <option value="{{ $p->id }}" @selected(($filters['plan_id'] ?? '') == $p->id)>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-col">
                            <label for="filter_status" class="text-sm text-gray-700 dark:text-gray-300">状態</label>
                            <select id="filter_status" name="status" class="border rounded p-2">
                                <option value="">状態</option>
                                <option value="active" @selected(($filters['status'] ?? '') == 'active')>active</option>
                                <option value="canceled" @selected(($filters['status'] ?? '') == 'canceled')>canceled</option>
                                <option value="past_due" @selected(($filters['status'] ?? '') == 'past_due')>past_due</option>
                                <option value="trialing" @selected(($filters['status'] ?? '') == 'trialing')>trialing</option>
                            </select>
                        </div>
                        <div class="flex flex-col">
                            <label for="filter_payment_status" class="text-sm text-gray-700 dark:text-gray-300">支払い状況</label>
                            <select id="filter_payment_status" name="payment_status" class="border rounded p-2">
                                <option value="">支払い状況</option>
                                <option value="paid" @selected(($filters['payment_status'] ?? '') == 'paid')>paid</option>
                                <option value="unpaid" @selected(($filters['payment_status'] ?? '') == 'unpaid')>unpaid</option>
                                <option value="failed" @selected(($filters['payment_status'] ?? '') == 'failed')>failed</option>
                                <option value="pending" @selected(($filters['payment_status'] ?? '') == 'pending')>pending</option>
                            </select>
                        </div>
                        <div class="flex flex-col">
                            <label for="filter_period_from" class="text-sm text-gray-700 dark:text-gray-300">期間(開始)</label>
                            <input id="filter_period_from" type="date" name="period_from" value="{{ $filters['period_from'] ?? '' }}" class="border rounded p-2">
                        </div>
                        <div class="flex flex-col">
                            <label for="filter_period_to" class="text-sm text-gray-700 dark:text-gray-300">期間(終了)</label>
                            <input id="filter_period_to" type="date" name="period_to" value="{{ $filters['period_to'] ?? '' }}" class="border rounded p-2">
                        </div>
                        <div class="md:col-span-5">
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
                                <th class="px-4 py-2 text-left">状態</th>
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
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-1 text-xs font-medium rounded {{ $s->status === 'active' ? 'bg-green-100 text-green-800' : ($s->status === 'canceled' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                                            {{ $s->status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 space-x-2">
                                        <a href="{{ route('admin.subscriptions.edit', $s) }}" class="text-blue-600">編集</a>
                                        <form action="{{ route('admin.subscriptions.destroy', $s) }}" method="POST" class="inline" onsubmit="return confirm(@js('削除しますか？キャンセル済みのみ削除可能です')); ">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600">削除</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            @if($subscriptions->isEmpty())
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-500">該当するサブスクリプションがありません</td>
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
