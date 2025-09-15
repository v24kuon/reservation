<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    private const GUARDED_ABILITIES = ['delete', 'forceDelete'];

    /**
     * Admin は（delete/forceDelete を除き）全権限を許可
     */
    public function before(User $user, string $ability): ?bool
    {
        // For destructive actions, defer to dedicated guards below
        if (in_array($ability, self::GUARDED_ABILITIES, true)) {
            return null;
        }
        if ($user->hasRole(User::ROLE_ADMIN)) {
            return true;
        }

        return null; // 他メソッドへフォールバック
    }

    /**
     * Determine whether the user can view any models.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        return $this->canDeleteUser($user, $model);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $this->canDeleteUser($user, $model);
    }

    /**
     * Shared guard used by delete and forceDelete.
     */
    protected function canDeleteUser(User $actor, User $target): bool
    {
        // 自己削除禁止
        if ($actor->id === $target->id) {
            return false;
        }

        // 最後の管理者削除禁止
        if ($target->hasRole(User::ROLE_ADMIN)) {
            $otherAdminExists = User::query()
                ->where('role', User::ROLE_ADMIN)
                ->whereKeyNot($target->getKey())
                ->exists();
            if (! $otherAdminExists) {
                return false;
            }
        }

        // 管理者のみが削除可能
        return $actor->hasRole(User::ROLE_ADMIN);
    }
}
