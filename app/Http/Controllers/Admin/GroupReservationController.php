<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GroupReservations\IndexGroupReservationsRequest;
use App\Models\Lesson;
use App\Models\Reservation;
use App\Models\Store;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;

class GroupReservationController extends Controller
{
    public function index(IndexGroupReservationsRequest $request): View
    {
        $filters = $request->validated();

        $query = Reservation::query()
            ->with(['user', 'lessonSchedule.lesson.store', 'lessonSchedule.lesson.instructor'])
            ->whereHas('lessonSchedule.lesson.store'); // 店舗を持つレッスン＝グループ扱い

        if (! empty($filters['store_id'])) {
            $query->whereHas('lessonSchedule.lesson', fn ($q) => $q->where('store_id', $filters['store_id']));
        }

        if (! empty($filters['instructor_id'])) {
            $query->whereHas('lessonSchedule.lesson', fn ($q) => $q->where('instructor_user_id', $filters['instructor_id']));
        }

        if (! empty($filters['lesson_id'])) {
            $query->whereHas('lessonSchedule', fn ($q) => $q->where('lesson_id', $filters['lesson_id']));
        }

        if (! empty($filters['user'])) {
            $term = $filters['user'];
            $query->whereHas('user', function ($uq) use ($term): void {
                $uq->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['date_from'])) {
            $from = Carbon::createFromFormat('Y-m-d', $filters['date_from'])->startOfDay();
            $query->whereHas('lessonSchedule', fn ($q) => $q->where('start_datetime', '>=', $from));
        }
        if (! empty($filters['date_to'])) {
            $to = Carbon::createFromFormat('Y-m-d', $filters['date_to'])->endOfDay();
            $query->whereHas('lessonSchedule', fn ($q) => $q->where('start_datetime', '<=', $to));
        }

        $reservations = $query
            ->join('lesson_schedules', 'lesson_schedules.id', '=', 'reservations.lesson_schedule_id')
            ->orderByDesc('lesson_schedules.start_datetime')
            ->orderByDesc('reservations.id')
            ->select('reservations.*')
            ->paginate(50)
            ->withQueryString();

        $stores = Store::query()->orderBy('name')->get(['id', 'name']);
        $instructors = User::query()->where('role', User::ROLE_INSTRUCTOR)->orderBy('name')->get(['id', 'name']);
        $lessons = Lesson::query()->whereHas('store')->orderBy('name')->get(['id', 'name']);

        return view('admin.group-reservations.index', compact('reservations', 'filters', 'stores', 'instructors', 'lessons'));
    }
}
