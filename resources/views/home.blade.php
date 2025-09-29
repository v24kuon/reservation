<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('ホーム') }}
        </h2>
    </x-slot>

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 pb-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Current Reservations Section -->
        <div class="flex items-center justify-between mt-4 mb-2">
            <h3 class="text-xl font-extrabold text-gray-900 dark:text-gray-100">予定一覧</h3>
            <a href="#" class="text-indigo-600 dark:text-indigo-400 text-sm font-semibold hover:underline">すべて表示</a>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-[inset_0_1px_0_0_rgba(255,255,255,0.1),0_1px_3px_0_rgba(0,0,0,0.1),0_1px_2px_-1px_rgba(0,0,0,0.1)] dark:shadow-[inset_0_1px_0_0_rgba(255,255,255,0.05),0_1px_3px_0_rgba(0,0,0,0.3),0_1px_2px_-1px_rgba(0,0,0,0.3)] rounded-xl p-6 mb-4">
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($currentReservations as $reservation)
                    <div class="flex items-center py-5">
                        <div class="flex items-center">
                            <div class="w-20 h-20 rounded-2xl bg-indigo-200 text-indigo-900 dark:bg-indigo-900/30 dark:text-indigo-200 shadow-[inset_0_1px_0_0_rgba(255,255,255,0.4),0_4px_8px_rgba(0,0,0,0.08)] flex flex-col items-center justify-center">
                                <div class="text-base font-bold">{{ $reservation->lessonSchedule?->start_datetime?->format('n月') }}</div>
                                <div class="text-base font-bold mt-1">{{ $reservation->lessonSchedule?->start_datetime?->format('j日') }}</div>
                            </div>
                        </div>
                        <div class="ml-6 flex-1">
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100">{{ $reservation->lessonSchedule?->lesson?->name }}</h4>
                            <div class="mt-1 text-gray-600 dark:text-gray-400 text-sm">
                                @php
                                    $categoryName = $reservation->lessonSchedule?->lesson?->category?->name;
                                @endphp
                                @if($categoryName === 'パーソナルレッスン')
                                    担当: {{ $reservation->lessonSchedule?->lesson?->instructor?->name ?? '未設定' }}
                                @else
                                    {{ $reservation->lessonSchedule?->lesson?->store?->name }}
                                @endif
                            </div>
                            <div class="mt-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ ($categoryName === 'パーソナルレッスン') ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-200' }}">
                                    {{ $categoryName === 'パーソナルレッスン' ? 'プライベート' : 'グループ' }}
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-6 text-center text-sm text-gray-500 dark:text-gray-400">現在の予約はありません</div>
                @endforelse
            </div>
        </div>

        <!-- Active Subscriptions Section -->
        <div class="flex items-center justify-between mt-4 mb-2">
            <h3 class="text-xl font-extrabold text-gray-900 dark:text-gray-100">月謝一覧</h3>
            <a href="#" class="text-indigo-600 dark:text-indigo-400 text-sm font-semibold hover:underline">すべて表示</a>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-[inset_0_1px_0_0_rgba(255,255,255,0.1),0_1px_3px_0_rgba(0,0,0,0.1),0_1px_2px_-1px_rgba(0,0,0,0.1)] dark:shadow-[inset_0_1px_0_0_rgba(255,255,255,0.05),0_1px_3px_0_rgba(0,0,0,0.3),0_1px_2px_-1px_rgba(0,0,0,0.3)] rounded-xl p-6 mt-4">
            <div class="space-y-3">
                @forelse($activeSubscriptions as $subscription)
                    <div class="bg-gray-50 dark:bg-gray-700/50 shadow-[inset_0_1px_0_0_rgba(255,255,255,0.1),0_1px_2px_0_rgba(0,0,0,0.05)] dark:shadow-[inset_0_1px_0_0_rgba(255,255,255,0.05),0_1px_2px_0_rgba(0,0,0,0.1)] rounded-lg p-4">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ $subscription->plan?->name }}</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    残り回数
                                    <span class="ml-2 px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs font-medium shadow-[inset_0_1px_0_0_rgba(255,255,255,0.1),0_1px_1px_0_rgba(0,0,0,0.05)] dark:shadow-[inset_0_1px_0_0_rgba(255,255,255,0.05),0_1px_1px_0_rgba(0,0,0,0.1)]">
                                        {{ $subscription->remaining_lessons ?? $subscription->plan?->lesson_count }}
                                    </span>
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    次回支払日<span class="ml-2 font-medium">{{ $subscription->current_period_end?->format('Y年m月d日') }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-2 text-center text-sm text-gray-500 dark:text-gray-400">契約中のプランはありません</div>
                @endforelse
            </div>
        </div>

        <!-- Studio Section -->
        <div class="bg-white dark:bg-gray-800 shadow-[inset_0_1px_0_0_rgba(255,255,255,0.1),0_1px_3px_0_rgba(0,0,0,0.1),0_1px_2px_-1px_rgba(0,0,0,0.1)] dark:shadow-[inset_0_1px_0_0_rgba(255,255,255,0.05),0_1px_3px_0_rgba(0,0,0,0.3),0_1px_2px_-1px_rgba(0,0,0,0.3)] rounded-xl p-6 sm:p-8 text-center mt-4">
            <div class="flex gap-1 sm:gap-2 md:gap-3 lg:gap-4 flex-nowrap justify-center">
                    <!-- Group Lessons Button -->
                <a href="{{ route('reservations.group') }}"
                   class="flex-1 max-w-none lg:max-w-sm xl:max-w-md 2xl:max-w-lg mx-auto bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 sm:py-4 px-2 sm:px-6 rounded-lg shadow-[inset_0_1px_0_0_rgba(255,255,255,0.2),0_2px_4px_0_rgba(0,0,0,0.1)] hover:shadow-[inset_0_1px_0_0_rgba(255,255,255,0.15),0_1px_2px_0_rgba(0,0,0,0.15)] transition-all duration-200">
                    <span class="text-sm sm:text-lg whitespace-nowrap">グループレッスン</span>
                    </a>

                    <!-- Personal Lessons Button -->
                <a href="{{ route('reservations.personal') }}"
                   class="flex-1 max-w-none lg:max-w-sm xl:max-w-md 2xl:max-w-lg mx-auto bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 sm:py-4 px-2 sm:px-6 rounded-lg shadow-[inset_0_1px_0_0_rgba(255,255,255,0.2),0_2px_4px_0_rgba(0,0,0,0.1)] hover:shadow-[inset_0_1px_0_0_rgba(255,255,255,0.15),0_1px_2px_0_rgba(0,0,0,0.15)] transition-all duration-200">
                    <span class="text-sm sm:text-lg whitespace-nowrap">パーソナルレッスン</span>
                </a>
            </div>
        </div>

    </div>


</x-app-layout>
