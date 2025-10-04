<?php

namespace App\Http\Requests\Subscription;

use Illuminate\Foundation\Http\FormRequest;

class SwitchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'from_plan_id' => ['required', 'integer', 'exists:subscription_plans,id'],
            'to_plan_id' => ['required', 'integer', 'different:from_plan_id', 'exists:subscription_plans,id'],
        ];
    }
}
