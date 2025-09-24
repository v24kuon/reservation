<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            サブスクリプション詳細
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-4">
                    <div>
                        <div class="text-sm text-gray-500">ユーザー</div>
                        <div class="text-base">{{ $subscription->user?->name }} ({{ $subscription->user?->email }})</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">プラン</div>
                        <div class="text-base">{{ $subscription->plan?->name }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">期間</div>
                        <div class="text-base">{{ $subscription->current_period_start?->format('Y-m-d H:i') }} ～ {{ $subscription->current_period_end?->format('Y-m-d H:i') }}</div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <div class="text-sm text-gray-500">ステータス</div>
                            <div class="text-base">{{ $subscription->status }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">支払い</div>
                            <div class="text-base">{{ $subscription->payment_status }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">残り回数</div>
                            <div class="text-base">{{ $subscription->remaining_lessons }}</div>
                        </div>
                    </div>
                    @if($subscription->failure_reason)
                        <div>
                            <div class="text-sm text-gray-500">失敗理由</div>
                            <div class="text-base whitespace-pre-wrap">{{ $subscription->failure_reason }}</div>
                        </div>
                    @endif

                    <div class="pt-4 flex space-x-2">
                        <x-secondary-button href="{{ route('admin.subscriptions.index') }}">一覧へ</x-secondary-button>
                        <x-primary-button as="a" href="{{ route('admin.subscriptions.edit', $subscription) }}">編集</x-primary-button>
                <form action="{{ route('admin.subscriptions.destroy', $subscription) }}" method="POST" class="inline" onsubmit="return confirm(@js('削除しますか？キャンセル済みのみ削除可能です')); ">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600">削除</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
