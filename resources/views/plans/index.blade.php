<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">プラン一覧</h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @if($plans->isEmpty())
            <p class="text-sm text-gray-600 dark:text-gray-400">現在申し込み可能なプランはありません。</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($plans as $plan)
                    <div class="rounded-xl bg-white dark:bg-gray-800 p-4 shadow-[inset_0_1px_0_rgba(255,255,255,0.1),0_1px_3px_rgba(0,0,0,0.1)] dark:shadow-[inset_0_1px_0_rgba(255,255,255,0.05),0_1px_3px_rgba(0,0,0,0.3)] hover:shadow-md transition">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $plan->name }}</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">月額: {{ number_format($plan->price) }}円</p>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">月{{ (int) $plan->lesson_count }}回まで</p>
                        @if($plan->description)
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ $plan->description }}</p>
                        @endif
                        <div class="mt-3">
                            @php
                                $isActiveForUser = auth()->check() && in_array($plan->stripe_price_id, $activePriceIds ?? [], true);
                            @endphp
                            @if(Route::has('subscription.checkout'))
                                @if($isActiveForUser)
                                    <button type="button" disabled aria-disabled="true" class="inline-flex items-center px-4 py-2 bg-indigo-100 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-700 rounded-md font-semibold text-xs text-indigo-700 dark:text-indigo-300 uppercase tracking-widest cursor-not-allowed">申し込み済み</button>
                                @else
                                    <a href="{{ route('subscription.checkout', ['plan' => $plan->id]) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 dark:bg-indigo-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 dark:hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">申し込む</a>
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
    @include('layouts.user-footer')
</x-app-layout>
