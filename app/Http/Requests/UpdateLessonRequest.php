<?php

namespace App\Http\Requests;

use App\Models\User;
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
            'instructor_user_id' => [
                'sometimes', 'integer',
                Rule::exists('users', 'id')->where('role', User::ROLE_INSTRUCTOR),
            ],
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
        foreach (['is_active'] as $f) {
            if ($this->has($f)) {
                $payload[$f] = $this->boolean($f);
            }
        }
        foreach (['store_id','category_id','instructor_user_id','duration','capacity','booking_deadline_hours','cancel_deadline_hours'] as $f) {
            if ($this->has($f)) {
                $payload[$f] = (int) $this->input($f);
            }
        }
        $this->merge($payload);
    }
}
