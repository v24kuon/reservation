<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            インストラクター一覧
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @if($instructors->count() === 0)
            <p class="text-sm text-gray-600 dark:text-gray-400">公開中のインストラクターはいません。</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($instructors as $instructor)
                    <a href="{{ route('instructors.show', $instructor) }}" class="block rounded-xl bg-white dark:bg-gray-800 p-4 shadow-[inset_0_1px_0_rgba(255,255,255,0.1),0_1px_3px_rgba(0,0,0,0.1)] dark:shadow-[inset_0_1px_0_rgba(255,255,255,0.05),0_1px_3px_rgba(0,0,0,0.3)] hover:shadow-md transition">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $instructor->name }}</h3>
                        @if(optional($instructor->instructorProfile)->bio)
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 line-clamp-2">{{ optional($instructor->instructorProfile)->bio }}</p>
                        @endif
                    </a>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $instructors->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
