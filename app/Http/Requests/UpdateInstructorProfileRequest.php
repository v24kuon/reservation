<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class UpdateInstructorProfileRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $payload = [
            'bio' => $this->bio ? trim($this->bio) : null,
            'qualifications' => $this->qualifications ? trim($this->qualifications) : null,
            'notes' => $this->notes ? trim($this->notes) : null,
        ];
        if ($this->has('remove_image')) {
            $payload['remove_image'] = $this->boolean('remove_image');
        }
        $this->merge($payload);
    }

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

        // If route has {instructor} or {user} parameter, only allow when targeting own ID
        $routeTarget = $this->route('instructor') ?? $this->route('user');
        if ($routeTarget !== null) {
            $routeId = (int) ($routeTarget->id ?? $routeTarget);

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
                'prohibited_if:remove_image,true',
                File::image()
                    ->types(config('uploads.instructor_profile.allowed_types', ['jpg', 'jpeg', 'png', 'webp']))
                    ->max(config('uploads.instructor_profile.max_kb', 10 * 1024)), // KB
            ],
            'remove_image' => ['sometimes', 'boolean'],
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
            'image.max' => '画像は:maxKB以下にしてください。',
            'image.mimes' => '画像はjpg, jpeg, png, webp形式でアップロードしてください。',
            'bio.max' => '自己紹介は2000文字以内で入力してください。',
            'qualifications.max' => '資格は2000文字以内で入力してください。',
            'notes.max' => '備考は2000文字以内で入力してください。',
        ];
    }
}
