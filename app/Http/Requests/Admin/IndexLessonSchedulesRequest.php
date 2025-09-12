<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class IndexLessonSchedulesRequest extends FormRequest
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
            'date_from' => ['sometimes', 'date_format:Y-m-d'],
            'date_to' => ['sometimes', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'lesson_id' => ['sometimes', 'integer', 'exists:lessons,id'],
            'instructor_user_id' => ['sometimes', 'integer', 'exists:users,id'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'date_from' => '開始日',
            'date_to' => '終了日',
            'lesson_id' => 'レッスン',
            'instructor_user_id' => 'インストラクター',
            'is_active' => '有効フラグ',
        ];
    }
}
