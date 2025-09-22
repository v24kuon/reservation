<?php

namespace App\Http\Requests\Reservation;

use App\Models\Reservation;
use Illuminate\Foundation\Http\FormRequest;

class CancelReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Reservation $reservation */
        $reservation = $this->route('reservation');

        return $this->user() && $reservation && (int) $reservation->user_id === (int) $this->user()->getKey();
    }

    public function rules(): array
    {
        return [];
    }
}
