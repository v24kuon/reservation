<?php

namespace App\Policies;

use App\Models\LessonSchedule;
use App\Models\User;

class LessonSchedulePolicy
{
    /**
     * Admin: allow all by default
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole(User::ROLE_ADMIN)) {
            return true;
        }

        return null;
    }

    public function update(User $user, LessonSchedule $lessonSchedule): bool
    {
        return false;
    }

    public function delete(User $user, LessonSchedule $lessonSchedule): bool
    {
        return false;
    }
}
