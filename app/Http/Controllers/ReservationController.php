<?php

namespace App\Http\Controllers;

use App\Http\Requests\Reservation\CancelReservationRequest;
use App\Http\Requests\Reservation\StoreReservationRequest;
use App\Models\LessonSchedule;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;

class ReservationController extends Controller
{
    /**
     * Store a newly created reservation for the authenticated user.
     */
    public function store(StoreReservationRequest $request, LessonSchedule $lessonSchedule): RedirectResponse
    {
        $user = $request->user();

        $subscription = $request->validated()['user_subscription'] ?? null;

        $result = $lessonSchedule->createReservationForUser($user, $subscription);

        if (! $result['success']) {
            return back()
                ->withErrors(['reservation' => $result['errors'] ?? [trans('reservation.errors.generic_failure')]])
                ->withInput();
        }

        return back()->with('status', trans('reservation.success.created'));
    }

    /**
     * Cancel the specified reservation for the authenticated user.
     */
    public function destroy(CancelReservationRequest $request, Reservation $reservation): RedirectResponse
    {
        $result = $reservation->cancelWithValidation();

        if (! ($result['success'] ?? false)) {
            $error = $result['error'] ?? trans('reservation.errors.cancel_generic_failure');

            return back()->withErrors(['reservation' => [$error]]);
        }

        return back()->with('status', trans('reservation.success.canceled'));
    }
}
