<div wire:poll.10s.visible>
    @if($mode !== 'group')
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2" role="group" aria-label="タブ切替">
                <button type="button"
                    class="px-3 py-1 rounded-md text-sm font-medium border"
                    wire:click="$set('tab','favorites')"
                    @class(['bg-blue-600 text-white border-blue-600'=> $tab==='favorites','bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 border-gray-300'=> $tab!=='favorites'])
                    data-testid="tab-favorites">お気に入り</button>
                <button type="button"
                    class="px-3 py-1 rounded-md text-sm font-medium border"
                    wire:click="$set('tab','all')"
                    @class(['bg-blue-600 text-white border-blue-600'=> $tab==='all','bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 border-gray-300'=> $tab!=='all'])
                    data-testid="tab-all">すべて</button>
            </div>

            <div class="flex items-center gap-2" aria-label="日付ナビゲーション">
                <button type="button" class="px-2 py-1 border rounded" wire:click="jumpDays(-7)">« 1週間</button>
                <button type="button" class="px-2 py-1 border rounded" wire:click="jumpDays(-1)">« 前日</button>
                <input type="date" class="border rounded p-1" wire:model.live="date" />
                <button type="button" class="px-2 py-1 border rounded" wire:click="jumpDays(1)">翌日 »</button>
                <button type="button" class="px-2 py-1 border rounded" wire:click="jumpDays(7)">1週間 »</button>
            </div>
        </div>
    @endif

    @if($mode !== 'group')
        <div class="flex items-center gap-2 mb-4" role="group" aria-label="時間帯フィルタ">
            <label class="text-sm">時間帯:</label>
            <select class="border rounded p-1" wire:model.live="timeSlot" data-testid="select-time-slot">
                <option value="all">すべて</option>
                <option value="morning">朝 (6:00 - 12:00)</option>
                <option value="afternoon">昼 (12:00 - 18:00)</option>
                <option value="evening">夜 (18:00 - 24:00)</option>
            </select>
        </div>
    @endif

    @if($mode !== 'group')
        <div class="overflow-x-auto whitespace-nowrap mb-4" aria-label="インストラクター選択">
            @php $instructors = $tab==='favorites' ? $this->favoriteInstructors : \App\Models\User::query()->whereHas('instructorProfile')->orderBy('name')->get(); @endphp
            <div class="inline-flex gap-2">
                @foreach($instructors as $ins)
                    <button type="button"
                        class="px-3 py-1 border rounded-full text-sm"
                        wire:click="toggleInstructorId({{ $ins->id }})"
                        @class(['bg-blue-50 dark:bg-blue-900/30 border-blue-500'=> in_array($ins->id, $selectedInstructorIds)])
                        aria-pressed="{{ in_array($ins->id, $selectedInstructorIds) ? 'true' : 'false' }}"
                        wire:key="ins-{{ $ins->id }}">
                        {{ $ins->name }}
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    <div class="space-y-6" aria-live="polite">
        @if($mode === 'group')
            <div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($this->schedules as $s)
                        @php
                            $lesson = $s->lesson;
                            $store = $lesson?->store;
                            $full = $s->isFullyBooked();
                        @endphp
                        <div class="border rounded-xl p-4 bg-white dark:bg-gray-800 shadow" wire:key="s-{{ $s->id }}">
                            <div class="flex items-center justify-between mb-2">
                                <div class="text-sm text-gray-600 dark:text-gray-400">{{ $s->start_datetime->format('Y/m/d') }} {{ $s->formatted_start_time }} - {{ $s->formatted_end_time }}</div>
                                <span class="text-xs px-2 py-0.5 rounded font-medium {{ $full ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' : 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300' }}">
                                    {{ $full ? '満員/待機' : '空きあり' }}
                                </span>
                            </div>
                            <div class="mb-2">
                                <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $lesson?->name ?? '未設定' }}</div>
                                <div class="text-xs text-gray-600 dark:text-gray-400">店舗: {{ $store?->name ?? '未設定' }}</div>
                            </div>
                            @php
                                $sub = optional(auth()->user())->getActiveSubscriptionForCategory((int)($lesson?->category_id ?? 0));
                                $canBook = $sub && $s->canBookWithSubscription($sub);
                                $reservedByMe = (bool) ($s->reserved_by_me ?? false);
                            @endphp
                            <div class="mb-3 text-xs text-gray-700 dark:text-gray-300">
                                @if($sub)
                                    残り回数: <span class="font-semibold">{{ $sub->remaining_lessons }}</span> 回
                                @else
                                    対象プラン未契約または対象外カテゴリ
                                @endif
                            </div>
                            <form method="POST" action="{{ route('reservations.store', ['lessonSchedule' => $s->id]) }}">
                                @csrf
                                <input type="hidden" name="user_subscription_id" value="{{ $sub?->id }}" />
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary text-white border border-transparent rounded-md font-semibold text-sm hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-50 disabled:pointer-events-none transition ease-in-out duration-150" {{ ($full || ! $canBook || $reservedByMe) ? 'disabled' : '' }} aria-label="予約する">{{ $reservedByMe ? '予約済み' : '予約する' }}</button>
                            </form>
                        </div>
                    @empty
                        <p class="text-sm text-gray-600 dark:text-gray-400">該当するレッスンはありません。</p>
                    @endforelse
                </div>
            </div>
        @else
            @forelse($this->schedulesByTime as $time => $items)
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ $time }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($items as $s)
                            @php
                                $lesson = $s->lesson;
                                $store = $lesson?->store;
                                $full = $s->isFullyBooked();
                            @endphp
                            <div class="border rounded-xl p-4 bg-white dark:bg-gray-800 shadow" wire:key="s-{{ $s->id }}">
                            <div class="flex items-center justify-between mb-2">
                                <div class="text-sm text-gray-600 dark:text-gray-400">{{ $s->start_datetime->format('Y/m/d') }} {{ $s->formatted_start_time }} - {{ $s->formatted_end_time }}</div>
                                    <span class="text-xs px-2 py-0.5 rounded font-medium {{ $full ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' : 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300' }}">
                                        {{ $full ? '満員/待機' : '空きあり' }}
                                    </span>
                                </div>
                                <div class="mb-2">
                                    <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $lesson?->name ?? '未設定' }}</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400">店舗: {{ $store?->name ?? '未設定' }}</div>
                                </div>
                                @php
                                    $sub = optional(auth()->user())->getActiveSubscriptionForCategory((int)($lesson?->category_id ?? 0));
                                    $canBook = $sub && $s->canBookWithSubscription($sub);
                                    $reservedByMe = (bool) ($s->reserved_by_me ?? false);
                                @endphp
                                <div class="mb-3 text-xs text-gray-700 dark:text-gray-300">
                                    @if($sub)
                                        残り回数: <span class="font-semibold">{{ $sub->remaining_lessons }}</span> 回
                                    @else
                                        対象プラン未契約または対象外カテゴリ
                                    @endif
                                </div>
                                <form method="POST" action="{{ route('reservations.store', ['lessonSchedule' => $s->id]) }}">
                                    @csrf
                                    <input type="hidden" name="user_subscription_id" value="{{ $sub?->id }}" />
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary text-white border border-transparent rounded-md font-semibold text-sm hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-50 disabled:pointer-events-none transition ease-in-out duration-150" {{ ($full || ! $canBook || $reservedByMe) ? 'disabled' : '' }} aria-label="予約する">{{ $reservedByMe ? '予約済み' : '予約する' }}</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-600 dark:text-gray-400">該当するレッスンはありません。</p>
            @endforelse
        @endif
    </div>


</div>
