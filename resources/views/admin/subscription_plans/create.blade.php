<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            月謝プラン作成
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @include('admin.subscription_plans._form', [
                        'action' => route('admin.subscription_plans.store'),
                        'method' => 'POST',
                        'plan' => null,
                    ])
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
