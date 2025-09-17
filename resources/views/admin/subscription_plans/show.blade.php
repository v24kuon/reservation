<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            月謝プラン詳細
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-2">
                    <p><strong>プラン名:</strong> {{ $plan->name }}</p>
                    <p><strong>価格:</strong> ¥{{ number_format($plan->price) }}</p>
                    <p><strong>月間回数:</strong> {{ $plan->lesson_count }}回</p>
                    <p><strong>状態:</strong> {{ $plan->is_active ? '有効' : '無効' }}</p>
                    <p><strong>Stripe Product ID:</strong> <span class="font-mono">{{ $plan->stripe_product_id }}</span></p>
                    <p><strong>Stripe Price ID:</strong> <span class="font-mono">{{ $plan->stripe_price_id }}</span></p>
                    @if($plan->description)
                    <p><strong>説明:</strong> <span class="whitespace-pre-line">{{ $plan->description }}</span></p>
                    @endif
                    <p><strong>対象カテゴリ:</strong>
                        @if($plan->allowed_category_ids)
                            @foreach($allowedCategories as $category)
                                <span class="inline-block bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-xs px-2 py-1 rounded mr-2 mb-1">{{ $category->name }}</span>
                            @endforeach
                        @else
                            <span class="text-gray-500 dark:text-gray-400">設定されていません</span>
                        @endif
                    </p>

                    <div class="pt-4 flex space-x-2">
                        <x-primary-button as="a" href="{{ route('admin.subscription-plans.edit', $plan) }}">編集</x-primary-button>
                        <x-secondary-button href="{{ route('admin.subscription-plans.index') }}">戻る</x-secondary-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
