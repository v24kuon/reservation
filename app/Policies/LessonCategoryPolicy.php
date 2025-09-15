<?php

namespace App\Policies;

use App\Models\LessonCategory;
use App\Models\User;

class LessonCategoryPolicy
{
    /**
     * Admin は全権限を許可
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function before(User $user, string $ability): ?bool
    {
        // 破壊的操作は各メソッドで個別判定
        if (in_array($ability, ['delete', 'forceDelete'], true)) {
            return null;
        }
        if ($user->hasRole(User::ROLE_ADMIN)) {
            return true;
        }

        return null; // 他のメソッドへフォールバック
    }

    /**
     * Determine whether the user can update the model.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function update(User $user, LessonCategory $lessonCategory): bool
    {
        // 現状、管理者以外は更新不可
        return false;
    }
}
