<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'name',
        'category_id',
        'instructor_user_id',
        'duration',
        'capacity',
        'booking_deadline_hours',
        'cancel_deadline_hours',
        'is_active',
    ];

    protected $casts = [
        'duration' => 'integer',
        'capacity' => 'integer',
        'booking_deadline_hours' => 'integer',
        'cancel_deadline_hours' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the store that owns the lesson.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the category that owns the lesson.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(LessonCategory::class, 'category_id');
    }

    /**
     * Get the instructor for this lesson.
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_user_id');
    }

    /**
     * Get the schedules for this lesson.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(LessonSchedule::class);
    }

    /**
     * Scope a query to only include active lessons.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include lessons by a specific instructor.
     */
    public function scopeByInstructor($query, int $instructorId)
    {
        return $query->where('instructor_user_id', $instructorId);
    }

    /**
     * Scope a query to only include lessons in a specific category.
     */
    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Check if the lesson is fully booked.
     */
    public function isFullyBooked(): bool
    {
        return $this->schedules()->sum('current_bookings') >= $this->capacity;
    }

    /**
     * Get the formatted duration.
     */
    public function getFormattedDurationAttribute(): string
    {
        return $this->duration . '分';
    }
}
