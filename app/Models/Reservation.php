<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lesson_schedule_id',
        'user_subscription_id',
        'status',
        'reserved_at',
    ];

    protected $casts = [
        'reserved_at' => 'datetime',
    ];

    /**
     * Get the user that owns the reservation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the lesson schedule that owns the reservation.
     */
    public function lessonSchedule(): BelongsTo
    {
        return $this->belongsTo(LessonSchedule::class);
    }

    /**
     * Get the user subscription that owns the reservation.
     */
    public function userSubscription(): BelongsTo
    {
        return $this->belongsTo(UserSubscription::class);
    }

    /**
     * Scope a query to only include confirmed reservations.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope a query to only include canceled reservations.
     */
    public function scopeCanceled($query)
    {
        return $query->where('status', 'canceled');
    }

    /**
     * Scope a query to only include completed reservations.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Check if the reservation is confirmed.
     */
    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    /**
     * Check if the reservation is canceled.
     */
    public function isCanceled(): bool
    {
        return $this->status === 'canceled';
    }

    /**
     * Check if the reservation is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if the reservation can be canceled.
     */
    public function canBeCanceled(): bool
    {
        if ($this->isCanceled() || $this->isCompleted()) {
            return false;
        }

        $lesson = $this->lessonSchedule->lesson;
        $cancelDeadline = $this->lessonSchedule->start_datetime->subHours($lesson->cancel_deadline_hours);

        return now()->lt($cancelDeadline);
    }

    /**
     * Get the formatted status.
     */
    public function getFormattedStatusAttribute(): string
    {
        return match ($this->status) {
            'confirmed' => '予約済み',
            'canceled' => 'キャンセル済み',
            'completed' => '完了',
            'no_show' => '欠席',
            default => $this->status,
        };
    }
}
