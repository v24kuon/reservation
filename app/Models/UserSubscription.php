<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserSubscription extends Model
{
    use HasFactory;

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
    ];

    protected $casts = [
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'current_month_used_count' => 'integer',
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
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include paid subscriptions.
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
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
        return $this->current_month_used_count < $this->plan->lesson_count;
    }

    /**
     * Get the remaining lessons count.
     */
    public function getRemainingLessonsAttribute(): int
    {
        return max(0, $this->plan->lesson_count - $this->current_month_used_count);
    }

    /**
     * Check if the subscription allows a specific category.
     */
    public function allowsCategory(int $categoryId): bool
    {
        return $this->plan->allowsCategory($categoryId);
    }

    /**
     * Get the formatted period.
     */
    public function getFormattedPeriodAttribute(): string
    {
        return $this->current_period_start->format('Y年m月d日') . ' ～ ' . $this->current_period_end->format('Y年m月d日');
    }
}
