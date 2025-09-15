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
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-surface border-b border-token shadow-sm">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="bg-background">
                {{ $slot }}
            </main>
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
