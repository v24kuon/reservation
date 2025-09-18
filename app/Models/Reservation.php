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

    /**
     * Check if booking deadline has passed.
     */
    public function hasBookingDeadlinePassed(): bool
    {
        $lesson = $this->lessonSchedule->lesson;
        $bookingDeadline = $this->lessonSchedule->start_datetime->subHours($lesson->booking_deadline_hours);

        return now()->gt($bookingDeadline);
    }

    /**
     * Check if the reservation can be made (subscription and slot availability).
     */
    public function canBeCreated(): bool
    {
        // Check subscription status and available lessons
        if (! $this->userSubscription || ! $this->userSubscription->canBookLesson()) {
            return false;
        }

        // Check if booking deadline has passed
        if ($this->hasBookingDeadlinePassed()) {
            return false;
        }

        // Check slot availability (capacity check)
        return $this->lessonSchedule->hasAvailableSlots();
    }

    /**
     * Validate reservation constraints before creation.
     */
    public function validateReservationConstraints(): array
    {
        $errors = [];

        // Requirement 8.1: Check subscription status and available slots
        if (! $this->userSubscription || ! $this->userSubscription->canBookLesson()) {
            $errors[] = 'サブスクリプションが無効であるか、利用可能なレッスン回数がありません。';
        }

        // Requirement 8.2: Check booking deadline
        if ($this->hasBookingDeadlinePassed()) {
            $errors[] = '予約受付期限を過ぎています。';
        }

        // Requirement 8.1: Check capacity
        if (! $this->lessonSchedule->hasAvailableSlots()) {
            $errors[] = 'このレッスンは満員です。';
        }

        return $errors;
    }

    /**
     * Create a reservation with proper validation and slot management.
     */
    public static function createWithValidation(array $attributes): array
    {
        $reservation = new self($attributes);

        // Validate constraints
        $errors = $reservation->validateReservationConstraints();
        if (! empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Save reservation and update counts
        $reservation->save();

        // Requirement 8.1: Decrement available slots
        $reservation->lessonSchedule->incrementCurrentBookings();

        // Update user subscription lesson count
        $reservation->userSubscription->incrementUsedCount();

        return ['success' => true, 'reservation' => $reservation];
    }

    /**
     * Cancel the reservation with deadline and permission checks.
     */
    public function cancelWithValidation(): array
    {
        // Requirement 8.4: Check cancel deadline and permissions
        if (! $this->canBeCanceled()) {
            return ['success' => false, 'error' => 'キャンセル期限を過ぎているため、キャンセルできません。'];
        }

        // Update status
        $this->update(['status' => 'canceled']);

        // Decrement current bookings
        $this->lessonSchedule->decrementCurrentBookings();

        // Return lesson count if within deadline
        $this->userSubscription->decrementUsedCount();

        return ['success' => true];
    }
}
