<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ dark: localStorage.getItem('theme') === 'dark' }" x-bind:class="{ 'dark': dark }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-background text-foreground">
        <div class="min-h-screen">
            @can('access-admin')
                @include('layouts.navigation')
            @endcan

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700">
                    <div class="max-w-7xl mx-auto h-16 px-4 sm:px-6 lg:px-8 flex items-center justify-between">
                        {{ $header }}
                        <div class="flex items-center gap-3">
                            <button
                                x-on:click="dark = !dark; document.documentElement.classList.toggle('dark'); localStorage.setItem('theme', dark ? 'dark' : 'light')"
                                class="inline-flex items-center px-3 py-2 text-sm rounded-md bg-background border border-token hover:bg-surface text-gray-700 dark:text-gray-200"
                                aria-label="ダークモード切替"
                            >
                                <span x-show="!dark">🌙</span>
                                <span x-show="dark" style="display: none;">☀️</span>
                            </button>
                            <a href="{{ route('profile.edit') }}" class="text-gray-800 dark:text-gray-200 hover:text-gray-900 dark:hover:text-white transition-colors" aria-label="設定">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M11.983 2a1 1 0 01.943.667l.342.986c.49.137.963.328 1.41.568l1.005-.58a1 1 0 011.366.366l1 1.732a1 1 0 01-.366 1.366l-.996.575c.077.47.077.95 0 1.42l.996.575a1 1 0 01.366 1.366l-1 1.732a1 1 0 01-1.366.366l-1.005-.58a7.97 7.97 0 01-1.41.568l-.342.986a1 1 0 01-1.886 0l-.342-.986a7.97 7.97 0 01-1.41-.568l-1.005.58a1 1 0 01-1.366-.366l-1-1.732a1 1 0 01.366-1.366l.996-.575a6.5 6.5 0 010-1.42l-.996-.575a1 1 0 01-.366-1.366l1-1.732a1 1 0 011.366-.366l1.005.58c.447-.24.92-.431 1.41-.568l.342-.986A1 1 0 0111.983 2zm0 9a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="bg-background pb-[calc(3.5rem+env(safe-area-inset-bottom))] sm:pb-[calc(4rem+env(safe-area-inset-bottom))]">
                {{ $slot }}
            </main>

            @auth
                @cannot('access-admin')
                    @cannot('access-instructor')
                        @include('layouts.user-footer')
                    @endcannot
                @endcannot
            @endauth
        </div>
        @livewireScripts
        <script>
            // Auto-open native pickers on focus for date, time, and datetime-local
            (function(){
                const bind = (el) => {
                    if (!el || el.dataset.pickerBound === '1') return;
                    el.addEventListener('focus', () => {
                        if (typeof el.showPicker === 'function') {
                            try { el.showPicker(); } catch(_) {}
                        }
                    });
                    el.dataset.pickerBound = '1';
                };

                const applyToAll = () => {
                    document.querySelectorAll('input[type="date"], input[type="time"], input[type="datetime-local"]').forEach(bind);
                };

                document.addEventListener('DOMContentLoaded', applyToAll);
                document.addEventListener('focusin', (e) => {
                    const t = e.target;
                    if (t && (t.type === 'date' || t.type === 'time' || t.type === 'datetime-local')) bind(t);
                });
            })();
        </script>
    </body>
</html>
