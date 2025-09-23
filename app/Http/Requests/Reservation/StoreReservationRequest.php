<?php

namespace App\Http\Requests\Reservation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\LessonSchedule|null $lessonSchedule */
        $lessonSchedule = $this->route('lessonSchedule');
        return $lessonSchedule !== null
            && $this->user()?->can('create', [\App\Models\Reservation::class, $lessonSchedule]) === true;
    }

    public function rules(): array
    {
        return [
            'user_subscription_id' => [
                'nullable',
                'integer',
                Rule::exists('user_subscriptions', 'id')
                    ->where(fn ($q) => $q->where('user_id', $this->user()?->getKey() ?? 0)),
            ],
        ];
    }
}
