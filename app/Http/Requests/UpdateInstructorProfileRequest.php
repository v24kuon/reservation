<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class UpdateInstructorProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        // Allow admins
        if ($user->isAdmin()) {
            return true;
        }

        // If route has {instructor} parameter, only allow when targeting own ID
        $routeInstructor = $this->route('instructor');
        if ($routeInstructor !== null) {
            $routeId = (int) ($routeInstructor->id ?? $routeInstructor);

            return $user->id === $routeId;
        }

        // For self-edit routes without parameter (e.g. /instructor/profile), allow instructors
        return $user->isInstructor();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'image' => [
                'nullable',
                File::image()
                    ->types(['jpg', 'jpeg', 'png', 'webp'])
                    ->max(10 * 1024), // 10MB
            ],
            'bio' => ['nullable', 'string', 'max:2000'],
            'qualifications' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'image.image' => '画像ファイルを指定してください。',
            'image.max' => '画像は10MB以下にしてください。',
            'image.mimes' => '画像はjpg/png/webp形式でアップロードしてください。',
            'bio.max' => '自己紹介は2000文字以内で入力してください。',
            'qualifications.max' => '資格は2000文字以内で入力してください。',
            'notes.max' => '備考は2000文字以内で入力してください。',
        ];
    }
}
