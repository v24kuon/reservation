<?php

namespace App\Http\Controllers\Reservation;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reservation\HistoryFilterRequest;
use App\Models\Reservation;
use Illuminate\Contracts\View\View;

class HistoryController extends Controller
{
    public function index(HistoryFilterRequest $request): View
    {
        $user = $request->user();

        $base = Reservation::query()
            ->with(['lessonSchedule.lesson.store', 'lessonSchedule.lesson.instructor'])
            ->where('user_id', $user->id)
            ->join('lesson_schedules', 'lesson_schedules.id', '=', 'reservations.lesson_schedule_id')
            ->select('reservations.*');

        $filters = $request->validated();
        $status = $filters['status'] ?? null;
        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;

        $applyFilters = function ($query) use ($status, $from, $to) {
            if ($status !== null && $status !== '') {
                $query->where('reservations.status', $status);
            }
            if ($from) {
                $query->where('lesson_schedules.start_datetime', '>=', $from);
            }
            if ($to) {
                $query->where('lesson_schedules.start_datetime', '<=', \Carbon\Carbon::parse($to)->endOfDay());
            }
        };

        $perPage = (int) config('pagination.profile_reservations', 10);
        $now = now();

        // Upcoming: start >= now (asc)
        $upcomingQuery = (clone $base)
            ->where('lesson_schedules.start_datetime', '>=', $now)
            ->orderBy('lesson_schedules.start_datetime', 'asc');
        $applyFilters($upcomingQuery);
        $upcoming = $upcomingQuery->paginate($perPage, ['*'], 'up_page')->withQueryString();

        // Past: start < now (desc)
        $pastQuery = (clone $base)
            ->where('lesson_schedules.start_datetime', '<', $now)
            ->orderBy('lesson_schedules.start_datetime', 'desc');
        $applyFilters($pastQuery);
        $past = $pastQuery->paginate($perPage, ['*'], 'past_page')->withQueryString();

        return view('reservations.history', [
            'upcoming' => $upcoming,
            'past' => $past,
            'filters' => [
                'status' => $status,
                'from' => $from,
                'to' => $to,
            ],
        ]);
    }
}
