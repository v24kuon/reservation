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
    /**
     * Detects unique constraint violations for the (lesson_id, start_datetime) pair across drivers.
     */
    private static function isScheduleUniqueViolation(\Illuminate\Database\QueryException $e): bool
    {
        $sqlState = (string) ($e->errorInfo[0] ?? '');
        $driverCode = (string) ($e->errorInfo[1] ?? ''); // MySQL: 1062, SQLite: 19
        $msg = (string) ($e->errorInfo[2] ?? '');

        $hasIndexName = str_contains($msg, 'lesson_schedules_lesson_start_unique')
            || (str_contains($msg, 'lesson_schedules') && str_contains($msg, 'lesson_id') && str_contains($msg, 'start_datetime'));

        // MySQL
        if ($sqlState === '23000' && $driverCode === '1062' && $hasIndexName) {
            return true;
        }
        // SQLite
        if ($sqlState === '23000' && $driverCode === '19' && str_contains($msg, 'UNIQUE constraint failed') && $hasIndexName) {
            return true;
        }
        // PostgreSQL (unique_violation)
        if ($sqlState === '23505' && $hasIndexName) {
            return true;
        }

        return false;
    }

    public function index(IndexLessonSchedulesRequest $request): View
    {
        $query = LessonSchedule::query()
            ->with(['lesson.store', 'lesson.category', 'lesson.instructor']);

        $validated = $request->validated();

        $from = ! empty($validated['date_from'])
            ? \Illuminate\Support\Carbon::parse($validated['date_from'])->startOfDay()
            : null;
        $to = ! empty($validated['date_to'])
            ? \Illuminate\Support\Carbon::parse($validated['date_to'])->endOfDay()
            : null;
        if ($from && $to) {
            $query->overlapping($from, $to);
        } elseif ($from) {
            // from に少しでもかかるもの
            $query->where('end_datetime', '>', $from);
        } elseif ($to) {
            // to に少しでもかかるもの
            $query->where('start_datetime', '<', $to);
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
                \App\Models\Lesson::query()
                    ->whereKey($data['lesson_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                LessonSchedule::query()->create($data);
            });
        } catch (\Illuminate\Database\QueryException $e) {
            if (self::isScheduleUniqueViolation($e)) {
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
                $lessonId = $data['lesson_id'] ?? $lesson_schedule->lesson_id;

                \App\Models\Lesson::query()
                    ->whereKey($lessonId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $lesson_schedule->update($data);
            });
        } catch (\Illuminate\Database\QueryException $e) {
            if (self::isScheduleUniqueViolation($e)) {
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
                \App\Models\Lesson::query()
                    ->whereKey($lessonId)
                    ->lockForUpdate()
                    ->firstOrFail();

                // insert in chunks to avoid size limits
                foreach (array_chunk($payloads, 500) as $chunk) {
                    LessonSchedule::query()->insert($chunk);
                }
            });
        } catch (\Illuminate\Database\QueryException $e) {
            if (self::isScheduleUniqueViolation($e)) {
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

        // Early validation: endTime must be after startTime
        $startProbe = $startDate->copy()->setTimeFromTimeString($startTime);
        $endProbe = $startDate->copy()->setTimeFromTimeString($endTime);
        if ($endProbe->lte($startProbe)) {
            return response()->json(['message' => '終了時刻は開始時刻より後である必要があります。'], 422);
        }

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
                    // Return local-like ISO without timezone to avoid browser TZ shifting
                    // Example: 2025-01-05T10:00:00
                    'start_datetime' => $start->format('Y-m-d\TH:i:s'),
                    'end_datetime' => $end->format('Y-m-d\TH:i:s'),
                ];
                if (count($items) > $limit) {
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
