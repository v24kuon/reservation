<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the home page with user's current reservations and subscriptions.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Get next reservations (start time from now, soonest first), confirmed, limit 2
        $currentReservations = Reservation::query()
            ->select('reservations.*')
            ->join('lesson_schedules', 'lesson_schedules.id', '=', 'reservations.lesson_schedule_id')
            ->with([
                'lessonSchedule.lesson.store',
                'lessonSchedule.lesson.category.parent',
                'lessonSchedule.lesson.instructor',
            ])
            ->where('reservations.user_id', $user->id)
            ->confirmed()
            ->where('lesson_schedules.start_datetime', '>=', now())
            ->orderBy('lesson_schedules.start_datetime', 'asc')
            ->limit(2)
            ->get();

        // Get active subscriptions (all currently valid)
        $activeSubscriptions = UserSubscription::query()
            ->with('plan')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->where('payment_status', 'paid')
            ->where('current_period_end', '>', now())
            ->orderBy('current_period_end', 'asc')
            ->get();

        return view('home', [
            'currentReservations' => $currentReservations,
            'activeSubscriptions' => $activeSubscriptions,
        ]);
    }
}
