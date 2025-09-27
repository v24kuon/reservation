<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PersonalReservations\IndexPersonalReservationsRequest;
use App\Models\Lesson;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;

class PersonalReservationController extends Controller
{
    public function index(IndexPersonalReservationsRequest $request): View
    {
        $filters = $request->validated();

        $query = Reservation::query()
            ->with(['user', 'lessonSchedule.lesson.instructor'])
            ->whereHas('lessonSchedule.lesson', fn ($q) => $q->where('capacity', 1));

        if (! empty($filters['instructor_id'])) {
            $query->whereHas('lessonSchedule.lesson', fn ($q) => $q->where('instructor_user_id', $filters['instructor_id']));
        }

        if (! empty($filters['lesson_id'])) {
            $query->whereHas('lessonSchedule', fn ($q) => $q->where('lesson_id', $filters['lesson_id']));
        }

        if (! empty($filters['user'])) {
            $term = $filters['user'];
            $query->whereHas('user', function ($uq) use ($term): void {
                $escape = '!';
                $pattern = '%'.str_replace([
                    $escape, '%', '_',
                ], [
                    $escape.$escape, $escape.'%', $escape.'_',
                ], $term).'%';

                $uq->where(function ($inner) use ($pattern, $escape): void {
                    $inner->whereRaw("name LIKE ? ESCAPE '{$escape}'", [$pattern])
                        ->orWhereRaw("email LIKE ? ESCAPE '{$escape}'", [$pattern]);
                });
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

        $instructors = User::query()
            ->where('role', User::ROLE_INSTRUCTOR)
            ->whereHas('taughtLessons', fn ($q) => $q->where('capacity', 1))
            ->orderBy('name')
            ->get(['id', 'name']);
        $lessons = Lesson::query()->where('capacity', 1)->orderBy('name')->get(['id', 'name']);

        return view('admin.personal-reservations.index', compact('reservations', 'filters', 'instructors', 'lessons'));
    }
}
