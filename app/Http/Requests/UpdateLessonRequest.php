<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('access-admin') ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('lesson_categories', 'id')->whereNotNull('parent_id'),
            ],
            'instructor_user_id' => ['required', 'integer', 'exists:users,id'],
            'duration' => ['required', 'integer', 'min:10', 'max:600'],
            'capacity' => ['required', 'integer', 'min:1', 'max:500'],
            'booking_deadline_hours' => ['required', 'integer', 'min:0', 'max:336'],
            'cancel_deadline_hours' => ['required', 'integer', 'min:0', 'max:336'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $payload = [];
        foreach (['is_active'] as $booleanField) {
            if ($this->has($booleanField)) {
                $payload[$booleanField] = $this->boolean($booleanField);
            }
        }
        $this->merge($payload);
    }
}
