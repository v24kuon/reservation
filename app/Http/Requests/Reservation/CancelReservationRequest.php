<?php

namespace App\Http\Requests\Reservation;

use App\Models\Reservation;
use Illuminate\Foundation\Http\FormRequest;

class CancelReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Reservation|null $reservation */
        $reservation = $this->route('reservation');

        return $reservation !== null
            && $this->user()?->can('cancel', $reservation) === true;
    }

    public function rules(): array
    {
        return [];
    }
}
