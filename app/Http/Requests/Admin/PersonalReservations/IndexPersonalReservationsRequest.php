<?php

namespace App\Http\Requests\Admin\PersonalReservations;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexPersonalReservationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('access-admin') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'instructor_id' => $this->filled('instructor_id') ? (int) $this->input('instructor_id') : null,
            'lesson_id' => $this->filled('lesson_id') ? (int) $this->input('lesson_id') : null,
            'user' => is_string($this->input('user')) ? trim($this->input('user')) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'instructor_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('role', User::ROLE_INSTRUCTOR)],
            'lesson_id' => ['nullable', 'integer', 'exists:lessons,id'],
            'user' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', Rule::in(Reservation::STATUSES)],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ];
    }
}
