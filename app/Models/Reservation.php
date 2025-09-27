<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_CANCELED = 'canceled';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_NO_SHOW = 'no_show';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_CANCELED,
        self::STATUS_COMPLETED,
        self::STATUS_NO_SHOW,
    ];

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

        $schedule = $this->lessonSchedule;
        if (! $schedule || ! $schedule->lesson) {
            // 関連欠損時は安全側にキャンセル不可
            return false;
        }
        $lesson = $schedule->lesson;
        $cancelHours = max(0, (int) ($lesson->cancel_deadline_hours ?? 0));
        $cancelDeadline = $schedule->start_datetime->copy()->subHours($cancelHours);

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
        $schedule = $this->lessonSchedule;
        if (! $schedule || ! $schedule->lesson) {
            return true; // 安全側: 関連欠損時は受付終了扱い
        }
        $lesson = $schedule->lesson;
        $bookingHours = max(0, (int) ($lesson->booking_deadline_hours ?? 0));
        $bookingDeadline = $schedule->start_datetime->copy()->subHours($bookingHours);

        return now()->gt($bookingDeadline);
    }

    /**
     * Check if the reservation can be made (subscription and slot availability).
     */
    public function canBeCreated(): bool
    {
        // Check subscription status and available lessons
        $schedule = $this->lessonSchedule;
        $lesson = $schedule?->lesson;
        if (! $this->userSubscription || ! $schedule || ! $lesson || ! $this->userSubscription->canBookLesson($lesson)) {
            return false;
        }

        // Check if booking deadline has passed
        if ($this->hasBookingDeadlinePassed()) {
            return false;
        }

        // Check slot availability (capacity check)
        return $schedule->hasAvailableSpots();
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
                ->where('status', self::STATUS_CONFIRMED)
                ->exists();

            if ($exists) {
                $errors[] = trans('reservation.errors.duplicate_active_reservation');
            }
        }

        // Use canBeCreated() to check comprehensive validation logic
        if (! $this->canBeCreated()) {
            $lesson = $this->lessonSchedule?->lesson;
            if (! $this->userSubscription || ! $lesson || ! $this->userSubscription->canBookLesson($lesson)) {
                $errors[] = trans('reservation.errors.subscription_invalid_or_no_remaining');
            }

            if ($this->hasBookingDeadlinePassed()) {
                $errors[] = trans('reservation.errors.booking_deadline_passed');
            }

            if (! $this->lessonSchedule || ! $this->lessonSchedule->hasAvailableSpots()) {
                $errors[] = trans('reservation.errors.lesson_full');
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
                    return ['success' => false, 'errors' => [trans('reservation.errors.target_not_found')]];
                }

                $lesson = $lockedSchedule->lesson;

                // 所有者整合（他人のサブスク悪用防止）
                if ((int) $lockedSubscription->user_id !== (int) $reservation->user_id) {
                    return ['success' => false, 'errors' => [trans('reservation.errors.subscription_owner_mismatch')]];
                }

                // ロック下での受付期限再確認
                $bookingHours = max(0, (int) ($lesson->booking_deadline_hours ?? 0));
                $bookingDeadline = $lockedSchedule->start_datetime->copy()->subHours($bookingHours);
                if (now()->gt($bookingDeadline)) {
                    return ['success' => false, 'errors' => [trans('reservation.errors.booking_deadline_passed')]];
                }

                // Re-validate under locks
                if (! $lockedSchedule->hasAvailableSpots()) {
                    return ['success' => false, 'errors' => [trans('reservation.errors.lesson_full')]];
                }
                if (! $lockedSubscription->canBookLesson($lesson)) {
                    return ['success' => false, 'errors' => [trans('reservation.errors.no_remaining_lessons')]];
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
                return ['success' => false, 'errors' => [trans('reservation.errors.duplicate_active_reservation')]];
            }
            report($e);

            return ['success' => false, 'errors' => [trans('reservation.errors.generic_failure')]];
        } catch (\Throwable $e) {
            report($e);

            return ['success' => false, 'errors' => [trans('reservation.errors.generic_failure')]];
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
                    return ['success' => false, 'error' => trans('reservation.errors.cancel_deadline_passed')];
                }

                // Update status (avoid mass-assignment; status is not fillable)
                $this->status = self::STATUS_CANCELED;
                $this->save();

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
                    \App\Models\LessonSchedule::query()
                        ->whereKey($lockedSchedule->getKey())
                        ->where('current_bookings', '>', 0)
                        ->decrement('current_bookings');
                }
                if ($lockedSubscription) {
                    $rawRemaining = $lockedSubscription->getRawOriginal('remaining_lessons');
                    if ($rawRemaining !== null) {
                        // 上限超過防止（可能なら plan->lesson_count を参照）
                        $lockedSubscription->increment('remaining_lessons');
                        // 例: 上限キャップ（疑似コード）
                        // $cap = optional($lockedSubscription->plan)->lesson_count;
                        // if ($cap) {
                        //     $lockedSubscription->refresh();
                        //     if ($lockedSubscription->remaining_lessons > $cap) {
                        //         $lockedSubscription->update(['remaining_lessons' => $cap]);
                        //     }
                        // }
                    } else {
                        // 下限0の保証（重複キャンセル等の防御）
                        \App\Models\UserSubscription::query()
                            ->whereKey($lockedSubscription->getKey())
                            ->where('current_month_used_count', '>', 0)
                            ->decrement('current_month_used_count');
                    }
                }

                // Sync any DB defaults
                $this->refresh();

                return ['success' => true];
            });
        } catch (\Throwable $e) {
            report($e);

            return ['success' => false, 'error' => trans('reservation.errors.cancel_generic_failure')];
        }
    }
}
