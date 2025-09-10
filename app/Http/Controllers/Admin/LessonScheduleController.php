<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkStoreLessonSchedulesRequest;
use App\Http\Requests\Admin\GenerateRecurringLessonSchedulesRequest;
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

    public function bulkCreate(): View
    {
        $lessons = Lesson::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.lesson_schedules.bulk-create', compact('lessons'));
    }

    public function bulkStore(BulkStoreLessonSchedulesRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $lessonId = $validated['lesson_id'];
        $items = $validated['items'];

        $payloads = [];
        foreach ($items as $row) {
            $payloads[] = [
                'lesson_id' => $lessonId,
                'start_datetime' => $row['start_datetime'],
                'end_datetime' => $row['end_datetime'],
                'current_bookings' => 0,
                'is_active' => $row['is_active'] ?? true,
            ];
        }

        LessonSchedule::query()->insert($payloads);

        return redirect()->route('admin.lesson-schedules.index')->with('status', 'スケジュールを一括作成しました');
    }

    public function bulkGenerate(GenerateRecurringLessonSchedulesRequest $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validated();
        $startDate = \Illuminate\Support\Carbon::parse($validated['start_date'])->startOfDay();
        $endDate = \Illuminate\Support\Carbon::parse($validated['end_date'])->startOfDay();
        $startTime = $validated['start_time'];
        $endTime = $validated['end_time'];
        $intervalWeeks = (int) ($validated['interval_weeks'] ?? 1);
        $weekdays = (array) $validated['weekdays']; // 0 (Sun) ... 6 (Sat)

        $items = [];
        foreach ($weekdays as $weekday) {
            $cursor = $startDate->copy();
            // advance to first matching weekday
            while ($cursor->dayOfWeek !== (int) $weekday) {
                $cursor->addDay();
                if ($cursor->gt($endDate)) {
                    continue 2;
                }
            }

            // collect dates by interval weeks
            for ($date = $cursor->copy(); $date->lte($endDate); $date->addWeeks($intervalWeeks)) {
                $start = \Illuminate\Support\Carbon::parse($date->format('Y-m-d').' '.$startTime);
                $end = \Illuminate\Support\Carbon::parse($date->format('Y-m-d').' '.$endTime);
                $items[] = [
                    'start_datetime' => $start->format('Y-m-d H:i:s'),
                    'end_datetime' => $end->format('Y-m-d H:i:s'),
                ];
            }
        }

        // sort by start_datetime asc
        usort($items, static function ($a, $b) {
            return strcmp($a['start_datetime'], $b['start_datetime']);
        });

        return response()->json([
            'items' => $items,
            'count' => count($items),
        ]);
    }
}
