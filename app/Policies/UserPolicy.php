<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Admin は全権限を許可
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function before(User $user, string $ability): ?bool
    {
        // For destructive actions, defer to dedicated guards below
        if (in_array($ability, ['delete', 'forceDelete'], true)) {
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
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function delete(User $user, User $model): bool
    {
        // 自己削除禁止
        if ($user->id === $model->id) {
            return false;
        }

        // 最後の管理者削除禁止
        if ($model->hasRole(User::ROLE_ADMIN)) {
            $adminCount = \App\Models\User::query()->where('role', User::ROLE_ADMIN)->count();
            if ($adminCount <= 1) {
                return false;
            }
        }

        // 管理者のみが削除可能
        return $user->hasRole(User::ROLE_ADMIN);
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function forceDelete(User $user, User $model): bool
    {
        // 自己削除禁止
        if ($user->id === $model->id) {
            return false;
        }

        // 最後の管理者削除禁止
        if ($model->hasRole(User::ROLE_ADMIN)) {
            $adminCount = \App\Models\User::query()->where('role', User::ROLE_ADMIN)->count();
            if ($adminCount <= 1) {
                return false;
            }
        }

        // 管理者のみが削除可能
        return $user->hasRole(User::ROLE_ADMIN);
    }
}
