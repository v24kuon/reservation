<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class UpdateLessonScheduleRequest extends FormRequest
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
            'lesson_id' => ['sometimes', 'integer', Rule::exists('lessons', 'id')],
            'start_datetime' => [
                'sometimes',
                'date_format:Y-m-d H:i',
                'after_or_equal:now',
            ],
            'end_datetime' => [
                'sometimes',
                'date',
                function (string $attribute, $value, \Closure $fail) {
                    $current = $this->input('start_datetime')
                        ?? optional($this->route('lesson_schedule'))->start_datetime;
                    if ($current) {
                        $end = Carbon::parse($value);
                        $start = $current instanceof \Carbon\CarbonInterface ? $current : Carbon::parse($current);
                        if ($end->lte($start)) {
                            $fail('終了日時は開始日時より後である必要があります。');
                        }
                    }
                },
            ],
            'current_bookings' => [
                'sometimes', 'integer', 'min:0',
                function (string $attribute, $value, \Closure $fail) {
                    $lesson = $this->input('lesson_id')
                        ? \App\Models\Lesson::find($this->input('lesson_id'))
                        : optional($this->route('lesson_schedule'))->lesson;
                    if ($lesson && $value > $lesson->capacity) {
                        $fail('現在予約数は定員を超えられません。');
                    }
                },
            ],
            'is_active' => ['sometimes', 'boolean'],
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
