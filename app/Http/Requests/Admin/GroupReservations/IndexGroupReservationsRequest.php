<?php

namespace App\Http\Requests\Admin\GroupReservations;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexGroupReservationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('access-admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
            'instructor_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('role', User::ROLE_INSTRUCTOR)],
            'lesson_id' => ['nullable', 'integer', 'exists:lessons,id'],
            'user' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', Rule::in(['pending', 'confirmed', 'canceled', 'completed', 'no_show'])],
            'date_from' => ['nullable', 'date', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ];
    }
}
