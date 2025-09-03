<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('access-admin') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:2000'],
            // 日本の電話番号: 先頭のみ+許容、7〜20文字
            'phone' => ['required', 'string', 'min:7', 'max:20', 'regex:/^\+?[0-9\-\(\)\s]+$/'],
            'access_info' => ['nullable', 'string', 'max:2000'],
            'google_map_url' => ['nullable', 'url', 'max:255', 'starts_with:https://,http://'],
            'parking_info' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $payload = [
            'phone' => $this->phone ? mb_convert_kana($this->phone, 'as') : null,
        ];
        if ($this->has('is_active')) {
            $payload['is_active'] = $this->boolean('is_active');
        }
        $this->merge($payload);
    }

    public function attributes(): array
    {
        return [
            'name' => '名称',
            'address' => '住所',
            'phone' => '電話番号',
            'access_info' => 'アクセス情報',
            'google_map_url' => 'GoogleマップURL',
            'parking_info' => '駐車場情報',
            'notes' => '備考',
            'is_active' => '有効フラグ',
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => '電話番号は数字・スペース・()+- のみ使用できます。',
        ];
    }
}
