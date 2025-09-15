<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">インストラクター詳細</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-2">
                    <p><strong>ID:</strong> {{ $instructor->id }}</p>
                    <p><strong>氏名:</strong> {{ $instructor->name }}</p>
                    <p><strong>メール:</strong> {{ $instructor->email }}</p>
                    <p><strong>作成日:</strong> {{ $instructor->created_at?->format('Y-m-d H:i') }}</p>

                    <p><strong>自己紹介:</strong> <span class="whitespace-pre-line">{{ $profile->bio }}</span></p>
                    <p><strong>資格:</strong> <span class="whitespace-pre-line">{{ $profile->qualifications }}</span></p>
                    <p><strong>備考:</strong> <span class="whitespace-pre-line">{{ $profile->notes }}</span></p>

                    <div class="pt-4 flex space-x-2">
                        <x-primary-button as="a" href="{{ route('admin.instructors.edit', $instructor) }}">編集</x-primary-button>
                        <x-secondary-button href="{{ route('admin.instructors.index') }}">戻る</x-secondary-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
