<?php

namespace App\Http\Requests\Admin\Subscriptions;

use Illuminate\Foundation\Http\FormRequest;

class IndexSubscriptionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('access-admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'user' => ['nullable', 'string', 'max:255'],
            'plan_id' => ['nullable', 'integer', 'exists:subscription_plans,id'],
            'status' => ['nullable', 'string', 'max:50'],
            'payment_status' => ['nullable', 'string', 'max:50'],
            'period_from' => ['nullable', 'date'],
            'period_to' => ['nullable', 'date', 'after_or_equal:period_from'],
        ];
    }
}
