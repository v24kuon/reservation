<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStoreRequest extends FormRequest
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
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9\-\+\(\)\s]+$/'],
            'access_info' => ['nullable', 'string', 'max:2000'],
            'google_map_url' => ['nullable', 'url', 'max:2000'],
            'parking_info' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => $this->phone ? mb_convert_kana($this->phone, 'as') : null,
            'is_active' => $this->boolean('is_active'),
        ]);
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
