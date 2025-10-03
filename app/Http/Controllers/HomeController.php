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

        // Get next reservations (start time from now, soonest first), confirmed, limit 3
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
            ->limit(3)
            ->get();

        // Get active subscriptions (all currently valid), limit 3
        $activeSubscriptions = UserSubscription::query()
            ->with('plan')
            ->where('user_id', $user->id)
            ->active()
            ->paid()
            ->notExpired()
            ->orderBy('current_period_end', 'asc')
            ->limit(3)
            ->get();

        // Enrich with Stripe cancel flags for display
        $this->appendStripeCancelFlagsToCollection($activeSubscriptions);

        return view('home', [
            'currentReservations' => $currentReservations,
            'activeSubscriptions' => $activeSubscriptions,
        ]);
    }

    /**
     * Append Stripe cancel flags to a collection of subscriptions for UI display.
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\UserSubscription>  $items
     */
    private function appendStripeCancelFlagsToCollection($items): void
    {
        $secret = config('services.stripe.secret');
        if (empty($secret)) {
            return;
        }

        $client = new \Stripe\StripeClient($secret);
        foreach ($items as $sub) {
            $sid = (string) ($sub->stripe_subscription_id ?? '');
            if ($sid === '') {
                continue;
            }
            try {
                $remote = $client->subscriptions->retrieve($sid, []);
                $sub->cancel_at_period_end = (bool) ($remote->cancel_at_period_end ?? false);
                $sub->cancel_at = isset($remote->cancel_at)
                    ? \Carbon\Carbon::createFromTimestamp((int) $remote->cancel_at)
                    : null;
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }
}
