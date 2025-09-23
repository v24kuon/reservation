@php
    $method = $method ?? 'POST';
    $subscription = $subscription ?? null;
    $plans = $plans ?? collect();
@endphp

<form method="POST" action="{{ $action }}">
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif

    <div class="space-y-6">
        @if(!$subscription)
            <div>
                <label class="block text-sm font-medium" for="user_id">ユーザーID</label>
                <input id="user_id" name="user_id" type="number" class="mt-1 w-full border rounded p-2" required min="1" value="{{ old('user_id') }}" autocomplete="off">
                @error('user_id')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium" for="plan_id">プラン</label>
                <select id="plan_id" name="plan_id" class="mt-1 w-full border rounded p-2" required>
                    <option value="">選択してください</option>
                    @foreach($plans as $p)
                        <option value="{{ $p->id }}" @selected(old('plan_id') == $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
                @error('plan_id')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium" for="stripe_subscription_id">Stripe Subscription ID</label>
                <input id="stripe_subscription_id" name="stripe_subscription_id" type="text" class="mt-1 w-full border rounded p-2" required maxlength="255" value="{{ old('stripe_subscription_id') }}" autocomplete="off">
                @error('stripe_subscription_id')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium" for="status">ステータス</label>
                <input id="status" name="status" type="text" class="mt-1 w-full border rounded p-2" required maxlength="50" value="{{ old('status', $subscription->status ?? '') }}" autocomplete="off">
                @error('status')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium" for="payment_status">支払い</label>
                <input id="payment_status" name="payment_status" type="text" class="mt-1 w-full border rounded p-2" required maxlength="50" value="{{ old('payment_status', $subscription->payment_status ?? '') }}" autocomplete="off">
                @error('payment_status')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium" for="failure_reason">失敗理由（任意）</label>
            <textarea id="failure_reason" name="failure_reason" class="mt-1 w-full border rounded p-2" rows="3">{{ old('failure_reason', $subscription->failure_reason ?? '') }}</textarea>
            @error('failure_reason')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium" for="current_period_start">開始日</label>
                <input id="current_period_start" name="current_period_start" type="datetime-local" class="mt-1 w-full border rounded p-2" required value="{{ old('current_period_start', isset($subscription) ? $subscription->current_period_start?->format('Y-m-d\TH:i') : '') }}">
                @error('current_period_start')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium" for="current_period_end">終了日</label>
                <input id="current_period_end" name="current_period_end" type="datetime-local" class="mt-1 w-full border rounded p-2" required value="{{ old('current_period_end', isset($subscription) ? $subscription->current_period_end?->format('Y-m-d\TH:i') : '') }}">
                @error('current_period_end')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium" for="remaining_lessons">残り回数</label>
                <input id="remaining_lessons" name="remaining_lessons" type="number" min="0" class="mt-1 w-full border rounded p-2" value="{{ old('remaining_lessons', $subscription->remaining_lessons ?? 0) }}">
                @error('remaining_lessons')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium" for="current_month_used_count">当月使用回数</label>
                <input id="current_month_used_count" name="current_month_used_count" type="number" min="0" class="mt-1 w-full border rounded p-2" value="{{ old('current_month_used_count', $subscription->current_month_used_count ?? 0) }}">
                @error('current_month_used_count')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="pt-4 flex space-x-2">
            <x-primary-button type="submit">保存</x-primary-button>
            <x-secondary-button href="{{ route('admin.subscriptions.index') }}">戻る</x-secondary-button>
        </div>
    </div>
</form>
