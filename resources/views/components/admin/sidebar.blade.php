<nav class="h-full p-4 space-y-1 text-sm">
    <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('dashboard') ? 'bg-gray-100 dark:bg-gray-700 font-semibold' : '' }}">ダッシュボード</a>

    <div class="pt-2 text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">マスター</div>
    <a href="{{ route('admin.stores.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('admin.stores.*') ? 'bg-gray-100 dark:bg-gray-700 font-semibold' : '' }}">店舗</a>
    <a href="{{ route('admin.instructors.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('admin.instructors.*') ? 'bg-gray-100 dark:bg-gray-700 font-semibold' : '' }}">インストラクター</a>
    <a href="{{ route('admin.lesson-categories.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('admin.lesson-categories.*') ? 'bg-gray-100 dark:bg-gray-700 font-semibold' : '' }}">レッスンカテゴリ</a>
    <a href="{{ route('admin.lessons.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('admin.lessons.*') ? 'bg-gray-100 dark:bg-gray-700 font-semibold' : '' }}">レッスン</a>
    <a href="{{ route('admin.notification-templates.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('admin.notification-templates.*') ? 'bg-gray-100 dark:bg-gray-700 font-semibold' : '' }}">通知テンプレート</a>
    <a href="{{ route('admin.lesson-schedules.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('admin.lesson-schedules.*') ? 'bg-gray-100 dark:bg-gray-700 font-semibold' : '' }}">レッスンスケジュール</a>
    <a href="{{ route('admin.settings.edit') }}" class="block px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('admin.settings.*') ? 'bg-gray-100 dark:bg-gray-700 font-semibold' : '' }}">システム設定</a>

    <div class="pt-4 text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">アカウント</div>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="w-full text-left px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700">ログアウト</button>
    </form>
</nav>
