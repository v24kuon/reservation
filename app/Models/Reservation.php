<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_CANCELED = 'canceled';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_NO_SHOW = 'no_show';

    protected $fillable = [
        'user_id',
        'lesson_schedule_id',
        'user_subscription_id',
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
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    /**
     * Scope a query to only include canceled reservations.
     */
    public function scopeCanceled($query)
    {
        return $query->where('status', self::STATUS_CANCELED);
    }

    /**
     * Scope a query to only include completed reservations.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Check if the reservation is confirmed.
     */
    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    /**
     * Check if the reservation is canceled.
     */
    public function isCanceled(): bool
    {
        return $this->status === self::STATUS_CANCELED;
    }

    /**
     * Check if the reservation is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
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
        $cancelHours = (int) ($lesson->cancel_deadline_hours ?? 0);
        $cancelDeadline = $this->lessonSchedule->start_datetime->copy()->subHours($cancelHours);

        return now()->lte($cancelDeadline);
    }

    /**
     * Get the formatted status.
     */
    public function getFormattedStatusAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_CONFIRMED => '予約済み',
            self::STATUS_CANCELED => 'キャンセル済み',
            self::STATUS_COMPLETED => '完了',
            self::STATUS_NO_SHOW => '欠席',
            default => $this->status,
        };
    }

    /**
     * Check if booking deadline has passed.
     */
    public function hasBookingDeadlinePassed(): bool
    {
        $lesson = $this->lessonSchedule->lesson;
        $bookingHours = (int) ($lesson->booking_deadline_hours ?? 0);
        $bookingDeadline = $this->lessonSchedule->start_datetime->copy()->subHours($bookingHours);

        return now()->gt($bookingDeadline);
    }

    /**
     * Check if the reservation can be made (subscription and slot availability).
     */
    public function canBeCreated(): bool
    {
        // Check subscription status and available lessons
        $lesson = $this->lessonSchedule?->lesson;
        if (! $this->userSubscription || ! $lesson || ! $this->userSubscription->canBookLesson($lesson)) {
            return false;
        }

        // Check if booking deadline has passed
        if ($this->hasBookingDeadlinePassed()) {
            return false;
        }

        // Check slot availability (capacity check)
        return $this->lessonSchedule->hasAvailableSpots();
    }

    /**
     * Validate reservation constraints before creation.
     */
    public function validateReservationConstraints(): array
    {
        $errors = [];

        // Check duplicate active reservation for the same schedule (UX-friendly pre-check)
        if ($this->user_id && $this->lesson_schedule_id) {
            $exists = self::query()
                ->where('user_id', $this->user_id)
                ->where('lesson_schedule_id', $this->lesson_schedule_id)
                ->whereIn('status', [self::STATUS_CONFIRMED])
                ->exists();

            if ($exists) {
                $errors[] = '同じレッスン枠に既に予約があります。';
            }
        }

        // Use canBeCreated() to check comprehensive validation logic
        if (! $this->canBeCreated()) {
            $lesson = $this->lessonSchedule?->lesson;
            if (! $this->userSubscription || ! $lesson || ! $this->userSubscription->canBookLesson($lesson)) {
                $errors[] = 'サブスクリプションが無効であるか、利用可能なレッスン回数がありません。';
            }

            if ($this->hasBookingDeadlinePassed()) {
                $errors[] = '予約受付期限を過ぎています。';
            }

            if (! $this->lessonSchedule->hasAvailableSpots()) {
                $errors[] = 'このレッスンは満員です。';
            }
        }

        return $errors;
    }

    /**
     * Create a reservation with proper validation and slot management.
     */
    public static function createWithValidation(array $attributes): array
    {
        try {
            return \Illuminate\Support\Facades\DB::transaction(function () use ($attributes) {
                $reservation = new self($attributes);

                // Early validation (non-locking, for UX). Will be rechecked under locks.
                $errors = $reservation->validateReservationConstraints();
                if (! empty($errors)) {
                    return ['success' => false, 'errors' => $errors];
                }

                // Acquire locks on related rows to prevent race conditions
                /** @var \App\Models\LessonSchedule|null $lockedSchedule */
                $lockedSchedule = \App\Models\LessonSchedule::query()
                    ->whereKey($reservation->lesson_schedule_id)
                    ->lockForUpdate()
                    ->first();

                /** @var \App\Models\UserSubscription|null $lockedSubscription */
                $lockedSubscription = \App\Models\UserSubscription::query()
                    ->whereKey($reservation->user_subscription_id)
                    ->lockForUpdate()
                    ->first();

                if (! $lockedSchedule || ! $lockedSubscription) {
                    return ['success' => false, 'errors' => ['予約対象のデータが見つかりません。']];
                }

                $lesson = $lockedSchedule->lesson;

                // Re-validate under locks
                if (! $lockedSchedule->hasAvailableSpots()) {
                    return ['success' => false, 'errors' => ['このレッスンは満員です。']];
                }
                if (! $lockedSubscription->canBookLesson($lesson)) {
                    return ['success' => false, 'errors' => ['利用可能なレッスン回数がありません。']];
                }

                // Set safe defaults if not provided
                $reservation->status = $reservation->status ?? self::STATUS_CONFIRMED;
                $reservation->reserved_at = $reservation->reserved_at ?? now();

                // Persist reservation after successful locked checks
                $reservation->save();

                // Atomic counters update under locks
                $lockedSchedule->increment('current_bookings');

                // Maintain remaining lessons vs used count consistently
                $rawRemaining = $lockedSubscription->getRawOriginal('remaining_lessons');
                if ($rawRemaining !== null) {
                    // remaining_lessons is the source of truth when present
                    $lockedSubscription->decrement('remaining_lessons');
                } else {
                    // fallback to used count tracking
                    $lockedSubscription->increment('current_month_used_count');
                }

                // Refresh to sync defaults
                $reservation->refresh();

                return ['success' => true, 'reservation' => $reservation];
            });
        } catch (\Illuminate\Database\QueryException $e) {
            // Unique constraint violation (SQLSTATE 23000)
            if ((string) $e->getCode() === '23000') {
                return ['success' => false, 'errors' => ['同じレッスン枠に既に予約があります。']];
            }
            report($e);

            return ['success' => false, 'errors' => ['予約処理中にエラーが発生しました。時間をおいて再度お試しください。']];
        } catch (\Throwable $e) {
            report($e);

            return ['success' => false, 'errors' => ['予約処理中にエラーが発生しました。時間をおいて再度お試しください。']];
        }
    }

    /**
     * Cancel the reservation with deadline and permission checks.
     */
    public function cancelWithValidation(): array
    {
        try {
            return \Illuminate\Support\Facades\DB::transaction(function () {
                // Requirement 8.4: Check cancel deadline and permissions
                if (! $this->canBeCanceled()) {
                    return ['success' => false, 'error' => 'キャンセル期限を過ぎているため、キャンセルできません。'];
                }

                // Update status
                $this->update(['status' => self::STATUS_CANCELED]);

                // Lock related rows to avoid race conditions when returning capacity/counts
                /** @var \App\Models\LessonSchedule|null $lockedSchedule */
                $lockedSchedule = \App\Models\LessonSchedule::query()
                    ->whereKey($this->lesson_schedule_id)
                    ->lockForUpdate()
                    ->first();
                /** @var \App\Models\UserSubscription|null $lockedSubscription */
                $lockedSubscription = \App\Models\UserSubscription::query()
                    ->whereKey($this->user_subscription_id)
                    ->lockForUpdate()
                    ->first();

                if ($lockedSchedule) {
                    $lockedSchedule->decrement('current_bookings');
                }
                if ($lockedSubscription) {
                    $rawRemaining = $lockedSubscription->getRawOriginal('remaining_lessons');
                    if ($rawRemaining !== null) {
                        $lockedSubscription->increment('remaining_lessons');
                    } else {
                        $lockedSubscription->decrement('current_month_used_count');
                    }
                }

                // Sync any DB defaults
                $this->refresh();

                return ['success' => true];
            });
        } catch (\Throwable $e) {
            report($e);

            return ['success' => false, 'error' => 'キャンセル処理中にエラーが発生しました。'];
        }
    }
}
