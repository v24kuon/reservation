<?php

namespace App\Http\Requests\Admin\Subscriptions;

use App\Models\UserSubscription;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('access-admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'plan_id' => ['required', 'integer', 'exists:subscription_plans,id'],
            'stripe_subscription_id' => ['required', 'string', 'max:255', 'unique:user_subscriptions,stripe_subscription_id'],
            'status' => ['required', 'string', 'max:50', Rule::in(UserSubscription::ALLOWED_STATUSES)],
            'payment_status' => ['required', 'string', 'max:50', Rule::in(UserSubscription::ALLOWED_PAYMENT_STATUSES)],
            'failure_reason' => ['nullable', 'string', 'max:2000'],
            'current_period_start' => ['required', 'date'],
            'current_period_end' => ['required', 'date', 'after_or_equal:current_period_start'],
            'current_month_used_count' => ['nullable', 'integer', 'min:0'],
            'remaining_lessons' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
