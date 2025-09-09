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
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->middleware(['auth', 'verified'])->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
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

        // Admin: lesson schedules CRUD
        Route::resource('lesson-schedules', LessonScheduleController::class);

        // Admin: notification templates CRUD
        Route::resource('notification-templates', NotificationTemplateController::class);

        // Admin: system settings
        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::patch('settings', [SettingController::class, 'update'])->name('settings.update');

        // Admin: instructors CRUD (create by admin only)
        Route::resource('instructors', InstructorController::class)->parameters([
            'instructors' => 'instructor',
        ]);
    });
});

require __DIR__.'/auth.php';
