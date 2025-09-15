<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Admin は全権限を許可
     */
    public function before(User $user): ?bool
    {
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
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function view(User $user, User $model): bool
    {
        return false;
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
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function update(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function delete(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function restore(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
