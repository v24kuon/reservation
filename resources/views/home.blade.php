<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('ホーム') }}
        </h2>
    </x-slot>

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 pb-20">
        <!-- Current Reservations Section -->
        <div class="bg-white dark:bg-gray-800 shadow-sm">
            <div class="px-4 py-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    現在の予約
                </h3>

                @if($currentReservations->count() > 0)
                    <div class="space-y-3">
                        @foreach($currentReservations as $reservation)
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ $reservation->lessonSchedule?->lesson?->name }}
                                        </h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $reservation->lessonSchedule?->lesson?->store?->name }}
                                        </p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $reservation->lessonSchedule?->start_datetime?->format('m/d H:i') }}
                                        </p>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        予約済み
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-gray-500 dark:text-gray-400">現在の予約はありません</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Active Subscriptions Section -->
        <div class="bg-white dark:bg-gray-800 shadow-sm mt-4">
            <div class="px-4 py-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    契約中のプラン
                </h3>

                @if($activeSubscriptions->count() > 0)
                    <div class="space-y-3">
                        @foreach($activeSubscriptions as $subscription)
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ $subscription->plan?->name }}
                                        </h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            残り回数: {{ $subscription->remaining_lessons ?? $subscription->plan?->lesson_count }}回
                                        </p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            次回更新: {{ $subscription->current_period_end?->format('m/d') }}
                                        </p>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        アクティブ
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-gray-500 dark:text-gray-400">契約中のプランはありません</p>
                        <a href="{{ route('subscription-plans.index') }}" class="mt-2 inline-block text-blue-600 dark:text-blue-400 hover:underline">
                            プランを確認する
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Spacer for middle section -->
        <div class="h-32"></div>

        <!-- Navigation Buttons Section -->
        <div class="bg-white dark:bg-gray-800 shadow-sm">
            <div class="px-4 py-8">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6 text-center">
                    レッスンを予約する
                </h3>

                <div class="space-y-4">
                    <!-- Group Lessons Button -->
                    <a href="{{ route('reservations.group') }}"
                       class="block w-full bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold py-4 px-6 rounded-lg shadow-md transition-all duration-200 transform hover:scale-105">
                        <div class="flex items-center justify-center space-x-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <span class="text-lg">グループレッスン</span>
                        </div>
                        <p class="text-sm text-green-100 mt-2">ヨガ・ピラティスなどのグループレッスン</p>
                    </a>

                    <!-- Personal Lessons Button -->
                    <a href="{{ route('reservations.personal') }}"
                       class="block w-full bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-semibold py-4 px-6 rounded-lg shadow-md transition-all duration-200 transform hover:scale-105">
                        <div class="flex items-center justify-center space-x-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span class="text-lg">パーソナルレッスン</span>
                        </div>
                        <p class="text-sm text-purple-100 mt-2">マンツーマンのパーソナルレッスン</p>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Fixed Footer -->
    <div class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 shadow-lg">
        <div class="px-4 py-3">
            <div class="flex justify-around items-center">
                <!-- Home Button -->
                <a href="{{ route('home') }}" class="flex flex-col items-center space-y-1 text-blue-600 dark:text-blue-400">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                    </svg>
                    <span class="text-xs font-medium">ホーム</span>
                </a>

                <!-- Reservations Button -->
                <a aria-disabled="true" class="flex flex-col items-center space-y-1 text-gray-500 dark:text-gray-400 pointer-events-none opacity-50">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="text-xs font-medium">予約履歴</span>
                </a>

                <!-- Subscriptions Button -->
                <a aria-disabled="true" class="flex flex-col items-center space-y-1 text-gray-500 dark:text-gray-400 pointer-events-none opacity-50">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-xs font-medium">プラン</span>
                </a>

                <!-- Profile Button -->
                <a href="{{ route('profile.edit') }}" class="flex flex-col items-center space-y-1 text-gray-500 dark:text-gray-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span class="text-xs font-medium">プロフィール</span>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
