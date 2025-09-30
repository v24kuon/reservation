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

        // Get next reservations (today or later), confirmed, limit 2
        $currentReservations = Reservation::query()
            ->with([
                'lessonSchedule.lesson.store',
                'lessonSchedule.lesson.category.parent',
                'lessonSchedule.lesson.instructor',
            ])
            ->where('user_id', $user->id)
            ->confirmed()
            ->whereHas('lessonSchedule', function ($query) {
                $query->whereDate('start_datetime', '>=', now()->toDateString());
            })
            ->orderBy('reserved_at', 'desc')
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
