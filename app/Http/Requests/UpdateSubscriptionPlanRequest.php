<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubscriptionPlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()?->can('access-admin') === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            // フォームの price は表示用。保存時は Stripe から上書きするため必須ではない。
            'price' => ['nullable', 'integer', 'min:1'],
            'lesson_count' => ['required', 'integer', 'min:1'],
            'allowed_category_ids' => ['required', 'array', 'min:1'],
            'allowed_category_ids.*' => ['integer', 'exists:lesson_categories,id'],
            'stripe_product_id' => ['required', 'regex:/^prod_[A-Za-z0-9]+$/'],
            'stripe_price_id' => ['required', 'regex:/^price_[A-Za-z0-9]+$/'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
