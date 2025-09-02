<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'lesson_count',
        'allowed_category_ids',
        'stripe_product_id',
        'stripe_price_id',
        'description',
        'is_active',
    ];

    protected $casts = [
        'allowed_category_ids' => 'array',
        'is_active' => 'boolean',
        'price' => 'integer',
        'lesson_count' => 'integer',
    ];

    /**
     * Get the user subscriptions for this plan.
     */
    public function userSubscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class, 'plan_id');
    }

    /**
     * Check if the plan allows a specific category.
     */
    public function allowsCategory(int $categoryId): bool
    {
        return in_array($categoryId, $this->allowed_category_ids ?? [], true);
    }

    /**
     * Get the formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return '¥' . number_format($this->price);
    }
}
