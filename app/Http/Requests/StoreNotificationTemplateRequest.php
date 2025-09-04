<?php

namespace App\Http\Requests;

use App\Models\SystemSetting;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNotificationTemplateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('access-admin') === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $types = \App\Models\NotificationTemplate::TYPES;

        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:255', Rule::in($types), 'unique:notification_templates,type'],
            'subject' => ['required', 'string', 'max:255'],
            'body_text' => ['nullable', 'string'],
            'variables' => ['nullable', 'array'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'variables' => is_string($this->input('variables'))
                ? (json_decode($this->input('variables'), true) ?? $this->input('variables'))
                : $this->input('variables'),
        ]);
    }

    public function withValidator(ValidatorContract $validator): void
    {
        $validator->after(function (ValidatorContract $v) {
            $body = (string) $this->input('body_text', '');
            if ($body === '') {
                return;
            }
            preg_match_all('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', $body, $m);
            $used = array_unique($m[1] ?? []);
            $whitelist = SystemSetting::getJson('email_variables_whitelist', []);
            $disallowed = array_values(array_diff($used, $whitelist));
            if (! empty($disallowed)) {
                $v->errors()->add('body_text', '未許可の変数が含まれています: '.implode(', ', $disallowed));
            }
        });
    }
}
