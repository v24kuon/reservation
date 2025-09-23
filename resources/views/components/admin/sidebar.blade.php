<nav class="h-full p-4 space-y-1 text-sm">
    <x-admin.sidebar-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">ダッシュボード</x-admin.sidebar-link>

    <div class="pt-2 text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">マスター</div>
    <x-admin.sidebar-link href="{{ route('admin.stores.index') }}" :active="request()->routeIs('admin.stores.*')">店舗</x-admin.sidebar-link>
    <x-admin.sidebar-link href="{{ route('admin.instructors.index') }}" :active="request()->routeIs('admin.instructors.*')">インストラクター</x-admin.sidebar-link>
    <x-admin.sidebar-link href="{{ route('admin.lesson-categories.index') }}" :active="request()->routeIs('admin.lesson-categories.*')">レッスンカテゴリ</x-admin.sidebar-link>
    <x-admin.sidebar-link href="{{ route('admin.lessons.index') }}" :active="request()->routeIs('admin.lessons.*')">レッスン</x-admin.sidebar-link>
    <x-admin.sidebar-link href="{{ route('admin.notification-templates.index') }}" :active="request()->routeIs('admin.notification-templates.*')">通知テンプレート</x-admin.sidebar-link>
    <x-admin.sidebar-link href="{{ route('admin.lesson-schedules.index') }}" :active="request()->routeIs('admin.lesson-schedules.*')">レッスンスケジュール</x-admin.sidebar-link>
    <x-admin.sidebar-link href="{{ route('admin.subscription-plans.index') }}" :active="request()->routeIs('admin.subscription-plans.*')">月謝プラン</x-admin.sidebar-link>
    <x-admin.sidebar-link href="{{ route('admin.settings.edit') }}" :active="request()->routeIs('admin.settings.*')">システム設定</x-admin.sidebar-link>

    <div class="pt-4 text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">ユーザー管理</div>
    <x-admin.sidebar-link href="{{ route('admin.users.index') }}" :active="request()->routeIs('admin.users.*')">ユーザー</x-admin.sidebar-link>

    <div class="pt-4 text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">アカウント</div>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="w-full text-left px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700">ログアウト</button>
    </form>
</nav>
