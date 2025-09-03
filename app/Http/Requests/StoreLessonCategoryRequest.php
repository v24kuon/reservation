<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLessonCategoryRequest extends FormRequest
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
            'parent_id' => ['nullable', 'integer', 'exists:lesson_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:100000'],
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
            'parent_id' => '親カテゴリ',
            'name' => '名称',
            'description' => '説明',
            'is_active' => '有効フラグ',
            'sort_order' => '表示順',
        ];
    }
}
