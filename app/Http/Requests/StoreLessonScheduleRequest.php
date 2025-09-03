<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLessonScheduleRequest extends FormRequest
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
            'lesson_id' => ['required', 'integer', Rule::exists('lessons', 'id')],
            'start_datetime' => [
                'required',
                Rule::date()->afterOrEqual('now'),
            ],
            'end_datetime' => [
                'required',
                Rule::date()->after('start_datetime'),
            ],
            'current_bookings' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $payload = [];
        if ($this->has('is_active')) {
            $payload['is_active'] = $this->boolean('is_active');
        }
        $this->merge($payload);
    }

    public function attributes(): array
    {
        return [
            'lesson_id' => 'レッスン',
            'start_datetime' => '開始日時',
            'end_datetime' => '終了日時',
            'current_bookings' => '現在予約数',
            'is_active' => '有効フラグ',
        ];
    }
}
