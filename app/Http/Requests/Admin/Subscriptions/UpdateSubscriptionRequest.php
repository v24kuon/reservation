<?php

namespace App\Http\Requests\Admin\Subscriptions;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('access-admin') ?? false;
    }

    public function rules(): array
    {
        return [
            // user_id/plan_id are immutable on this screen
            'status' => ['required', 'string', 'max:50'],
            'payment_status' => ['required', 'string', 'max:50'],
            'failure_reason' => ['nullable', 'string', 'max:2000'],
            'current_period_start' => ['required', 'date'],
            'current_period_end' => ['required', 'date', 'after_or_equal:current_period_start'],
            'current_month_used_count' => ['nullable', 'integer', 'min:0'],
            'remaining_lessons' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
