<?php

namespace App\Policies;

use App\Models\NotificationTemplate;
use App\Models\User;

class NotificationTemplatePolicy
{
    /**
     * Admin は全権限を許可
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole(User::ROLE_ADMIN)) {
            return true;
        }

        return null; // 他のメソッドへフォールバック
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
    public function view(User $user, NotificationTemplate $notificationTemplate): bool
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
    public function update(User $user, NotificationTemplate $notificationTemplate): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function delete(User $user, NotificationTemplate $notificationTemplate): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function restore(User $user, NotificationTemplate $notificationTemplate): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function forceDelete(User $user, NotificationTemplate $notificationTemplate): bool
    {
        return false;
    }
}
