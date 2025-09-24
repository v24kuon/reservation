<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GroupReservations\IndexGroupReservationsRequest;
use App\Models\Lesson;
use App\Models\Reservation;
use App\Models\Store;
use App\Models\User;
use Illuminate\Contracts\View\View;

class GroupReservationController extends Controller
{
    public function index(IndexGroupReservationsRequest $request): View
    {
        $filters = $request->validated();

        $query = Reservation::query()
            ->with(['user', 'lessonSchedule.lesson.store', 'lessonSchedule.lesson.instructor'])
            ->whereHas('lessonSchedule.lesson.store'); // 店舗を持つレッスン＝グループ扱い

        if (! empty($filters['store_id'])) {
            $storeId = (int) $filters['store_id'];
            $query->whereHas('lessonSchedule.lesson', fn ($q) => $q->where('store_id', $storeId));
        }

        if (! empty($filters['instructor_id'])) {
            $instructorId = (int) $filters['instructor_id'];
            $query->whereHas('lessonSchedule.lesson', fn ($q) => $q->where('instructor_user_id', $instructorId));
        }

        if (! empty($filters['lesson_id'])) {
            $lessonId = (int) $filters['lesson_id'];
            $query->whereHas('lessonSchedule', fn ($q) => $q->where('lesson_id', $lessonId));
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
            $query->whereDate('reserved_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('reserved_at', '<=', $filters['date_to']);
        }

        $reservations = $query->orderByDesc('reserved_at')->paginate(50)->withQueryString();

        $stores = Store::query()->orderBy('name')->get(['id', 'name']);
        $instructors = User::query()->where('role', User::ROLE_INSTRUCTOR)->orderBy('name')->get(['id', 'name']);
        $lessons = Lesson::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.group-reservations.index', compact('reservations', 'filters', 'stores', 'instructors', 'lessons'));
    }
}
