<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ sidebarOpen: false, dark: (function(){ try { const saved = localStorage.getItem('theme'); if (saved) return saved === 'dark'; return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches; } catch(_) { return false; } })() }" x-bind:class="{ 'dark': dark }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <style>[x-cloak]{display:none!important}</style>
        <script>
            (function () {
                try {
                    const saved = localStorage.getItem('theme');
                    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                    const isDark = saved ? saved === 'dark' : prefersDark;
                    if (isDark) document.documentElement.classList.add('dark');
                } catch (_) {}
            })();
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-background text-foreground">
        <div class="min-h-screen">
            <!-- Top Bar -->
            <header class="bg-surface border-b border-token">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <button type="button" class="md:hidden inline-flex items-center p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:bg-gray-900 focus:outline-none"
                                @click="sidebarOpen = !sidebarOpen" aria-label="Toggle navigation">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <a href="{{ route('dashboard') }}" class="font-semibold">
                            {{ config('app.name', 'Admin') }}
                        </a>
                        <span class="hidden md:inline text-xs text-gray-500 dark:text-gray-400">/ 管理</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <button
                            x-on:click="dark = !dark; localStorage.setItem('theme', dark ? 'dark' : 'light')"
                            x-bind:aria-pressed="dark.toString()"
                            aria-label="ダークモード切替"
                            class="inline-flex items-center px-3 py-2 text-sm rounded-md bg-background border border-token hover:bg-surface">
                            <span x-show="!dark" aria-hidden="true">🌙</span>
                            <span x-show="dark" aria-hidden="true">☀️</span>
                        </button>
                        @auth
                            <span class="hidden sm:inline text-sm">{{ Auth::user()->name }}</span>
                        @endauth
                    </div>
                </div>
            </header>

            <!-- Main -->
            <div class="flex">
                <!-- Sidebar (Desktop) -->
                <aside class="hidden md:block w-64 shrink-0 border-r border-token bg-surface min-h-[calc(100vh-4rem)]">
                    <x-admin.sidebar />
                </aside>

                <!-- Sidebar (Mobile) -->
                <div class="md:hidden" x-cloak x-show="sidebarOpen" @click.outside="sidebarOpen=false">
                    <div class="fixed inset-0 bg-black/40" @click="sidebarOpen=false"></div>
                    <aside class="fixed inset-y-0 left-0 w-72 bg-surface border-r border-token p-4">
                        <div class="flex items-center justify-between mb-4">
                            <span class="font-semibold">メニュー</span>
                            <button class="p-2" @click="sidebarOpen=false" aria-label="Close menu">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <x-admin.sidebar />
                    </aside>
                </div>

                <!-- Content -->
                <main class="flex-1 min-w-0">
                    @isset($header)
                        <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                                {{ $header }}
                            </div>
                        </div>
                    @endisset

                    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
                        {{ $slot }}
                    </div>
                </main>
            </div>
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
                    if (t && (t.type === 'date' || t.type === 'time' || t.type === 'datetime-local')) {
                        if (typeof t.showPicker === 'function') { try { t.showPicker(); } catch(_) {} }
                        bind(t);
                    }
                });
                document.addEventListener('livewire:load', applyToAll);
                document.addEventListener('livewire:navigated', applyToAll);
            })();
        </script>
        @stack('scripts')
    </body>
</html>
