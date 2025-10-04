<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            マイページ
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        <div data-testid="reservation-history" class="rounded-xl bg-white dark:bg-gray-800 p-6 shadow-[inset_0_1px_0_rgba(255,255,255,0.1),0_1px_3px_rgba(0,0,0,0.1)] dark:shadow-[inset_0_1px_0_rgba(255,255,255,0.05),0_1px_3px_rgba(0,0,0,0.3)]">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">予約履歴</h3>
                <a href="{{ route('reservations.history') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">すべて表示 →</a>
            </div>
            @if($reservations->isEmpty())
                <p class="text-sm text-gray-600 dark:text-gray-400">予約履歴はありません。</p>
            @else
                <ul class="space-y-3">
                    @foreach($reservations as $reservation)
                        <li class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-900 dark:text-gray-100 font-medium">{{ $reservation->lessonSchedule?->lesson?->name ?? '未設定' }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">店舗: {{ $reservation->lessonSchedule?->lesson?->store?->name ?? '未設定' }}</p>
                            </div>
                            @if($reservation->lessonSchedule?->start_datetime)
                                <time datetime="{{ $reservation->lessonSchedule->start_datetime->toIso8601String() }}" class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ $reservation->lessonSchedule->start_datetime->format('Y/m/d H:i') }}
                                </time>
                            @else
                                <span class="text-sm text-gray-700 dark:text-gray-300">未設定</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div data-testid="current-subscriptions" class="rounded-xl bg-white dark:bg-gray-800 p-6 shadow-[inset_0_1px_0_rgba(255,255,255,0.1),0_1px_3px_rgba(0,0,0,0.1)] dark:shadow-[inset_0_1px_0_rgba(255,255,255,0.05),0_1px_3px_rgba(0,0,0,0.3)]">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">契約中のプラン</h3>
                <a href="{{ route('subscriptions.manage') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">すべて表示 →</a>
            </div>
            @if($subscriptions->isEmpty())
                <p class="text-sm text-gray-600 dark:text-gray-400">契約中のプランはありません。</p>
            @else
                <ul class="space-y-3">
                    @foreach($subscriptions as $sub)
                        <li>
                            <x-subscription.simple-card :subscription="$sub" />
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Favorites Section --}}
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">お気に入り一覧</h3>
            <div class="grid grid-cols-2 gap-3 sm:gap-6">
                {{-- Favorite Stores --}}
                <div class="rounded-xl bg-white dark:bg-gray-800 p-3 sm:p-6 shadow-[inset_0_1px_0_rgba(255,255,255,0.1),0_1px_3px_rgba(0,0,0,0.1)] dark:shadow-[inset_0_1px_0_rgba(255,255,255,0.05),0_1px_3px_rgba(0,0,0,0.3)]">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-3 sm:mb-4">
                        <h4 class="text-sm sm:text-base font-semibold text-gray-900 dark:text-gray-100">店舗</h4>
                        <a href="{{ route('stores.index') }}" class="self-end sm:self-auto text-right text-xs sm:text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">
                            すべて表示 →
                        </a>
                    </div>
                    @if($favoriteStores->isEmpty())
                        <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">お気に入りの店舗はありません。</p>
                    @else
                        <ul class="space-y-2 sm:space-y-3">
                            @foreach($favoriteStores as $store)
                                <li>
                                    <a href="{{ route('stores.show', $store) }}" aria-label="{{ $store->name }}の詳細を見る" class="group relative flex items-center justify-between p-3 sm:p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm hover:shadow-md transition focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 active:scale-[0.99] overflow-hidden">
                                        <span class="pointer-events-none absolute inset-0 group-active:bg-gray-200/30 dark:group-active:bg-white/10 transition"></span>
                                        <div class="py-1 sm:py-0 flex-1">
                                            <p class="text-sm sm:text-base text-gray-900 dark:text-gray-100 font-medium group-hover:underline">{{ $store->name }}</p>
                                            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">{{ $store->address }}</p>
                                        </div>
                                        <svg class="w-5 h-5 text-gray-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                {{-- Favorite Instructors --}}
                <div class="rounded-xl bg-white dark:bg-gray-800 p-3 sm:p-6 shadow-[inset_0_1px_0_rgba(255,255,255,0.1),0_1px_3px_rgba(0,0,0,0.1)] dark:shadow-[inset_0_1px_0_rgba(255,255,255,0.05),0_1px_3px_rgba(0,0,0,0.3)]">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-3 sm:mb-4">
                        <h4 class="text-sm sm:text-base font-semibold text-gray-900 dark:text-gray-100">インストラクター</h4>
                        <a href="{{ route('instructors.index') }}" class="self-end sm:self-auto text-right text-xs sm:text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">
                            すべて表示 →
                        </a>
                    </div>
                    @if($favoriteInstructors->isEmpty())
                        <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">お気に入りのインストラクターはいません。</p>
                    @else
                        <ul class="space-y-2 sm:space-y-3">
                            @foreach($favoriteInstructors as $instructor)
                                <li>
                                    <a href="{{ route('instructors.show', $instructor) }}" aria-label="{{ $instructor->name }}の詳細を見る" class="group relative flex items-center justify-between p-3 sm:p-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm hover:shadow-md transition focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 active:scale-[0.99] overflow-hidden">
                                        <span class="pointer-events-none absolute inset-0 group-active:bg-gray-200/30 dark:group-active:bg-white/10 transition"></span>
                                        <div class="py-1 sm:py-0 flex-1">
                                            <p class="text-sm sm:text-base text-gray-900 dark:text-gray-100 font-medium group-hover:underline">{{ $instructor->name }}</p>
                                            @if($instructor->instructorProfile?->bio)
                                                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 line-clamp-1">{{ $instructor->instructorProfile->bio }}</p>
                                            @endif
                                        </div>
                                        <svg class="w-5 h-5 text-gray-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
        <div class="mt-6 flex items-center gap-3">
            <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 dark:bg-indigo-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 dark:hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                プロフィール編集
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 dark:bg-red-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 dark:hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    ログアウト
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
