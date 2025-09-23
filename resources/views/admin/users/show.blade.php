<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            ユーザー詳細
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-4">
                    <div>
                        <div class="text-sm text-gray-500">ID</div>
                        <div class="text-base">{{ $user->id }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">名前</div>
                        <div class="text-base">{{ $user->name }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">メール</div>
                        <div class="text-base">{{ $user->email }}</div>
                    </div>
                    <!-- role display removed -->

                    <div class="pt-4 space-x-2">
                        <x-secondary-button href="{{ route('admin.users.edit', $user) }}">編集</x-secondary-button>
                        <x-secondary-button href="{{ route('admin.users.index') }}">一覧へ戻る</x-secondary-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
