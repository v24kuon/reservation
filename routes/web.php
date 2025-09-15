<?php

use App\Http\Controllers\Admin\InstructorController;
use App\Http\Controllers\Admin\LessonCategoryController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\LessonScheduleController;
use App\Http\Controllers\Admin\NotificationTemplateController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StoreController;
use App\Http\Controllers\InstructorProfileController;
use App\Http\Controllers\ProfileController;
use App\Models\LessonSchedule;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->middleware(['auth', 'verified'])->name('home');

Route::get('/dashboard', function () {
    $adminStats = null;

    if (auth()->user()?->can('access-admin')) {
        $start = now()->startOfDay();
        $end = now()->copy()->addDays(6)->endOfDay(); // 7-day window (today + next 6 days)

        $schedules = LessonSchedule::query()
            ->with('lesson')
            ->active()
            ->whereBetween('start_datetime', [$start, $end])
            ->get();

        // Group by date (Y-m-d)
        $byDate = $schedules->groupBy(fn ($s) => $s->start_datetime->toDateString());

        $daily = [];
        $totalCapacity = 0;
        $totalBooked = 0;
        for ($i = 0; $i < 7; $i++) {
            $day = $start->copy()->addDays($i);
            $key = $day->toDateString();

            $dayCapacity = 0;
            $dayBooked = 0;
            foreach ($byDate->get($key, collect()) as $schedule) {
                $capacity = (int) ($schedule->lesson?->capacity ?? 0);
                $dayCapacity += $capacity;
                $dayBooked += (int) $schedule->current_bookings;
            }

            $daily[] = [
                'date' => $key,
                'label' => $day->format('n/j'),
                'capacity' => $dayCapacity,
                'booked' => $dayBooked,
            ];

            $totalCapacity += $dayCapacity;
            $totalBooked += $dayBooked;
        }

        $todayKey = $start->toDateString();
        $today = collect($daily)->firstWhere('date', $todayKey) ?? ['capacity' => 0, 'booked' => 0];

        $adminStats = [
            'totalCapacity' => $totalCapacity,
            'totalBooked' => $totalBooked,
            'utilization' => $totalCapacity > 0 ? round(($totalBooked / $totalCapacity) * 100) : 0,
            'today' => $today,
            'daily' => $daily,
            'schedulesCount' => $schedules->count(),
        ];
    }

    return view('dashboard', [
        'adminStats' => $adminStats,
    ]);
})->middleware(['auth', 'verified', 'can:access-dashboard'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Instructor self profile
    Route::get('/instructor/profile', [InstructorProfileController::class, 'editSelf'])
        ->middleware('can:access-instructor')
        ->name('instructor.profile.edit');
    Route::put('/instructor/profile', [InstructorProfileController::class, 'updateSelf'])
        ->middleware('can:access-instructor')
        ->name('instructor.profile.update');

    // Admin: stores CRUD
    Route::prefix('admin')->as('admin.')->middleware('can:access-admin')->group(function () {
        Route::resource('stores', StoreController::class);

        // Admin: lesson categories CRUD
        Route::resource('lesson-categories', LessonCategoryController::class);

        // Admin: lessons CRUD
        Route::resource('lessons', LessonController::class);

        // Admin: lesson schedules bulk create
        Route::get('lesson-schedules/bulk/create', [LessonScheduleController::class, 'bulkCreate'])
            ->name('lesson-schedules.bulk.create');
        Route::post('lesson-schedules/bulk', [LessonScheduleController::class, 'bulkStore'])
            ->name('lesson-schedules.bulk.store');

        // Admin: lesson schedules recurrence generator (server-side)
        Route::post('lesson-schedules/bulk/generate', [LessonScheduleController::class, 'bulkGenerate'])
            ->name('lesson-schedules.bulk.generate');

        // Admin: lesson schedules CRUD
        Route::resource('lesson-schedules', LessonScheduleController::class);

        // Admin: notification templates CRUD
        Route::resource('notification-templates', NotificationTemplateController::class);

        // Admin: system settings
        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::patch('settings', [SettingController::class, 'update'])->name('settings.update');

        // Admin: instructors CRUD (create by admin only)
        Route::resource('instructors', InstructorController::class)
            ->parameters([
                'instructors' => 'instructor',
            ]);
    });
});

require __DIR__.'/auth.php';
