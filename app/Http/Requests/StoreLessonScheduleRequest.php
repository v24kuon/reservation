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
                'date_format:Y-m-d\TH:i',
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
                    if ($lesson && $lesson->capacity !== null && $value > $lesson->capacity) {
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

        // Server-side end time recalculation for integrity
        $lessonId = $this->input('lesson_id');
        $start = $this->input('start_datetime');
        if ($lessonId && $start) {
            $lesson = \App\Models\Lesson::find($lessonId);
            if ($lesson && is_numeric($lesson->duration)) {
                try {
                    $normalized = str_replace('T', ' ', (string) $start);
                    $startAt = \Illuminate\Support\Carbon::parse($normalized, config('app.timezone'));
                    $endAt = $startAt->copy()->addMinutes((int) $lesson->duration);
                    // Override client-provided end_datetime
                    $this->merge([
                        'end_datetime' => $endAt->toDateTimeString(), // Y-m-d H:i:s
                    ]);
                } catch (\Throwable $e) {
                    // Invalid start_datetime will be handled by validation rules
                }
            }
        }
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
