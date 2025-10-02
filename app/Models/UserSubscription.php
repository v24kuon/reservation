<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserSubscription extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_TRIALING = 'trialing';

    public const STATUS_CANCELED = 'canceled';

    public const STATUS_PAST_DUE = 'past_due';

    public const PAYMENT_STATUS_PAID = 'paid';

    public const PAYMENT_STATUS_UNPAID = 'unpaid';

    public const PAYMENT_STATUS_FAILED = 'failed';

    public const PAYMENT_STATUS_PENDING = 'pending';

    public const ALLOWED_STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_TRIALING,
        self::STATUS_CANCELED,
        self::STATUS_PAST_DUE,
    ];

    public const ALLOWED_PAYMENT_STATUSES = [
        self::PAYMENT_STATUS_PAID,
        self::PAYMENT_STATUS_UNPAID,
        self::PAYMENT_STATUS_FAILED,
        self::PAYMENT_STATUS_PENDING,
    ];

    protected $fillable = [
        'user_id',
        'plan_id',
        'stripe_subscription_id',
        'status',
        'payment_status',
        'failure_reason',
        'current_period_start',
        'current_period_end',
        'current_month_used_count',
        'remaining_lessons',
    ];

    protected $casts = [
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'current_month_used_count' => 'integer',
        'remaining_lessons' => 'integer',
    ];

    /**
     * Get the user that owns the subscription.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the plan that owns the subscription.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    /**
     * Get the reservations for this subscription.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Scope a query to only include active subscriptions.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include paid subscriptions.
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Scope by lesson category allowed by the plan.
     */
    public function scopeForCategory(Builder $query, int $categoryId): Builder
    {
        return $query->whereHas('plan', function (Builder $planQuery) use ($categoryId): void {
            $planQuery->whereJsonContains('allowed_category_ids', $categoryId);
        });
    }

    /**
     * Check if the subscription is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if the subscription is paid.
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Check if the subscription has remaining lessons.
     */
    public function hasRemainingLessons(): bool
    {
        return $this->getRemainingLessons() > 0;
    }

    /**
     * Get the remaining lessons count.
     */
    public function getRemainingLessonsAttribute(): int
    {
        // 永続値が存在すればそれを優先（負値は0に丸め）
        $raw = $this->getRawOriginal('remaining_lessons');
        if ($raw !== null) {
            return max(0, (int) $raw);
        }
        $limit = (int) ($this->plan?->lesson_count ?? 0);

        return max(0, $limit - (int) $this->current_month_used_count);
    }

    /**
     * Total available lessons for the current period (plan-defined quota).
     */
    public function getTotalAvailableLessons(): int
    {
        return (int) ($this->plan?->lesson_count ?? 0);
    }

    /**
     * Remaining lessons for the current period (method form).
     * Mirrors the accessor while providing an explicit method per spec.
     */
    public function getRemainingLessons(): int
    {
        // Accessorに集約
        return $this->getRemainingLessonsAttribute();
    }

    /**
     * Check if the subscription allows a specific category.
     */
    public function allowsCategory(int $categoryId): bool
    {
        return $this->plan?->allowsCategory($categoryId) ?? false;
    }

    /**
     * Alias for allowsCategory to match task specification.
     */
    public function hasCategory(int $categoryId): bool
    {
        return $this->allowsCategory($categoryId);
    }

    /**
     * Determine if the user can book the given lesson under this subscription.
     */
    public function canBookLesson(Lesson $lesson): bool
    {
        if (! $this->isActive() || ! $this->isPaid()) {
            return false;
        }

        // Period validity check
        $now = now();
        if ($this->current_period_start && $now->lt($this->current_period_start)) {
            return false;
        }
        if ($this->current_period_end && $now->gt($this->current_period_end)) {
            return false;
        }

        // Category permission
        if (! $this->hasCategory((int) $lesson->category_id)) {
            return false;
        }

        // Lesson count enforcement
        return $this->hasRemainingLessons();
    }

    /**
     * Get the formatted period.
     */
    public function getFormattedPeriodAttribute(): string
    {
        $start = $this->current_period_start?->format('Y年m月d日') ?? '-';
        $end = $this->current_period_end?->format('Y年m月d日') ?? '-';

        return "{$start} ～ {$end}";
    }

    /**
     * Human readable status label in Japanese.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => '有効',
            self::STATUS_CANCELED => 'キャンセル済み',
            self::STATUS_PAST_DUE => '支払い遅延',
            self::STATUS_TRIALING => 'トライアル中',
            default => (string) $this->status,
        };
    }
}
