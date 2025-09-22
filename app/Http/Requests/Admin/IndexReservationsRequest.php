<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class IndexReservationsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('access-admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'lesson_schedule_id' => ['nullable', 'integer', 'exists:lesson_schedules,id'],
            'status' => ['nullable', 'in:confirmed,canceled,completed,no_show'],
            'reserved_from' => ['nullable', 'date'],
            'reserved_to' => ['nullable', 'date', 'after_or_equal:reserved_from'],
        ];
    }
}
