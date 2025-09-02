<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserFavorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'favoritable_type',
        'favoritable_id',
    ];

    /**
     * Get the user that owns the favorite.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the favoritable model (Store or User).
     */
    public function favoritable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include favorites for stores.
     */
    public function scopeStores($query)
    {
        return $query->where('favoritable_type', Store::class);
    }

    /**
     * Scope a query to only include favorites for instructors.
     */
    public function scopeInstructors($query)
    {
        return $query->where('favoritable_type', User::class);
    }

    /**
     * Check if the favorite is for a store.
     */
    public function isStore(): bool
    {
        return $this->favoritable_type === Store::class;
    }

    /**
     * Check if the favorite is for an instructor.
     */
    public function isInstructor(): bool
    {
        return $this->favoritable_type === User::class;
    }

    /**
     * Get the favoritable name.
     */
    public function getFavoritableNameAttribute(): string
    {
        return $this->favoritable->name ?? 'Unknown';
    }

    /**
     * Get the favoritable type in Japanese.
     */
    public function getFavoritableTypeJaAttribute(): string
    {
        return match ($this->favoritable_type) {
            Store::class => '店舗',
            User::class => 'インストラクター',
            default => 'その他',
        };
    }
}
