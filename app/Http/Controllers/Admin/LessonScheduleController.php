<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkStoreLessonSchedulesRequest;
use App\Http\Requests\Admin\GenerateRecurringLessonSchedulesRequest;
use App\Http\Requests\Admin\IndexLessonSchedulesRequest;
use App\Http\Requests\StoreLessonScheduleRequest;
use App\Http\Requests\UpdateLessonScheduleRequest;
use App\Models\Lesson;
use App\Models\LessonSchedule;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class LessonScheduleController extends Controller
{
    public function index(IndexLessonSchedulesRequest $request): View
    {
        $query = LessonSchedule::query()
            ->with(['lesson.store', 'lesson.category', 'lesson.instructor']);

        $validated = $request->validated();

        if (! empty($validated['date_from'])) {
            $from = \Illuminate\Support\Carbon::parse($validated['date_from'])->startOfDay();
            $query->where('start_datetime', '>=', $from);
        }
        if (! empty($validated['date_to'])) {
            $to = \Illuminate\Support\Carbon::parse($validated['date_to'])->endOfDay();
            $query->where('end_datetime', '<=', $to);
        }
        if (! empty($validated['lesson_id'])) {
            $query->where('lesson_id', $validated['lesson_id']);
        }
        if (! empty($validated['instructor_user_id'])) {
            $query->whereHas('lesson', function ($q) use ($validated) {
                $q->where('instructor_user_id', $validated['instructor_user_id']);
            });
        }
        if (isset($validated['is_active'])) {
            $query->where('is_active', (bool) $validated['is_active']);
        }

        $schedules = $query->latest('start_datetime')->paginate(15)->withQueryString();

        $lessons = Lesson::query()->orderBy('name')->get(['id', 'name']);
        $instructors = \App\Models\User::query()
            ->whereIn('id', Lesson::query()
                ->whereNotNull('instructor_user_id')
                ->distinct()
                ->pluck('instructor_user_id'))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.lesson_schedules.index', compact('schedules', 'lessons', 'instructors'));
    }

    public function create(): View
    {
        $lessons = Lesson::query()->orderBy('name')->get(['id', 'name', 'duration']);

        return view('admin.lesson_schedules.create', compact('lessons'));
    }

    public function store(StoreLessonScheduleRequest $request): RedirectResponse
    {
        $data = $request->validated();

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
                // Optional: lock the parent lesson row to serialize concurrent inserts for same lesson
                \App\Models\Lesson::query()->where('id', $data['lesson_id'])->lockForUpdate()->first();

                LessonSchedule::query()->create($data);
            });
        } catch (\Illuminate\Database\QueryException $e) {
            // 23000 is SQLSTATE for integrity constraint violation across many drivers
            if ((string) ($e->errorInfo[0] ?? '') === '23000') {
                return back()->withErrors('同一レッスンの同時刻スケジュールが既に存在します。')->withInput();
            }
            throw $e;
        }

        return redirect()->route('admin.lesson-schedules.index')->with('status', 'スケジュールを作成しました');
    }

    public function show(LessonSchedule $lesson_schedule): View
    {
        $lesson_schedule->load(['lesson.store', 'lesson.category', 'lesson.instructor']);

        return view('admin.lesson_schedules.show', ['schedule' => $lesson_schedule]);
    }

    public function edit(LessonSchedule $lesson_schedule): View
    {
        $lessons = Lesson::query()->orderBy('name')->get(['id', 'name', 'duration']);

        return view('admin.lesson_schedules.edit', [
            'schedule' => $lesson_schedule,
            'lessons' => $lessons,
        ]);
    }

    public function update(UpdateLessonScheduleRequest $request, LessonSchedule $lesson_schedule): RedirectResponse
    {
        $data = $request->validated();

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($lesson_schedule, $data) {
                \App\Models\Lesson::query()->where('id', $data['lesson_id'])->lockForUpdate()->first();

                $lesson_schedule->update($data);
            });
        } catch (\Illuminate\Database\QueryException $e) {
            if ((string) ($e->errorInfo[0] ?? '') === '23000') {
                return back()->withErrors('同一レッスンの同時刻スケジュールが既に存在します。')->withInput();
            }
            throw $e;
        }

        return redirect()->route('admin.lesson-schedules.index')->with('status', 'スケジュールを更新しました');
    }

    public function destroy(LessonSchedule $lesson_schedule): RedirectResponse
    {
        if ($lesson_schedule->reservations()->exists()) {
            return redirect()->route('admin.lesson-schedules.index')
                ->withErrors(['error' => '予約が存在するため削除できません']);
        }
        $lesson_schedule->delete();

        return redirect()->route('admin.lesson-schedules.index')->with('status', 'スケジュールを削除しました');
    }

    public function bulkCreate(): View
    {
        $lessons = Lesson::query()->orderBy('name')->get(['id', 'name', 'duration']);

        return view('admin.lesson_schedules.bulk-create', compact('lessons'));
    }

    public function bulkStore(BulkStoreLessonSchedulesRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $lessonId = $validated['lesson_id'];
        $items = $validated['items'];

        $now = now();
        $payloads = [];
        foreach ($items as $row) {
            $payloads[] = [
                'lesson_id' => $lessonId,
                'start_datetime' => $row['start_datetime'],
                'end_datetime' => $row['end_datetime'],
                'current_bookings' => 0,
                'is_active' => isset($row['is_active']) ? (int) (bool) $row['is_active'] : 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($lessonId, $payloads) {
                \App\Models\Lesson::query()->where('id', $lessonId)->lockForUpdate()->first();

                // insert in chunks to avoid size limits
                foreach (array_chunk($payloads, 500) as $chunk) {
                    LessonSchedule::query()->insert($chunk);
                }
            });
        } catch (\Illuminate\Database\QueryException $e) {
            if ((string) ($e->errorInfo[0] ?? '') === '23000') {
                return back()->withErrors('一部または全てのスケジュールが重複しています。対象の時間帯を見直してください。')->withInput();
            }
            throw $e;
        }

        return redirect()->route('admin.lesson-schedules.index')->with('status', 'スケジュールを一括作成しました');
    }

    public function bulkGenerate(GenerateRecurringLessonSchedulesRequest $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validated();
        $startDate = \Illuminate\Support\Carbon::parse($validated['start_date'])->startOfDay();
        $endDate = \Illuminate\Support\Carbon::parse($validated['end_date'])->startOfDay();
        $startTime = $validated['start_time'];
        $endTime = $validated['end_time'];
        $intervalWeeks = max(1, (int) ($validated['interval_weeks'] ?? 1));
        $weekdays = (array) $validated['weekdays']; // 0 (Sun) ... 6 (Sat)

        $items = [];
        $limit = 1000;
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
                $start = $date->copy()->setTimeFromTimeString($startTime);
                $end = $date->copy()->setTimeFromTimeString($endTime);
                // Guard against invalid intervals (no overnight support here)
                if ($end->lte($start)) {
                    return response()->json(['message' => '終了時刻は開始時刻より後である必要があります。'], 422);
                }
                $items[] = [
                    // Return ISO-8601 with offset to prevent TZ drift on clients
                    'start_datetime' => $start->toIso8601String(),
                    'end_datetime' => $end->toIso8601String(),
                ];
                if (count($items) >= $limit) {
                    return response()->json(['message' => "生成件数が多すぎます（上限: {$limit}件）。期間や曜日を見直してください。"], 422);
                }
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
