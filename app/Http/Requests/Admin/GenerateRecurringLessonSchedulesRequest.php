<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class GenerateRecurringLessonSchedulesRequest extends FormRequest
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
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'interval_weeks' => ['sometimes', 'integer', 'min:1'],
            'weekdays' => ['required', 'array', 'min:1'],
            'weekdays.*' => ['integer', 'between:0,6'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $weekdays = $this->input('weekdays');
        if (is_array($weekdays)) {
            $weekdays = array_values(array_unique(array_map('intval', $weekdays)));
        }
        $interval = (int) ($this->input('interval_weeks') ?? 1);
        if ($interval < 1) {
            $interval = 1;
        }
        $this->merge([
            'weekdays' => $weekdays,
            'interval_weeks' => $interval,
        ]);
    }

    public function attributes(): array
    {
        return [
            'start_date' => '期間（開始日）',
            'end_date' => '期間（終了日）',
            'start_time' => '開始時刻',
            'end_time' => '終了時刻',
            'interval_weeks' => '間隔（週）',
            'weekdays' => '曜日',
        ];
    }
}
