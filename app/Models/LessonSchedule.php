<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
     * Return the schedule's end time formatted as `H:i` (24-hour).
     *
     * Formats the model's `end_datetime` attribute to a short time string (hours and minutes).
     *
     * @return string The formatted end time, e.g. "14:30".
     */
    public function getFormattedEndTimeAttribute(): string
    {
        return $this->end_datetime->format('H:i');
    }

    /**
     * Scope a query to schedules that overlap the given half-open interval [start, end).
     *
     * Returns schedules with start_datetime < $end and end_datetime > $start (i.e. any record that
     * intersects the provided interval).
     *
     * @param  Builder<self> $query
     * @param  \DateTimeInterface $start Start of the interval (inclusive).
     * @param  \DateTimeInterface $end   End of the interval (exclusive).
     * @return Builder<self>
     */
    public function scopeOverlapping(Builder $query, \DateTimeInterface $start, \DateTimeInterface $end): Builder
    {
        return $query
            ->where('start_datetime', '<', $end)
            ->where('end_datetime', '>', $start);
    }

    /**
     * Determine whether any schedule for the given lesson overlaps the half-open interval [start, end).
     *
     * Accepts DateTimeInterface or a date/time string. Strings are parsed with Carbon and, when the
     * timezone offset is omitted, interpreted using the application timezone configured via
     * config('app.timezone'). Supplying ISO‑8601 strings with explicit timezone is recommended.
     * Returns true if any existing LessonSchedule for the given lesson_id intersects the interval
     * (i.e. start_datetime < $end AND end_datetime > $start).
     *
     * @param  int  $lessonId  ID of the lesson to check.
     * @param  \DateTimeInterface|string  $start  Interval start (ISO‑8601 推奨。TZ 省略時は config('app.timezone') として解釈)。
     * @param  \DateTimeInterface|string  $end    Interval end   (ISO‑8601 推奨。TZ 省略時は config('app.timezone') として解釈)。
     * @return bool True if an overlapping schedule exists, false otherwise.
     */
    public static function hasOverlap(int $lessonId, \DateTimeInterface|string $start, \DateTimeInterface|string $end): bool
    {
        $startAt = $start instanceof \DateTimeInterface
            ? Carbon::instance($start)
            : Carbon::parse((string) $start, config('app.timezone'));
        $endAt = $end instanceof \DateTimeInterface
            ? Carbon::instance($end)
            : Carbon::parse((string) $end, config('app.timezone'));

        if ($endAt <= $startAt) {
            return false;
        }

        return static::query()
            ->where('lesson_id', $lessonId)
            ->overlapping($startAt, $endAt)
            ->exists();
    }
}
