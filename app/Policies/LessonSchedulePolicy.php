<?php

namespace App\Policies;

use App\Models\LessonSchedule;
use App\Models\User;

class LessonSchedulePolicy
{
    /**
     * Admin: allow all by default
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole(User::ROLE_ADMIN)) {
            return true;
        }

        return null;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function update(User $user, LessonSchedule $lessonSchedule): bool
    {
        return false;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function delete(User $user, LessonSchedule $lessonSchedule): bool
    {
        return false;
    }
}
