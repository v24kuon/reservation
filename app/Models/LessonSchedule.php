<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class LessonSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'start_datetime',
        'end_datetime',
        'current_bookings',
        'is_active',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'current_bookings' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the lesson that owns the schedule.
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Get the reservations for this schedule.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Scope a query to only include active schedules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include future schedules.
     */
    public function scopeFuture($query)
    {
        return $query->where('start_datetime', '>', now());
    }

    /**
     * Scope a query to only include schedules on a specific date.
     */
    public function scopeOnDate($query, string $date)
    {
        return $query->whereDate('start_datetime', $date);
    }

    /**
     * Check if the schedule is fully booked.
     */
    public function isFullyBooked(): bool
    {
        return $this->current_bookings >= $this->lesson->capacity;
    }

    /**
     * Check if the schedule has available spots.
     */
    public function hasAvailableSpots(): bool
    {
        return ! $this->isFullyBooked();
    }

    /**
     * Get the available spots count.
     */
    public function getAvailableSpotsAttribute(): int
    {
        return max(0, $this->lesson->capacity - $this->current_bookings);
    }

    /**
     * Get the formatted start time.
     */
    public function getFormattedStartTimeAttribute(): string
    {
        return $this->start_datetime->format('H:i');
    }

    /**
     * Get the formatted end time.
     */
    public function getFormattedEndTimeAttribute(): string
    {
        return $this->end_datetime->format('H:i');
    }

    /**
     * Scope: schedules that overlap the given [start, end) interval.
     */
    public function scopeOverlapping(Builder $query, \DateTimeInterface $start, \DateTimeInterface $end): Builder
    {
        return $query
            ->where('start_datetime', '<', $end)
            ->where('end_datetime', '>', $start);
    }

    /**
     * Determine if any existing schedule for the given lesson overlaps the interval.
     */
    public static function hasOverlap(int $lessonId, \DateTimeInterface|string $start, \DateTimeInterface|string $end): bool
    {
        $startAt = $start instanceof \DateTimeInterface ? $start : Carbon::parse((string) $start);
        $endAt = $end instanceof \DateTimeInterface ? $end : Carbon::parse((string) $end);

        return static::query()
            ->where('lesson_id', $lessonId)
            ->overlapping($startAt, $endAt)
            ->exists();
    }
}
