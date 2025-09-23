<?php

namespace App\Policies;

use App\Models\LessonSchedule;
use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    /**
     * Determine whether the user can cancel the reservation.
     */
    public function cancel(User $user, Reservation $reservation): bool
    {
        return (string) $reservation->user_id === (string) $user->getKey();
    }

    /**
     * Determine whether the user can create a reservation for the given schedule.
     */
    public function create(User $user, LessonSchedule $lessonSchedule): bool
    {
        return $lessonSchedule->canUserBook($user);
    }
}
