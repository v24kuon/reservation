<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            レッスン詳細
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-2">
                    <p><strong>名称:</strong> {{ $lesson->name }}</p>
                    <p><strong>店舗:</strong> {{ $lesson->store->name ?? '-' }}</p>
                    <p><strong>カテゴリ:</strong> {{ $lesson->category->name ?? '-' }}</p>
                    <p><strong>インストラクター:</strong> {{ $lesson->instructor->name ?? '-' }}</p>
                    <p><strong>時間:</strong> {{ $lesson->duration }} 分</p>
                    <p><strong>定員:</strong> {{ $lesson->capacity }}</p>
                    <p><strong>予約期限:</strong> {{ $lesson->booking_deadline_hours }} 時間前</p>
                    <p><strong>キャンセル期限:</strong> {{ $lesson->cancel_deadline_hours }} 時間前</p>
                    <p><strong>有効:</strong> {{ $lesson->is_active ? '有効' : '無効' }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


