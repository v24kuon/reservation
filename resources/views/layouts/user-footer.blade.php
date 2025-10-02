<div class="fixed bottom-0 left-0 right-0 z-40 bg-white dark:bg-gray-800/90 backdrop-blur-sm border-t border-gray-200 dark:border-gray-700 max-w-7xl mx-auto">
    <div class="px-4 py-2 pb-[calc(env(safe-area-inset-bottom))]">
        <div class="flex justify-around items-center">
@php
    $activeClass = 'flex flex-col items-center space-y-0.5 text-blue-600 dark:text-blue-400 p-1.5 rounded-lg bg-blue-50 dark:bg-blue-900/20';
    $inactiveClass = 'flex flex-col items-center space-y-0.5 text-gray-600 dark:text-gray-300 p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors';
@endphp
            <!-- ホーム -->
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? $activeClass : $inactiveClass }}">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                </svg>
                <span class="text-[11px] font-medium leading-4 whitespace-nowrap">ホーム</span>
            </a>

            <!-- 店舗一覧 -->
            <a href="{{ route('stores.index') }}" class="{{ request()->routeIs('stores.*') ? $activeClass : $inactiveClass }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M5 7v10a2 2 0 002 2h10a2 2 0 002-2V7m-9 4h4"/>
                </svg>
                <span class="text-[11px] font-medium leading-4 whitespace-nowrap">店舗一覧</span>
            </a>

            <!-- インストラクター一覧（未実装なら無効） -->
            @if(Route::has('instructors.index'))
                <a href="{{ route('instructors.index') }}" class="{{ request()->routeIs('instructors.*') ? $activeClass : $inactiveClass }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="text-[11px] font-medium leading-4 whitespace-nowrap">インストラクター</span>
                </a>
            @else
                <a aria-disabled="true" class="flex flex-col items-center space-y-0.5 text-gray-400 dark:text-gray-500 p-1.5 rounded-lg pointer-events-none opacity-60">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="text-[11px] font-medium leading-4 whitespace-nowrap">インストラクター</span>
                </a>
            @endif

            <!-- プラン一覧（未実装なら無効） -->
            @if(Route::has('plans.index'))
                <a href="{{ route('plans.index') }}" class="{{ request()->routeIs('plans.*') ? $activeClass : $inactiveClass }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2-1.343-2-3-2z"/>
                    </svg>
                    <span class="text-[11px] font-medium leading-4 whitespace-nowrap">プラン一覧</span>
                </a>
            @else
                <a aria-disabled="true" class="flex flex-col items-center space-y-0.5 text-gray-400 dark:text-gray-500 p-1.5 rounded-lg pointer-events-none opacity-60">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2-1.343-2-3-2z"/>
                    </svg>
                    <span class="text-[11px] font-medium leading-4 whitespace-nowrap">プラン一覧</span>
                </a>
            @endif

            <!-- マイページ -->
            <a href="{{ route('profile.index') }}" class="{{ request()->routeIs('profile.index') ? $activeClass : $inactiveClass }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span class="text-[11px] font-medium leading-4 whitespace-nowrap">マイページ</span>
            </a>
        </div>
    </div>
</div>
