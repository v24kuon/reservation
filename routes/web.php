<?php

use App\Http\Controllers\Admin\InstructorController;
use App\Http\Controllers\Admin\LessonCategoryController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\LessonScheduleController;
use App\Http\Controllers\Admin\NotificationTemplateController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ReservationController as AdminReservationController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StoreController;
use App\Http\Controllers\Admin\SubscriptionPlanController;
use App\Http\Controllers\Admin\SubscriptionController as AdminUserSubscriptionController;
use App\Http\Controllers\Admin\GroupReservationController;
use App\Http\Controllers\Admin\PersonalReservationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstructorProfileController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\SubscriptionController;
use App\Models\LessonSchedule;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->middleware(['auth', 'verified'])->name('home');

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
    // Subscription checkout (auth required)
    Route::get('/subscription/checkout/{plan}', [SubscriptionController::class, 'createCheckoutSession'])
        ->name('subscription.checkout');
    Route::get('/subscription/success', [SubscriptionController::class, 'success'])
        ->name('subscription.success');
    Route::get('/subscription/cancel', [SubscriptionController::class, 'cancel'])
        ->name('subscription.cancel');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Reservation pages (placeholder views)
    Route::view('/reservations/group', 'reservations.group')->name('reservations.group');
    Route::view('/reservations/personal', 'reservations.personal')->name('reservations.personal');

    // User reservation actions
    Route::post('/lesson-schedules/{lessonSchedule}/reservations', [ReservationController::class, 'store'])
        ->middleware('throttle:20,1')
        ->name('reservations.store');
    Route::delete('/reservations/{reservation}', [ReservationController::class, 'destroy'])
        ->middleware('throttle:30,1')
        ->name('reservations.destroy');

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

        // Admin: reservations CRUD
        Route::resource('reservations', AdminReservationController::class);

        // Admin: group lesson reservations index
        Route::resource('group-reservations', GroupReservationController::class)->only(['index']);

        // Admin: personal lesson reservations index
        Route::resource('personal-reservations', PersonalReservationController::class)->only(['index']);

        // Admin: notification templates CRUD
        Route::resource('notification-templates', NotificationTemplateController::class);

        // Admin: users CRUD
        Route::resource('users', AdminUserController::class);

        // Admin: subscription plans CRUD
        Route::resource('subscription-plans', SubscriptionPlanController::class);
        // Ajax: lookup Stripe price amount
        Route::post('subscription-plans/price-lookup', [SubscriptionPlanController::class, 'priceLookup'])
            ->name('subscription-plans.price-lookup')
            ->middleware('throttle:30,1');

        // Admin: subscriptions CRUD
        Route::resource('subscriptions', AdminUserSubscriptionController::class);

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
