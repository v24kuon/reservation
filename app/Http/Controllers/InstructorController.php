<?php

namespace App\Http\Controllers;

use App\Models\LessonSchedule;
use App\Models\User;
use Illuminate\View\View;

class InstructorController extends Controller
{
    public function index(): View
    {
        $instructors = User::query()
            ->where('role', User::ROLE_INSTRUCTOR)
            ->with('instructorProfile')
            ->orderBy('name')
            ->paginate(config('pagination.instructors', 12));

        return view('instructors.index', compact('instructors'));
    }

    public function show(User $instructor): View
    {
        abort_unless($instructor->role === User::ROLE_INSTRUCTOR, 404);

        $upcomingSchedules = LessonSchedule::query()
            ->with(['lesson.store'])
            ->whereHas('lesson', fn ($q) => $q->where('instructor_user_id', $instructor->id))
            ->where('start_datetime', '>=', now())
            ->orderBy('start_datetime')
            ->limit(config('pagination.instructor_upcoming_schedules', 10))
            ->get();

        return view('instructors.show', compact('instructor', 'upcomingSchedules'));
    }
}
