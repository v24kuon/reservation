<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLessonScheduleRequest;
use App\Http\Requests\UpdateLessonScheduleRequest;
use App\Models\Lesson;
use App\Models\LessonSchedule;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class LessonScheduleController extends Controller
{
    public function index(): View
    {
        $schedules = LessonSchedule::query()
            ->with(['lesson.store', 'lesson.category', 'lesson.instructor'])
            ->latest('start_datetime')
            ->paginate(15);

        return view('admin.lesson_schedules.index', compact('schedules'));
    }

    public function create(): View
    {
        $lessons = Lesson::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.lesson_schedules.create', compact('lessons'));
    }

    public function store(StoreLessonScheduleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        LessonSchedule::query()->create($data);

        return redirect()->route('admin.lesson-schedules.index')->with('status', 'スケジュールを作成しました');
    }

    public function show(LessonSchedule $lesson_schedule): View
    {
        $lesson_schedule->load(['lesson.store', 'lesson.category', 'lesson.instructor']);

        return view('admin.lesson_schedules.show', ['schedule' => $lesson_schedule]);
    }

    public function edit(LessonSchedule $lesson_schedule): View
    {
        $lessons = Lesson::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.lesson_schedules.edit', [
            'schedule' => $lesson_schedule,
            'lessons' => $lessons,
        ]);
    }

    public function update(UpdateLessonScheduleRequest $request, LessonSchedule $lesson_schedule): RedirectResponse
    {
        $data = $request->validated();
        $lesson_schedule->update($data);

        return redirect()->route('admin.lesson-schedules.index')->with('status', 'スケジュールを更新しました');
    }

    public function destroy(LessonSchedule $lesson_schedule): RedirectResponse
    {
        if ($lesson_schedule->reservations()->exists()) {
            return redirect()->route('admin.lesson-schedules.index')
                ->withErrors('予約が存在するため削除できません');
        }
        $lesson_schedule->delete();

        return redirect()->route('admin.lesson-schedules.index')->with('status', 'スケジュールを削除しました');
    }
}
