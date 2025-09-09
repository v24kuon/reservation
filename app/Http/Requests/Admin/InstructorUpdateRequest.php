<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class InstructorUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('access-admin') ?? false;
    }

    public function rules(): array
    {
        $routeInstructor = $this->route('instructor');
        $instructorId = is_object($routeInstructor) ? ($routeInstructor->id ?? null) : (int) $routeInstructor;

        return [
            // User basic fields
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($instructorId),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],

            // Profile fields
            'image' => [
                'nullable',
                File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max(10 * 1024),
            ],
            'remove_image' => ['sometimes', 'boolean'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'qualifications' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => '氏名は必須です。',
            'email.required' => 'メールアドレスは必須です。',
            'email.email' => 'メールアドレスの形式が正しくありません。',
            'email.unique' => 'このメールアドレスは既に使用されています。',
            'password.min' => 'パスワードは8文字以上で入力してください。',
            'password.confirmed' => 'パスワード（確認）が一致しません。',
            'image.image' => '画像ファイルを指定してください。',
            'image.mimes' => '画像はjpg, jpeg, png, webp形式でアップロードしてください。',
            'image.max' => '画像は10MB以下にしてください。',
            'bio.max' => '自己紹介は2000文字以内で入力してください。',
            'qualifications.max' => '資格は2000文字以内で入力してください。',
            'notes.max' => '備考は2000文字以内で入力してください。',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => '氏名',
            'email' => 'メールアドレス',
            'password' => 'パスワード',
            'password_confirmation' => 'パスワード（確認）',
            'image' => '画像',
            'bio' => '自己紹介',
            'qualifications' => '資格',
            'notes' => '備考',
        ];
    }
}
