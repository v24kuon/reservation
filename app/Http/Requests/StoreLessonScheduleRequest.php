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
                'date',
                'after_or_equal:now',
            ],
            'end_datetime' => [
                'required',
                'date',
                'after:start_datetime',
            ],
            'current_bookings' => [
                'required', 'integer', 'min:0',
                function (string $attribute, $value, \Closure $fail) {
                    $lessonId = $this->input('lesson_id');
                    $lesson = $lessonId ? \App\Models\Lesson::find($lessonId) : null;
                    if ($lesson && $value > $lesson->capacity) {
                        $fail('現在予約数は定員を超えられません。');
                    }
                },
            ],
            'is_active' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
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
