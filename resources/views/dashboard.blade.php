@can('access-admin')
<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            管理ダッシュボード
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">

                <div class="p-6 border-t border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-100">予約状況（7日間）</h3>

                    @if(!empty($adminStats))
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded border border-gray-200 dark:border-gray-700">
                                <div class="text-sm text-gray-500 dark:text-gray-400">本日の予約 / キャパ</div>
                                <div class="mt-1 text-2xl font-semibold">{{ $adminStats['today']['booked'] }} / {{ $adminStats['today']['capacity'] }}</div>
                            </div>
                            <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded border border-gray-200 dark:border-gray-700">
                                <div class="text-sm text-gray-500 dark:text-gray-400">7日合計 予約 / キャパ</div>
                                <div class="mt-1 text-2xl font-semibold">{{ $adminStats['totalBooked'] }} / {{ $adminStats['totalCapacity'] }}</div>
                            </div>
                            <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded border border-gray-200 dark:border-gray-700">
                                <div class="text-sm text-gray-500 dark:text-gray-400">稼働率</div>
                                <div class="mt-1 text-2xl font-semibold">{{ $adminStats['utilization'] }}%</div>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="text-left text-gray-600 dark:text-gray-300">
                                        <th class="py-2 pr-4">日付</th>
                                        <th class="py-2 pr-4">予約</th>
                                        <th class="py-2 pr-4">キャパ</th>
                                        <th class="py-2 pr-4">バー</th>
                                    </tr>
                                </thead>
                                <tbody class="align-middle">
                                    @foreach($adminStats['daily'] as $day)
                                        <tr class="border-t border-gray-200 dark:border-gray-700">
                                            <td class="py-2 pr-4">{{ $day['label'] }}</td>
                                            <td class="py-2 pr-4 font-medium">{{ $day['booked'] }}</td>
                                            <td class="py-2 pr-4">{{ $day['capacity'] }}</td>
                                            <td class="py-2 pr-4 w-64">
                                                @php
                                                    $cap = max(1, (int) $day['capacity']);
                                                    $pct = min(100, (int) round(($day['booked'] / $cap) * 100));
                                                @endphp
                                                <div class="h-3 bg-muted dark:bg-gray-700 rounded" aria-hidden="true">
                                                    <div class="h-3 bg-indigo-500 rounded"
                                                         role="progressbar"
                                                         aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"
                                                         aria-label="{{ $day['label'] }}の稼働率 {{ $pct }}%"
                                                         style="width: {{ $pct }}%"></div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-gray-500 dark:text-gray-400">表示できる予約データがありません。</div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</x-admin-layout>
@else
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
                        <p class="text-gray-600 dark:text-gray-400">ダッシュボードへようこそ</p>
                    </div>
                </div>
            </div>
        </div>
</x-app-layout>
@endcan
