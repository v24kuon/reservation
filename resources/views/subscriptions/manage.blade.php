<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            サブスクリプション管理
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="rounded-xl bg-white dark:bg-gray-800 p-6 shadow-[inset_0_1px_0_rgba(255,255,255,0.1),0_1px_3px_rgba(0,0,0,0.1)] dark:shadow-[inset_0_1px_0_rgba(255,255,255,0.05),0_1px_3px_rgba(0,0,0,0.3)]">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">契約中のプラン</h3>
                </div>
                @if($active->isEmpty())
                    <p class="text-sm text-gray-600 dark:text-gray-400">契約中のプランはありません。</p>
                @else
                    <ul class="space-y-3">
                        @foreach($active as $sub)
                            <li class="flex flex-col gap-1 p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                                <p class="text-gray-900 dark:text-gray-100 font-medium">{{ $sub->plan?->name ?? '未設定' }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">期間: {{ $sub->formatted_period }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">次回支払日:
                                    @if(!empty($sub->cancel_at_period_end))
                                        解約のため無し
                                    @else
                                        {{ $sub->current_period_end?->format('Y/m/d') ?? '未定' }}
                                    @endif
                                </p>
                                @php
                                    $status = (string) $sub->status;
                                    $statusClasses = match ($status) {
                                        'active', 'trialing' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200',
                                        'past_due' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-200',
                                        'canceled' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200',
                                        default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                                    };
                                @endphp
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium w-fit {{ $statusClasses }}">
                                        ステータス: {{ $sub->status_label }}
                                    </span>
                                    @if(!empty($sub->cancel_at_period_end))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium w-fit bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200">
                                            解約@if(!empty($sub->cancel_at))(有効期限:{{ $sub->cancel_at->format('Y/m/d') }})@endif
                                        </span>
                                    @endif
                                </div>
                                <form method="POST" action="{{ route('subscriptions.cancel') }}" class="mt-2">
                                    @csrf
                                    <input type="hidden" name="subscription_id" value="{{ $sub->id }}">
                                    <button type="submit" @if(!empty($sub->cancel_at_period_end)) disabled aria-disabled="true" @endif class="inline-flex items-center px-4 py-2 bg-red-600 dark:bg-red-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 dark:hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-50 disabled:pointer-events-none transition ease-in-out duration-150">
                                        解約（期末）
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-3">{{ $active->links() }}</div>
                @endif
            </div>

            <div class="rounded-xl bg-white dark:bg-gray-800 p-6 shadow-[inset_0_1px_0_rgba(255,255,255,0.1),0_1px_3px_rgba(0,0,0,0.1)] dark:shadow-[inset_0_1px_0_rgba(255,255,255,0.05),0_1px_3px_rgba(0,0,0,0.3)]">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">過去のプラン</h3>
                </div>
                @if($past->isEmpty())
                    <p class="text-sm text-gray-600 dark:text-gray-400">過去のプランはありません。</p>
                @else
                    <ul class="space-y-3">
                        @foreach($past as $sub)
                            <li class="flex flex-col gap-1 p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                                <p class="text-gray-900 dark:text-gray-100 font-medium">{{ $sub->plan?->name ?? '未設定' }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">期間: {{ $sub->formatted_period }}</p>
                                @php
                                    $status = (string) $sub->status;
                                    $statusClasses = match ($status) {
                                        'active', 'trialing' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200',
                                        'past_due' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-200',
                                        'canceled' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200',
                                        default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium w-fit {{ $statusClasses }}">
                                    ステータス: {{ $sub->status_label }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-3">{{ $past->links() }}</div>
                @endif
            </div>
        </div>
    </div>
    @include('layouts.user-footer')
</x-app-layout>
