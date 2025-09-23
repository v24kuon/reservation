<?php

namespace App\Http\Controllers;

use App\Http\Requests\Reservation\CancelReservationRequest;
use App\Http\Requests\Reservation\StoreReservationRequest;
use App\Models\LessonSchedule;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

class ReservationController extends Controller
{
    /**
     * Store a newly created reservation for the authenticated user.
     */
    public function store(StoreReservationRequest $request, LessonSchedule $lessonSchedule): RedirectResponse
    {
        $user = $request->user();

        $subscriptionId = $request->validated()['user_subscription_id'] ?? null;
        $subscription = null;
        if ($subscriptionId) {
            $subscription = $user->userSubscriptions()->find($subscriptionId);
        }

        $result = $lessonSchedule->createReservationForUser($user, $subscription);

        if (! ($result['success'] ?? false)) {
            return back()
                ->withErrors(['reservation' => Arr::wrap($result['errors'] ?? trans('reservation.errors.generic_failure'))])
                ->withInput();
        }

        return back()->with('status', trans('reservation.success.created'));
    }

    /**
     * Cancel the specified reservation for the authenticated user.
     */
    public function destroy(CancelReservationRequest $request, Reservation $reservation): RedirectResponse
    {
        // ensure FormRequest side-effects (authorize/validation) are executed and silence unused param warning
        $request->validated();
        $result = $reservation->cancelWithValidation();

        if (! ($result['success'] ?? false)) {
            $error = $result['errors'] ?? ($result['error'] ?? trans('reservation.errors.cancel_generic_failure'));
            return back()->withErrors(['reservation' => \Illuminate\Support\Arr::wrap($error)]);
        }

        return back()->with('status', trans('reservation.success.canceled'));
    }
}
