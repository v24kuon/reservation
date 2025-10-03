@props(['subscription'])

<div class="bg-gray-50 dark:bg-gray-700/50 shadow-[inset_0_1px_0_rgba(255,255,255,0.1),0_1px_2px_0_rgba(0,0,0,0.05)] dark:shadow-[inset_0_1px_0_rgba(255,255,255,0.05),0_1px_2px_0_rgba(0,0,0,0.1)] rounded-lg p-4">
    <div class="flex justify-between items-start">
        <div class="flex-1">
            <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ $subscription->plan?->name ?? '未設定' }}</h4>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                残り回数
                <span class="ml-2 px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs font-medium shadow-[inset_0_1px_0_rgba(255,255,255,0.1),0_1px_1px_0_rgba(0,0,0,0.05)] dark:shadow-[inset_0_1px_0_rgba(255,255,255,0.05),0_1px_1px_0_rgba(0,0,0,0.1)]">
                    {{ $subscription->remaining_lessons ?? $subscription->plan?->lesson_count }}
                </span>
            </p>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                次回支払日
                <span class="ml-2 font-medium">
                    @if(isset($subscription->cancel_at_period_end) && $subscription->cancel_at_period_end === true)
                        解約のため無し
                    @else
                        {{ $subscription->current_period_end?->format('Y年m月d日') ?? '未定' }}
                    @endif
                </span>
            </p>
        </div>
    </div>
</div>
