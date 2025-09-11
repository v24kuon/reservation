<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkStoreLessonSchedulesRequest extends FormRequest
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
            'items' => ['required', 'array', 'min:1'],
            'items.*.start_datetime' => ['required', 'date_format:Y-m-d H:i:s', 'after_or_equal:now'],
            'items.*.end_datetime' => ['required', 'date_format:Y-m-d H:i:s', 'after:start_datetime'],
            'items.*.is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $items = $this->input('items');
        if (is_array($items)) {
            foreach ($items as $idx => $row) {
                if (is_array($row)) {
                    if (array_key_exists('is_active', $row)) {
                        $casted = filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                        if ($casted !== null) {
                            $items[$idx]['is_active'] = $casted;
                        }
                        // invalid values are left as-is to be caught by validation
                    } else {
                        $items[$idx]['is_active'] = true; // default when not provided
                    }
                    // Normalize datetime-local (YYYY-MM-DDTHH:MM) or other formats to Y-m-d H:i:s
                    if (! empty($row['start_datetime'])) {
                        try {
                            $items[$idx]['start_datetime'] = \Illuminate\Support\Carbon::parse(str_replace('T', ' ', (string) $row['start_datetime']))->format('Y-m-d H:i:s');
                        } catch (\Throwable $e) {
                            // keep original; validation will catch invalid date
                        }
                    }
                    if (! empty($row['end_datetime'])) {
                        try {
                            $items[$idx]['end_datetime'] = \Illuminate\Support\Carbon::parse(str_replace('T', ' ', (string) $row['end_datetime']))->format('Y-m-d H:i:s');
                        } catch (\Throwable $e) {
                            // keep original; validation will catch invalid date
                        }
                    }
                }
            }
            $this->merge(['items' => $items]);
        }
    }

    public function attributes(): array
    {
        return [
            'lesson_id' => 'レッスン',
            'items' => 'スケジュール',
            'items.*.start_datetime' => '開始日時',
            'items.*.end_datetime' => '終了日時',
            'items.*.is_active' => '有効フラグ',
        ];
    }
}
