<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class InstructorUpdateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->name ? trim($this->name) : null,
            'email' => $this->email ? trim($this->email) : null,
            'bio' => $this->bio ? trim($this->bio) : null,
            'qualifications' => $this->qualifications ? trim($this->qualifications) : null,
            'notes' => $this->notes ? trim($this->notes) : null,
            'remove_image' => $this->boolean('remove_image'),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()?->can('access-admin') ?? false;
    }

    public function rules(): array
    {
        $routeTarget = $this->route('instructor') ?? $this->route('user');
        $instructorId = is_object($routeTarget) ? ($routeTarget->id ?? null) : (int) $routeTarget;

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
                Rule::excludeIf(fn () => $this->boolean('remove_image')),
                File::image()
                    ->types(config('uploads.instructor_profile.allowed_types', ['jpg', 'jpeg', 'png', 'webp']))
                    ->max(config('uploads.instructor_profile.max_kb', 10 * 1024)),
            ],
            'remove_image' => ['sometimes', 'boolean'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'qualifications' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        $maxMb = intdiv((int) config('uploads.instructor_profile.max_kb', 10 * 1024), 1024);

        return [
            'name.required' => '氏名は必須です。',
            'email.required' => 'メールアドレスは必須です。',
            'email.email' => 'メールアドレスの形式が正しくありません。',
            'email.unique' => 'このメールアドレスは既に使用されています。',
            'password.min' => 'パスワードは8文字以上で入力してください。',
            'password.confirmed' => 'パスワード（確認）が一致しません。',
            'image.image' => '画像ファイルを指定してください。',
            'image.mimes' => '画像はjpg, jpeg, png, webp形式でアップロードしてください。',
            'image.max' => "画像は{$maxMb}MB以下にしてください。",
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
            'remove_image' => 'プロフィール画像を削除する',
            'bio' => '自己紹介',
            'qualifications' => '資格',
            'notes' => '備考',
        ];
    }
}
