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
            'items.*.start_datetime' => ['required', 'date', 'after_or_equal:now'],
            'items.*.end_datetime' => ['required', 'date', 'after:start_datetime'],
            'items.*.is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $items = $this->input('items');
        if (is_array($items)) {
            foreach ($items as $idx => $row) {
                if (is_array($row)) {
                    $items[$idx]['is_active'] = filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;
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
