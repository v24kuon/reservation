<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("You're logged in!") }}
                </div>
                <div class="p-6">
                    @livewire('demo.counter')
                </div>
                @can('access-admin')
                <div class="p-6 border-t border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-100">管理メニュー</h3>
                    <ul class="list-disc ml-6 space-y-2">
                        <li>
                            <a class="text-indigo-600" href="{{ route('admin.stores.index') }}">店舗一覧</a>
                        </li>
                        <li>
                            <a class="text-indigo-600" href="{{ route('admin.lesson-categories.index') }}">レッスンカテゴリ一覧</a>
                        </li>
                        <li>
                            <a class="text-indigo-600" href="{{ route('admin.lessons.index') }}">レッスン一覧</a>
                        </li>
                        <li>
                            <a class="text-indigo-600" href="{{ route('admin.lesson-schedules.index') }}">レッスンスケジュール一覧</a>
                        </li>
                        <li>
                            <a class="text-indigo-600" href="{{ route('admin.notification-templates.index') }}">通知テンプレート一覧</a>
                        </li>
                    </ul>
                </div>
                @endcan
            </div>
        </div>
    </div>
</x-app-layout>
