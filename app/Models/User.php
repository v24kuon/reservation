<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property string $role
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_USER = 'user';

    public const ROLE_INSTRUCTOR = 'instructor';

    public const ROLE_ADMIN = 'admin';

    public const PRIVILEGED = [self::ROLE_ADMIN, self::ROLE_INSTRUCTOR];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Determine if the user has one of the privileged roles.
     */
    public function hasPrivilegedRole(): bool
    {
        return $this->hasRole(...self::PRIVILEGED);
    }

    /**
     * Determine if the user has any of the provided roles.
     */
    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    /**
     * Get the lessons taught by this instructor.
     */
    public function taughtLessons(): HasMany
    {
        return $this->hasMany(Lesson::class, 'instructor_user_id');
    }

    /**
     * Get the user subscriptions for this user.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    /**
     * Get the reservations for this user.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Get the favorites for this user.
     */
    public function favorites(): MorphMany
    {
        return $this->morphMany(UserFavorite::class, 'user');
    }

    /**
     * Check if the user is an instructor.
     */
    public function isInstructor(): bool
    {
        return $this->hasRole(self::ROLE_INSTRUCTOR);
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    /**
     * Get the active subscriptions for this user.
     */
    public function activeSubscriptions()
    {
        return $this->subscriptions()->active()->paid();
    }
}
